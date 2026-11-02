<?php

namespace DemocracyPoll;

class Poll_Utils {

	/**
	 * Gets the URL to edit a poll in the admin panel.
	 *
	 * @param int $poll_id  Poll ID
	 */
	public static function edit_poll_url( int $poll_id ): string {
		return container()->get( Plugin::class )->admin_page_url . '&edit_poll=' . (int) $poll_id;
	}

	/**
	 * Check whether the current user can edit a specified poll.
	 *
	 * @param Poll|object|int $poll  Poll object or poll id.
	 */
	public static function cuser_can_edit_poll( $poll ): bool {
		$plugin = container()->get( Plugin::class );

		if( $plugin->super_access ){
			return true;
		}

		if( ! $plugin->admin_access ){
			return false;
		}

		if( is_numeric( $poll ) ){
			$poll = Poll_Storage::get_db_data( $poll );
		}

		return $poll && (int) $poll->added_user === (int) get_current_user_id();
	}

	/**
	 * @internal
	 */
	public static function get_minified_styles(): string {
		$demcss = get_option( 'democracy_css' );
		$minified = $demcss['minify'] ?? '';

		return $minified
			? "\n" . '<style id="democracy-poll-css">' . $minified . '</style>' . "\n"
			: '';
	}

	/**
	 * @internal
	 */
	public static function enqueue_js(): void {
		$plugin  = container()->get( Plugin::class );
		$options = container()->get( Options::class );

		$handle = 'democracy';
		wp_enqueue_script( $handle, "$plugin->url/assets/js/democracy.min.js", [], $plugin->ver, [
			'in_footer' => true,
			'strategy'  => 'defer',
		] );

		$config = [
			'ajax_url'          => container()->get( Poll_Ajax::class )->ajax_url,
			'cookie_days'       => (float) $options->cookie_days,
			'anim_speed'        => (int) $options->anim_speed,
			'line_anim_speed'   => (int) $options->line_anim_speed,
			'is_user_logged_in' => is_user_logged_in(),
		];

		$json = wp_json_encode( $config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		wp_add_inline_script( $handle, "window.democracyPollConfig = $json;", 'before' );
	}

	/**
	 * @internal
	 * @return int[]
	 */
	public static function parse_voted_str( string $answer_ids ): array {
		return array_values( array_filter(
			array_map( 'intval', explode( ',', $answer_ids ) )
		) );
	}

}
