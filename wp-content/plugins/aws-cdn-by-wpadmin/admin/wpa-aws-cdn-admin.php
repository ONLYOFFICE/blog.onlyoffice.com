<?php
define( 'wpacPLUGIN_URL', plugin_dir_url( __FILE__ ) );
$wpaac_bootStrapJS = wpacPLUGIN_URL."asset/js/bootstrap.min.js";
$wpaac_bootStrapCSS = wpacPLUGIN_URL."asset/css/bootstrap.min.css";
wp_register_script('wpaac-bootstrap_init', $wpaac_bootStrapJS);
wp_enqueue_script('wpaac-bootstrap_init');
wp_register_style('wpaac-bootstrapCSS_init', $wpaac_bootStrapCSS);
wp_enqueue_style('wpaac-bootstrapCSS_init');
if ( ! defined( 'wpaacbasedir' ) ) define( 'wpaacbasedir', plugin_dir_path( __FILE__ ) );

$wpa_subfolder = content_url();
$wpa_domain = $_SERVER['HTTP_HOST'];
$wpa_these = array("http:","https:","/","wp-content",$wpa_domain);
$wpa_subfolder = str_replace($wpa_these,"",$wpa_subfolder);


if(substr(phpversion(),0,3) < '5.4')
{
echo "<h2>ALERT: The plugin requires PHP version 5.4 or higher</h2>";
}

$cdnurl =  get_option("WPAdmin_CDN_URL"); 
if($cdnurl == "Blank") $cdnurl = "";	

$cdnminttl = 0;
$cdnmaxttl = 0;
if($cdnminttl == "" || $cdnminttl == 0) $cdnminttl = 3600;
if($cdnmaxttl == "" || $cdnmaxttl == 0) $cdnmaxttl = 86400;
if(isset($_POST['senddebuglog']))
{
$from =  "awsplugin@" . $_SERVER['HTTP_HOST']; 
$to = "support@wpadmin.ca";
$sub = "Debug Log from " . $_SERVER['HTTP_HOST'];
$msg = $_REQUEST['wpadebuglog'];
$replyto =  get_bloginfo(admin_email);
$headers = 'From: DoNotReply@' . $_SERVER['HTTP_HOST'] . "\r\n" . 'Reply-To: ' . $replyto . "\r\n" . 'X-Mailer: PHP/' . phpversion();
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
if(wp_mail($to,$sub,$msg,$headers))
{
echo "<div class='notice notice-success is-dismissible'><h3>Debug log sent to the developer</h3></div>";
}
else
{
echo "<div class='notice notice-warning is-dismissible'><h3>Failed to send the debug log to developer</h3></div>";
}
}
?>
<div id=wpaac_WPAresult></div>
<div id=wpaac_WPAResult></div>
<div class=container-fluid>
<div class=row>

