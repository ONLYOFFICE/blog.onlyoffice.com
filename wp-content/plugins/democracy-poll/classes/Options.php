<?php

namespace DemocracyPoll;

/**
 * Main:
 * @property-read int    $inline_js_css          Eg: 1
 * @property-read int    $keep_logs              Eg: 1
 * @property-read string $before_title           Eg: '<strong class="dem-poll-title">'
 * @property-read string $after_title            Eg: '</strong>'
 * @property-read int    $force_cachegear        Eg: 0
 * @property-read int    $archive_page_id        Eg: 0
 * @property-read string $order_answers          Eg: 'by_winner'
 * @property-read int    $use_widget             Eg: 1
 * @property-read int    $hide_vote_button       Eg: 0
 * @property-read int    $toolbar_menu           Eg: 1
 * @property-read int    $tinymce_button         Eg: 1
 * @property-read int    $show_copyright         Eg: 1
 * @property-read int    $only_for_users         Eg: 0
 * @property-read int    $dont_show_results      Eg: 0
 * @property-read int    $dont_show_results_link Eg: 0
 * @property-read int    $democracy_off          Eg: 0
 * @property-read int    $revote_off             Eg: 0
 * @property-read int    $cookie_days            Eg: 365
 * @property-read array  $access_roles           Eg: []
 * @property-read int    $soft_ip_detect         Eg: 0
 * @property-read int    $post_metabox_off       Eg: 0
 * @property-read int    $disable_js             Eg: 0
 *
 * Design:
 * @property-read string $loader_fname         Eg: 'css-roller.css3'
 * @property-read string $css_file_name        Eg: 'alternate.css'
 * @property-read string $css_button           Eg: 'flat.css'
 * @property-read string $loader_fill          Eg: ''
 * @property-read int    $graph_from_total     Eg: 1
 * @property-read int    $answs_max_height     Eg: 500
 * @property-read int    $anim_speed           Eg: 400
 * @property-read string $checkradio_fname     Eg: ''
 * @property-read string $line_bg              Eg: ''
 * @property-read string $line_fill            Eg: ''
 * @property-read string $line_height          Eg: ''
 * @property-read string $line_fill_voted      Eg: ''
 * @property-read int    $line_anim_speed      Eg: 1500
 * @property-read string $btn_bg_color         Eg: ''
 * @property-read string $btn_color            Eg: ''
 * @property-read string $btn_border_color     Eg: ''
 * @property-read string $btn_hov_bg           Eg: ''
 * @property-read string $btn_hov_color        Eg: ''
 * @property-read string $btn_hov_border_color Eg: ''
 * @property-read string $btn_class            Eg: ''
 */
class Options {

	const OPT_NAME = 'democracy_options';

	private $opt = [];

	private $default_options = [
		'main'   => [
			// встараивать стили и скрипты в HTML
			'inline_js_css'          => 1,
			// вести лог в БД
			'keep_logs'              => 1,
			'before_title'           => '<strong class="dem-poll-title">',
			'after_title'            => '</strong>',
			'force_cachegear'        => 0,
			'archive_page_id'        => 0,
			'order_answers'          => 'by_winner',
			'use_widget'             => 1,
			// прятать кнопку голосования где это можно, тогда голосование будет происходить по клику на ответ
			'hide_vote_button'       => 0,
			'toolbar_menu'           => 1,
			'tinymce_button'         => 1,
			'show_copyright'         => 1,
			'only_for_users'         => 0,
			// Не показывать результаты опроса. До закрытия голосования. Глобальная опция.
			'dont_show_results'      => 0,
			// Не показывать только ссылку на результаты. Результаты будут видны после голосования. Глобальная опция.
			'dont_show_results_link' => 0,
			'democracy_off'          => 0,
			// глобальная опция democracy
			'revote_off'             => 0,
			// глобальная опция переголосование
			'cookie_days'            => 365,
			'access_roles'           => [],
			'soft_ip_detect'         => 0,
			// определять IP не только через REMOTE_ADDR
			'post_metabox_off'       => 0,
			// выключить ли метабокс для записей?
			'disable_js'             => 0,
			// Дебаг: отключает JS
		],
		'design' => [
			'loader_fname'         => 'css-roller.css3',
			'css_file_name'        => 'alternate.css',
			// название файла стилей который будет использоваться для опроса.
			'css_button'           => 'flat.css',
			'loader_fill'          => '',
			// как заполнять шкалу прогресса
			'graph_from_total'     => 1,
			'answs_max_height'     => 500,
			// px
			'anim_speed'           => 400,
			// msec
			// radio checkbox
			'checkradio_fname'     => '',
			// progress
			'line_bg'              => '',
			'line_fill'            => '',
			'line_height'          => '',
			'line_fill_voted'      => '',
			'line_anim_speed'      => 1500,
			// button
			'btn_bg_color'         => '',
			'btn_color'            => '',
			'btn_border_color'     => '',
			'btn_hov_bg'           => '',
			'btn_hov_color'        => '',
			'btn_hov_border_color' => '',
			'btn_class'            => '',
		],
	];

