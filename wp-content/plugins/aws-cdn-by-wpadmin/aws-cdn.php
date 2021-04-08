<?php
/*
Plugin Name: Amazon AWS CDN 
Plugin URI: http://wpadmin.ca/amazon-cloudfront-cdn/
Description: Setting up Amazon CloudFront Distribution can’t get any simple. Use Amazon Cloudfront as a <acronym title='Content Delivery Network'>CDN</acronym> for your WordPress Site. Create per site distribution for Multi-site setup. Let us know what features would you like to have in this plugin.
Author: WPAdmin
Version: 1.5.4
Author URI: https://wpadmin.ca
*/


if ( ! defined( 'wpaacbasedir' ) ) define( 'wpaacbasedir', plugin_dir_path( __FILE__ ) );
if ( ! defined( 'wpaacPLUGIN_BASENAME' ) ) define( 'wpaacPLUGIN_BASENAME', plugin_basename( __FILE__ ) );
if ( ! defined( 'wpaacPLUGIN_DIRNAME' ) ) define( 'wpaacPLUGIN_DIRNAME', dirname( wpaacPLUGIN_BASENAME ) );
if ( ! defined( 'wpaacPLUGIN_URL' ) ) define( 'wpaacPLUGIN_URL', plugin_dir_url( __FILE__ ) );
if ( ! defined( 'wpaacDOCOLD_ROOT' ) ) define( 'wpaacDOCOLD_ROOT', $_SERVER['DOCUMENT_ROOT'] . "/" );
$wpaac_upload_dir   = wp_upload_dir();
if ( ! defined( 'wpaacDOC_ROOT' ) ) define( 'wpaacDOC_ROOT', $wpaac_upload_dir['basedir'] . "/" );


