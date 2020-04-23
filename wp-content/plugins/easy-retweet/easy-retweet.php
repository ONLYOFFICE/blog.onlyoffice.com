<?php
/**
 Plugin Name: Easy Retweet
 Plugin Script: easy-retweet.php
 Plugin URI: http://sudarmuthu.com/wordpress/easy-retweet
 Description: Adds a Retweet button to your WordPress posts.
 Donate Link: http://sudarmuthu.com/if-you-wanna-thank-me
 License: GPL
 Author: Sudar
 Version: 3.1.1
 Author URI: http://sudarmuthu.com/
 Text Domain: easy-retweet
 Domain Path: languages/

 === RELEASE NOTES ===
 Check readme file for full release notes

 Uses the script created by John Resig http://ejohn.org/blog/retweet/
 */

/**  Copyright 2010  Sudar Muthu  (email : sudar@sudarmuthu.com)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License, version 2, as
 published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Easy Retweet Plugin Class
 */
class EasyRetweet {

	/**
	 * Initialize the plugin by registering the hooks
	 */
	function __construct() {

		// Load localization domain
		load_plugin_textdomain( 'easy-retweet', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		// Register hooks
		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
		add_action( 'admin_init', array( $this, 'add_settings' ) );

		/* Use the admin_menu action to define the custom boxes */
		add_action( 'admin_menu', array( $this, 'add_custom_box' ) );

		/* Use the save_post action to do something with the data entered */
		add_action( 'save_post', array( $this, 'save_postdata' ) );

		// Enqueue the script
		add_action( 'wp_head', array( $this, 'add_twitter_js' ) );

		// Register filters
		add_filter( 'the_content', array( $this, 'append_retweet_button' ) , 99 );

		// register short code
		add_shortcode( 'easy-retweet', array( $this, 'shortcode_handler' ) );

		$plugin = plugin_basename( __FILE__ );
		add_filter( "plugin_action_links_$plugin", array( $this, 'add_action_links' ) );
	}

	/**
	 * Register the settings page
	 */
	function register_settings_page() {
		add_options_page( __( 'Easy Retweet', 'easy-retweet' ), __( 'Easy Retweet', 'easy-retweet' ), 'manage_options', 'easy-retweet', array( $this, 'settings_page' ) );
	}

	/**
	 * add options
	 */
	function add_settings() {
		// Register options
		register_setting( 'easy-retweet', 'retweet-style' );
	}

	/**
	 * Add twitter JS
	 */
	function add_twitter_js() {
?>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
<?php
	}

	/**
	 * Adds the custom section in the Post and Page edit screens
	 */
	function add_custom_box() {

		add_meta_box( 'retweet_enable_button', __( 'Easy Retweet Button', 'easy-retweet' ),
			array( $this, 'inner_custom_box' ), 'post', 'side' );
		add_meta_box( 'retweet_enable_button', __( 'Easy Retweet Button', 'easy-retweet' ),
			array( $this, 'inner_custom_box' ), 'page', 'side' );
	}

	/**
	 * Prints the inner fields for the custom post/page section
	 */
	function inner_custom_box() {
		global $post;
		$post_id = $post->ID;

		$option_value = '';

		if ( $post_id > 0 ) {
			$enable_retweet = get_post_meta( $post_id, 'enable_retweet_button', true );
			if ( $enable_retweet != '' ) {
				$option_value = $enable_retweet;
			}

			$custom_retweet_text = get_post_meta( $post_id, 'custom_retweet_text', true );

		}
		// Use nonce for verification
?>
        <input type="hidden" name="retweet_noncename" id="retweet_noncename" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) );?>" />
        <p>
        <label><input type="radio" name="retweet_button" value ="1" <?php checked( '1', $option_value ); ?> /> <?php _e( 'Enabled', 'easy-retweet' ); ?></label>
        <label><input type="radio" name="retweet_button" value ="0"  <?php checked( '0', $option_value ); ?> /> <?php _e( 'Disabled', 'easy-retweet' ); ?></label>
        </p>
        <p>
            <label><?php _e( 'Custom Retweet Text:', 'easy-retweet' ); ?><input type ="text" name="custom_retweet_text" value ="<?php echo $custom_retweet_text;?>" /></label>
        </p>
        <p>
            <?php _e( 'If left blank, the post title will be used.', 'easy-retweet' ); ?>
        </p>
<?php
	}

	/**
	 * When the post is saved, saves our custom data
	 * @param string $post_id
	 * @return string return post id if nothing is saved
	 */
	function save_postdata( $post_id ) {

		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times

		if ( ! isset( $_POST['retweet_noncename'] ) || ! wp_verify_nonce( $_POST['retweet_noncename'], plugin_basename( __FILE__ ) ) ) {
			return $post_id;
		}

		if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}

		// OK, we're authenticated: we need to find and save the data

		if ( isset( $_POST['retweet_button'] ) ) {
			$choice = $_POST['retweet_button'];
			$choice = ( $choice == '1' )? '1' : '0';
			update_post_meta( $post_id, 'enable_retweet_button', $choice );
		}

		if ( isset( $_POST['custom_retweet_text'] ) ) {
			$custom_retweet_text = esc_attr( $_POST['custom_retweet_text'] );
			update_post_meta( $post_id, 'custom_retweet_text', $custom_retweet_text );
		}
	}

	/**
	 * hook to add action links
	 * @param <type> $links
	 * @return <type>
	 */
	function add_action_links( $links ) {
		// Add a link to this plugin's settings page
		$settings_link = '<a href="options-general.php?page=easy-retweet">' . __( "Settings", 'easy-retweet' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Adds Footer links. Based on http://striderweb.com/nerdaphernalia/2008/06/give-your-wordpress-plugin-credit/
	 */
	function add_footer_links() {
		$plugin_data = get_plugin_data( __FILE__ );
		printf( '%1$s ' . __( "plugin", 'easy-retweet' ) .' | ' . __( "Version", 'easy-retweet' ) . ' %2$s | '. __( 'by', 'easy-retweet' ) . ' %3$s<br />', $plugin_data['Title'], $plugin_data['Version'], $plugin_data['Author'] );
	}

	/**
	 * Dipslay the Settings page
	 */
	function settings_page() {
?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2><?php _e( 'Easy Retweet Settings', 'easy-retweet' ); ?></h2>

            <form id="smer_form" method="post" action="options.php">
<?php
		settings_fields( 'easy-retweet' );
		$options = get_option( 'retweet-style' );

		$defaults = array(
			'position'        => 'after',
			'size'            => 'small',
			'display-page'    => '',
			'display-archive' => '',
			'display-home'    => '',
			'account1'        => '',
			't-style'         => '',
			'utm_campaign'    => '',
			'utm_source'      => '',
			'utm_medium'      => '',
			't-language'      => '',
		);
		$options = wp_parse_args( $options, $defaults );
?>
                <h3><?php _e( 'General Settings', 'easy-retweet' ); ?></h3>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e( 'Display', 'easy-retweet' ); ?></th>
                        <td>
                            <p><label><input type="checkbox" name="retweet-style[display-page]" value="1" <?php checked( "1", $options['display-page'] ); ?> /> <?php _e( "Display the button on pages", 'easy-retweet' );?></label></p>
                            <p><label><input type="checkbox" name="retweet-style[display-archive]" value="1" <?php checked( "1", $options['display-archive'] ); ?> /> <?php _e( "Display the button on archive pages", 'easy-retweet' );?></label></p>
                            <p><label><input type="checkbox" name="retweet-style[display-home]" value="1" <?php checked( "1", $options['display-home'] ); ?> /> <?php _e( "Display the button in home page", 'easy-retweet' );?></label></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'Position', 'easy-retweet' ); ?></th>
                        <td>
                            <p><label><input type="radio" name="retweet-style[position]" value="before" <?php checked( "before", $options['position'] ); ?> /> <?php _e( "Before the content of your post", 'easy-retweet' );?></label></p>
                            <p><label><input type="radio" name="retweet-style[position]" value="after" <?php checked( "after", $options['position'] ); ?> /> <?php _e( "After the content of your post", 'easy-retweet' );?></label></p>
                            <p><label><input type="radio" name="retweet-style[position]" value="both" <?php checked( "both", $options['position'] ); ?> /> <?php _e( "Before AND After the content of your post", 'easy-retweet' );?></label></p>
                            <p><label><input type="radio" name="retweet-style[position]" value="manual" <?php checked( "manual", $options['position'] ); ?> /> <?php _e( "Manually call the retweet button", 'easy-retweet' );?></label></p>
							<p><?php _e( 'You can manually call the <code>easy_retweet_button</code> function.', 'easy-retweet' );?></p>
							<p><?php _e( " E.g. <code>if (function_exists('easy_retweet_button')) echo easy_retweet_button();</code>", 'easy-retweet' ); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'Button Size', 'easy-retweet' ); ?></th>
                        <td>
                            <p><label><input type="radio" name="retweet-style[size]" value="small" <?php checked( "small", $options['size'] ); ?>> <?php _e( "Small", 'easy-retweet' );?></label></p>
                            <p><label><input type="radio" name="retweet-style[size]" value="large" <?php checked( "large", $options['size'] ); ?>> <?php _e( "Large", 'easy-retweet' );?></label></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'Recommended Twitter account', 'easy-retweet' ); ?></th>
                        <td>
                            <p><label><input type="text" name="retweet-style[account1]" value="<?php echo $options['account1']; ?>" /></label></p>
                            <p><?php _e( "Twitter account for users to follow after they share content from your website. This account could include your own, or that of a contributor or a partner.", 'easy-retweet' );?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'Additional styles', 'easy-retweet' ); ?></th>
                        <td>
                            <p><label><input type="text" name="retweet-style[t-style]" value="<?php echo $options['t-style']; ?>" /></label></p>
                            <p><?php _e( "eg: <code>float: left; margin-right: 10px;</code>", 'easy-retweet' );?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'Google Analytics Tracking', 'easy-retweet' ); ?></th>
                        <td>
                            <p> <input type="text" name="retweet-style[utm_campaign]" value="<?php echo $options['utm_campaign']; ?>" /> <label for = "retweet-style[utm_campaign]">Campaign</label></p>
                            <p> <input type="text" name="retweet-style[utm_source]" value="<?php echo $options['utm_source']; ?>" /> <label for = "retweet-style[utm_source]">Source</label></p>
                            <p> <input type="text" name="retweet-style[utm_medium]" value="<?php echo $options['utm_medium']; ?>" /> <label for = "retweet-style[utm_medium]">Medium</label></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'Language', 'easy-retweet' ); ?></th>
                        <td>
                            <select name="retweet-style[t-language]">
                                <option value="en" <?php selected( "en", $options['t-language'] ); ?>><?php _e( 'English', 'easy-retweet' ); ?></option>
                                <option value="fr" <?php selected( "fr", $options['t-language'] ); ?>><?php _e( 'French', 'easy-retweet' ); ?></option>
                                <option value="de" <?php selected( "de", $options['t-language'] ); ?>><?php _e( 'German', 'easy-retweet' ); ?></option>
                                <option value="es" <?php selected( "es", $options['t-language'] ); ?>><?php _e( 'Spanish', 'easy-retweet' ); ?></option>
                                <option value="ja" <?php selected( "ja", $options['t-language'] ); ?>><?php _e( 'Japanese', 'easy-retweet' ); ?></option>
                            </select>
                            <p><?php _e( "This is the language that the button will render in on your website.", 'easy-retweet' );?></p>
                        </td>
                    </tr>

                </table>

                <p class="submit">
                    <input type="submit" name="easy-retweet-submit" class="button-primary" value="<?php _e( 'Save Changes', 'easy-retweet' ); ?>" />
                </p>
            </form>

            <h3><?php _e( 'Support', 'easy-retweet' ); ?></h3>
            <p><?php _e( 'If you have any questions/comments/feedback about the Plugin then post a comment in the <a target="_blank" href = "http://sudarmuthu.com/wordpress/easy-retweet">Plugins homepage</a>.', 'easy-retweet' ); ?></p>
            <p><?php _e( 'If you like the Plugin, then consider doing one of the following.', 'easy-retweet' ); ?></p>
            <ul style="list-style:disc inside">
                <li><?php _e( 'Write a blog post about the Plugin.', 'easy-retweet' ); ?></li>
                <li><a href="http://twitter.com/share" class="twitter-share-button" data-url="http://sudarmuthu.com/wordpress/easy-retweet" data-text="Easy Retweet WordPress Plugin" data-count="none" data-via="sudarmuthu">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script><?php _e( ' about it.', 'easy-retweet' ); ?></li>
                <li><?php _e( 'Give a <a href = "http://wordpress.org/extend/plugins/easy-retweet/" target="_blank">good rating</a>.', 'easy-retweet' ); ?></li>
                <li><?php _e( 'Say <a href = "http://sudarmuthu.com/if-you-wanna-thank-me" target="_blank">thank you</a>.', 'easy-retweet' ); ?></li>
            </ul>
        </div>
