<?php
define( 'wpacPLUGIN_URL', plugin_dir_url( __FILE__ ) );
$wpaac_bootStrapJS = wpacPLUGIN_URL."asset/js/bootstrap.min.js";
$wpaac_bootStrapCSS = wpacPLUGIN_URL."asset/css/bootstrap.min.css";
wp_register_script('wpaac-bootstrap_init', $wpaac_bootStrapJS);
wp_enqueue_script('wpaac-bootstrap_init');
wp_register_style('wpaac-bootstrapCSS_init', $wpaac_bootStrapCSS);
wp_enqueue_style('wpaac-bootstrapCSS_init');
if ( ! defined( 'wpaacbasedir' ) )
define( 'wpaacbasedir', plugin_dir_path( __FILE__ ) );
$cdndomain = site_url();
$cdndomain = preg_replace("(^https?://)", "", $cdndomain);
if(substr(phpversion(),0,3) < '5.4')
{
echo "<h2>ALERT: The plugin requires PHP version 5.4 or higher</h2>";
}
$wpa_cdnkey =  get_option("WPAdmin_CDN_KEY"); 
if($wpa_cdnkey == "Blank") $wpa_cdnkey = "";
?>
<div class=col-sm-6>
<P>Thank you for using our plugin and making a donation towards future development.<br>Your Name will be added to our Donor's list on <a href='https://wpadmin.ca/free-wordpress-plugin-amazon-cloudfront-cdn/' target=_BLANK>The Plugin Page on WPAdmin.ca</a></p>
<h3>Enter Your Donation ID</h3>
<p><sub>If you made a donation and don't have the Donation ID, please send an email to <b>Support@WPAdmin.ca</b></sub> with the details of your donation.</p>
<input type=text id=wpaac_cdnregistration class=form-control placeholder="Donation ID" value="<?php echo $wpa_cdnkey ; ?>">
<p>&nbsp;</p>
<button id=wpaac_cdnregistration_btn class='btn btn-primary form-control'>Update</button>
<p>&nbsp;</p>
<div id=wpaac_WPAResult></div>
</div>
<script>
jQuery(document).ready(function(){
jQuery("#wpaac_cdnregistration_btn").live('click',function(){
var wpaac_cdnregistration = jQuery("#wpaac_cdnregistration").val();
jQuery.ajax({
url: ajaxurl,
data: {
'action':'wpaac_cdn_registration',
'wpaac_cdnregistration' : wpaac_cdnregistration
},
success:function(data) {
jQuery("#wpaac_WPAResult").html(data);
},
error: function(errorThrown){
jQuery("#wpaac_WPAResult").html(errorThrown);
}
});
});
});
</script>