<?php
if(file_exists(wpaacDOCOLD_ROOT . $wpa_domain . ".txt"))
{
copy(wpaacDOCOLD_ROOT . $wpa_domain . ".txt",wpaacDOC_ROOT . $wpa_domain . ".txt");	
unlink(wpaacDOCOLD_ROOT . $wpa_domain . ".txt");
}
if(file_exists(wpaacDOC_ROOT . $wpa_domain . $wpa_subfolder . ".txt"))
{
	
if ( (is_multisite()  && is_super_admin()) || is_admin() )
{
?>
<div class=col-sm-12>
<h3>Activate Cloudfront on <?php echo $wpa_domain; ?></h3>
<input type=text id=wpaac_cdnurl class=form-control placeholder="CDN Domain" value="<?php echo $cdnurl; ?>" data-val="<?php echo $cdnurl; ?>">
<?php
$cdn_details = file_get_contents(wpaacDOC_ROOT . $wpa_domain . $wpa_subfolder . ".txt");
$wpa_notice = substr($cdn_details,strpos($cdn_details,"<wpa_notice>"));
$wpa_notice = substr($wpa_notice,0,strpos($wpa_notice,"</wpa_notice>"));

$cdn_details = substr($cdn_details,0,stripos($cdn_details,"cloudfront.net")+14);
$cdn_details = substr($cdn_details,strrpos($cdn_details,"<b>")+3);
echo '<div class=col-sm-6><p><button class="retval btn btn-success formcontrol" href="JavaScript:void(0);">Activate ' . $cdn_details . '</button></p></div><div class=col-sm-6><p><button class="retval btn btn-success formcontrol" href="JavaScript:void(0);">Activate cdn.' . $wpa_domain . '</button></p></div><div class=col-sm-6><p><button class="retval btn btn-success formcontrol" href="JavaScript:void(0);">Disable CDN</button></p></div><div class="col col-sm-4">';
}

if ( is_multisite()  && is_super_admin()) 
{
?>
<select id=wpaac_cdnorigin class=form-control >
<?php	
$wpasites = get_sites();
foreach($wpasites as $key => $wpasite)
{
echo "<option value=\"".$wpasite->domain.$wpasite->path."\">".$wpasite->domain.$wpasite->path."</option>";
}
?>
</select>
<?php 
}
else
{
?>
<input READONLY type=text id=wpaac_cdnorigin class=form-control placeholder="Domain Name" value="<?php echo $wpa_domain; ?>">	
<?php
}
?>

</div>
<?php
if ( (is_multisite()  && is_super_admin()) || is_admin() )
{
echo '<div class=col-sm-2><button id=wpaac_resetcdn class="btn btn-danger formcontrol">Reset Configuration</button></div>';
}

if ( (is_multisite()  && is_super_admin()) || is_admin() ) 
{
echo '<div style="clear:both"></div><div class=col-sm-6>'. $wpa_notice .'</div><div class=col-sm-6>[ <a target=_BLANK href="http://' . $cdn_details . '">Test ' . $cdn_details . '</a> ] &nbsp;  [ <a target=_BLANK href="http://cdn.' . $wpa_domain . '">Test cdn.' . $wpa_domain . '</a> ]';
echo "<p>&nbsp;</p></div>";
}
?>
<div style='clear:both;'></div>
<div class=row>
<div class=col-sm-4>
<h3>Do Not Load These File Types From CDN</h3>
<div class=col-sm-4>
<input <?php echo get_option('WPAdmin_CDN_JS'); ?> type=checkbox id=wpaac_cdnjs> Javascript
</div>
<div class=col-sm-4>
<input <?php echo get_option('WPAdmin_CDN_SS'); ?>  type=checkbox id=wpaac_cdnss> Stylesheets
</div>
<div class=col-sm-6>
<br>
<input type=button id=wpaac_cdnse class='btn btn-success formcontrol' value='Save Settings'>
</div>
</div>
<div class=col-sm-8>
<h3>Do Not Load These Files From CDN [One entry per line]</h3>
<?php
$serverproto = "http";
if(@$_SERVER['HTTPS'] == "on") $serverproto = "https";
$wpaac_cdnignorelist = "";
if(get_option('WPAdmin_CDN_Ignorelist')){
$wpaac_cdnignorelist = get_option('WPAdmin_CDN_Ignorelist');
$wpaac_cdnignorelist = str_replace("~wpa~",PHP_EOL,$wpaac_cdnignorelist);
if($wpaac_cdnignorelist == "Blank") $wpaac_cdnignorelist ="";
}
?>
<textarea class='form-control textarea' style='min-height:150px;' id=wpaac_cdnignorelist name=wpaac_cdnignorelist placeholder='<?php echo $serverproto . "://" . $wpa_domain; ?>/wp-content/plugins/aws-cdn-by-wpadmin/admin/asset/css/bootstrap.min.css'><?php echo $wpaac_cdnignorelist; ?></textarea>
</div>
</div>
<div style='clear:both;'></div>
<?php
}
else
{
?>




<div class='col col-sm-12'>
<div style="clear:both"></div>
<h3>Setup Cloudfront Distribution</h3>
<div class='col col-sm-6'>
<b>Access key ID:</b><br>
<input type=text id=wpaac_cdnak class=form-control placeholder="Access Key ID" value="">	
</div>
<div class='col col-sm-6'>
<b>Secret Key:</b><br>
<input type=text id=wpaac_cdnsk class=form-control placeholder="Secret Key" value="">	
</div>
<div class='col col-sm-4'>
<b> Min Cache Time in seconds:</b><br>
<input type=number min=0 max=86400 step=60  id=wpaac_cdnminttl class=form-control placeholder="Minimum TTL" value="<?php echo $cdnminttl ; ?>">
</div>
<div class='col col-sm-4'>
<b> Max Cache Time  in seconds:</b><br>
<input type=number min=3600 max=2592000  step=60 id=wpaac_cdnmaxttl class=form-control placeholder="Maximum TTL" value="<?php echo $cdnmaxttl ; ?>">
</div>
<div class='col col-sm-4'>
<b>Price Class:</b><br>
<select id=wpaac_cdnprice class=form-control placeholder="Price Class">	
<option value='PriceClass_100'>US, Canada and Europe</option>
<option value='PriceClass_200'>US, Canada, Europe & Asia</option>
<option value='PriceClass_All'>All Locations</option>
</select>
</div>
<div class='col col-sm-4'>
<b>Domain Name:</b><br>
<?php
if ( is_multisite()  && is_super_admin() ) 
{
?>
<select id=wpaac_cdnorigin class=form-control >
<?php	
$wpasites = get_sites();
foreach($wpasites as $key => $wpasite)
{
echo "<option ";
if($_SERVER['HTTP_HOST'] == $wpasite->domain) echo " SELECTED ";
echo " value=\"".$wpasite->domain;
if($wpasite->path != "/") echo $wpasite->path;
echo "\">".$wpasite->domain;
if($wpasite->path != "/") echo $wpasite->path;
echo "</option>";
}
?>
</select>
<?php 
}
else
{
?>
<input type=text id=wpaac_cdnorigin class=form-control placeholder="Domain Name" value="<?php echo $wpa_domain; ?>">	
<?php
}
?>
</div>
<div class='col col-sm-8'>
<?php
if(get_option('WPAdmin_CDN_AltDomain')){
$wpa_cdn_altdomain = get_option('WPAdmin_CDN_AltDomain');
if($wpa_cdn_altdomain == "Blank") $wpa_cdn_altdomain = "";
}
else
{
$wpa_cdn_altdomain = $wpa_subfolder;
}

if($wpa_subfolder == "")
{
echo "<b>It seems like your site isn't hosted in a sub-folder. if incorrect, add the sub-folder name in the text field.</b><br>";
echo "<input type=text class=form-control id=wpaac_cdn_altdomain_na name=wpaac_cdn_altdomain_na placeholder='' data-val='$wpa_cdn_altdomain' value=''>";
}
else
{
echo "<b>It seems like your site is hosted in a sub-folder. If incorrect, clear the text field.</b><br>";
echo "<input type=text class=form-control id=wpaac_cdn_altdomain name=wpaac_cdn_altdomain placeholder='$wpa_subfolder' data-val='$wpa_cdn_altdomain' value='$wpa_subfolder'>";
}
?>
</div>
<div class='col col-12'>
<div style='clear:both'></div>
<p>
<input type=checkbox id=wpaac_usecdn name=wpaac_usecdn> &nbsp; Instead of Amazon Cloudfront Domain name, I would like to use my custom domain: <b>cdn.<?php echo $wpa_domain;?></b>
<div id=usecdn_notice style='display:none'>
<?php 
echo "<p><b>NOTE</b>: This feature needs an SSL certificate. The plugin will request a <b>Free</b> certificate from <a href='https://aws.amazon.com/certificate-manager/pricing/' target=_BLANK>Amazon Certificate Manager (ACM)</a>.</p><p>A verification email will be sent to these email addresses: <li>admin@$wpa_domain</li><li>administrator@$wpa_domain</li><li>hostmaster@$wpa_domain</li><li>postmaster@$wpa_domain</li><li>webmaster@$wpa_domain</li></p><p>Please ensure you have any one of these emails address / alias configured.</p>";
?>
</p>
</div>
</div>
<div class=col-sm-4>
<button id=wpaac_deployAWSCDN class='btn btn-info form-control'>Create Distribution</button>
</div>
<div class=col-sm-4>
<button id=wpaac_modifyAWSCDN class='btn btn-info form-control'>Modify Distribution</button>
</div>
<div class=col-sm-4>
<button id=wpaac_listAWSCDN class='btn btn-info form-control'>List Distribution</button>
</div>
<div class='col-sm-3 hidden'>
<button id=wpaac_renewcert class='btn btn-warning form-control'>Renew Certificate</button>
</div>
<?php
}
?>
<div style='clear:both'></div>
</div>



