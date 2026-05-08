<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Pure-PHP MPEG-1 Layer 3 stream rebuilder.
 *
 * Takes a byte stream produced by concatenating multiple ElevenLabs TTS chunks
 * (each is a self-contained MP3 with its own ID3v2 tag and Xing/Info frame)
 * and produces a clean stream with a single, accurate Xing/Info frame at the
 * head — including a correct TOC for byte-accurate seeking and a frame count
 * that yields the true duration.
 *
 * Replaces the previous ffmpeg-based remux, which is too heavy for the host.
 *
 * Scope is intentionally narrow: ElevenLabs only emits MPEG-1 Layer 3 at
 * 44.1 kHz with the requested target bitrate. Anything else (free-format
 * frames, MPEG-2/2.5) is rejected with a clear error so future format changes
 * surface loudly rather than silently corrupting output.
 */
class OETL_MP3_Builder {

    /** Samples per MPEG-1 Layer 3 frame. */
    const SAMPLES_PER_FRAME = 1152;

    /** Side info size in a MPEG-1 stereo frame (joint stereo = stereo here). */
    const SIDE_INFO_STEREO = 32;

    /** Side info size in a MPEG-1 mono frame. */
    const SIDE_INFO_MONO = 17;

    /** Bitrate table (kbps) for MPEG-1 Layer 3, indexed by the 4-bit field. */
    const BITRATES_V1_L3 = array(
        0, 32, 40, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320, -1,
    );

    /** Sample-rate table (Hz) for MPEG-1, indexed by the 2-bit field. */
    const SAMPLE_RATES_V1 = array( 44100, 48000, 32000, -1 );

    /**
     * Rebuild a (possibly multi-chunk) MP3 byte stream.
     *
     * @param string $mp3_data Raw bytes — the result of imploding ElevenLabs
     *                         chunks, or the contents of an existing attachment.
     * @return array{data: string, duration: float, frames: int}
     *
     * @throws RuntimeException When the input contains no recognizable MPEG-1
     *                          Layer 3 frames or uses unsupported encodings.
     */
    public function rebuild( $mp3_data ) {
        $scan = $this->scan_audio_frames( $mp3_data );

        if ( $scan['frames'] === 0 ) {
            throw new RuntimeException( 'no MPEG-1 Layer 3 frames found in input' );
        }

        $audio_bytes = $scan['audio'];
        $audio_size  = strlen( $audio_bytes );

        $xing_frame = $this->build_xing_frame(
            $scan['frames'],
            $audio_size,
            $this->build_toc( $scan['positions'], $audio_size ),
            $scan['first_frame_header']
        );

        $duration = ( $scan['frames'] * self::SAMPLES_PER_FRAME ) / $scan['sample_rate'];

        return array(
            'data'     => $xing_frame . $audio_bytes,
            'duration' => $duration,
            'frames'   => $scan['frames'],
        );
    }

    /**
     * If an ID3v2 tag starts at $offset, return the total tag size (header +
     * body), otherwise null. The header is fixed at 10 bytes; bytes 6-9 hold
     * the body size as a syncsafe 28-bit integer.
     */
    private function parse_id3v2_size( $data, $offset ) {
        if ( $offset + 10 > strlen( $data ) ) {
            return null;
        }
        if ( substr( $data, $offset, 3 ) !== 'ID3' ) {
            return null;
        }
        $b6 = ord( $data[ $offset + 6 ] );
        $b7 = ord( $data[ $offset + 7 ] );
        $b8 = ord( $data[ $offset + 8 ] );
        $b9 = ord( $data[ $offset + 9 ] );
        // Syncsafe int: top bit of each byte is reserved (must be 0).
        if ( ( ( $b6 | $b7 | $b8 | $b9 ) & 0x80 ) !== 0 ) {
            return null;
        }
        $size = ( $b6 << 21 ) | ( $b7 << 14 ) | ( $b8 << 7 ) | $b9;
        return 10 + $size;
    }

