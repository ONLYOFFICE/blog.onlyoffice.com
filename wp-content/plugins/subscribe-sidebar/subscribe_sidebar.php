<?php
/*
Plugin Name: Subscribe Sidebar
Plugin URI: http://www.blubrry.com/subscribe_sidebar/
Description: Adds a list of Subscribe links to your sidebar. Options include your blog and podcast feed, Twitter page, iTunes, Facebook Fan Page and more.
Author: Blubrry.com
Version: 1.3.1
Author URI: http://www.blubrry.com/
*/

define('SUBSCRIBE_SIDEBAR_VERSION', '1.3.1');

function subscribe_sidebar_get_root_url()
{
	$dirname = basename( dirname(__FILE__) );
	return WP_PLUGIN_URL . '/'. $dirname .'/';
}

function subscribe_sidebar($show_header=true)
{
	// Default blog information
	$site_url = get_bloginfo('url');
	$blog_name = get_bloginfo('name');
    $rss2_url = get_bloginfo('rss2_url');
	$rss2_url = "http://feeds.feedburner.com/OnlyOfficeBlog";
	$atom_url = get_bloginfo('atom_url');
	$podcast_url = get_feed_link('podcast'); // Created by Podpress
	
	$plugin_url = subscribe_sidebar_get_root_url();
	
	
	$options = get_option('subscribe_sidebar');
	if( !isset($options['title']) || $options['title'] == '' )
		$options['title'] = __('Subscribe');
	if( isset($options['custom_style']) )
		unset($options['custom_style']);
	if( !empty($options['dropshadow_icons']) )
		$plugin_url .= 'dropshadow/';
	
	if( !$options ) // Default subscribe options
	{
		$options['rss2'] = true;
		$options['google'] = true;
		$options['yahoo'] = true;
	}

	$icons = array('feed'=>'feed.png', 'twitter'=>'twitter.png', 'google'=>'google.png', 'yahoo'=>'yahoo.png', 'itunes'=>'itunes.png', 'zune'=>'zune.png', 'facebook'=>'facebook.png', 'linkedin'=>'linkedin.png', 'myspace'=>'myspace.png', 'google_profile'=>'google_profile.png' );
	
	if( $show_header )
		echo "<h2>{$options['title']}</h2>\n";
	echo "\t<ul id=\"subscribe_sidebar_list\">\n";
	
	while( list($key,$value) = each($options) )
	{
		if( $value && $key != 'note_author' && $key != 'title' && $key != 'itunes_modern' && $key != 'dropshadow_icons' )
		{
			echo "\t\t<li>";
			
			switch( $key )
			{
				case 'facebook': {
					if( !strstr($value,'http://') ) // If the value entered is not a link, lets prefix it with the correct link.
						$value = 'http://www.facebook.com/pages/'. $value;
						
					// echo '<a href="'. $value .'" title="'. __('Facebook Fan Page') .'"><img src="'.$plugin_url.$icons[$key].'" alt="'. __('Facebook Fan Page') .'" /></a>';
					echo '<a class="facebook" href="'. $value .'" title="'. __('Facebook Fan Page') .'">'. __('Facebook') .'</a>';
				}; break;
				case 'twitter': {
					// echo '<a href="http://twitter.com/'. $value .'" title="Twitter"><img src="'.$plugin_url.$icons[$key].'" alt="Twitter" /></a>';
					echo '<a class="twitter" href="http://twitter.com/'. $value .'" title="Twitter">Twitter</a>';
				}; break;

				case 'google_profile': {
				if( !strstr($value,'http://') ) // If the value entered is not a link, lets prefix it with the correct link.
						$value = 'http://www.google.com/profiles/'. $value;
				
					// echo '<a href="'. $value .'" title="Google Profile"><img src="'.$plugin_url.$icons[$key].'" alt="Google Profile" /></a>';
					echo '<a class="google-profile" href="'. $value .'" title="Google Profile">Google Profile</a>';
				}; break;
				
				case 'yahoo': {
					//echo '<a href="http://add.my.yahoo.com/rss?url='. urlencode($rss2_url) .'" title="Add to My Yahoo"><img src="'.$plugin_url.$icons[$key].'" alt="Add to My Yahoo" /></a>';
					echo '<a class="yahoo" href="http://add.my.yahoo.com/rss?url='. urlencode($rss2_url) .'" title="Add to My Yahoo">My Yahoo</a>';
				}; break;
				
				case 'google': {
					//echo '<a href="http://fusion.google.com/add?feedurl='. urlencode($rss2_url) .'" title="Add to Google Reader/Homepage"><img src="'.$plugin_url.$icons[$key].'" alt="Add to Google Reader/Homepage" /></a>';
					echo '<a class="google" href="http://fusion.google.com/add?feedurl='. urlencode($rss2_url) .'" title="Add to Google Reader/Homepage">Add to Google</a>';
				}; break;
				
				case 'itunes': {
					// Try to get the one-click subscription link from PowerPress if enabled...
					if( $value == '1' && defined('POWERPRESS_VERSION') )
						$value = subscribe_sidebar_get_powerpress_itunes_url();
					
					$link = str_replace('http://', 'itpc://', $rss2_url);
					if( strlen($value) > 5 )
						$link = htmlspecialchars($value);
						
					if( !empty($options['itunes_modern']) )
						$icons[$key] = 'itunes_modern.png';
						
					// echo '<a href="'. $link .'" title="Add to iTunes"><img src="'.$plugin_url.$icons[$key].'" alt="Add to iTunes" /></a>';
					echo '<a class="itunes" href="'. $link .'" title="Add to iTunes">Add to iTunes</a>';
				}; break;
				
				case 'zune': {
					// echo '<a href="zune://subscribe/?'.str_replace('+', '_', urlencode($blog_name) ). '='. $rss2_url .'" title="Add to Zune"><img src="'.$plugin_url.$icons[$key].'" alt="Add to Zune" /></a>';
					echo '<a class="zune" href="zune://subscribe/?'.str_replace('+', '_', urlencode($blog_name) ). '='. $rss2_url .'" title="Add to Zune">Add to Zune</a>';
				}; break;
				
				case 'podcast': {
					// echo '<a href="'. $podcast_url .'" title="Podcast RSS Feed"><img src="'.$plugin_url.$icons['feed'].'" alt="Podcast RSS Feed" /></a>';
					echo '<a class="podcast" href="'. $podcast_url .'" title="Podcast RSS Feed">Podcast Feed</a>';
				}; break;
				
				case 'atom': {
					// echo '<a href="'. $atom_url .'" title="Atom Feed"><img src="'.$plugin_url.$icons['feed'].'" alt="Atom Feed" /></a>';
					echo '<a class="atom" href="'. $atom_url .'" title="Atom Feed">Atom Feed</a>';
				}; break;
				
				case 'rss2': {
					// echo '<a href="'. $rss2_url .'" title="RSS Feed"><img src="'.$plugin_url.$icons['feed'].'" alt="RSS Feed" /></a>';
					echo '<a class="rss" href="'. $rss2_url .'" title="RSS Feed">RSS Feed</a>';
				}; break;
				
				case 'dropshadow_icons':
				case 'note_author': {
					// Do nothing for this option here
				}; break;
				
				default: {
					$link = $rss2_url;
					if( strlen($value) > 5 )
						$link = $value;
					// echo '<a href="'. $link .'" title="'. $key .'"><img src="'.$plugin_url.$icons[$key].'" alt="'. $key .'" /></a>';
					echo '<a class="default" href="'. $link .'" title="'. $key .'">'. $key .'</a>';
				}; break;
			}
			echo "</li>\n";
		}
	}
	
	echo "\t</ul>\n";		
	if( isset($options['note_author']) && $options['note_author'] )
	{
		echo '<div style="margin-top: 8px;"><span style="font-size: 10px; border-top: 1px dotted;">Subscribe plugin by <a href="http://www.blubrry.com/subscribe_sidebar/" target="_blank" title="Subscribe plugin by Blubrry.com">Blubrry.com</a></span></div>';
	}
}


