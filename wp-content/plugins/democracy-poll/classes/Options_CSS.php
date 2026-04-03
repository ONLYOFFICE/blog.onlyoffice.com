<?php

namespace DemocracyPoll;

class Options_CSS {

	public function __construct(){
	}

	/**
	 * Regenerates styles in the settings, based on the settings.
	 * does not touch additional styles.
	 *
	 * @param $additional
	 *
	 * @return void
	 */
	public function regenerate_democracy_css( $additional = null ) {

		// so that when the plugin is updated, the additional styles will not be removed.
		if( $additional === null ){
			$css = get_option( 'democracy_css', [] );
			$additional = $css['additional_css'] ?? '';
		}

		// If empty, the theme is off
		$base = $this->collect_base_css();

		$newdata = [
			'base_css'       => $base,
			'additional_css' => $additional,
			'minify'         => $this->cssmin( $base . $additional ),
		];

		update_option( 'democracy_css', $newdata );
	}

	/**
	 * Collects basic styles.
	 *
	 * @return string css styles code or empty string if the template is disabled.
	 */
	private function collect_base_css(): string {

		$tpl = options()->css_file_name;

		// выходим если не указан шаблон
		if( ! $tpl ){
			return '';
		}

		$button = options()->css_button;
		$loader = options()->loader_fill;

		$radios = options()->checkradio_fname;

		$out = '';
		$styledir = plugin()->dir . '/styles';

		$out .= $this->parse_css_import( "$styledir/$tpl" );
		$out .= $radios ? "\n" . file_get_contents( "$styledir/checkbox-radio/$radios" ) : '';
		$out .= $button ? "\n" . file_get_contents( "$styledir/buttons/$button" ) : '';

		if( $loader ){
			$out .= "\n.dem-loader .fill{ fill: $loader !important; }\n";
			$out .= ".dem-loader .css-fill{ background-color: $loader !important; }\n";
			$out .= ".dem-loader .stroke{ stroke: $loader !important; }\n";
		}

		// progress line
		$d_bg       = options()->line_bg;
		$d_fill     = options()->line_fill;
		$d_height   = options()->line_height;
		$d_fillThis = options()->line_fill_voted;

		if( $d_bg ){
			$out .= "\n.dem-graph{ background: $d_bg !important; }\n";
		}
		if( $d_fill ){
			$out .= "\n.dem-fill{ background-color: $d_fill !important; }\n";
		}
		if( $d_fillThis ){
			$out .= ".dem-voted-this .dem-fill{ background-color:$d_fillThis !important; }\n";
		}
		if( $d_height ){
			$out .= ".dem-graph{ height:{$d_height}px; line-height:{$d_height}px; }\n";
		}

		if( $button ){
			// button
			$bbackground = options()->btn_bg_color;
			$bcolor      = options()->btn_color;
			$bbcolor     = options()->btn_border_color;
			// hover
			$bh_bg     = options()->btn_hov_bg;
			$bh_color  = options()->btn_hov_color;
			$bh_bcolor = options()->btn_hov_border_color;

			if( $bbackground ){
				$out .= "\n.dem-button{ background-color:$bbackground !important; }\n";
			}
			if( $bcolor ){
				$out .= ".dem-button{ color:$bcolor !important; }\n";
			}
			if( $bbcolor ){
				$out .= ".dem-button{ border-color:$bbcolor !important; }\n";
			}

			if( $bh_bg ){
				$out .= "\n.dem-button:hover{ background-color:$bh_bg !important; }\n";
			}
			if( $bh_color ){
				$out .= ".dem-button:hover{ color:$bh_color !important; }\n";
			}
			if( $bh_bcolor ){
				$out .= ".dem-button:hover{ border-color:$bh_bcolor !important; }\n";
			}
		}

		return $out;
	}

	/**
	 * Compresses css using YUICompressor
	 */
	public function cssmin( string $input_css ): string {
		require_once plugin()->dir . '/admin/CssMin/cssmin.php';

		$compressor = new \tubalmartin\CssMin\Minifier();
		// $compressor->set_memory_limit('256M');
		// $compressor->set_max_execution_time(120);

		return $compressor->run( $input_css );
	}

	/**
	 * Imports @import in css.
	 */
	private function parse_css_import( $css_filepath ) {
		$filecode = file_get_contents( $css_filepath );

		$filecode = preg_replace_callback( '~@import [\'"](.*?)[\'"];~', static function( $m ) use ( $css_filepath ) {
			return file_get_contents( dirname( $css_filepath ) . '/' . $m[1] );
		}, $filecode );

		return $filecode;
	}

}