	public function __construct() {
	}

	public function __get( $name ){
		return $this->opt[ $name ] ?? null;
	}

	public function __isset( $name ){
		return (bool) $this->__get( $name );
	}

	public function __set( $name, $val ){
		// prohibit to set any additional options
	}

	public function get_default_options(): array {
		return $this->default_options;
	}

	/**
	 * Sets $this->opt. Update options in DB if it's not set yet.
	 */
	public function set_opt(): void {
		if( ! $this->opt ){
			$this->opt = get_option( self::OPT_NAME, [] );

			if( ! $this->opt ){
				$this->reset_options( 'all' );
			}
		}

		// append default values
		foreach( $this->default_options as $part => $options ){
			foreach( $options as $key => $val ){
				if( ! isset( $this->opt[ $key ] ) ){
					$this->opt[ $key ] = $val;
				}
			}
		}
	}

	// TODO: refactor and join with update_options()
	public function update_single_option( $option_name, $value ): bool {

		if( $this->is_option_exists( $option_name ) ){
			$newopt = $this->opt;
			$newopt[ $option_name ] = $value;

			return (bool) update_option( self::OPT_NAME, $newopt );
		}

		return false;
	}

	/**
	 * @param string $type  What group of option to update: main, design.
	 */
	public function update_options( string $type ): bool {

		// sanitize on POST request
		$POSTDATA = wp_unslash( $_POST ); // TODO: move it out of here
		if( isset( $POSTDATA['dem'] ) && ( $type === 'main' || $type === 'design' ) ){
			$this->sanitize_request_options( $POSTDATA, $type );
		}

		// update css styles option
		if( $type === 'design' ){
			## Обновляет опцию "democracy_css"
			$additional_css = $_POST['additional_css'] ?? '';
			$additional = strip_tags( stripslashes( $additional_css ) );

			( new \DemocracyPoll\Options_CSS() )->regenerate_democracy_css( $additional );
		}

		return (bool) update_option( self::OPT_NAME, $this->opt );
	}

	public function reset_options( $type ): bool {

		if( $type === 'all' ){
			foreach( $this->default_options[ 'main' ] as $key => $value ){
				$this->opt[ $key ] = $value;
			}
			foreach( $this->default_options[ 'design' ] as $key => $value ){
				$this->opt[ $key ] = $value;
			}
		}
		elseif( in_array( $type, [ 'main', 'design' ], true ) ){
			foreach( $this->default_options[ $type ] as $key => $value ){
				$this->opt[ $key ] = $value;
			}

			if( $type === 'design' ){
				( new \DemocracyPoll\Options_CSS() )->regenerate_democracy_css( '' );
			}
		}

		return (bool) update_option( self::OPT_NAME, $this->opt );
	}

	/**
	 * Updates {@see self::$opt} based on request data.
	 * If the option is not passed, 0 will be written in its place.
	 */
	private function sanitize_request_options( array $request_data, string $type ): void {

		foreach( $this->default_options[ $type ] as $key => $v ){

			$value = $request_data['dem'][ $key ] ?? 0; // именно 0/null, а не $v для checkbox

			if( in_array( $key, [ 'before_title', 'after_title' ] ) ){
				$value = wp_kses( $value, 'post' );
			}
			elseif( $key === 'access_roles' ){
				// sanitize anyway
				if( plugin()->super_access ){
					$value = array_map( 'sanitize_key', (array) $value );
				}
				// leave as it is - only admin can change 'access_roles'
				else{
					$value = (array) $this->opt[ $key ];
				}
			}
			else{
				$value = is_array( $value )
					? array_map( 'sanitize_text_field', $value )
					: sanitize_text_field( $value );
			}

			$this->opt[ $key ] = $value;
		}
	}

	private function is_option_exists( string $option_name ): bool {

		foreach( $this->default_options as $part => $options ){
			if( array_key_exists( $option_name, $options ) ){
				return true;
			}
		}

		return false;
	}

}