/*ini_set('log_errors', 1);	
error_reporting(E_ALL);
$wpaws3_logpath = wpaacbasedir . 'debug.log';
ini_set('error_log', $wpaws3_logpath);*/
$autoload = true;
/*start class*/
class wpaawscdn
{
public function __construct(){
add_action('wp_footer', 'comment_in_footer');
function comment_in_footer(){
echo "<!--Amazon AWS CDN Plugin. Powered by WPAdmin.ca-->";
}
function wpadmin_add_settings_link( $links ) {
$feature_link = '<a target=_BLANK href="https://wpadmin.ca/#contact">' . __( 'Feature Request' ) . '</a>';
array_unshift( $links, $feature_link );
$forum_link = '<a target=_BLANK href="https://wordpress.org/support/plugin/aws-cdn-by-wpadmin/">' . __( 'Support' ) . '</a>';
array_unshift( $links, $forum_link );
$review_link = '<a target=_BLANK href="https://wordpress.org/support/plugin/aws-cdn-by-wpadmin/reviews/#new-post">' . __( 'Review' ) . '</a>';
array_unshift( $links, $review_link );
$donate_link = '<a target=_BLANK href="https://wpadmin.ca/donation/">' . __( '<b><i>Buy Me a Coffee</i></b>' ) . '</a>';
array_unshift( $links, $donate_link );
$settings_link = '<a href="admin.php?page=wpa-aws-cloudfront">' . __( 'Setup' ) . '</a>';
array_unshift( $links, $settings_link );
return $links;
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'wpadmin_add_settings_link' );
function wpadmin_cdn_notice() {
$wpa_donor_id = get_option('WPAdmin_CDN_KEY');
if($wpa_donor_id == NULL)
{
?>
<br>
<?php	global $pagenow; 
if($pagenow == "plugins.php" )
{
?>
<div class="notice" style='background:#efefef'>
<b>Amazon AWS CDN</b>: <a href='https://PayPal.Me/GraySquareSolutions' target=_BLANK>Support this plugin</a>
</div>
<?php
}
}
}
add_action( 'admin_notices', 'wpadmin_cdn_notice' );
}
public function load()
{
register_activation_hook( __FILE__, array(&$this,'wpaawscdn_activate') );
add_action("admin_menu", array(&$this,'WPAcdnMenus'));
}
public function wpaawscdn_activate() {
}
function WPAcdnMenus() {
$wpa_current_user = wp_get_current_user();
if($wpa_current_user->caps['administrator'])
{
add_menu_page("WPAdmin AWS CDN", "WPAdmin AWS CDN", 'edit_pages', "wpa-aws-cloudfront", array($this,'wpaactoplevel_page'),'dashicons-performance');
if(is_multisite())
{
add_submenu_page("wpa-aws-cloudfront","Multi-site Setup", "Multi-site Setup",'edit_pages',"multisite-aws-cdn",array($this,'wpaacsublevel_page'));
}
add_submenu_page("wpa-aws-cloudfront","Registration", "Registration",'edit_pages',"aws-cdn-registration",array($this,'wpaacsublevel1_page'));}
}
function wpaactoplevel_page() {
echo "<p><h2>" . __( 'WPAdmin AWS CDN Setup', 'wpaac_menu' ) . "</h2><p>";
require_once(wpaacbasedir . "admin/wpa-aws-cdn-admin.php");
}
function wpaacsublevel_page() {
if(get_current_blog_id()  == 1 && is_super_admin())
{
echo "<p><h2>" . __( 'WPAdmin AWS CDN Multi-site Setup', 'wpaac_menu' ) . "</h2><p>";
require_once(wpaacbasedir . "admin/wpa-aws-multisite-cdn-admin.php");
}
else
{
echo "<br><div class='notice notice-error is-dismissible'><h2>Error!!!</h2><P>Only <i>Super Admin</i> can manage Multisite setup on the <i>Primary</i> site.</p></div>";	
}
}
function wpaacsublevel1_page() {
if(get_current_blog_id()  == 1 && is_super_admin())
{
echo "<p><h2>" . __( 'WPAdmin AWS CDN - Donor Registration', 'wpaac_menu' ) . "</h2><p>";
require_once(wpaacbasedir . "admin/wpa-aws-multisite-cdn-registration.php");
}
else
{
echo "<br><div class='notice notice-error is-dismissible'><h2>Error!!!</h2><P>Only <i>Super Admin</i> can manage registration on the <i>Primary</i> site.</p></div>";	
}
}
function wpaac_reset_cdn(){
if ( isset($_REQUEST) ) {
@$wpaac_ori = sanitize_text_field($_REQUEST['wpaac_ori']);

$wpafilename = wpaacDOC_ROOT . $wpaac_ori . ".txt";

if(file_exists($wpafilename))
{
if(unlink($wpafilename))
{
$wpaac_cdnurl = "Blank";
update_option('WPAdmin_CDN_URL', $wpaac_cdnurl, $autoload);	
echo  "<div class='alert alert-warning'>CDN Reset Completed.<p> <a href='./admin.php?page=wpa-aws-cloudfront'>Reload the page</a></p></div>";
}
else
{
echo  "<div class='alert alert-danger'>Unable to reset CDN.<p> Failed to delete $wpafilename</p></div>";	
}
}	
}	
wp_die();
}
function wpaac_modify_cdn(){
if ( isset($_REQUEST) ) {
@$wpaac_ak = $_REQUEST['wpaac_ak'];
@$wpaac_sk = $_REQUEST['wpaac_sk'];
@$wpaac_ap = $_REQUEST['wpaac_ap'];
@$wpaac_ori = sanitize_text_field($_REQUEST['wpaac_ori']);
@$wpaac_subfolder = sanitize_text_field($_REQUEST['wpaac_subfolder']);
@$wpaac_usecdn = $_REQUEST['wpaac_usecdn'];
@$wpaac_minttl = $_REQUEST['wpaac_minttl'];
@$wpaac_maxttl = $_REQUEST['wpaac_maxttl'];

$wpafilename = wpaacDOC_ROOT . $wpaac_ori . $wpaac_subfolder . ".txt";

$barewww = str_replace("www.","",$wpaac_ori);
$nowww = 'cdn.' . str_replace("www.","",$wpaac_ori);
$wpaac_client = $this->wpaac_authClient($wpaac_ak,$wpaac_sk);

if(file_exists($wpafilename))
{
$wpaawscdnurl = file_get_contents($wpafilename);
preg_match_all('#<awsid[^>]*>(.*?)</awsid>#', $wpaawscdnurl, $awsid);
preg_match_all('#<awsdomain[^>]*>(.*?)</awsdomain>#', $wpaawscdnurl, $awsdomain);
$wpa_did = trim(strip_tags($awsid[0][0]));
$wpa_domain = trim(strip_tags($awsdomain[0][0]));
}
else
{
echo "<div class='alert alert-warning'><h3>Distribution not found!!!</h3><p> Click the <i>List Distribution</i> button to verify.</p></div>";
wp_die();
}
if($wpa_did == NULL)
{
echo "<div class='alert alert-warning'><h3>Distribution not found!!!</h3><p> Click the <i>List Distribution</i> button to verify.</p></div>";
wp_die()	;
}

if($wpaac_usecdn == "yes")
{

/*check cetificate exists*/
$wpaac_certarn = $this->wpaac_checkcertificate($wpaac_ak,$wpaac_sk,$barewww);
$wpaaccertexists = $wpaac_certarn['exists'];
$wpaaccertarn = $wpaac_certarn['certarn'];
/*request ACM certificate*/
$wpaac_cert = $this->wpaac_authCert($wpaac_ak,$wpaac_sk);
$wpa_certstat = $this->wpaac_checkcertificatestatus($wpaac_ak,$wpaac_sk,$wpaaccertexists,$barewww,$wpaaccertarn);
}
else
{
$wpa_certstat = "proceed";	
}
if($wpa_certstat != "proceed")
{
echo $wpa_certstat;
wp_die();
}
/*get distribtion*/
$preset = array('Id' => $wpa_did);
$presetresult = $wpaac_client->getDistribution($preset);
$presetresult = $presetresult->toArray();
$awsdomain = $presetresult['Distribution']['DomainName'];
$awsdomainid = $presetresult['Distribution']['Id'];
$wpa_etag = $presetresult["ETag"];
$presetresult = $presetresult["Distribution"]["DistributionConfig"];
unset($presetresult['ETag']);
/*echo "<pre>";
var_dump($presetresult);
echo "</pre*/

$presetresult['PriceClass'] = $wpaac_ap;
$presetresult["DefaultCacheBehavior"]["MinTTL"] = $wpaac_minttl;
$presetresult["DefaultCacheBehavior"]["MaxTTL"] = $wpaac_maxttl;
$presetresult["DefaultCacheBehavior"]["AllowedMethods"]["Quantity"] = 3;
$presetresult["DefaultCacheBehavior"]["AllowedMethods"]["Items"][0] = 'HEAD';
$presetresult["DefaultCacheBehavior"]["AllowedMethods"]["Items"][1] = 'GET';
$presetresult["DefaultCacheBehavior"]["AllowedMethods"]["Items"][2] = 'OPTIONS';
$presetresult["DefaultCacheBehavior"]["AllowedMethods"]["CachedMethods"]["Quantity"] = 3;
$presetresult["DefaultCacheBehavior"]["AllowedMethods"]["CachedMethods"]["Items"][0] = 'HEAD';
$presetresult["DefaultCacheBehavior"]["AllowedMethods"]["CachedMethods"]["Items"][1] = 'GET';
$presetresult["DefaultCacheBehavior"]["AllowedMethods"]["CachedMethods"]["Items"][2] = 'OPTIONS';
$presetresult["DefaultCacheBehavior"]["ForwardedValues"]["Headers"]["Quantity"] = 3;
$presetresult["DefaultCacheBehavior"]["ForwardedValues"]["Headers"]["Items"][0] = 'Access-Control-Request-Headers';
$presetresult["DefaultCacheBehavior"]["ForwardedValues"]["Headers"]["Items"][1] = 'Access-Control-Request-Method';
$presetresult["DefaultCacheBehavior"]["ForwardedValues"]["Headers"]["Items"][2] = 'Origin';
if($wpaac_subfolder == "")
{
$presetresult["Origins"]["Items"]["0"]["OriginPath"] = "";	
}
else
{
$presetresult["Origins"]["Items"]["0"]["OriginPath"] = "/" .  $wpaac_subfolder;
}

$presetresult["Origins"]["Items"][0]["CustomHeaders"]["Quantity"] = 1;
$presetresult["Origins"]["Items"][0]["CustomHeaders"]["Items"][0]["HeaderName"] = "Access-Control-Allow-Origin";
$presetresult["Origins"]["Items"][0]["CustomHeaders"]["Items"][0]["HeaderValue"] = '*';
if($wpaac_usecdn == "yes")
{

$presetresult["Aliases"]= array(
'Quantity' => 1,
'Items' => array($nowww),
);

if($wpaaccertexists == true)
{
$presetresult["ViewerCertificate"]["CloudFrontDefaultCertificate"] = false;
$presetresult["ViewerCertificate"] =  array(
'ACMCertificateArn' => $wpaaccertarn,
'SSLSupportMethod' => 'sni-only',
'MinimumProtocolVersion' => 'TLSv1.1_2016',
);
}
else
{
$wpaac_certid = "*." . $barewww; 
$presetresult["ViewerCertificate"] =  array(
'ACMCertificateArn' => $wpaaccertarn,
'SSLSupportMethod' => 'sni-only',
'MinimumProtocolVersion' => 'TLSv1.1_2016',
);
}
}
try{
$presetresult = array("DistributionConfig"=>$presetresult,
"Id" => $wpa_did,
"IfMatch" => $wpa_etag
);
$result = $wpaac_client->updateDistribution($presetresult);	
$awsdomain = $result['Distribution']['DomainName'];
if($wpaac_subfolder <> "")
{
/*$nowww = str_replace(substr($wpaac_ori,strpos($wpaac_ori,"/")),"",$nowww);*/
update_option('WPAdmin_CDN_AltDomain', $wpaac_subfolder, $autoload);
}

$retval =  "<div class='alert alert-success'><p>Successfully modified Cloudfront Distribution with ID # <awsid>$awsdomainid</awsid><br><b>IMP: Before performing the below mentioned steps</b><ol><li>Confirm that the status of the the <i>CloudFront Distribution</i> is <b>Deployed</b> in AWS Console</li><li>Ensure that you have modified the DNS (If you plan to use $nowww)</li></ol><hr><p>In the <U>CDN DOMAIN / CNAME</U> box</p><p>Add <a class=retval href='JavaScript:void(0);'><b>$awsdomain</b></a></p><p>OR<p>Add <a class=retval href='JavaScript:void(0);'><B>$nowww</B></a><br>[you will have to add a CNAME entry in your DNS pointing <B>$nowww</B> to <b><awsdomain>$awsdomain</awsdomain></b>]<p> <a href='./admin.php?page=wpa-aws-cloudfront'>Reload the page</a></p></div>";
file_put_contents($wpafilename,$retval);
}
catch (Exception $e) {
$err = $e->getMessage();
$er = explode("response",$err);
echo "<form method=POST>";
echo "<textarea id=wpadebuglog name=wpadebuglog style='display:none'><pre>";
var_dump($err);
var_dump($presetresult);
echo "</pre></textarea>";
echo "Error:" . $err . "";
echo "<p><input type=submit name=senddebuglog id=senddebuglog class='btn btn-primary' value='Send Debug Log to Developer'></form>";
}
echo $retval;
}
wp_die();	
}

function wpaac_renew_cert(){
if ( isset($_REQUEST) ) {
@$wpaac_ak = $_REQUEST['wpaac_ak'];
@$wpaac_sk = $_REQUEST['wpaac_sk'];
@$wpaac_ori = $_REQUEST['wpaac_ori'];
$barewww = str_replace("www.","",$wpaac_ori);
$wpaac_cert = $this->wpaac_authCert($wpaac_ak,$wpaac_sk);

$wpaac_cert = $this->wpaac_authCert($wpaac_ak,$wpaac_sk);

$result = $wpaac_cert->listCertificates();

$wpaac_allcerts =  $result["CertificateSummaryList"];	

$wpaac_certexists = false;
foreach($wpaac_allcerts as $wpaac_allcert)
{
if("*." . $barewww == $wpaac_allcert["DomainName"] || $barewww == $wpaac_allcert["DomainName"])
{
$wpaac_certexists = true;
$wpaac_certarn = $wpaac_allcert["CertificateArn"];
break;	
}
}

if($wpaac_certexists ==true)
{
$cert_preset = array(
'CertificateArn' => $wpaac_certarn, 
);
try
{
$result = $wpaac_cert->renewCertificate($cert_preset);
echo "<div class='notice notice-success'>Certificate Renewed Successfully.</div>";
}
catch  (Exception $e) {
echo "<pre>";
var_dump($e);	
echo "</pre>";
}
}	
}	
}

function wpaac_validation_email(){
if ( isset($_REQUEST) ) {
@$wpaac_ak = $_REQUEST['wpaac_ak'];
@$wpaac_sk = $_REQUEST['wpaac_sk'];
@$wpaac_ori = $_REQUEST['wpaac_ori'];
$barewww = str_replace("www.","",$wpaac_ori);
$wpaac_cert = $this->wpaac_authCert($wpaac_ak,$wpaac_sk);
$cert_preset = array(
'DomainName' => "*." . $barewww,
'ValidationMethod' => 'EMAIL',
);
$result = $wpaac_cert->requestCertificate($cert_preset);
$wpaac_certarn =  $result['CertificateArn'];

$cert_preset = array(
'CertificateArn' => $wpaac_certarn, 
'Domain' => "*." . $barewww,
'ValidationDomain' => "*." . $barewww, 
);
try
{
$result = $wpaac_cert->resendValidationEmail($cert_preset);
echo "<div class='notice notice-success'>An email has been sent to one of these addresses:<p>postmaster@$barewww, webmaster@$barewww, admin@$barewww, administrator@$barewww, hostmaster@$barewww.</p>Please check & approve the email from Amazon for <b>Certificate approval</b></div>";
}
catch  (Exception $e) {
echo "<pre>";
var_dump($e);	
echo "</pre>";
}	
}
wp_die();
}

function wpaac_checkcertificatestatus($wpaac_ak,$wpaac_sk,$wpaac_certexists,$barewww,$wpaac_certarn = NULL)
{
$wpaac_cert = $this->wpaac_authCert($wpaac_ak,$wpaac_sk);	


if($wpaac_certexists == false)
{
$cert_preset = array(
'DomainName' => "*." . $barewww,
'ValidationMethod' => 'EMAIL',
);
try{
$result = $wpaac_cert->requestCertificate($cert_preset);
$wpaac_certarn =  $result['CertificateArn'];
$result = $wpaac_cert->describeCertificate(array('CertificateArn' => $wpaac_certarn));
$wpaac_certstatus = $result["Certificate"]["DomainValidationOptions"][0]["ValidationStatus"];	

if($wpaac_certstatus <> "SUCCESS")
{
return "<div class='alert alert-success'>An email has been sent to one of these addresses: postmaster@$barewww, webmaster@$barewww, admin@$barewww, administrator@$barewww, hostmaster@$barewww.<br>Please check & approve the email from Amazon for <b>Certificate approval</b> <button id=wpaac_validate class='btn btn-warning form-control'>Re-send Validation Email</button></div>";	
}
}
catch (Exception $e) {
$err = $e->getMessage();
return $err;
}
}
else
{
/*check if cert is active*/	
$result = $wpaac_cert->describeCertificate(array('CertificateArn' => $wpaac_certarn));
$wpaac_certstatus = $result["Certificate"]["DomainValidationOptions"][0]["ValidationStatus"];
if($wpaac_certstatus <> "SUCCESS")
{
return "<div class='alert alert-warning'><p>Certificate not active yet. Please check & approve the email from Amazon for <b>Certificate approval</b></p> <button id=wpaac_validate class='btn btn-warning form-control'>Re-send Validation Email</button></div>";	
}
else
{
return "proceed";
}
}
}

function wpaac_checkcertificate($wpaac_ak,$wpaac_sk,$barewww)
{
$wpaac_cert = $this->wpaac_authCert($wpaac_ak,$wpaac_sk);	
$result = $wpaac_cert->listCertificates();
$wpaac_allcerts =  $result["CertificateSummaryList"];	
$retval['exists']= false;
$retval['certarn'] = "notfound";
foreach($wpaac_allcerts as $wpaac_allcert)
{
if("*." . $barewww == $wpaac_allcert["DomainName"] || $barewww == $wpaac_allcert["DomainName"])
{
$retval['exists'] = true;
$retval['certarn'] = $wpaac_allcert["CertificateArn"];
}
}
return $retval;	
}


function wpaac_deploy_cdn() {
if ( isset($_REQUEST) ) {
@$wpaac_ak = $_REQUEST['wpaac_ak'];
@$wpaac_sk = $_REQUEST['wpaac_sk'];
@$wpaac_ap = $_REQUEST['wpaac_ap'];
@$wpaac_usecdn = $_REQUEST['wpaac_usecdn'];
@$wpaac_ori = sanitize_text_field($_REQUEST['wpaac_ori']);
@$wpaac_subfolder = sanitize_text_field($_REQUEST['wpaac_subfolder']);
@$wpaac_minttl = $_REQUEST['wpaac_minttl'];
@$wpaac_maxttl = $_REQUEST['wpaac_maxttl'];

$wpafilename = wpaacDOC_ROOT . $wpaac_ori .  $wpaac_subfolder . ".txt";

if($wpaac_subfolder <> NULL)
{
$wpaac_subfolder = "/$wpaac_subfolder";
}

$barewww = str_replace("www.","",$wpaac_ori);
$nowww = 'cdn.' . $barewww;	

if(file_exists($wpafilename))
{
$retval = file_get_contents($wpafilename);	
}
else
{

if($wpaac_usecdn == "yes")
{
/*check cetificate exists*/
$wpaac_certarn = $this->wpaac_checkcertificate($wpaac_ak,$wpaac_sk,$barewww);

$wpaaccertexists = $wpaac_certarn['exists'];
$wpaaccertarn = $wpaac_certarn['certarn'];

/*request ACM certificate*/
$wpaac_cert = $this->wpaac_authCert($wpaac_ak,$wpaac_sk);
$wpa_certstat = $this->wpaac_checkcertificatestatus($wpaac_ak,$wpaac_sk,$wpaaccertexists,$barewww,$wpaaccertarn);
}
else
{
$wpa_certstat = "proceed";	
}
if($wpa_certstat != "proceed")
{
echo $wpa_certstat;
wp_die();
}
/*create CloudFront Distribution*/
$wpaac_client = $this->wpaac_authClient($wpaac_ak,$wpaac_sk);
$preset = array("DistributionConfig"=>array(
'CacheBehaviors' => array('Quantity' => 0),
'Comment' => 'Configured using AWS CDN plugin by WPAdmin.CA',
'Enabled' => true,
'CallerReference' => 'WPAdmin-' . time(),
'DefaultCacheBehavior' => array(
'AllowedMethods' => array(
'CachedMethods' => array(
'Items' => array('GET','HEAD','OPTIONS'), 
'Quantity' => 3,
),
'Items' => array('GET','HEAD','OPTIONS'), 
'Quantity' => 3,
),
'MinTTL' => $wpaac_minttl,
'MaxTTL' => $wpaac_maxttl,
'ViewerProtocolPolicy' => 'allow-all',
'TargetOriginId' => 'WPAdminOrigin',
'TrustedSigners' => array(
'Enabled'  => false,
'Quantity' => 0,
),
'ForwardedValues' => array(
'QueryString' => false,
'Cookies' => array(
'Forward' => 'none'
),
'Headers' => array(
'Quantity' => 3,
'Items' => array('Origin','Access-Control-Request-Headers','Access-Control-Request-Method')	
),
),
),
'DefaultRootObject' => '',
'Logging' => array(
'Enabled' => false,
'Bucket' => '',
'Prefix' => '',
'IncludeCookies' => true,
),
'Origins' => array(
'Quantity' => 1,
'Items' => array(
array(
'CustomHeaders' => array(
'Items' => array(
array(
'HeaderName' => 'Access-Control-Allow-Origin',
'HeaderValue' => '*',
),
),
'Quantity' => 1,
),
'Id' => 'WPAdminOrigin',
'DomainName' => $wpaac_ori,
'OriginPath' => $wpaac_subfolder,
'CustomOriginConfig' => array(
'HTTPPort' => 80,
'HTTPSPort' => 443,
'OriginProtocolPolicy' => 'match-viewer',
)
)
)
),
'PriceClass' => $wpaac_ap,
));

if($wpaac_usecdn == "yes")
{

$preset["DistributionConfig"]["Aliases"]= array(
'Quantity' => 1,
'Items' => array($nowww),
);

$wpaac_certarnx = explode("/",$wpaac_certarn);
$wpaac_certid = "*." . $barewww; 
$preset["DistributionConfig"]["ViewerCertificate"] =  array(
'ACMCertificateArn' => $wpaac_certarn['certarn'],
'SSLSupportMethod' => 'sni-only',
);
}

try{
$result = $wpaac_client->createDistribution($preset);	
$awsdomain = $result['Distribution']['DomainName'];
$awsdomainid = $result['Distribution']['Id'];

$retval =  "<div class='alert alert-success'><p>Successfully created Cloudfront Distribution with ID # <awsid>$awsdomainid</awsid><br><b>IMP: Before performing the below mentioned steps</b><br>Confirm that the status of the the <i>CloudFront Distribution</i> is <b>Deployed</b> in AWS Console. </p><hr><p>In the <U>CDN DOMAIN / CNAME</U> box, add <b>$awsdomain</b> OR <B>$nowww</B><br>If you plan to use custom domain name, you will have to add a CNAME entry in your DNS pointing <B>$nowww</B> to <b><awsdomain>$awsdomain</awsdomain></b></p><wpa_notice>Note: <b>" . $awsdomain  . "</b> points to <b>" . $wpaac_ori . $wpaac_subfolder . "</b></wpa_notice><p> <a href='./admin.php?page=wpa-aws-cloudfront'>Reload the page</a></p></div>";
file_put_contents($wpafilename,$retval);
}
catch (Exception $e) {
$err = $e->getMessage();
$er = explode("response",$err);
echo "<form method=POST>";
echo "<textarea id=wpadebuglog name=wpadebuglog style='display:none'>";
echo "USE CDN: " . $wpaac_usecdn . "---";
echo "USE Origin: " . $wpaac_ori . "---";
echo "USE Sub-folder: " . $wpaac_subfolder . "---";
var_dump($err);
echo "</textarea>";
echo "Error:" . $err . "";
echo "<p><input type=submit name=senddebuglog id=senddebuglog class='btn btn-primary' value='Request Help From The Developer'></form>";
}
}
echo $retval;
}
wp_die();
}
function wpaac_set_cdn() {
if ( isset($_REQUEST) ) {
@$wpaac_cdnurl = $_REQUEST['wpaac_cdnurl'];
if($wpaac_cdnurl == "") $wpaac_cdnurl = "Blank";
if($wpaac_cdnurl == "Blank")
{
echo  "<div class='alert alert-warning'>CDN Disabled</b></div>";	
if(get_option('WPAdmin_CDN_URL'))
{
update_option('WPAdmin_CDN_URL', $wpaac_cdnurl, $autoload);
}
else
{
add_option('WPAdmin_CDN_URL', $wpaac_cdnurl, $autoload);
}
}
else
{
$wpaac_ipaddress = gethostbyname($wpaac_cdnurl);
if($wpaac_ipaddress == $wpaac_cdnurl)
{
echo "<div class='alert alert-danger'>CDN <b>$wpaac_cdnurl</b> isn't ready yet<br>Check If the CDN is deployed<br>If you are using your custom domain, ensure that the DNS entry exists and has propagated</div>";
wp_die();
}
if(get_option('WPAdmin_CDN_URL'))
{
update_option('WPAdmin_CDN_URL', $wpaac_cdnurl, $autoload);
}
else
{
add_option('WPAdmin_CDN_URL', $wpaac_cdnurl, $autoload);
update_option('WPAdmin_CDN_URL', $wpaac_cdnurl, $autoload);
}
echo  "<div class='alert alert-info'>CDN Domain Changed To <b>$wpaac_cdnurl</b></div>";
}
}
wp_die();
}
function wpaac_enqueue_style() {
if ( ! wp_style_is( 'style', 'done' ) ) {
wp_deregister_style( 'style' );
wp_dequeue_style( 'style' );
$style_fp = get_stylesheet_directory() . '/style.css';
if ( file_exists($style_fp) ) {
wp_enqueue_style( 'style', get_stylesheet_uri() . '?' . filemtime( $style_fp ) );
}
}
}
function wpaac_replace_content($content)
{
if(stripos($_SERVER['REQUEST_URI'],".xml") == NULL && get_option("WPAdmin_CDN_URL") <> "Blank"  && get_option("WPAdmin_CDN_URL") <> NULL)
{
$wpa_domain = 	home_url();

/* fix relative URLS*/
if(is_string($content)) 
{
$content = preg_replace("/src=\\'([^\']+)\\'/", 'src="$1"', $content);
if( strpos($content,'src="/wp-content') )
{
$content = str_replace('src="/wp-content','src="'.$wpa_domain.'/wp-content', $content);
}
}
/* fix relative URLS*/

$wpa_domain = str_replace("http://","",$wpa_domain);
$wpa_domain = str_replace("https://","",$wpa_domain);

/*replace content link*/
/*$wpa_domain = $_SERVER['HTTP_HOST'];*/
$serverproto = "http";
if(@$_SERVER['HTTPS'] == "on") $serverproto = "https";
$wpa_ori_domain_wpi = "$serverproto://$wpa_domain/wp-includes/";
$wpa_ori_domain_wpc = "$serverproto://$wpa_domain/wp-content/";
$wpa_cdn_domain = NULL;

if(get_option('WPAdmin_CDN_AltDomain')) $wpa_cdn_domain = get_option('WPAdmin_CDN_AltDomain');

if($wpa_cdn_domain <> "Blank" && $wpa_cdn_domain <> NULL)
{
$wpa_ori_subdomain_wpi = "$serverproto://$wpa_domain/$wpa_cdn_domain/wp-includes/";
$wpa_ori_subdomain_wpc = "$serverproto://$wpa_domain/$wpa_cdn_domain/wp-content/";	
$wpa_replace_content_links_wpi = array($wpa_ori_domain_wpi,$wpa_ori_subdomain_wpi);
$wpa_replace_content_links_wpc = array($wpa_ori_domain_wpc,$wpa_ori_subdomain_wpc);
}
else
{
$wpa_replace_content_links_wpi = $wpa_ori_domain_wpi;
$wpa_replace_content_links_wpc = $wpa_ori_domain_wpc;	
}

/*replace content link*/
$wpa_awscf_link = get_option("WPAdmin_CDN_URL");
$wpa_awscf_link_wpi = "$serverproto://$wpa_awscf_link/wp-includes/";
$wpa_awscf_link_wpc = "$serverproto://$wpa_awscf_link/wp-content/";	

if(is_string($content))
{
$content = str_replace($wpa_replace_content_links_wpi,$wpa_awscf_link_wpi, $content);
$content = str_replace($wpa_replace_content_links_wpc,$wpa_awscf_link_wpc, $content);
}

/*exclude list*/
if(get_option('WPAdmin_CDN_Ignorelist')){
$wpaac_cdnignorelist = get_option('WPAdmin_CDN_Ignorelist');
$wpaac_cdnignorelist = explode("~wpa~",$wpaac_cdnignorelist);
foreach($wpaac_cdnignorelist as $wpaac_cdnlist)
{
if($wpaac_cdnlist <> "")
{
$wpa_replace = $wpa_domain;
if($wpa_cdn_domain <> "Blank" || $wpa_cdn_domain <> NULL || $wpa_domain <> $wpa_cdn_domain)
{
$wpa_replace = "$wpa_awscf_link";
}
$wpa_ignore_cdn_link = str_replace($wpa_domain,$wpa_awscf_link,$wpaac_cdnlist);
if(is_string($content))
{
$content = str_replace( $wpa_ignore_cdn_link, $wpaac_cdnlist, $content );
}
}
}
}

}
return $content;
}
function wpaac_list_cdn(){
if ( isset($_REQUEST) ) {
@$wpaac_ak = $_REQUEST['wpaac_ak'];
@$wpaac_sk = $_REQUEST['wpaac_sk'];
@$wpaac_ori = sanitize_text_field($_REQUEST['wpaac_ori']);

$wpafilename = wpaacDOC_ROOT . $wpaac_ori .  ".txt";	

$wpaac_client = $this->wpaac_authClient($wpaac_ak,$wpaac_sk);
try{
$result = $wpaac_client->listDistributions();	
$result = $result['DistributionList']['Items'];
$foundsite = "no";

if($result <> NULL)
{
foreach($result as $dl)
{
if($dl['Origins']['Items'][0]['DomainName'] == $wpaac_ori)
{
echo "<div class='alert alert-success'><div class=row><div class='col col-xs-4'>" . $dl['Id'] . " [<b>" . $dl['Status']  . "</b>]</div><div style='word-break: break-all;' class='col col-xs-4'>" . $dl['DomainName']  . "</div><div style='word-break: break-all;'  class='col col-xs-4'>" .  $dl['Origins']['Items'][0]['DomainName'] . "</div><p> <a href='./admin.php?page=wpa-aws-cloudfront'>Reload the page</a></p></div></div><div style='clear:both'></div>";
/*update txt file if needed*/
$nowww = 'cdn.' . str_replace("www.","",$wpaac_ori);
$wpaawscdnurl = file_get_contents($wpafilename);
preg_match_all('#<awsid[^>]*>(.*?)</awsid>#', $wpaawscdnurl, $awsid);
preg_match_all('#<awsdomain[^>]*>(.*?)</awsdomain>#', $wpaawscdnurl, $awsdomain);
$wpa_did = trim(strip_tags($awsid[0][0]));
$wpa_domain = trim(strip_tags($awsdomain[0][0]));
if($wpa_did == NULL)
{
$awsdomainid = $dl['Id'];
$retval =  "<div class='alert alert-success'><p>Successfully created Cloudfront Distribution with ID # <awsid>$awsdomainid</awsid><br><b>IMP: Before performing the below mentioned steps</b><ol><li>Confirm that the status of the the <i>CloudFront Distribution</i> is <b>Deployed</b> in AWS Console</li><li>Ensure that you have modified the DNS (If you plan to use $nowww)</li></ol><hr><p>In the <U>CDN DOMAIN / CNAME</U> box</p><p>Add <a class=retval href='JavaScript:void(0);'><b>" . $dl['DomainName']  . "</b></a></p><p>OR<p>Add <a class=retval href='JavaScript:void(0);'><B>$nowww</B></a><br>[you will have to add a CNAME entry in your DNS pointing <B>$nowww</B> to <b><awsdomain>" . $dl['DomainName']  . "</awsdomain></b>]<wpa_notice>Note: <b>" . $dl['DomainName']  . "</b> points to <b>" . $dl["Origins"]["Items"][0]['DomainName']  . $dl["Origins"]["Items"][0]['OriginPath']  . "</b></wpa_notice></div>";
file_put_contents($wpafilename,$retval);	
}
$foundsite = "yes";
}
}
if($foundsite == "no")
{
echo "<div class='alert alert-warning'><h3>No cloudfront distribution found for " .$wpaac_ori. "</h3></div>";
}
}
else
{
echo "<div class='alert alert-warning'><h3>No distribution Found on Amazon Cloudfront.</h3></div>";
}
}
catch (Exception $e) {
$err = $e->getMessage();
$er = explode("response",$err);
echo "<form method=POST>";
echo "<textarea id=wpadebuglog name=wpadebuglog style='display:none'>";
var_dump($err);
echo "</textarea>";
echo "Error:" . $er[0] . "";
echo "<p><input type=submit name=senddebuglog id=senddebuglog class='btn btn-primary' value='Send Debug Log to Developer'></form>";
}
}	
wp_die();
}
function wpaac_authCert($wpaac_ak,$wpaac_sk)
{
if( !function_exists( 'GuzzleHttp\Psr7\str' ) ) { require  wpaacbasedir . 'admin/aws.phar'; }
$wpaac_cert = new Aws\Acm\AcmClient([
'version'     => 'latest',
'region'  => 'us-east-1',
'credentials' => [
'key'    => $wpaac_ak,
'secret' => $wpaac_sk
],
'http'    => [
'verify' => wpaacbasedir.'admin/cacert.pem'
]
]);	
return $wpaac_cert;
}

function wpaac_authClient($wpaac_ak,$wpaac_sk)
{
if( !function_exists( 'GuzzleHttp\Psr7\str' ) ) { require  wpaacbasedir . 'admin/aws.phar'; }
$wpaac_client = new Aws\CloudFront\CloudFrontClient([
'version'     => 'latest',
'region'  => 'us-east-1',
'credentials' => [
'key'    => $wpaac_ak,
'secret' => $wpaac_sk
],
'http'    => [
'verify' => wpaacbasedir.'admin/cacert.pem'
]
]);	
return $wpaac_client;
}
function wpaac_cdn_registration()
{
if ( isset($_REQUEST) ) {
@$wpaac_cdnregistration = $_REQUEST['wpaac_cdnregistration'];
$wpaac_cdnregistest = $wpaac_cdnregistration;

$wpaac_cdnregistest = str_replace(" " ,"",$wpaac_cdnregistest);

if($wpaac_cdnregistest == "")
{
echo "<br><div class='notice notice-error is-dismissible'><h2>Error!!!</h2><P>Donor ID Missing.</p></div>";
exit;
}
$wpares = file_get_contents("https://wpadmin.ca/donorCheck.php?domain=".$_SERVER['HTTP_HOST']."&donationID=" . $wpaac_cdnregistration);
if($wpares == $wpaac_cdnregistration)
{
if(get_option('WPAdmin_CDN_KEY'))
{
update_option('WPAdmin_CDN_KEY', $wpaac_cdnregistration, $autoload);
}
else
{
add_option('WPAdmin_CDN_KEY', $wpaac_cdnregistration, $autoload);
}
echo "<br><div class='notice notice-success is-dismissible'><h2>Success!!!</h2><P>Thank you.</p></div>";
}
else
{
echo "<br><div class='notice notice-error is-dismissible'><h2>Failed!!!</h2><P>Invalid Donation ID.</p></div>";	
}
}
wp_die();
}
function wpaac_alt_domain(){
if ( isset($_REQUEST) ) {
@$wpaac_cdn_altdomain = sanitize_text_field($_REQUEST['wpaac_cdn_altdomain']);

if($wpaac_cdn_altdomain == "") $wpaac_cdn_altdomain ="Blank";
if(get_option('WPAdmin_CDN_AltDomain'))
{
update_option('WPAdmin_CDN_AltDomain', $wpaac_cdn_altdomain, $autoload);
echo "<br><div class='alert alert-success'><P>Sub-Folder Location Set to $wpaac_cdn_altdomain.</p></div>";
}
else
{
add_option('WPAdmin_CDN_AltDomain', $wpaac_cdn_altdomain, $autoload);
update_option('WPAdmin_CDN_AltDomain', $wpaac_cdn_altdomain, $autoload);
echo "<br><div class='alert alert-success'><P>Sub-Folder Location Set to $wpaac_cdn_altdomain.</p></div>";
}

}
wp_die();	
}

function wpaac_exclude_types(){
if ( isset($_REQUEST) ) {
$wpaac_cdnjs = $_REQUEST['wpaac_cdnjs'];
$wpaac_cdnss = $_REQUEST['wpaac_cdnss'];
if(get_option('WPAdmin_CDN_JS'))
{
update_option('WPAdmin_CDN_JS', $wpaac_cdnjs, $autoload);
}
else
{
add_option('WPAdmin_CDN_JS', $wpaac_cdnjs, $autoload);
update_option('WPAdmin_CDN_JS', $wpaac_cdnjs, $autoload);
}

if(get_option('WPAdmin_CDN_SS'))
{
update_option('WPAdmin_CDN_SS', $wpaac_cdnss, $autoload);
}
else
{
add_option('WPAdmin_CDN_SS', $wpaac_cdnss, $autoload);
update_option('WPAdmin_CDN_SS', $wpaac_cdnss, $autoload);
}

$msg = "<div class='alert alert-success'>";
if($wpaac_cdnjs == "checked")
{
$msg .= "<P>Javascript will not be loaded from CDN</p>";
}
else
{
$msg .= "<P>Javascript will be loaded from CDN</p>";
}
if($wpaac_cdnss == "checked")
{
$msg .= "<P>Stylesheet will not be loaded from CDN</p>";
}
else
{
$msg .= "<P>Stylesheet will be loaded from CDN</p>";
}
$msg .= "</div>";
echo $msg;
wp_die();	
}
}

function wpaac_ignore_list(){
if ( isset($_REQUEST) ) {
@$wpaac_cdnignorelist = $_REQUEST['wpaac_cdnignorelist'];
$wpaac_cdnignorelist = str_replace(PHP_EOL,"~wpa~",$wpaac_cdnignorelist);
if($wpaac_cdnignorelist == "") $wpaac_cdnignorelist ="Blank";
if(get_option('WPAdmin_CDN_Ignorelist'))
{
update_option('WPAdmin_CDN_Ignorelist', $wpaac_cdnignorelist, $autoload);
}
else
{
add_option('WPAdmin_CDN_Ignorelist', $wpaac_cdnignorelist, $autoload);
update_option('WPAdmin_CDN_Ignorelist', $wpaac_cdnignorelist, $autoload);
}
echo "<br><div class='alert alert-success'><P>Exclude List Updated.</p></div>";
}
wp_die();
}
function wpaac_multisite_set_cdn() {
if ( isset($_REQUEST) ) {
@$wpaac_multi_cdnurl = $_REQUEST['wpaac_multi_cdnurl'];
@$wpaac_multi_cdnsite = $_REQUEST['wpaac_multi_cdnsite'];
if($wpaac_multi_cdnurl == "") $wpaac_multi_cdnurl = "Blank";
switch_to_blog($wpaac_multi_cdnsite);
if(get_option('WPAdmin_CDN_URL'))
{
update_option('WPAdmin_CDN_URL', $wpaac_multi_cdnurl, $autoload);
}
else
{
add_option('WPAdmin_CDN_URL', $wpaac_multi_cdnurl, $autoload);
}
echo "<div class='alert alert-success'>CDN Changed To <b>$wpaac_multi_cdnurl</b></div>";
restore_current_blog();
}	
wp_die();
}
function wpaac_multisite_reset_cdn() {
if ( isset($_REQUEST) ) {
@$wpaac_multi_cdnurlold = $_REQUEST['wpaac_multi_cdnurl'];	
@$wpaac_multi_cdnsite = $_REQUEST['wpaac_multi_cdnsite'];
$wpaac_multi_cdnurl = "Blank";
switch_to_blog($wpaac_multi_cdnsite);
if(get_option('WPAdmin_CDN_URL'))
{
update_option('WPAdmin_CDN_URL', $wpaac_multi_cdnurl, $autoload);
}
else
{
add_option('WPAdmin_CDN_URL', $wpaac_multi_cdnurl, $autoload);
}
echo "<div class='alert alert-success'>CDN <b>$wpaac_multi_cdnurlold</b> Removed Successfully</div>";
restore_current_blog();
}	
wp_die();
}
/*class ends here*/
}
$wpaawscdn = new wpaawscdn();
$wpaawscdn->load();
add_action( 'wp_ajax_wpaac_multisite_reset_cdn', array($wpaawscdn,'wpaac_multisite_reset_cdn') );
add_action( 'wp_ajax_wpaac_multisite_set_cdn', array($wpaawscdn,'wpaac_multisite_set_cdn') );
add_action( 'wp_ajax_wpaac_cdn_registration', array($wpaawscdn,'wpaac_cdn_registration') );
add_action( 'wp_ajax_wpaac_validation_email', array($wpaawscdn,'wpaac_validation_email') );
add_action( 'wp_ajax_wpaac_renew_cert', array($wpaawscdn,'wpaac_renew_cert') );
add_action( 'wp_ajax_wpaac_exclude_types', array($wpaawscdn,'wpaac_exclude_types') );
add_action( 'wp_ajax_wpaac_reset_cdn', array($wpaawscdn,'wpaac_reset_cdn') );
add_action( 'wp_ajax_wpaac_list_cdn', array($wpaawscdn,'wpaac_list_cdn'));
add_action( 'wp_ajax_wpaac_deploy_cdn', array($wpaawscdn,'wpaac_deploy_cdn') );
add_action( 'wp_ajax_wpaac_modify_cdn', array($wpaawscdn,'wpaac_modify_cdn') );
add_action( 'wp_ajax_wpaac_set_cdn', array($wpaawscdn,'wpaac_set_cdn') );
add_action( 'wp_ajax_wpaac_ignore_list', array($wpaawscdn,'wpaac_ignore_list') );
add_action( 'wp_ajax_wpaac_alt_domain', array($wpaawscdn,'wpaac_alt_domain') );
add_action( 'wp_enqueue_style', array($wpaawscdn,'wpaac_enqueue_style'), 999 );
add_filter('the_content',array($wpaawscdn,'wpaac_replace_content'),777);
add_filter( 'post_thumbnail_html', array($wpaawscdn,'wpaac_replace_content') ,777);
add_filter( 'widget_text', array($wpaawscdn,'wpaac_replace_content'),777 );
add_filter( 'wp_get_attachment_link', array($wpaawscdn,'wpaac_replace_content'),777 );
add_filter('theme_root_uri',array($wpaawscdn,'wpaac_replace_content'),777);
add_filter('plugins_url',array($wpaawscdn,'wpaac_replace_content'),777);
add_filter( 'wp_get_attachment_thumb_file', array($wpaawscdn,'wpaac_replace_content'),777 );
add_filter( 'wp_get_attachment_thumb_url', array($wpaawscdn,'wpaac_replace_content'),777 );
add_filter( 'metaslider_resized_image_url', array($wpaawscdn,'wpaac_replace_content'),777);
add_filter( 'wp_get_attachment_url', array($wpaawscdn,'wpaac_replace_content'),777 );
add_filter( 'wp_get_attachment_image_attributes', array($wpaawscdn,'wpaac_replace_content'),777 );
add_filter( 'post_gallery ', array($wpaawscdn,'wpaac_replace_content'),777 );
add_filter( 'bloginfo', array($wpaawscdn,'wpaac_replace_content'),777 );
add_filter( 'header_image', array($wpaawscdn,'wpaac_replace_content'),777 );
add_filter( 'get_header_image_tag', array($wpaawscdn,'wpaac_replace_content'),777 );
add_filter( 'theme_mod_header_image', array($wpaawscdn,'wpaac_replace_content'),777 );
if(get_option('WPAdmin_CDN_SS') <> "checked")
{
add_filter( 'style_loader_src', array($wpaawscdn,'wpaac_replace_content') ,777);
}
if(get_option('WPAdmin_CDN_JS') <> "checked")
{
add_filter( 'script_loader_src', array($wpaawscdn,'wpaac_replace_content') ,777);
}

function wpaac_deactivation() {
update_option('WPAdmin_CDN_URL', 'Blank', 'true');
}
register_deactivation_hook( __FILE__, 'wpaac_deactivation' );
?>