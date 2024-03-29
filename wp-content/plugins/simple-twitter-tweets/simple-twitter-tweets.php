<?php
/*
Plugin Name: Simple Twitter Tweets
Plugin URI: https://www.planet-interactive.co.uk/simple-twitter-tweets
Description: Display last x number tweets from Twitter API stream, store locally in database to present past tweets when failure to access Twitters restrictive API occurs
Author: Ashley Sheinwald
Version: 4.4
Author URI: https://www.planet-interactive.co.uk/
Text Domain: simple-twitter-tweets
*/

/*  Copyright 2014-2015  Ashley Sheinwald  (email : ashley@planet-interactive.co.uk)

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

// USED FOR DEBUG
// include 'console.php';

// if(!class_exists('helpers')) {
// 	require 'libs/helpers.php';
// }

class PI_SimpleTwitterTweets extends WP_Widget{

	function __construct()  {
		$widget_ops = array('classname' => 'PI_SimpleTwitterTweets', 'description' => 'Displays the most recent tweets from your Twitter Stream' );
		//$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'Not-required-this-time' );
		parent::__construct('PI_SimpleTwitterTweets', 'Simple Twitter Tweets', $widget_ops);

		// Load (enqueue) some JS in Admin ONLY on widgets page
		add_action('admin_enqueue_scripts', array(&$this, 'PI_load_admin_scripts'));

		// Front End ONLY
		if(!is_admin()){
			// Register style sheet.
			add_action( 'wp_enqueue_scripts', array( &$this, 'register_stt_styles' ) );
		}
	}

	// FRONT END - Register and enqueue style sheet.
	function register_stt_styles() {
		if(!is_admin()){
			wp_register_style( 'PI_stt_front', plugins_url( 'simple-twitter-tweets/css/stt.min.css' ) );
			wp_enqueue_style( 'PI_stt_front' );
		}
	}

	// ADMIN - Lets load some JS to aid widget display in Appearance->Widgets
	function PI_load_admin_scripts($hook) {
		if( $hook != 'widgets.php' )
			return;

		// Get global $wp_version - what version of WordPress is installed.
			global $wp_version;

			// If the WordPress >= 3.5, then load the new WordPress color picker.
			if ( 3.5 <= $wp_version ){
					//Both the necessary css and javascript have been registered already by WordPress, so all we have to do is load them with their handle.
					wp_enqueue_style( 'wp-color-picker' );
					wp_enqueue_script( 'wp-color-picker' );
			}
			// If the WP < 3.5 load the older farbtasic color picker.
			else {
					// As above load both style sna scripts
					wp_enqueue_style( 'farbtastic' );
					wp_enqueue_script( 'farbtastic' );
			}
			// Now load STT JS
		wp_enqueue_script('PI_stt_js', plugins_url( '/simple-twitter-tweets/js/sttAdmin.min.js' , dirname(__FILE__) ), array('jquery'));
	}

	function process_links($tweet) {

		// Is the Tweet a ReTweet - then grab the full text of the original Tweet
		if(isset($tweet->retweeted_status)) {
			// Split it so indices count correctly for @mentions etc.
			$rt_section = current(explode(":", $tweet->text));
			$text = $rt_section.": ";
			// Get Text
			$text .= $tweet->retweeted_status->text;
		} else {
			// Not a retweet - get Tweet
			$text = $tweet->text;
		}

		// NEW Link Creation from clickable items in the text
		$text = preg_replace('/((http)+(s)?:\/\/[^<>\s]+)/i', '<a href="$0" target="_blank" rel="nofollow">$0</a>', $text );
		// Clickable Twitter names
		$text = preg_replace('/[@]+([A-Za-z0-9-_]+)/', '<a href="https://twitter.com/$1" target="_blank" rel="nofollow">@$1</a>', $text );
		// Clickable Twitter hash tags
		$text = preg_replace('/[#]+([A-Za-z0-9-_]+)/', '<a href="https://twitter.com/search?q=%23$1" target="_blank" rel="nofollow">$0</a>', $text );
		// END TWEET CONTENT REGEX
		return $text;

	}
	// END PROCESS LINKS - Using Entities


	// Clean four-byte Emoji icons out of tweet text.
	// MySQL utf8 columns cannot store four byte Unicode sequences
	function twitter_api_strip_emoji( $text ){
		// four byte utf8: 11110www 10xxxxxx 10yyyyyy 10zzzzzz
		return preg_replace('/[\xF0-\xF7][\x80-\xBF]{3}/', '', $text );
	}

	function form($instance){

		//Set up some default widget settings.
		$defaults = array(
			  'title' 					=> __('Recent Tweets', 'simple-twitter-tweets')
			, 'name' 				=> __('iPlanetUK', 'simple-twitter-tweets')
			, 'numTweets' 			=> __(4, 'simple-twitter-tweets') // How many to display
			, 'cacheTime' 			=> __(5, 'simple-twitter-tweets') // Time in minutes between updates
			, 'consumerKey' 		=> __('xxxxxxxxxxxx', 'simple-twitter-tweets') // Consumer key
			, 'consumerSecret' 	=> __('xxxxxxxxxxxx', 'simple-twitter-tweets') // Consumer secret
			, 'accessToken' 		=> __('xxxxxxxxxxxx', 'simple-twitter-tweets') // Access token
			, 'accessTokenSecret'	=> __('xxxxxxxxxxxx', 'simple-twitter-tweets') // Access token secret
			, 'exclude_replies'		=> true
			, 'twitterFollow'			=> false
			, 'dataShowCount'		=> false
			, 'dataShowScreenName'	=> false
			, 'dataLang'				=> __('en', 'simple-twitter-tweets') // Language reference
			// STARTING NEW FOR 2.0
			// Time
			, 'timeRef'				=> false // false = use old full hour ref, true if selected will use hour ref as h (twitter style)
			, 'timeAgo'				=> true // true = show ago, false will turn it off
			// Intents
			, 'twitterIntents'			=> false // true = Default: show Twitter Intents
			, 'twitterIntentsText'	=> false // false = Default: Show text - activate to turn off text display and use icons only
			, 'intentColor'			=> "#999999" // Default colour, light grey
			// Avatar
			, 'showAvatar'			=> false // Show the avatar ?
			, 'roundCorners'		=> false // Do we want rounded corners
			, 'avatarSize'			=> "" // what size should it be - defaults to 48px
		);
		$instance 			= wp_parse_args( (array) $instance, $defaults );
		$title 				= $instance['title'];
		$name 				= $instance['name'];
		$numTweets 		= $instance['numTweets'];
		$cacheTime 		= $instance['cacheTime'];
		$consumerKey 	= trim($instance['consumerKey']);
		$consumerSecret 	= trim($instance['consumerSecret']);
		$accessToken 		= trim($instance['accessToken']);
		$accessTokenSecret	= trim($instance['accessTokenSecret']);
		$exclude_replies 	= $instance['exclude_replies'];
		$twitterFollow 		= $instance['twitterFollow'];
		$dataShowCount 	= $instance['dataShowCount'];
		$dataShowScreenName = $instance['dataShowScreenName'];
		$dataLang 			= $instance['dataLang'];
		// STARTING NEW FOR 2.0
		$timeRef 			= $instance['timeRef'];
		$timeAgo 			= $instance['timeAgo'];
		$twitterIntents 		= $instance['twitterIntents'];
		$twitterIntentsText = $instance['twitterIntentsText'];
		$intentColor 		= $instance['intentColor'];
		$showAvatar 		= $instance['showAvatar'];
		$roundCorners 	= $instance['roundCorners'];
		$avatarSize 		= $instance['avatarSize'];
		?>

		<?php
			// Show error if cURL not installed - extension required for Twitter API calls
			if (!in_array('curl', get_loaded_extensions())) {
							echo '<p style="background-color:pink;padding:10px;border:1px solid red;"><strong>';
							_e('You do not have cURL installed! This is a required PHP extension to use the Twitter API:', 'simple-twitter-tweets');
							echo ' <a href="https://curl.haxx.se/docs/install.html" taget="_blank">';
							_e('cURL install', 'simple-twitter-tweets');
							echo '</a></strong></p>';
					}
		?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'simple-twitter-tweets') ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id("titleLink"); ?>">
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("titleLink"); ?>" name="<?php echo $this->get_field_name("titleLink"); ?>"<?php checked( (bool) $instance["titleLink"], true ); ?> />
				<?php _e( 'Make widget title link', 'simple-twitter-tweets' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('name'); ?>"><?php _e('Twitter Name (without @ symbol):', 'simple-twitter-tweets') ?> <input class="widefat" id="<?php echo $this->get_field_id('name'); ?>" name="<?php echo $this->get_field_name('name'); ?>" type="text" value="<?php echo esc_attr($name); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('numTweets'); ?>"><?php _e('Number of Tweets:', 'simple-twitter-tweets') ?> <input class="widefat" id="<?php echo $this->get_field_id('numTweets'); ?>" name="<?php echo $this->get_field_name('numTweets'); ?>" type="text" value="<?php echo esc_attr($numTweets); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('cacheTime'); ?>"><?php _e('Time in Minutes between updates:', 'simple-twitter-tweets') ?> <input class="widefat" id="<?php echo $this->get_field_id('cacheTime'); ?>" name="<?php echo $this->get_field_name('cacheTime'); ?>" type="text" value="<?php echo esc_attr($cacheTime); ?>" /></label>
		</p>

		<?php // NEW FOR 2.0 ?>
		<?php // Time display options ?>
		<div class="secrets" style="background:#d6eef9; margin-bottom:10px;">
			<h4 class="button-secondary" style="width:100%; text-align:center;"><?php _e('Twitter API settings', 'simple-twitter-tweets') ?> <span style="font-size:75%;">&#9660;</span></h4>
			<div style="padding:10px;">
				<p>
					<label for="<?php echo $this->get_field_id('consumerKey'); ?>"><?php _e('Consumer Key:', 'simple-twitter-tweets') ?> <input class="widefat" id="<?php echo $this->get_field_id('consumerKey'); ?>" name="<?php echo $this->get_field_name('consumerKey'); ?>" type="text" value="<?php echo esc_attr($consumerKey); ?>" /></label>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('consumerSecret'); ?>"><?php _e('Consumer Secret:', 'simple-twitter-tweets') ?> <input class="widefat" id="<?php echo $this->get_field_id('consumerSecret'); ?>" name="<?php echo $this->get_field_name('consumerSecret'); ?>" type="text" value="<?php echo esc_attr($consumerSecret); ?>" /></label>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('accessToken'); ?>"><?php _e('Access Token:', 'simple-twitter-tweets') ?> <input class="widefat" id="<?php echo $this->get_field_id('accessToken'); ?>" name="<?php echo $this->get_field_name('accessToken'); ?>" type="text" value="<?php echo esc_attr($accessToken); ?>" /></label>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('accessTokenSecret'); ?>"><?php _e('Access Token Secret:', 'simple-twitter-tweets') ?> <input class="widefat" id="<?php echo $this->get_field_id('accessTokenSecret'); ?>" name="<?php echo $this->get_field_name('accessTokenSecret'); ?>" type="text" value="<?php echo esc_attr($accessTokenSecret); ?>" /></label>
				</p>
				<p>
						<input class="checkbox" type="checkbox" <?php checked( isset( $instance['exclude_replies']), true ); ?> id="<?php echo $this->get_field_id( 'exclude_replies' ); ?>" name="<?php echo $this->get_field_name( 'exclude_replies' ); ?>" />
						<label for="<?php echo $this->get_field_id( 'exclude_replies' ); ?>"><?php _e('Exclude_@replies', 'simple-twitter-tweets'); ?></label>
				</p>
			</div>
		</div>

		<?php // NEW FOR 2.0 ?>
		<?php // Avatar display options ?>
		<div class="avatar" style="background:#d6eef9; margin-bottom:10px;">
			<h4 class="button-secondary" style="width:100%; text-align:center;"><?php _e('Twitter Avatar settings', 'simple-twitter-tweets'); ?> <span style="font-size:75%;">&#9660;</span></h4>
			<div style="padding:10px;">
				<p><?php _e('Display your Twitter Avatar: image', 'simple-twitter-tweets'); ?></p>
				<p>
						<input class="checkbox" type="checkbox" value="true" <?php checked( ( isset( $instance['showAvatar']) && ($instance['showAvatar'] == "true") ), true ); ?> id="<?php echo $this->get_field_id( 'showAvatar' ); ?>" name="<?php echo $this->get_field_name( 'showAvatar' ); ?>" />
						<label for="<?php echo $this->get_field_id( 'showAvatar' ); ?>"><?php _e('Show your avatar image', 'simple-twitter-tweets'); ?></label>
				</p>
				<p>
						<input class="checkbox" type="checkbox" value="true" <?php checked( ( isset( $instance['roundCorners']) && ($instance['showAvatar'] == "true") ), true ); ?> id="<?php echo $this->get_field_id( 'roundCorners' ); ?>" name="<?php echo $this->get_field_name( 'roundCorners' ); ?>" />
						<label for="<?php echo $this->get_field_id( 'roundCorners' ); ?>"><?php _e('Round avatar corners (5px)', 'simple-twitter-tweets'); ?></label>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('avatarSize'); ?>"><?php _e('Size of Avatar (default: 48):', 'simple-twitter-tweets'); ?> <input class="widefat" id="<?php echo $this->get_field_id('avatarSize'); ?>" name="<?php echo $this->get_field_name('avatarSize'); ?>" type="text" value="<?php echo esc_attr($avatarSize); ?>" /><br><em><?php _e('input number only', 'simple-twitter-tweets'); ?></em></label>
				</p>
			</div>
		</div>

		<?php // NEW FOR 2.0 ?>
		<?php // Time display options ?>
		<div class="modTime" style="background:#d6eef9; margin-bottom:10px;">
			<h4 class="button-secondary" style="width:100%; text-align:center;"><?php _e('Time Display Options', 'simple-twitter-tweets'); ?> <span style="font-size:75%;">&#9660;</span></h4>
			<div style="padding:10px;">
				<p>
						<input class="checkbox" type="checkbox" value="true" <?php checked( (isset( $instance['timeRef']) && ($instance['timeRef'] == "true") ), true ); ?> id="<?php echo $this->get_field_id( 'timeRef' ); ?>" name="<?php echo $this->get_field_name( 'timeRef' ); ?>" />
						<label for="<?php echo $this->get_field_id( 'timeRef' ); ?>"><?php _e('Change to short time reference <br><em>h for Hour(s), d for Day(s) ... <strong>Twitter style</strong></em>', 'simple-twitter-tweets'); ?></label>
				</p>
				<p>
						<input class="checkbox" type="checkbox" value="true" <?php checked( (isset( $instance['timeAgo']) && ($instance['timeAgo'] == "true") ), true ); ?> id="<?php echo $this->get_field_id( 'timeAgo' ); ?>" name="<?php echo $this->get_field_name( 'timeAgo' ); ?>" value="true" />
						<label for="<?php echo $this->get_field_id( 'timeAgo' ); ?>"><?php _e('Show "ago" after the time', 'simple-twitter-tweets'); ?></label>
				</p>
			</div>
		</div>

		<?php // NEW FOR 2.0 ?>
		<?php // Twitter Intents and Display Options ?>
		<div class="twitterIntents" style="background:#d6eef9; margin-bottom:10px;">
			<h4 class="button-secondary" style="width:100%; text-align:center;"><?php _e('Twitter Intents', 'simple-twitter-tweets'); ?> <span style="font-size:75%;">&#9660;</span></h4>
			<div style="padding:10px;">
				<p>
						<input class="checkbox" type="checkbox" value="true" <?php checked( (isset( $instance['twitterIntents']) && ($instance['twitterIntents'] == "true") ), true ); ?> id="<?php echo $this->get_field_id( 'twitterIntents' ); ?>" name="<?php echo $this->get_field_name( 'twitterIntents' ); ?>" />
						<label for="<?php echo $this->get_field_id( 'twitterIntents' ); ?>"><?php _e('Show Twitter Intents', 'simple-twitter-tweets'); ?></label>
				</p>
				<p>
						<input class="checkbox" type="checkbox" value="true" <?php checked( (isset( $instance['twitterIntentsText']) && ($instance['twitterIntentsText'] == "true") ), true ); ?> id="<?php echo $this->get_field_id( 'twitterIntentsText' ); ?>" name="<?php echo $this->get_field_name( 'twitterIntentsText' ); ?>" />
						<label for="<?php echo $this->get_field_id( 'twitterIntentsText' ); ?>"><?php _e('Hide Twitter Intents Text <br><em>e.g. just use icons</em>', 'simple-twitter-tweets'); ?></label>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('intentColor'); ?>"><?php _e('Intent icons colour:', 'simple-twitter-tweets'); ?> <input class="intentColor" id="<?php echo $this->get_field_id('intentColor'); ?>" name="<?php echo $this->get_field_name('intentColor'); ?>" type="text" value="<?php echo esc_attr($intentColor); ?>" /></label>
					<div id="colorpicker"></div>
				</p>
			</div>
		</div>

		<div class="twitterFollow" style="background:#d6eef9;">
			<h4 class="button-secondary" style="width:100%; text-align:center;"><?php _e('Twitter Follow Button', 'simple-twitter-tweets'); ?> <span style="font-size:75%;">&#9660;</span></h4>
			<div style="padding:10px;">
				<p>
						<input class="checkbox" type="checkbox" <?php checked( (isset( $instance['twitterFollow']) && ($instance['twitterFollow'] == "on") ), true ); ?> id="<?php echo $this->get_field_id( 'twitterFollow' ); ?>" name="<?php echo $this->get_field_name( 'twitterFollow' ); ?>" />
						<label for="<?php echo $this->get_field_id( 'twitterFollow' ); ?>"><?php _e('Show Twitter Follow Button', 'simple-twitter-tweets'); ?></label>
				</p>
				<p>
						<input class="checkbox" type="checkbox" <?php checked( (isset( $instance['dataShowScreenName']) && ($instance['dataShowScreenName'] == "true") ), true ); ?> id="<?php echo $this->get_field_id( 'dataShowScreenName' ); ?>" name="<?php echo $this->get_field_name( 'dataShowScreenName' ); ?>" value="true" />
						<label for="<?php echo $this->get_field_id( 'dataShowScreenName' ); ?>"><?php _e('Show Twitter Screen Name', 'simple-twitter-tweets'); ?></label>
				</p>
				<p>
						<input class="checkbox" type="checkbox" <?php checked( (isset( $instance['dataShowCount']) && ($instance['dataShowCount'] == "true") ), true ); ?> id="<?php echo $this->get_field_id( 'dataShowCount' ); ?>" name="<?php echo $this->get_field_name( 'dataShowCount' ); ?>" value="true" />
						<label for="<?php echo $this->get_field_id( 'dataShowCount' ); ?>"><?php _e('Show Twitter Followers Count', 'simple-twitter-tweets'); ?></label>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('dataLang'); ?>"><?php _e('Language:', 'simple-twitter-tweets'); ?> <input class="widefat" id="<?php echo $this->get_field_id('dataLang'); ?>" name="<?php echo $this->get_field_name('dataLang'); ?>" type="text" value="<?php echo esc_attr($dataLang); ?>" /></label>
				</p>
			</div>
		</div>
	<?php
	}

	function update($new_instance, $old_instance){
		$instance = $old_instance;

		//Strip tags from title and name to remove HTML
		$instance['title'] 				= strip_tags( $new_instance['title'] );
		$instance['titleLink'] 				= $new_instance['titleLink'];
		$instance['name'] 				= strip_tags( $new_instance['name'] );
		$instance['numTweets'] 		= $new_instance['numTweets'];
		$instance['cacheTime'] 		= $new_instance['cacheTime'];
		$instance['consumerKey'] 		= trim($new_instance['consumerKey']);
		$instance['consumerSecret'] 	= trim($new_instance['consumerSecret']);
		$instance['accessToken'] 		= trim($new_instance['accessToken']);
		$instance['accessTokenSecret'] = trim($new_instance['accessTokenSecret']);
		$instance['exclude_replies'] 	= $new_instance['exclude_replies'];
		$instance['twitterFollow'] 		= $new_instance['twitterFollow'];
		$instance['dataShowCount']	= $new_instance['dataShowCount'];
		$instance['dataShowScreenName']	= $new_instance['dataShowScreenName'];
		$instance['dataLang']			= $new_instance['dataLang'];
		// STARTING NEW FOR 2.0
		$instance['timeRef'] 			= $new_instance['timeRef'];
		$instance['timeAgo'] 			= $new_instance['timeAgo'];
		$instance['twitterIntents'] 		= $new_instance['twitterIntents'];
		$instance['twitterIntentsText'] 	= $new_instance['twitterIntentsText'];
		$instance['intentColor']		= strip_tags( $new_instance['intentColor'] );
		$instance['showAvatar'] 		= $new_instance['showAvatar'];
		$instance['roundCorners'] 		= $new_instance['roundCorners'];
		$instance['avatarSize'] 			= strip_tags( $new_instance['avatarSize'] );

		return $instance;
	}

	function widget($args, $instance){

		extract($args, EXTR_SKIP);

		echo $before_widget;

		//Our variables from the widget settings.
		$PI_title 				= empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
		if( isset($instance['titleLink']) && $instance['titleLink'] == true){
			$PI_title = '<a target="_blank" href="https://twitter.com/'.$instance['name'].'">'.$PI_title.'</a>';
		}
		$PI_name 				= $instance['name'];
		$PI_numTweets 		= $instance['numTweets'];
		$PI_cacheTime 		= $instance['cacheTime'];

		//Setup Twitter API OAuth tokens
		$PI_consumerKey 		= trim($instance['consumerKey']);
		$PI_consumerSecret 	= trim($instance['consumerSecret']);
		$PI_accessToken 		= trim($instance['accessToken']);
		$PI_accessTokenSecret	= trim($instance['accessTokenSecret']);

		$PI_exclude_replies 	= isset( $instance['exclude_replies'] ) ? $instance['exclude_replies'] : false;
		$PI_twitterFollow 		= isset( $instance['twitterFollow'] ) ? $instance['twitterFollow'] : false;

		$PI_dataShowCount 	= isset( $instance['dataShowCount'] ) ? $instance['dataShowCount'] : false;
		$PI_dataShowScreenName 	= isset( $instance['dataShowScreenName'] ) ? $instance['dataShowScreenName'] : false;
		$PI_dataLang 			= $instance['dataLang'];

		// 2.0 updates
		$PI_timeRef 			= isset( $instance['timeRef'] ) ? $instance['timeRef'] : false;
		$PI_timeAgo 			= isset( $instance['timeAgo'] ) ? $instance['timeAgo'] : false;
		$PI_twitterIntents 		= isset( $instance['twitterIntents'] ) ? $instance['twitterIntents'] : false;
		$PI_twitterIntentsText 	= isset( $instance['twitterIntentsText'] ) ? $instance['twitterIntentsText'] : false;
		$PI_intentColor		= $instance['intentColor'];

		// Avatar
		$PI_showAvatar 		= isset( $instance['showAvatar'] ) ? $instance['showAvatar'] : false;
		$PI_roundCorners 		= isset( $instance['roundCorners'] ) ? $instance['roundCorners'] : false;
		$PI_avatarSize 			= $instance['avatarSize'];

		if (!empty($PI_title))
			echo $before_title . $PI_title . $after_title;

			// START WIDGET CODE HERE
			?>

			<ul class="tweets">
			<?php
			/*
			 * Uses:
			 * Twitter API call:
			 *     https://dev.twitter.com/doc/get/statuses/user_timeline
			 * WP transient API ref.
			 *		https://www.problogdesign.com/wordpress/use-the-transients-api-to-list-the-latest-commenter/
			 * Plugin Development and Script enhancement
			 *    https://www.planet-interactive.co.uk
			 */

			// Configuration.
			$numTweets 			= $PI_numTweets; 		// Num tweets to show
			$name 					= $PI_name;				// Twitter UserName
			$cacheTime 			= $PI_cacheTime; 		// Time in minutes between updates.

			// Get from https://dev.twitter.com/
			// Login - Create New Application, fill in details and use required data below
			$consumerKey 		= trim($PI_consumerKey);		// OAuth Key
			$consumerSecret 		= trim($PI_consumerSecret);		// OAuth Secret
			$accessToken 			= trim($PI_accessToken);		// OAuth Access Token
			$accessTokenSecret 	= trim($PI_accessTokenSecret);	// OAuth Token Secret

			$exclude_replies 		= $PI_exclude_replies; 	// Leave out @replies?
			$twitterFollow 			= $PI_twitterFollow; 	// Whether to show Twitter Follow button

			$dataShowCount 		= ($PI_dataShowCount != "true") ? "false" : "true"; // Whether to show Twitter Follower Count
			$dataShowScreenName	= ($PI_dataShowScreenName != "true") ? "false" : "true"; // Whether to show Twitter Screen Name
			$dataLang 				= $PI_dataLang; // Tell Twitter what Language is being used

			$timeRef 				= $PI_timeRef; // Time ref: hours or short h
			$timeAgo 				= $PI_timeAgo; // Human Time: ago ref or not
			$twitterIntents			= $PI_twitterIntents; // Intent on/off
			$twitterIntentsText 	= $PI_twitterIntentsText; // Intents Text on/off
			$intentColor 			= $PI_intentColor; // Intent icons colour

			$showAvatar 			= $PI_showAvatar;
			$roundCorners 		= $PI_roundCorners;
			$avatarSize 			= $PI_avatarSize;

			// COMMUNITY REQUEST! (1)
			$transName = 'list-tweets-'.$name; // Name of value in database. [added $name for multiple account use]
			$backupName = $transName . '-backup'; // Name of backup value in database.

			// if(false === ($tweets = unserialize( base64_decode(get_transient( $transName ) ) ) ) ) :
			if(false === ($tweets = get_transient( $transName ) ) ) :

			// Get the tweets from Twitter.
			if ( ! class_exists('TwitterOAuth') )
				include 'twitteroauth/twitteroauth.php';

			$connection = new TwitterOAuth(
				$consumerKey,   		// Consumer key
				$consumerSecret,   	// Consumer secret
				$accessToken,   		// Access token
				$accessTokenSecret	// Access token secret
			);

			// If excluding replies, we need to fetch more than requested as the
			// total is fetched first, and then replies removed.
			$totalToFetch = ($exclude_replies) ? max(50, $numTweets * 3) : $numTweets;

			$fetchedTweets = $connection->get(
				'statuses/user_timeline',
				array(
					'screen_name'    => $name,
					'count'           	=> $totalToFetch,
					'exclude_replies' => $exclude_replies
				)
			);

			// Did the fetch fail?
			if($connection->http_code != 200) :
				$tweets = get_option($backupName); // False if there has never been data saved.
			else :
				// Fetch succeeded.
				// Now update the array to store just what we need.
				// (Done here instead of PHP doing this for every page load)
				$limitToDisplay = min($numTweets, count($fetchedTweets));

				for($i = 0; $i < $limitToDisplay; $i++) :
					$tweet = $fetchedTweets[$i];

						// Core info.
						$name = $tweet->user->name;

						// COMMUNITY REQUEST !!!!!! (2)
						$screen_name = $tweet->user->screen_name;

						$permalink = 'https://twitter.com/'. $screen_name .'/status/'. $tweet->id_str;
						$tweet_id = $tweet->id_str;

						/* Alternative image sizes method: https://dev.twitter.com/doc/get/users/profile_image/:screen_name */
						//  Check for SSL via protocol https then display relevant image - thanks SO - this should do
						if (isset($_SERVER['HTTPS']) &&
								($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
								isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
								$_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
							// $protocol = 'https://';
							$image = $tweet->user->profile_image_url_https;
						}
						else {
							// $protocol = 'https://';
							$image = $tweet->user->profile_image_url;
						}
						// $image = $tweet->user->profile_image_url;

						// Process Tweets - Use Twitter entities for correct URL, hash and mentions
						$text = $this->process_links($tweet);

						// lets strip 4-byte emojis
						$text = $this->twitter_api_strip_emoji( $text );

						// Need to get time in Unix format.
						$time = $tweet->created_at;
						$time = date_parse($time);
						$uTime = mktime($time['hour'], $time['minute'], $time['second'], $time['month'], $time['day'], $time['year']);

						// Now make the new array.
						$tweets[] = array(
							'text' => $text,
							'name' => $name,
							'permalink' => $permalink,
							'image' => $image,
							'time' => $uTime,
							'tweet_id' => $tweet_id
							);
				endfor;

				set_transient($transName, $tweets, 60 * $cacheTime);
				update_option($backupName, $tweets );
				endif;
			endif;

			if(!function_exists('twitter_time_diff'))
			{
				function twitter_time_diff( $from, $to = '' ) {
						$diff = human_time_diff($from,$to);
						$replace = array(
								' hour' => 'h',
								' hours' => 'h',
								' day' => 'd',
								' days' => 'd',
								' minute' => 'm',
								' minutes' => 'm',
								' second' => 's',
								' seconds' => 's',
						);
						return strtr($diff,$replace);
				}
			}
			// Now display the tweets, if we can.
			if($tweets) : ?>
					<?php foreach( (array) $tweets as $t) : // casting array to array just in case it's empty - then prevents PHP warning ?>
							<li<?php echo ($showAvatar) ? ' class="avatar"':""; ?><?php echo ($showAvatar && $avatarSize) ? ' style="margin-left:'.($avatarSize+5).'px"':""; ?>>
								<?php
									if ($showAvatar){
										echo '<img ';
										echo ($avatarSize) ? ' style="margin-left:-'.($avatarSize+5).'px"':"";
										echo ($avatarSize) ? 'width="'.$avatarSize.'px" height="'.$avatarSize.'px"' : 'width="48px" height="48px"';
										echo 'src="'.$t['image'].'" alt="';
										_e('Tweet Avatar', 'simple-twitter-tweets');
										echo '" class="';
										echo ($roundCorners) ? 'a-corn':'';
										echo '"/>';
									}
								?>
								<?php echo $t['text']; ?>
					

							</li>
										<div class="meta twitter">
									
								
									<span class="stt-em date">
						
							<?php

								// Original - long time ref: hours...
								if($timeRef == "true"){
								// New - short Twitter style time ref: h...
									$timeDisplay = twitter_time_diff($t['time'], current_time('timestamp'));
								}else{
									$timeDisplay = human_time_diff($t['time'], current_time('timestamp'));
								}
								// Ago - to show?
								if($timeAgo == "true"){
									$displayAgo = _x(' ago', 'leading space is required to keep gap from date', 'simple-twitter-tweets');
								}else{
									// Added to counter 'no ago var' setting undefined variable warning
									$displayAgo = "";
								}
								// Use to make il8n compliant
								printf(__( '%1$s%2$s', 'simple-twitter-tweets' ), $timeDisplay, $displayAgo);

							?>
							
									</span>
									</div>

						<?php // INTENTS REF: DISPLAY OR NOT
						if($twitterIntents == "true"){
						?>
						<div class="intent-meta">
							<a href="https://twitter.com/intent/tweet?in_reply_to=<?php echo $t['tweet_id']; ?>" data-lang="en" class="in-reply-to" title="<?php _e('Reply','simple-twitter-tweets'); ?>" target="_blank">
								<span aria-hidden="true" data-icon="&#xf079;" <?php echo ($intentColor) ? 'style="color:'.$intentColor.';"' :''; ?>></span>
								<span <?php echo ($twitterIntentsText) ? 'class="pi-visuallyhidden"':''; ?>><?php _e('Reply','simple-twitter-tweets'); ?></span></a>
							<a href="https://twitter.com/intent/retweet?tweet_id=<?php echo $t['tweet_id']; ?>" data-lang="en" class="retweet" title="<?php _e('Retweet','simple-twitter-tweets'); ?>" target="_blank">
								<span aria-hidden="true" data-icon="&#xf112;" <?php echo ($intentColor) ? 'style="color:'.$intentColor.';"' :''; ?>></span>
								<span <?php echo ($twitterIntentsText) ? 'class="pi-visuallyhidden"':''; ?>><?php _e('Retweet','simple-twitter-tweets'); ?></span></a>
							<a href="https://twitter.com/intent/favorite?tweet_id=<?php echo $t['tweet_id']; ?>" data-lang="en" class="favorite" title="<?php _e('Favourite','simple-twitter-tweets'); ?>" target="_blank">
								<span aria-hidden="true" data-icon="&#xf005;" <?php echo ($intentColor) ? 'style="color:'.$intentColor.';"' :''; ?>></span>
								<span <?php echo ($twitterIntentsText) ? 'class="pi-visuallyhidden"':''; ?>><?php _e('Favourite','simple-twitter-tweets'); ?></span></a>
						</div>
						<?php } ?>
					<?php endforeach; ?>

			<?php else : ?>
					<li><?php _e('Waiting for Twitter... Once Twitter is ready they will display my Tweets again.','simple-twitter-tweets'); ?></li>
			<?php endif; ?>
			</ul>

			<?php
				// ADD Twitter follow button - to increase engagement
				// Make it an options choice though
			if($twitterFollow){ ?>
				<div class="twitter-follow-container"><a href="https://twitter.com/<?php echo $PI_name; ?>" class="twitter-follow-button" data-show-count="<?php echo $dataShowCount; ?>" data-show-screen-name="<?php echo $dataShowScreenName; ?>" data-lang="<?php echo $dataLang; ?>"><?php _e('Follow','simple-twitter-tweets'); ?> @<?php echo $PI_name; ?></a></div>
				<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
			<?php
			}
			// END OF WIDGET CODE HERE
			echo $after_widget;
		}

}
add_action( 'widgets_init', function() {return register_widget("PI_SimpleTwitterTweets");} );

add_action( 'plugins_loaded', 'PI_SimpleTwitterTweets_load_plugin_textdomain' );
// Now loads language files
function PI_SimpleTwitterTweets_load_plugin_textdomain() {
    load_plugin_textdomain( 'simple-twitter-tweets', false, dirname(plugin_basename(__FILE__)) . '/languages/' );
}
?>