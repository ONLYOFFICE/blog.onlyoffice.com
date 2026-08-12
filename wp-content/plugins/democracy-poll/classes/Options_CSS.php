<?php

namespace DemocracyPoll;

class Options_CSS {

	private Plugin $plugin;
	private Options $options;

	public function __construct( Plugin $plugin, Options $options ){
		$this->plugin = $plugin;
		$this->options = $options;
	}

	/**
	 * Regenerates styles in the settings, based on the settings.
	 * does not touch additional styles.
	 *
	 * @param string|null $additional
	 */
	public function regenerate_democracy_css( $additional = null ): void {

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
		$opt = $this->options;
		$tpl = $opt->css_file_name;

		// Stop when no template is specified.
		if( ! $tpl ){
			return '';
		}

		$button      = $opt->css_button;
		$loader_fill = $opt->loader_fill;

		$radios = $opt->checkradio_fname;

		$out = '';
		$styledir = $this->plugin->dir . '/assets/styles';

		$out .= $this->parse_css_import( "$styledir/$tpl" );
		$out .= $radios ? "\n" . file_get_contents( "$styledir/checkbox-radio/$radios" ) : '';
		$out .= $button ? "\n" . file_get_contents( "$styledir/buttons/$button" ) : '';

		if( $loader_fill ){
			$out .= "\n.democracy{ --dem-loader-color: $loader_fill; }\n";
		}

		// progress line
		$d_bg         = $opt->line_bg;
		$d_fill       = $opt->line_fill;
		$d_height     = is_numeric( $opt->line_height ) ? "{$opt->line_height}px" : $opt->line_height;
		$d_fill_voted = $opt->line_fill_voted;

		$css_vars = array_filter( [
			$d_bg         ? "--dem-graph-bg: $d_bg"                  : '',
			$d_fill       ? "--dem-fill-color: $d_fill"              : '',
			$d_height     ? "--dem-graph-height: $d_height"          : '',
			$d_fill_voted ? "--dem-fill-voted-color: $d_fill_voted"  : '',
		] );

		if( $button ){
			// button
			$bbg     = $opt->btn_bg_color;
			$bcolor  = $opt->btn_color;
			$bbcolor = $opt->btn_border_color;
			// button hover
			$bh_bg     = $opt->btn_hov_bg;
			$bh_color  = $opt->btn_hov_color;
			$bh_bcolor = $opt->btn_hov_border_color;

			$css_vars = array_filter( [
				...$css_vars,
				$bbg       ? "--dem-button-bg: $bbg"                       : '',
				$bcolor    ? "--dem-button-color: $bcolor"                 : '',
				$bbcolor   ? "--dem-button-border-color: $bbcolor"         : '',
				$bh_bg     ? "--dem-button-hover-bg: $bh_bg"               : '',
				$bh_color  ? "--dem-button-hover-color: $bh_color"         : '',
				$bh_bcolor ? "--dem-button-hover-border-color: $bh_bcolor" : '',
			] );
		}

		if( $css_vars ){
			$out .= "\n.democracy{ " . implode( "; ", $css_vars ) . "; }\n";
		}

		return $out;
	}

	/**
	 * Compresses css using YUICompressor
	 */
	public function cssmin( string $input_css ): string {
		$compressor = new Libs\CssMin\Minifier();
		// $compressor->set_memory_limit('256M');
		// $compressor->set_max_execution_time(120);

		return $compressor->run( $input_css );
	}

	/**
	 * Imports @import in css.
	 */
	private function parse_css_import( $css_filepath ) {
		$filecode = file_get_contents( $css_filepath );
		$maindir  = dirname( $css_filepath );

		$filecode = preg_replace_callback( '~@import [\'"](.*?)[\'"];~',
			static fn( $m ) => file_get_contents( "$maindir/$m[1]" ),
			$filecode
		);

		return $filecode;
	}

}