<div class=col-sm-12>
<h3>Howto</h3>
<div class="tab-pane active" id="tab1">
<p>Setup CloudFront</p>
<ol>
<li>Setup your AWS Account @ <a href='http://aws.amazon.com/' target=_BLANK>aws.amazon.com</a></li>
<li>Refer to <a href='https://wpadmin.ca/how-to-create-an-aws-user-with-limited-permissions-to-access-cloudfront-only/' target=_BLANK>this article</a> to setup the correct permissions</li>
<li>Retrieve the <i>Access Key ID</i> & <i>Secret Key</i></li>
<li>Enter the <i>Access Key ID</i> & <i>Secret Key</i> in the respective input boxes on the left</li>
<li>The domain name is automatically listed, change if required</li>
<li>Select the <u>Price Class</u> (AWS charges may vary depending on your selection)</li>
<li>Click the <u>Create AWS Distribution</u> button</li>
<li>Wait for AWS to setup the Distribtuion. Check the progress by clicking the <u>List AWS Distribution</u> button</li>
<li>Enter the AWS assigned sub domain (<i>E.G: <small>rAnd0mChA6s.cloudfront.net</small></i>) in the <u>CDN DOMAIN / CNAME</u> box</li>
</ol>
<p>Disable Cloudfront Temporarily</p>
<ol>
<li>Click the <u>Disable CDN</u> button</li>
<li>Clear cache if you are using any caching plugin</li>
<li>Visit <a href='http://aws.amazon.com/' target=_BLANK>aws.amazon.com</a>, <i>Disable</i> the Distribution</li>
</ol>
<p>Re-enable Cloudfront</p>
<ol>
<li>Visit <a href='http://aws.amazon.com/' target=_BLANK>aws.amazon.com</a>, <i>Enable</i> the Distribution</li>
<li>Click the <b>Activate rAnd0mChA6s.cloudfront.net</b> OR <b>Activate cdn.<?php echo $wpa_domain;?></b> button</li>
<li>Clear cache if you are using any caching plugin</li>
</ol>
<p>Delete Cloudfront Setup</p>
<ol>
<li>Visit <a href='http://aws.amazon.com/' target=_BLANK>aws.amazon.com</a>, <i>Disable the Cloudfront distribution</i></li><li>Click the <u>Reset Configuration</u> button</li>
</i> and then <i>Delete</i> the Distribution</li>
</ol>
</p>
</div>
<div class="tab-pane" id="tab2">
<h3>FAQ</h3>
<p>
<dl>
<dt>How does the plugin work?</dt>
<dd>The plugin replaces the domain name on all static assets (images, scripts, stylesheets,etc) in the wp-content & wp-includes folder.</dd>
<dt>Does this plugin support WordPress Multisite Setup?</dt>
<dd>Yes, it does. If you have setup the multisite correctly and the <b>Domain Name:</b> field in STEP 1 shows a FQDN (Fully Qualified Domain Name), the plugin should work just fine.</dd>
<dt>Where are the AWS Access Key ID and Secret Key Stored?</dt>
<dd>The AWS Access Key ID and Secret Key is only used to communicate with AWS.Amazon.com and are not stored. It is your responsibility to keep them safe - do not share them with anyone.</dd>
<dt>I upgraded from a previous version, how do I fix CORS issue?</dt>
<dd>Version 1.3.7 has the code to fix CORS issue. Re-enter your <b>Access Key ID</b> & <b>Secret Key</b>, then click <u>Modify AWS Distribution</u>.</dd>
<dt>What does the <u>Reset Configuration</u> button do?</dt>
<dd>When you click the <u>Create AWS Distribution</u>. button, it checks if the file <b><?php echo $wpa_domain;?>.txt</b> exists in the plugin folder.<br>
This stops the plugin from sending a duplicate request to Amazon (which will be declined anyway).<br>
The <u>Reset Configuration</u> button only deletes this file <br>
The CDN setup does not rely on this file.</dd>
<dt>What is stored in the <b><?php echo $wpa_domain;?>.txt</b> file?</dt>
<dd>The message you see after a successful request to Amazon to create a Distribution is stored in the <b><?php echo $wpa_domain;?>.txt</b> file</dd>
<dt>Can I use any other CDN?</dt>
<dd>Although not tested, if you have the domain name from any other CDN, Enter it in the <u>CDN DOMAIN / CNAME</u> box & it should work</dd>
<dt> What content is moved to AWS CDN</dt>
<dd> All Static files and images are moved to AWS CDN. There have been cases where some contents failed, please send me an email to report such issues</dd>
<dt> Can I  edit what goes and what does not?</dt>
<dd> Unfortunately, the plugin does not support granular control over contents that can be moved to AWS CDN</dd>
<dt>Is there a way to flush the CDN</dt>
<dd>Amazon refers this to '<b>Invalidation</b>' and charges for any invalidation requests. The easiest way is to rename the file or add a version tag</dd>
<dt>What if I have a few Questions?</dt>
<dd>Visit  <a href='http://wpadmin.ca?utm_source=Websites&utm_medium=WordPress&utm_campaign=WordPressCDNPlugin' target=_BLANK>WPAdmin.ca</a>, Chat with me If I am online or Leave a Message using the <a href='http://wpadmin.ca/contact-us/?utm_source=Websites&utm_medium=WordPress&utm_campaign=WordPressCDNPlugin' target=_BLANK>contact form</a>  </dd>
<dt>I don't get a response while trying to setup CDN</dt>
<dd>The plugin needs <em>php-xml</em> to process requests. This module is enabled by most hosting serivce providers. If you are using your own cloud server, please ensure the module is enabled on your server. </dd>
<dt>I want to buy you a coffee?</dt>
<dd>Thanks! <a href='https://wpadmin.ca/donation/' target=_BLANK>Please click Here to buy me one ;)</a></dd>
<dt>How do I stop the <b>Notice</b> from being displayed?</dt>
<dd>The plugin is 100% free & the ability to remove the <B>Notice</B> is only provided to our generous Donors. Please make a <a href='https://wpadmin.ca/donation/' target=_BLANK>Donation</a> to support this plugin, send us an email and we will provide you with your unique <B>Donation ID</b> to remove the <B>Notice</b> </a></dd>
</dl>
</p>
</div>
</div>
</div>
</div>
<script>
jQuery(document).ready(function(){

jQuery("#wpaac_cdnse").click(function(){
var wpaac_cdnjs = "unchecked";
var wpaac_cdnss = "unchecked";
if(jQuery("#wpaac_cdnjs").is(':checked')) wpaac_cdnjs = "checked";;
if(jQuery("#wpaac_cdnss").is(':checked')) wpaac_cdnss = "checked";;
jQuery.ajax({
url: ajaxurl,
data: {
'action':'wpaac_exclude_types',
'wpaac_cdnjs' : wpaac_cdnjs,
'wpaac_cdnss' : wpaac_cdnss
},
success:function(data) {
jQuery("#wpaac_WPAresult").html(data);
},
error: function(errorThrown){
jQuery("#wpaac_WPAresult").html(errorThrown);
}
});

});

jQuery("#wpaac_usecdn").click(function(){
if(jQuery("#wpaac_usecdn").is(':checked'))
{
jQuery("#usecdn_notice").show();
}
else{
jQuery("#usecdn_notice").hide();
}
});

jQuery(".retval").live('click',function(){
var cdndomain = jQuery(this).text();
cdndomain = cdndomain.replace('Activate ','');
cdndomain = cdndomain.replace('Disable CDN','');
jQuery("#wpaac_cdnurl").val(cdndomain).focus();	
jQuery("#wpaac_cdnurl").trigger('blur');
});

jQuery("#wpaac_renewcert").click(function(){
var ak = jQuery("#wpaac_cdnak").val();
jQuery("#wpaac_cdnak").focus();
if(ak == "") return;
var sk = jQuery("#wpaac_cdnsk").val();
jQuery("#wpaac_cdnsk").focus();
if(sk == "") return;
var ori = jQuery("#wpaac_cdnorigin").val();
jQuery.ajax({
url: ajaxurl,
data: {
'action':'wpaac_renew_cert',
'wpaac_ak' : ak,
'wpaac_sk' : sk,
'wpaac_ori' : ori
},
success:function(data) {
jQuery("#wpaac_WPAresult").html(data);
},
error: function(errorThrown){
jQuery("#wpaac_WPAresult").html(errorThrown);
}
});

});

jQuery("#wpaac_validate").live('click',function(){
var ak = jQuery("#wpaac_cdnak").val();
jQuery("#wpaac_cdnak").focus();
if(ak == "") return;
var sk = jQuery("#wpaac_cdnsk").val();
jQuery("#wpaac_cdnsk").focus();
if(sk == "") return;
var ori = jQuery("#wpaac_cdnorigin").val();
jQuery.ajax({
url: ajaxurl,
data: {
'action':'wpaac_validation_email',
'wpaac_ak' : ak,
'wpaac_sk' : sk,
'wpaac_ori' : ori
},
success:function(data) {
jQuery("#wpaac_WPAresult").html(data);
},
error: function(errorThrown){
jQuery("#wpaac_WPAresult").html(errorThrown);
}
});

});

jQuery("#wpaac_listAWSCDN").click(function(){
var ak = jQuery("#wpaac_cdnak").val();
jQuery("#wpaac_cdnak").focus();
if(ak == "") return;
var sk = jQuery("#wpaac_cdnsk").val();
jQuery("#wpaac_cdnsk").focus();
if(sk == "") return;
var ori = jQuery("#wpaac_cdnorigin").val();
var ap = jQuery("#wpaac_cdnprice").val();
jQuery("#wpaac_listAWSCDN").focus();
jQuery.ajax({
url: ajaxurl,
data: {
'action':'wpaac_list_cdn',
'wpaac_ak' : ak,
'wpaac_sk' : sk,
'wpaac_ori' : ori
},
success:function(data) {
jQuery("#wpaac_WPAresult").html(data);
},
error: function(errorThrown){
jQuery("#wpaac_WPAresult").html(errorThrown);
}
});	
});
jQuery("#wpaac_resetcdn").click(function(){
var ori = jQuery("#wpaac_cdnorigin").val();
jQuery.ajax({
url: ajaxurl,
data: {
'action':'wpaac_reset_cdn',
'wpaac_ori' : ori
},
success:function(data) {
jQuery("#wpaac_WPAresult").html(data);
},
error: function(errorThrown){
jQuery("#wpaac_WPAresult").html(errorThrown);
}
});	
});
jQuery("#wpaac_modifyAWSCDN").click(function(){	
var ak = jQuery("#wpaac_cdnak").val();
jQuery("#wpaac_cdnak").focus();
if(ak == "") return;
var sk = jQuery("#wpaac_cdnsk").val();
jQuery("#wpaac_cdnsk").focus();
if(jQuery("#wpaac_usecdn").is(':checked'))
{
var wpaac_usecdn = "yes";
}
else
{
var wpaac_usecdn = "";
}
if(sk == "") return;
var ori = jQuery("#wpaac_cdnorigin").val();
var subfolder = jQuery("#wpaac_cdn_altdomain").val();
var ap = jQuery("#wpaac_cdnprice").val();
var minttl = jQuery("#wpaac_cdnminttl").val();
var maxttl = jQuery("#wpaac_cdnmaxttl").val();
jQuery("#wpaac_modifyAWSCDN").focus();
jQuery.ajax({
url: ajaxurl,
data: {
'action':'wpaac_modify_cdn',
'wpaac_ak' : ak,
'wpaac_sk' : sk,
'wpaac_ap' : ap,
'wpaac_ori' : ori,
'wpaac_subfolder' : subfolder,
'wpaac_usecdn' : wpaac_usecdn,
'wpaac_minttl' : minttl,
'wpaac_maxttl' : maxttl
},
success:function(data) {
jQuery("#wpaac_WPAresult").html(data);
},
error: function(errorThrown){
jQuery("#wpaac_WPAresult").html(errorThrown);
}
});
});
jQuery("#wpaac_deployAWSCDN").click(function(){

if(jQuery("#wpaac_usecdn").is(':checked'))
{
var wpaac_usecdn = "yes";
}
else
{
var wpaac_usecdn = "";
}

var ak = jQuery("#wpaac_cdnak").val();
jQuery("#wpaac_cdnak").focus();
if(ak == "") return;
var sk = jQuery("#wpaac_cdnsk").val();
jQuery("#wpaac_cdnsk").focus();
if(sk == "") return;
var ori = jQuery("#wpaac_cdnorigin").val();
var ap = jQuery("#wpaac_cdnprice").val();
var minttl = jQuery("#wpaac_cdnminttl").val();
var maxttl = jQuery("#wpaac_cdnmaxttl").val();
var subfolder = jQuery("#wpaac_cdn_altdomain").val();
jQuery("#wpaac_deployAWSCDN").focus();
jQuery("#wpaac_WPAresult").html("<div class='alert alert-info'>Please Wait While The Distribution is Being Created.</div>");
jQuery.ajax({
url: ajaxurl,
data: {
'action':'wpaac_deploy_cdn',
'wpaac_ak' : ak,
'wpaac_sk' : sk,
'wpaac_ap' : ap,
'wpaac_ori' : ori,
'wpaac_subfolder' : subfolder,
'wpaac_usecdn' : wpaac_usecdn,
'wpaac_minttl' : minttl,
'wpaac_maxttl' : maxttl
},
success:function(data) {
jQuery("#wpaac_WPAresult").html(data);
},
error: function(errorThrown){
jQuery("#wpaac_WPAresult").html(errorThrown);
}
});
});

jQuery("#wpaac_cdn_altdomain").blur(function(){
var wpaac_cdn_altdomain = jQuery(this).val();
var wpaac_cdn_altdomain_ori = jQuery(this).attr('data-val');
if(wpaac_cdn_altdomain == wpaac_cdn_altdomain_ori) return;
jQuery.ajax({
url: ajaxurl,
data: {
'action':'wpaac_alt_domain',
'wpaac_cdn_altdomain' : wpaac_cdn_altdomain
},
success:function(data) {
jQuery("#wpaac_WPAresult").html(data);
},
error: function(errorThrown){
jQuery("#wpaac_WPAresult").html(errorThrown);
}
});
});


jQuery("#wpaac_cdnignorelist").blur(function(){
var wpaac_cdnignorelist = jQuery(this).val();
jQuery.ajax({
url: ajaxurl,
data: {
'action':'wpaac_ignore_list',
'wpaac_cdnignorelist' : wpaac_cdnignorelist
},
success:function(data) {
jQuery("#wpaac_WPAresult").html(data);
},
error: function(errorThrown){
jQuery("#wpaac_WPAresult").html(errorThrown);
}
});
});
jQuery("#wpaac_cdnurl").blur(function(){
var cdnurl = jQuery(this).val();
var ori_cdnurl = jQuery(this).attr('data-val');
if(cdnurl == ori_cdnurl) return;
jQuery.ajax({
url: ajaxurl,
data: {
'action':'wpaac_set_cdn',
'wpaac_cdnurl' : cdnurl
},
success:function(data) {
jQuery("#wpaac_WPAresult").html(data);
},
error: function(errorThrown){
jQuery("#wpaac_WPAresult").html(errorThrown);
}
});
});
});
</script>