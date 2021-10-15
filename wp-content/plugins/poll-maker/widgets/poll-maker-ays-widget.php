<?php
	if ( ! defined( 'AYS_POLL_URL' ) ) {
		define( 'AYS_POLL_URL', plugins_url( plugin_basename( dirname( __FILE__ ) ) ) );
	}

	class Poll_Maker_Widget extends WP_Widget {
		private $plugin_name;
		public $poll_maker_ays;

		public function __construct() {
			$this->plugin_name = POLL_MAKER_AYS_NAME;
			$widget_ops        = array(
				'classname'   => 'poll_maker_ays',
				'description' => 'Poll Maker Widget',
			);
			parent::__construct( 'poll_maker_ays', 'Poll Maker Widget', $widget_ops );
		}

		function form( $instance ) {

			// Check values
			if ( $instance ) {
				$poll_id = esc_attr( $instance['poll_maker_ays_id'] );
			} else {
				$poll_id = 0;
			}
			global $wpdb;
			$poll_table = esc_sql($wpdb->prefix."ayspoll_polls");
			$polls = $wpdb->get_results( "SELECT * FROM ".$poll_table, 'ARRAY_A' );

?>
            <p>
                <select class="widefat" id="<?= $this->get_field_id( 'ays-polls' ); ?>"
                        name="<?= $this->get_field_name( 'poll_maker_ays_id' ); ?>">
                    <option value="0" selected disabled>Select poll</option>
					<?php
						foreach ( $polls as $poll ) { ?>
                            <option value="<?= $poll['id']; ?>" <?= $poll['id'] == $poll_id ? "selected" : ""; ?> >
								<?= $poll['title']; ?>
                            </option>
						<?php }
					?>
                </select>
            </p>            
			<?php
		}

		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			// Fields
			if(isset($new_instance['poll_maker_ays_id'])){
				$instance['poll_maker_ays_id']    = absint( $new_instance['poll_maker_ays_id'] );				
			}			

			return $instance;
		}

		function widget( $args, $instance ) {

			$id  = $instance['poll_maker_ays_id'];			

			echo $args['before_widget'];
			echo do_shortcode("[ays_poll id='".$id."']");
			echo $args['after_widget'];
		}

	}