    /**
     * Try to parse a 4-byte MPEG audio frame header at $offset. Returns null
     * if the bytes don't form a valid MPEG-1 Layer 3 frame header (sync word,
     * version, layer, bitrate, sample rate must all be sane).
     *
     * On success returns an associative array with frame_size, sample_rate,
     * bitrate, side_info_size, and is_xing (true if the frame's payload starts
     * with "Xing" or "Info" magic — i.e. it carries metadata, not audio).
     */
    private function parse_frame_header( $data, $offset ) {
        if ( $offset + 4 > strlen( $data ) ) {
            return null;
        }

        $b0 = ord( $data[ $offset ] );
        $b1 = ord( $data[ $offset + 1 ] );
        $b2 = ord( $data[ $offset + 2 ] );
        $b3 = ord( $data[ $offset + 3 ] );

        // Sync: 11 bits of 1. Byte 0 = 0xFF, top 3 bits of byte 1 = 111.
        if ( $b0 !== 0xFF || ( $b1 & 0xE0 ) !== 0xE0 ) {
            return null;
        }

        // Version: bits 19-20 (counting from MSB of byte 0). Need MPEG-1 = 11.
        $version = ( $b1 >> 3 ) & 0x03;
        if ( $version !== 0x03 ) {
            return null;
        }

        // Layer: bits 17-18. Need Layer III = 01.
        $layer = ( $b1 >> 1 ) & 0x03;
        if ( $layer !== 0x01 ) {
            return null;
        }

        $bitrate_idx     = ( $b2 >> 4 ) & 0x0F;
        $sample_rate_idx = ( $b2 >> 2 ) & 0x03;
        $padding         = ( $b2 >> 1 ) & 0x01;
        $channel_mode    = ( $b3 >> 6 ) & 0x03;

        $bitrate_kbps = self::BITRATES_V1_L3[ $bitrate_idx ];
        $sample_rate  = self::SAMPLE_RATES_V1[ $sample_rate_idx ];

        // Reject bad/free-format/reserved values; ElevenLabs never emits them.
        if ( $bitrate_kbps <= 0 || $sample_rate <= 0 ) {
            return null;
        }

        $bitrate = $bitrate_kbps * 1000;
        $frame_size = (int) floor( 144 * $bitrate / $sample_rate ) + $padding;

        $side_info_size = ( $channel_mode === 0x03 )
            ? self::SIDE_INFO_MONO
            : self::SIDE_INFO_STEREO;

        // Detect Xing/Info metadata frame: magic at offset 4 + side_info_size,
        // followed by a 32-bit flags field. Real Xing/Info flags fit in the
        // bottom 4 bits (values 1..15); requiring the upper 28 bits to be zero
        // rules out coincidental "Xing"/"Info" ASCII matches inside real audio
        // data — without this guard, mono frames (where the magic position is
        // only 21 bytes from the frame start, deep in the audio bitstream)
        // can drop ~2-3 real frames per file as if they were metadata.
        $is_xing      = false;
        $magic_offset = $offset + 4 + $side_info_size;
        if ( $magic_offset + 8 <= strlen( $data ) ) {
            $magic = substr( $data, $magic_offset, 4 );
            if ( $magic === 'Xing' || $magic === 'Info' ) {
                $f0 = ord( $data[ $magic_offset + 4 ] );
                $f1 = ord( $data[ $magic_offset + 5 ] );
                $f2 = ord( $data[ $magic_offset + 6 ] );
                $f3 = ord( $data[ $magic_offset + 7 ] );
                if ( $f0 === 0 && $f1 === 0 && $f2 === 0
                    && ( $f3 & 0xF0 ) === 0 && $f3 !== 0 ) {
                    $is_xing = true;
                }
            }
        }

        return array(
            'frame_size'     => $frame_size,
            'sample_rate'    => $sample_rate,
            'bitrate'        => $bitrate,
            'channel_mode'   => $channel_mode,
            'side_info_size' => $side_info_size,
            'is_xing'        => $is_xing,
        );
    }

