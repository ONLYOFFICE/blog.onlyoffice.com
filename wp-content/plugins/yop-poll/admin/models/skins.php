<?php
class YOP_Poll_Skins {
	public static function get_skins() {
		$skins = $GLOBALS['wpdb']->get_results( "SELECT * FROM {$GLOBALS['wpdb']->yop_poll_skins}", OBJECT );
		return $skins;
	}
	public static function get_skin( $skin_id ) {
		$sql_query = $GLOBALS['wpdb']->prepare( "SELECT * FROM {$GLOBALS['wpdb']->yop_poll_skins} WHERE `id`=%s", $skin_id );
		return $GLOBALS['wpdb']->get_row( $sql_query, OBJECT );
	}
	public static function skin_already_exists( $template_base, $skin_base, $available_skins ) {
		$skin_exists = false;
		foreach ( $available_skins as $available_skin ) {
			if ( ( $available_skin->template_base === $template_base ) && ( $available_skin->base === $skin_base )  ) {
				$skin_exists = true;
			}
		}
		return $skin_exists;
	}
}