<?php
		// Display credits in Footer
		add_action( 'in_admin_footer', array( $this, 'add_footer_links' ) );
	}

	/**
	 * Append the retweet_button
	 *
	 * @global object $post Current post
	 * @param string $content Post content
	 * @return string modifiyed content
	 */
	function append_retweet_button( $content ) {
		global $post;
		$options = get_option( 'retweet-style' );
		$defaults = array(
			'position'        => 'after',
			'size'            => 'small',
			'display-page'    => '',
			'display-archive' => '',
			'display-home'    => '',
			'account1'        => '',
			't-style'         => '',
			'utm_campaign'    => '',
			'utm_source'      => '',
			'utm_medium'      => '',
			't-language'      => '',
		);
		$options = wp_parse_args( $options, $defaults );

		$enable_retweet = get_post_meta( $post->ID, 'enable_retweet_button', true );

		if ( $enable_retweet != '' ) {
			// if option per post/page is set
			if ( $enable_retweet == '1' ) {
				// Retweet button is enabled

				$content = $this->build_retweet_button( $content, $options['position'] );

			} elseif ( $enable_retweet == '0' ) {
				// Retweet button is disabled
				// Do nothing
			}

		} else {
			//Option per post/page is not set
			if ( is_single()
				|| ( $options['display-page'] == '1' && is_page() )
				|| ( $options['display-archive'] == '1' && is_archive() )
				|| ( $options['display-home'] == '1' && is_home() ) ) {

				$content = $this->build_retweet_button( $content, $options['position'] );
			}
		}
		return $content;
	}

	/**
	 * Helper function for append_retweet_button
	 *
	 * @param string $content The post content
	 * @param string $position Position of the button
	 * @return string Modifiyed content
	 */
	function build_retweet_button( $content, $position ) {
		$button = easy_retweet_button( false );

		switch ( $position ) {
			case 'before':
				$content = $button . $content;
				break;
			case 'after':
				$content = $content . $button;
				break;
			case 'both':
				$content = $button . $content . $button;
				break;
			case 'manual':
				break;
			default:
				$content = $content . $button;
				break;
		}
		return $content;
	}

	/**
	 * Short code handler
	 * @param <type> $attr
	 * @param <type> $content
	 * @return unknown
	 */
	function shortcode_handler( $attr, $content ) {
		return easy_retweet_button( false );
	}
}