    /**
     * Walk the entire stream once. Skip ID3v2 tags wherever they appear. Skip
     * Xing/Info "metadata" frames. Collect real audio frame bytes into a fresh
     * buffer and record cumulative byte positions of each frame for the TOC.
     *
     * Robustness notes:
     * - Any frame whose declared size overflows the buffer is treated as a
     *   ghost match (4 bytes that happened to look like a valid header); we
     *   just slide forward by 1 and resync rather than break out of the loop,
     *   so a single false positive can't truncate the tail of the file.
     * - Once we've seen a real frame we anchor `sample_rate` and `channel_mode`
     *   from it and reject any later frame whose params disagree. ElevenLabs
     *   only emits 44.1 kHz / joint stereo MPEG-1 L3, so anything else along
     *   the way is by definition a false match.
     */
    private function scan_audio_frames( $data ) {
        $len    = strlen( $data );
        $pos    = 0;
        $audio  = '';
        $positions = array();
        $first_header  = null;
        $anchor_rate   = 0;
        $anchor_chan   = -1;

        while ( $pos < $len ) {
            // ID3v2 tag boundary — only valid if it really is a tag here.
            $id3_size = $this->parse_id3v2_size( $data, $pos );
            if ( $id3_size !== null ) {
                $pos += $id3_size;
                continue;
            }

            $hdr = $this->parse_frame_header( $data, $pos );
            if ( $hdr === null ) {
                // Garbage byte — slide forward and resync.
                $pos++;
                continue;
            }

            // Declared size overflows EOF → ghost match, not a real frame.
            // Resync byte-by-byte instead of bailing out of the whole scan.
            if ( $pos + $hdr['frame_size'] > $len ) {
                $pos++;
                continue;
            }

            // Once anchored, reject frames with mismatched format params.
            if ( $anchor_rate !== 0
                && ( $hdr['sample_rate']  !== $anchor_rate
                  || $hdr['channel_mode'] !== $anchor_chan ) ) {
                $pos++;
                continue;
            }

            if ( $hdr['is_xing'] ) {
                // Anchor on the first Xing frame's params if we haven't yet.
                if ( $anchor_rate === 0 ) {
                    $anchor_rate = $hdr['sample_rate'];
                    $anchor_chan = $hdr['channel_mode'];
                }
                // Drop the metadata frame; we'll write our own at the end.
                $pos += $hdr['frame_size'];
                continue;
            }

            if ( $first_header === null ) {
                $first_header = substr( $data, $pos, 4 );
                if ( $anchor_rate === 0 ) {
                    $anchor_rate = $hdr['sample_rate'];
                    $anchor_chan = $hdr['channel_mode'];
                }
            }

            $positions[] = strlen( $audio );
            $audio      .= substr( $data, $pos, $hdr['frame_size'] );
            $pos        += $hdr['frame_size'];
        }

        return array(
            'audio'              => $audio,
            'frames'             => count( $positions ),
            'positions'          => $positions,
            'sample_rate'        => $anchor_rate,
            'first_frame_header' => (string) $first_header,
        );
    }

    /**
     * Build the 100-byte Xing TOC. Entry i is a uint8 representing the byte
     * offset (as a fraction of total audio bytes, scaled into 0..255) of the
     * frame that starts at i% of the duration.
     */
    private function build_toc( array $positions, $total_bytes ) {
        $toc        = '';
        $frame_count = count( $positions );

        if ( $frame_count === 0 || $total_bytes <= 0 ) {
            return str_repeat( "\x00", 100 );
        }

        for ( $i = 0; $i < 100; $i++ ) {
            $target_idx = (int) floor( $i * $frame_count / 100 );
            if ( $target_idx >= $frame_count ) {
                $target_idx = $frame_count - 1;
            }
            $byte_pos = $positions[ $target_idx ];
            $value    = (int) floor( 256 * $byte_pos / $total_bytes );
            if ( $value > 255 ) {
                $value = 255;
            }
            $toc .= chr( $value );
        }

        return $toc;
    }

    /**
     * Build the synthetic Xing-bearing MPEG frame that goes at the start of
     * the rebuilt file. Reuses the first audio frame's 4-byte header so the
     * synthetic frame has the same bitrate/sample-rate/channel mode as the
     * stream and decoders accept it as a normal frame.
     *
     * Frame layout: header (4) + zeroed side info (32) + "Xing" (4) +
     * flags (4) + frames (4) + bytes (4) + TOC (100) + zero pad to frame_size.
     *
     * The frame_count and total_bytes fields describe the *audio* portion only
     * (excluding this metadata frame), per LAME convention.
     */
    private function build_xing_frame( $frame_count, $total_bytes, $toc, $template_header ) {
        if ( strlen( $template_header ) !== 4 ) {
            throw new RuntimeException( 'invalid template frame header' );
        }

        $hdr = $this->parse_frame_header( $template_header, 0 );
        if ( $hdr === null ) {
            throw new RuntimeException( 'template frame header failed re-parse' );
        }

        $frame_size     = $hdr['frame_size'];
        $side_info_size = $hdr['side_info_size'];

        $payload  = $template_header;
        $payload .= str_repeat( "\x00", $side_info_size );
        $payload .= 'Xing';
        $payload .= pack( 'N', 0x07 );           // flags: frames + bytes + TOC
        $payload .= pack( 'N', $frame_count );
        $payload .= pack( 'N', $total_bytes );
        $payload .= $toc;

        if ( strlen( $payload ) > $frame_size ) {
            // Shouldn't happen for any standard MPEG-1 L3 frame size, but guard
            // anyway so we never emit a frame that overflows its declared size.
            throw new RuntimeException( sprintf(
                'Xing payload (%d bytes) exceeds frame size (%d bytes)',
                strlen( $payload ),
                $frame_size
            ) );
        }

        return $payload . str_repeat( "\x00", $frame_size - strlen( $payload ) );
    }
}