function subscribe_sidebar_header($args)
{
	$options = get_option('subscribe_sidebar');
	if( @$options['custom_style'] != true )
		echo "\n<!-- Subscribe Sidebar widget -->\n<link rel=\"stylesheet\" href=\"".get_bloginfo('url')."/wp-content/plugins/".basename(dirname(__FILE__))."/subscribe_sidebar.css\" type=\"text/css\" media=\"screen\" />\n";
}

function widget_subscribe_sidebar_init()
{
	if( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;
	
	function widget_subscribe_sidebar($args)
	{
		extract($args);
		$options = get_option('subscribe_sidebar');
		if( !isset($options['title']) || $options['title'] == '' )
			$options['title'] = __('Subscribe');
		
		echo "<!-- Start Subscribe Sidebar widget -->\n";
		echo $before_widget . $before_title . $options['title'] . $after_title;
		echo '<div id="subscribe_sidebar">';
		subscribe_sidebar(false);
		echo '</div>';
		echo $after_widget;
		echo "\t<!-- End Subscribe Sidebar widget -->\n";
	}
	
	function widget_subscribe_sidebar_control()
	{
		$options = $newoptions = get_option('subscribe_sidebar');
		if ( $_POST["subscribe-sidebar-submit"] ) {
			$newoptions['title'] = strip_tags(stripslashes($_POST["subscribe-sidebar-title"]));
		}
		if ( $options['title'] != $newoptions['title'] ) {
			$options = $newoptions;
			update_option('subscribe_sidebar', $options);
		}
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
	?>
				<p><label for="subscribe-sidebar-title"><?php _e('Heading Title:'); ?> <input style="width: 200px;" id="subscribe-sidebar-title" name="subscribe-sidebar-title" type="text" value="<?php echo $title; ?>" /></label></p>
				<input type="hidden" id="subscribe-sidebar-submit" name="subscribe-sidebar-submit" value="1" />
				<p style="font-size: 85%;">Additional options be configured from the <a href="options-general.php?page=<?php echo basename(__FILE__); ?>">Subscribe Sidebar</a> settings page.</p>
	<?php
	}
	
	register_sidebar_widget('Subscribe', 'widget_subscribe_sidebar');
	register_widget_control('Subscribe', 'widget_subscribe_sidebar_control', null, 75, 'subscribe-sidebar');
}

function subscribe_sidebar_get_powerpress_itunes_url()
{
	// Try to grab the iTunes URL from PowerPress settings...
	$powerpress_options = get_option('powerpress_general');
	if( !empty($powerpress_options['itunes_url']) )
		return $powerpress_options['itunes_url'];
	return false;
}

function subscribe_sidebar_admin_page()
{
		if ($_POST['subscribe_sidebar'] ) {
			
			if( $_POST['subscribe_sidebar']['facebook'] )
				$_POST['subscribe_sidebar']['facebook'] = $_POST['facebook_url'];
			
			if( $_POST['subscribe_sidebar']['twitter'] )
				$_POST['subscribe_sidebar']['twitter'] = $_POST['TwitterName'];
			
			if( $_POST['subscribe_sidebar']['google_profile'] )
				$_POST['subscribe_sidebar']['google_profile'] = $_POST['google_profile_name'];
				
			if( $_POST['subscribe_sidebar']['itunes'] && $_POST['iTunesURL'] )
				$_POST['subscribe_sidebar']['itunes'] = $_POST['iTunesURL'];
			
			update_option('subscribe_sidebar', $_POST['subscribe_sidebar']);
		}
	
		$options = get_option('subscribe_sidebar');
		if( !$options )
		{
			$options['rss2'] = true;
			$options['google'] = true;
			$options['yahoo'] = true;
		}
		
		$itunes_url = false;
		if( empty($options['itunes']) || strlen($options['itunes']) < 4  )
		{
			// Try to grab the iTunes URL from PowerPress settings...
			$itunes_url = subscribe_sidebar_get_powerpress_itunes_url();
		}
	
		?>
<style type="text/css">
	fieldset{margin:20px 0; 
	border:1px solid #cecece;
	padding:15px;
	}
</style>
<div class="wrap">
	<h2>Blubrry Subscribe Sidebar Options</h2>
	
	<?php
	if( $_GET['status'] )
	{
		echo "<div style=\"width=100% color: #770000; font-weight: normal;\">Status: ";
		echo $_GET['status'];
		echo "</div>\n";
	}
?>
	<form method="post">
	
	<div class="submit"><input type="submit" name="info_update" value="<?php _e('Update Options') ?> &raquo;" /></div>
	
	<div>
		<fieldset class="options">
			<legend><?php echo __('Subscribe Links:'); ?></legend>
			<p>Select the links to display in the Subscribe Sidebar.</p>
			
			<ul>
			<li>
				<label for="subscribe_sidebar[rss2]">
				<input name="subscribe_sidebar[rss2]" type="checkbox" id="subscribe_sidebar_rss2" value="1" <?php checked('1', $options['rss2']); ?> />
				<?php _e('RSS 2.0 Feed'); ?></label>
			</li>
			<li>
				<label for="subscribe_sidebar[atom]">
				<input name="subscribe_sidebar[atom]" type="checkbox" id="subscribe_sidebar_atom" value="1" <?php checked('1', $options['atom']); ?> />
				<?php _e('Atom Feed'); ?></label>
			</li>
<?php if ( subscribe_sidebar_podcast_check() ) { ?>
			<li>
				<label for="subscribe_sidebar[podcast]">
				<input name="subscribe_sidebar[podcast]" type="checkbox" id="subscribe_sidebar_podcast" value="1" <?php checked('1', $options['podcast']); ?> />
				<?php echo __('Podcast RSS 2.0 Feed'); ?></label>
			</li>
<?php } ?>
			<li>
				<label for="subscribe_sidebar[google]">
				<input name="subscribe_sidebar[google]" type="checkbox" id="subscribe_sidebar_google" value="1" <?php checked('1', $options['google']); ?> />
				<?php echo __('Google Reader/Homepage'); ?></label>
			</li>
			<li>
				<label for="subscribe_sidebar[yahoo]">
				<input name="subscribe_sidebar[yahoo]" type="checkbox" id="subscribe_sidebar_yahoo" value="1" <?php checked('1', $options['yahoo']); ?> />
				<?php echo __('My Yahoo'); ?></label>
			</li>
			<li>
				<label for="subscribe_sidebar[itunes]">
				<input name="subscribe_sidebar[itunes]" type="checkbox" id="subscribe_sidebar_itunes" value="1" <?php echo ( $options['itunes'] ? 'checked':''); ?> />
				<?php echo __('iTunes (for podcast subscription)'); ?></label>
				
				<div style="margin-left: 20px;">
					<label for="subscribe_sidebar[itunes_modern]">
					<input name="subscribe_sidebar[itunes_modern]" type="checkbox" id="subscribe_sidebar_itunes" value="2" <?php echo ( $options['itunes_modern'] ? 'checked':''); ?> />
					<?php echo __('Use modern iTunes icon'); ?>:</label>
					<img src="<?php echo subscribe_sidebar_get_root_url(); ?>itunes_modern.png" title="Modern iTunes icon" style="vertical-align:text-bottom;" />
				</div>
				
				<p style="margin-left: 20px;">
					<label for="iTunesURL">Your iTunes URL:</label>
					<input type="text" name="iTunesURL" id="itunesurl" value="<?php if( strlen($options['itunes']) > 4 ) echo($options['itunes']); else if( $itunes_url ) echo $itunes_url; ?>" style="width: 50%;" /> (leave blank if unknown)
				</p>
				<p style="margin-left: 20px;">e.g. http://itunes.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=000000000</p>
				<p style="margin-left: 20px;">
					Don't have your podcast listed on iTunes? Install and run <a href="http://www.apple.com/itunes/download/" target="_blank" title="iTunes">iTunes</a>, then <a href="https://phobos.apple.com/WebObjects/MZFinance.woa/wa/publishPodcast" target="_blank" title="Publish a Podcast on iTunes">Publish a Podcast</a> on iTunes.
				</p>
			</li>
			<li>
				<label for="subscribe_sidebar[zune]">
				<input name="subscribe_sidebar[zune]" type="checkbox" id="subscribe_sidebar_zune" value="1" <?php checked('1', $options['zune']); ?> />
				<?php _e('Zune (for podcast subscription)'); ?></label>
			</li>
			<li>
				<label for="subscribe_sidebar[twitter]">
				<input name="subscribe_sidebar[twitter]" type="checkbox" id="subscribe_sidebar_twitter" value="1" <?php echo ( $options['twitter'] ? 'checked':''); ?> />
				<?php _e('Twitter'); ?></label>
				<p style="margin-left: 20px;">
					<label for="twitterlogin">Your Twitter Name:</label>
					<input type="text" name="TwitterName" id="twittername" value="<?php if($options['twitter']) echo($options['twitter']); ?>" />
				</p>
				<p style="margin-left: 20px;">
					Don't have a <a href="http://twitter.com/" target="_blank" title="Twitter">Twitter</a> account? You should, <a href="http://twitter.com/" target="_blank" title="Twitter">Twitter</a> is a great way to communicate with your blog readers and podcast audience.
				</p>
			</li>
			<li>
				<label for="subscribe_sidebar[google_profile]">
				<input name="subscribe_sidebar[google_profile]" type="checkbox" id="subscribe_sidebar_google_profile" value="1" <?php echo ( $options['google_profile'] ? 'checked':''); ?> />
				<?php _e('Google Profile'); ?></label>
				<p style="margin-left: 20px;">
					<label for="google_profile_name">Your Google Profile URL:</label>
					<input type="text" name="google_profile_name" id="google_profile_name" value="<?php if($options['google_profile']) echo $options['google_profile']; ?>" style="width: 50%;" />
				</p>
				<p style="margin-left: 20px;">
					e.g. http://www.google.com/profiles/yourprofileid
				</p>
				<p style="margin-left: 20px;">
					Don't have a Google Profile? <a href="http://www.google.com/profiles?edit=f">Set one up Here</a>.
				</p>
			</li>
			<li>
				<label for="subscribe_sidebar[facebook]">
				<input name="subscribe_sidebar[facebook]" type="checkbox" id="subscribe_sidebar_facebook" value="1" <?php echo ( $options['facebook'] ? 'checked':''); ?> />
				<?php echo __('Facebook Fan Page'); ?></label>
				<p style="margin-left: 20px;">
					<label for="facebook_url">Your Facebook Page URL:</label>
					<input type="text" name="facebook_url" id="facebook_url" value="<?php if( !empty($options['facebook']) ) echo htmlspecialchars($options['facebook']); ?>" style="width: 50%;" />
				</p>
				<p style="margin-left: 20px;">
					e.g. http://www.facebook.com/pages/Your-Page-Name/123456789
				</p>
				<p style="margin-left: 20px;">
					Don't have a Facebook Fan Page? <a href="http://www.facebook.com/pages/create.php" target="_blank">Create one here</a>
				</p>
			</li>
			</ul>
		</fieldset>
		
		<fieldset class="options">
			<legend><?php echo __('Display Options:'); ?></legend>
			<ul>
			<li>
				<label for="subscribe_sidebar[custom_style]">
				<input name="subscribe_sidebar[custom_style]" type="checkbox" id="subscribe_sidebar_custom_style" value="1" <?php checked('1', $options['custom_style']); ?> />
				<?php _e('Override Icon Styling (disables default styling, relies on theme sidebar styling)'); ?></label>
			</li>
			<li>
				<label for="subscribe_sidebar[note_author]">
				<input name="subscribe_sidebar[note_author]" type="checkbox" id="subscribe_sidebar_note_author" value="1" <?php checked('1', $options['note_author']); ?> />
				<?php _e('Note Author under Subscribe list (displays &quot;Subscribe plugin by <a href="http://www.blubrry.com/subscribe_sidebar/" target="_blank" title="Subscribe plugin by Blubrry.com">Blubrry.com</a>&quot; at bottom of list)'); ?></label>
			</li>
				<li>
				<p style="margin-left: 20px;">
					<label for="subscribe_sidebar_title">Heading Title:</label>
					<input type="text" name="subscribe_sidebar[title]" id="subscribe_sidebar_title" value="<?php echo htmlspecialchars($options['title']); ?>" /> (Leave blank for default: Subscribe)
				</p>
			</li>
			<li>
				<label for="subscribe_sidebar[dropshadow_icons]">
				<input name="subscribe_sidebar[dropshadow_icons]" type="checkbox" id="subscribe_sidebar_dropshadow_icons" value="1" <?php checked('1', $options['dropshadow_icons']); ?> />
				<?php echo __('Use Drop Shadow icons (images are slightly larger, 21 x 21 pixels in size)'); ?>:</label>
					<img src="<?php echo subscribe_sidebar_get_root_url(); ?>dropshadow/feed.png" style="vertical-align:top;" />
			</li>
			</ul>
		</fieldset>

		</div>
	<div class="submit"><input type="submit" name="info_update" value="<?php _e('Update Options') ?> &raquo;" /></div>
	</form>
</div>

<div class="wrap">

	<p style="font-size: 85%; text-align: center; padding-bottom: 25px;">
		<a href="http://www.blubrry.com/subscribe_sidebar/" title="Blubrry Subscribe Sidebar" target="_blank">Blubrry Subscribe Sidebar</a> <?php echo SUBSCRIBE_SIDEBAR_VERSION; ?>	&#8212; <a href="http://twitter.com/blubrry" target="_blank" title="Follow Blubrry on Twitter">Follow Blubrry on Twitter</a>
	</p>

</div>
<?php
}

function subscribe_sidebar_podcast_check()
{
	if( defined('POWERPRESS_VERSION') )
		return true;
	$current_plugins = get_option('active_plugins');
	if (in_array('podpress/podpress.php', $current_plugins))
		return true;
	if (in_array('podcasting/podcasting.php', $current_plugins))
		return true;
	return false;
}

function subscribe_sidebar_admin()
{
	add_options_page('Subscribe Sidebar', 'Subscribe Sidebar', 9, basename(__FILE__), 'subscribe_sidebar_admin_page');
}
add_action('admin_menu','subscribe_sidebar_admin',1);
	
add_action('wp_head', 'subscribe_sidebar_header');
add_action('plugins_loaded', 'widget_subscribe_sidebar_init');

?>