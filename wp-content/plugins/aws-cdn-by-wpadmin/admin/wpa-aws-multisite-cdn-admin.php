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
if($cdnminttl == "") $cdnminttl = 3600;
if($cdnmaxttl == "") $cdnmaxttl = 86400;
?>
<div class=col-sm-6>
<h3>STEP I</h3>
<a href='/wp-admin/admin.php?page=wpa-aws-cloudfront'>Complete Step I, for each site, on <i>WPAdmin AWS CDN</i> Page</a>
<p>
<H3>Site list</h3>
<h4>Note: Switch to CDN URL after the CDN status has changes to <b>Deployed</b>.</h4>
<ul>
<?php
$wpasites = get_sites();
foreach($wpasites as $key => $wpasite)
{
$wpafilename = "";
if($wpasite->blog_id > 1)
{
$wpafilename = wpaacDOC_ROOT . "sites/" . $wpasite->blog_id . "/" .  $wpasite->domain . str_replace("/","",$wpasite->path) .".txt";
}
else
{
$wpafilename = wpaacDOC_ROOT . $wpasite->domain . ".txt";
}	
	
if(!file_exists($wpafilename))
{
$wpaoldfilename = wpaacDOCOLD_ROOT . $wpasite->domain . str_replace("/","",$wpasite->path) .".txt";
$wpafilename = wpaacDOC_ROOT . $wpasite->domain . str_replace("/","",$wpasite->path) .".txt";	
if(file_exists($wpaoldfilename))
{
copy($wpaoldfilename,$wpafilename);	
unlink($wpaoldfilename);	
}
}

if(file_exists($wpafilename))
{
$wpaawscdnurl = file_get_contents($wpafilename);
preg_match_all('#<awsid[^>]*>(.*?)</awsid>#', $wpaawscdnurl, $awsid);
preg_match_all('#<awsdomain[^>]*>(.*?)</awsdomain>#', $wpaawscdnurl, $awsdomain);
echo "<div style='cursor:pointer;display:inline-block;text-align:Center;' class='wpasitelist btn btn-success' data-code=\"".strip_tags($awsid[0][0])."\" data-target=\"".strip_tags($awsdomain[0][0])."\" data-id=\"".$wpasite->blog_id."\"><b>".$wpasite->domain . $wpasite->path .  "</b><br>" . strip_tags($awsdomain[0][0]) .  "</div>&nbsp;";
}
}
?>
</ul></p>
</div>
<?php 
$cdnurl =  get_option("WPAdmin_CDN_URL"); 
if($cdnurl == "Blank") $cdnurl = "";	
?>
<div class=col-sm-6>
<h3>STEP II</h3>
<b>CDN DOMAIN / CNAME</b><br>
<input type=text id=wpaac_multi_cdnsite class=hidden placeholder="Domain Name" value="">	
<input type=text id=wpaac_multi_cdnid class=hidden placeholder="Domain ID" value="">
<input type=text id=wpaac_multi_cdnurl class=form-control placeholder="CDN Domain" value="">	
<p>&nbsp;</p>
<button style='cursor:pointer;' id=wpaac_multisitecdn class='btn btn-primary form-control'>Apply CDN</button>
<p>&nbsp;</p>
<button style='cursor:pointer;' id=wpaac_multisiteresetcdn class='btn btn-danger form-control'>Remove CDN</button>
<p>&nbsp;</p>
<div id=wpaac_WPAResult></div>
<dl>
<dt><h2>Set CDN</h2></dt>
<dd><p>Click on the <b>Site Name</b> on the left & then click the '<b>Apply CDN</b>' button.</p>
</dd>
</dl>
<dl>
<dt><h2>Remove CDN</h2></dt>
<dd><p>Click on the <b>Site Name</b> on the left & then click the '<b>Remove CDN</b>' button.</p>
</dd>
</dl>
</div>
<script>
jQuery(document).ready(function(){
jQuery(".wpasitelist").click(function(){
var wpasiteid = jQuery(this).attr('data-id');
var wpasitecode = jQuery(this).attr('data-code');
var wpasitetarget = jQuery(this).attr('data-target');
jQuery("#wpaac_multi_cdnurl").val(wpasitetarget);
jQuery("#wpaac_multi_cdnsite").val(wpasiteid);
jQuery("#wpaac_multi_cdnid").val(wpasitecode);
});
jQuery(".retval").live('click',function(){
jQuery("#wpaac_multi_cdnurl").val(jQuery(this).text()).focus();	
});
jQuery("#wpaac_multisiteresetcdn").click(function(){
var cdnsite = jQuery("#wpaac_multi_cdnsite").val();
var cdnurl = jQuery("#wpaac_multi_cdnurl").val();	
jQuery.ajax({
url: ajaxurl,
data: {
'action':'wpaac_multisite_reset_cdn',
'wpaac_multi_cdnurl' : cdnurl,
'wpaac_multi_cdnsite' : cdnsite
},
success:function(data) {
jQuery("#wpaac_WPAResult").html(data);
},
error: function(errorThrown){
jQuery("#wpaac_WPAResult").html(errorThrown);
}
});	
});
jQuery("#wpaac_multisitecdn").live('click',function(){
var cdnsite = jQuery("#wpaac_multi_cdnsite").val();
var cdnurl = jQuery("#wpaac_multi_cdnurl").val();
jQuery.ajax({
url: ajaxurl,
data: {
'action':'wpaac_multisite_set_cdn',
'wpaac_multi_cdnurl' : cdnurl,
'wpaac_multi_cdnsite' : cdnsite
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