<?php

namespace DemocracyPoll\Admin;

class Post_Metabox {

	public const POLL_ID_MKEY = 'dem_poll_id';

	public static function init(): void {
		add_action( 'add_meta_boxes', [ __CLASS__, 'add_meta_box' ] );
		add_action( 'save_post', [ __CLASS__, 'on_save_post' ], 10, 2 );
	}

	public static function get_post_poll_id( int $post_id ): int {
		if( ! $post_id ){
			return 0;
		}

		return (int) get_post_meta( $post_id, self::POLL_ID_MKEY, true );
	}

	public static function add_meta_box(): void {

		$post_types = get_post_types( [ 'publicly_queryable' => true ] ) + [ 'page' => 'page' ];
		unset( $post_types['attachment'] );

		add_meta_box( 'democracy-metabox',
			__( 'Attach a poll to the post', 'democracy-poll' ),
			[ __CLASS__, 'meta_box' ],
			$post_types, 'side'
		);
	}

	public static function meta_box( $post ): void {
		global $wpdb;

		$poll_id = get_post_meta( $post->ID, self::POLL_ID_MKEY, true );

		$polls = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $wpdb->democracy_q WHERE ( open = 1 OR id = %d ) ORDER BY id DESC", $poll_id
		) );

		$options = [ '<option value="0">- ' . __( 'random active poll', 'democracy-poll' ) . ' -</option>' ];
		foreach( $polls as $poll ){
			$options[] = sprintf( '<option value="%d" %s>%s %s</option>',
				(int) $poll->id,
				selected( $poll->id, $poll_id, 0 ),
				esc_html( $poll->question ),
				( $poll->active ? ' [active]' : '' ) . ( $poll->open ? '' : ' [closed]' )
			);
		}

		?>
		<select name="democ_metabox[<?= esc_attr( self::POLL_ID_MKEY ) ?>]">
			<?= implode( '', $options ) ?>
		</select>
		<p>
			<?= sprintf( __( '%s - shortcode', 'democracy-poll' ), '<code>[democracy id="current"]</code>' ) ?>
		</p>
		<?php
	}

	public static function on_save_post( $post_id, $post ): void {
		if(
			! isset( $_POST['democ_metabox'] ) || // нет данных
			( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || // выходим, если это автосохр.
			! wp_verify_nonce( $_POST['_wpnonce'], 'update-post_' . $post_id ) || // nonce проверка
			! current_user_can( get_post_type_object( $post->post_type )->cap->edit_post, $post_id )      // нет права редакт. запись
		){
			return;
		}

		$pollid = (int) $_POST['democ_metabox'][ self::POLL_ID_MKEY ];

		if( $pollid ){
			update_post_meta( $post_id, self::POLL_ID_MKEY, $pollid );
		}
		else{
			delete_post_meta( $post_id, self::POLL_ID_MKEY );
		}
	}

}
