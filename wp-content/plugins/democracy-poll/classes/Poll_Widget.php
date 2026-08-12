<?php

namespace DemocracyPoll;

use DemocracyPoll\Admin\Post_Metabox;
use DemocracyPoll\Support\Kses;
use WP_Widget;

class Poll_Widget extends WP_Widget {

	public function __construct() {
		// Instantiate the parent object. Creates option 'Poll_Widget'
		parent::__construct( 'democracy', 'Democracy Poll', [
			'description' => __( 'Democracy Poll Widget', 'democracy-poll' )
		] );
	}

	// front
	public function widget( $args, $instance ) {
		global $post;

		$before_widget = $args['before_widget'];
		$after_widget  = $args['after_widget'];
		$before_title  = $args['before_title'];
		$after_title   = $args['after_title'];

		$title = $instance['title'] ?? '';
		$poll_id = $instance['show_poll'] ?? 0;

		if( $post && is_singular()
		    && ! container()->get( Options::class )->post_metabox_off
		    && ( $post_pid = Post_Metabox::get_post_poll_id( $post->ID ) )
		){
			$poll_id = $post_pid; // $poll_id may be: int, 'last', 'rand'
		}

		$poll_object = Poll_Storage::get_db_data( $poll_id ?: 'rand' ); // $poll_id may be: int, 'last', 'rand'

		if( isset( $instance['questionIsTitle'] ) ){
			echo $before_widget;
			echo get_democracy_poll( [
				'poll'         => $poll_object,
				'title_markup' => $before_title ? "$before_title{question}$after_title" : '',
			] );
			echo $after_widget;
		}
		else{
			echo $before_widget . $before_title . $title . $after_title;
			echo get_democracy_poll( [ 'poll' => $poll_object ] );
			echo $after_widget;
		}
	}

	// options
	public function update( $new_instance, $old_instance ): array {
		foreach( $new_instance as & $val ){
			$val = strip_tags( $val );
		}

		return $new_instance;
	}

	// admin
	public function form( $instance ) {
		add_action( 'admin_footer', [ $this, 'dem_widget_footer_js' ], 11 );

		$checked = isset( $instance['questionIsTitle'] ) ? ' checked="checked"' : '';
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : __( 'Poll', 'democracy-poll' );
		$show_poll = $instance['show_poll'] ?? 0;

		$title_style = $checked ? 'style="display:none;"' : '';
		?>
		<p>
			<label>
				<input type="checkbox" <?= $checked ?>
				       name="<?= $this->get_field_name( 'questionIsTitle' ) ?>"
				       value="1"
				       class="questionIsTitle" onchange="demHideTitle(this);">
				<small><?= esc_html__( 'Poll question = widget title?', 'democracy-poll' ) ?></small>
			</label>
		</p>

		<p class="demTitleWrap" <?= $title_style ?>>
			<label><?= esc_html__( 'Poll title:', 'democracy-poll' ) ?>
				<input style="width:100%;" type="text" id="demTitle"
				       name="<?= $this->get_field_name( 'title' ) ?>" value="<?= esc_attr( $title ) ?>">
			</label>
		</p>
		<?php
		global $wpdb;

		$options = '
		<option value="0">' . __( '- Active (random all active)', 'democracy-poll' ) . '</option>
		<option value="last" ' . selected( $show_poll, 'last', 0 ) . '>' . __( '- Last open poll', 'democracy-poll' ) . '</option>
		<option disabled></option>
		';

		$qu = $wpdb->get_results( "SELECT * FROM $wpdb->democracy_q ORDER BY added DESC LIMIT 70" );
		foreach( $qu as $quest ){
			$options .= sprintf( '<option value="%s" %s>%s</option>',
				$quest->id,
				selected( $show_poll, $quest->id, 0 ),
				Kses::kses_html( $quest->question )
			);
		}

		echo '
		<p>
			<label>' . __( 'Which poll to show?', 'democracy-poll' ) . '
				<select name="' . $this->get_field_name( 'show_poll' ) . '" style="max-width:100%;">' . $options . '</select>
			</label>
		</p>';
	}

	public function dem_widget_footer_js(): void {
		?>
		<script id="democracy-poll-wg-js">
			window.demHideTitle = function( that ){
				let getTitleObj = ( that ) => jQuery( that ).closest( '.widget-content' ).find( '.demTitleWrap' );

				that.checked
					? getTitleObj( that ).slideUp( 300 )
					: getTitleObj( that ).slideDown( 300 );
			}
		</script>
		<?php
	}

}