/**
 * Start this plugin once all other plugins are fully loaded
 */
add_action( 'init', 'EasyRetweet' ); function EasyRetweet() { global $EasyRetweet; $EasyRetweet = new EasyRetweet(); }

/**
 * Template function to add the retweet button
 * @param bool $display (optional)
 */
function easy_retweet_button( $display = true ) {
	global $wp_query;

	$post = $wp_query->post;
	$permalink = get_permalink( $post->ID );
	$custom_retweet_text = get_post_meta( $post->ID, 'custom_retweet_text', true );

	if ( $custom_retweet_text == '' ) {
		// if the custom text message is empty default to post title

		// Easy Digital Downloads tries to filter the title. Check https://github.com/sudar/easy-retweet/issues/5
		if ( class_exists( 'Easy_Digital_Downloads' ) ) {
			remove_filter( 'the_title', 'edd_microdata_title', 10, 2 );
		}
		$custom_retweet_text = get_the_title( $post->ID );
		if ( class_exists( 'Easy_Digital_Downloads' ) ) {
			add_filter( 'the_title', 'edd_microdata_title', 10, 2 );
		}
	}

	$enable_retweet = get_post_meta( $post->ID, 'enable_retweet_button', true );

	$output = '';

	if ( $enable_retweet == '' || $enable_retweet == '1' ) {
		// if option per post/page is set or
		// Retweet button is enabled

		$options = get_option( 'retweet-style' );
		$defaults = array(
			'position'        => 'after',
			'size'            => 'small',
			'display-page'    => '',
			'display-archive' => '',
			'display-home'    => '',
			'account1'        => '',
			't-style'         => '',
			'utm_campaign'    => '',
			'utm_source'      => '',
			'utm_medium'      => '',
			't-language'      => '',
		);
		$options = wp_parse_args( $options, $defaults );

		$size         = $options['size'];
		$account1     = $options['account1'];
		$lang         = $options['t-language'];
		$style        = $options['t-style'];

		$utm_campaign = $options['utm_campaign'];
		$utm_source   = $options['utm_source'];
		$utm_medium   = $options['utm_medium'];

		if ( $lang != '' && $lang != 'en' ) {
			$lang = "data-lang='$lang'";
		}

		if ( $utm_campaign != '' && $utm_source != '' && $utm_medium != '' ) {
			if ( strpos( $permalink, '?' ) ) {
				$url = "$permalink&utm_campaign=$utm_campaign&utm_source=$utm_source&utm_medium=$utm_medium";
			} else {
				$url = "$permalink?utm_campaign=$utm_campaign&utm_source=$utm_source&utm_medium=$utm_medium";
			}
		} else {
			$url = $permalink;
		}

		$output = <<<EOD
			<a href="http://twitter.com/share" class="twitter-share-button"
				data-size="$size"
				data-text="$custom_retweet_text"
				data-via="$account1"
				data-url="$url"
				$lang>Tweet</a>
EOD;

		if ( $style != '' ) {
			$output = "<div style = '$style'>$output</div>";
		}
	}

	if ( $display ) {
		echo $output;
	} else {
		return $output;
	}
}
?>
