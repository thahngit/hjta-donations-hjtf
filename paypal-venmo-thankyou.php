<?php

 if (session_status() === PHP_SESSION_NONE){
    session_start();
    if (!isset($_COOKIE['session_name()'])) setcookie(session_name(), session_id(), 0, NULL, NULL, 0);
} 

// STEP 4

require_once($_SERVER['DOCUMENT_ROOT']."/common/monologger.php");

require '../vendor/autoload.php';

$folder = dirname( __FILE__, 3 ) . '/Logs/ANet';
$logfile = $folder . '/' . date('Y-m') . '.anet.hjtf.log' ;
define("AUTHORIZENET_LOG_FILE", $logfile);

$_root  =$_SERVER['DOCUMENT_ROOT'];

require_once($_root."/common/utils.php");
require_once($_root."/common/ezMysql_ntw.php") ;

 $cust = '';
 $event = '';
 if(isset($_SESSION['cust'])) $cust  = $_SESSION['cust'] ;
 if(isset($_SESSION['event'])) $event = $_SESSION['event'] ;

require_once($_root."/common/Z_HJTFglobals.php");
require_once($_root."/common/connect_REAL.php");

/// LINKS
if ($_root == 'C:/wamp/www'  || strpos($_root, 'H:') !== false) : // WAMP - LOCALHOST
      define( 'DONATE_LINK', "http://localhost/DataReply/HJTA_SUBDOM/Zdon2HJTA/ZZ_0_HJTA_donations_form.php?event=$cust-$event" ) ;
      define( 'RENEW_LINK' , "http://localhost/DataReply/HJTA_SUBDOM/Zdon2HJTA/ZZ_0_HJTA_renewals_form.php?event=$cust-$event" ) ;           
else :  // LIVE SITE 
      define( 'DONATE_LINK', "https://donations.hjta.org/hjtf/donations_form.php?event=$cust-$event" ) ;
      define( 'RENEW_LINK' , "https://donations.hjta.org/hjtf/donations_form.php?event=$cust-$event" ) ;
endif ;


// build reply values for form
$a_ReplyVals = $_SESSION['a_ReplyVals'] ;
$isVenmo = $_GET["v"];
$PayPalOrVenmo = "PayPal";
if ($isVenmo != null && $isVenmo == "Y") $PayPalOrVenmo = "Venmo";

if ($a_ReplyVals == null) {

    // DBINE:  TBD ERROR HANDLING
    // done!
    exit() ;

} 

$MAIN_SITE_URL = "https://hjta.org";

$PAGE_TITLE = "HJTF Donations | Howard Jarvis Taxpayers Foundation";
$HEADER_TITLE = "HJTF Secure Online Donation Form";

$vHJTA_SDS_ROOT = HJTA_SDS_ROOT ;
$vHJTA_SD_ROOT  = HJTA_SD_ROOT ;

$trans_id    =   $a_ReplyVals["trans_id"]    ;
$amt_charged =   $a_ReplyVals["amt_charged"] ;
$sku         =   $a_ReplyVals["sku"]         ;
$visittype   =   $a_ReplyVals["visittype"]   ;

$vRENEW_LINK  = RENEW_LINK ;
$vDONATE_LINK = DONATE_LINK ;

global $PAGE_TITLE;

include('../global/ui-components/header.php');

/// NEW GTM 09/29/2014
echo "<!-- \n";
echo "Google Tag Manager Data Layer for Google Analytics Ecommerce tracking\n";
echo "-->\n";
echo "<script>\n";
echo "dataLayer = [{\n";
echo "    \"transactionId\": \"$trans_id\",\n";
echo "    \"transactionTotal\": \"$amt_charged\",\n";
echo "    \"transactionProducts\": [{\n";
echo "        \"sku\": \"$sku\",\n";
echo "        \"name\": \"$visittype\",\n";
echo "        \"price\": \"$amt_charged\",\n";
echo "        \"quantity\": 1\n";
echo "    }]\n";
echo "}];\n";
echo "</script>\n";
/// END GTM

//
echo "<!-- Google Tag Manager -->\n";
echo "<noscript>\n";
echo "<iframe src=\"//www.googletagmanager.com/ns.html?id=GTM-NQP3DX\" height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\">\n";
echo "</iframe>\n";
echo "</noscript>\n";
echo "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\"gtm.start\":new Date().getTime(),event:\"gtm.js\"});\n";
echo "var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!=\"dataLayer\"?\"&l=\"+l:\"\";\n";
echo "j.async=true;j.src=\"//www.googletagmanager.com/gtm.js?id=\"+i+dl;\n";
echo "f.parentNode.insertBefore(j,f);\n";
echo "})(window,document,\"script\",\"dataLayer\",\"GTM-NQP3DX\");\n";
echo "</script>\n";
echo "<!-- End Google Tag Manager -->\n";
//

echo "		<div class=\"siteorigin-widget-tinymce textwidget\">\n";
echo "			<h1><span>Thank You For Your Donation</span></h1>\n";
echo "			<p>Your $PayPalOrVenmo account was charged the following: </p>\n";
echo "			<p><strong><span class=\"Label\">Amount </span> \$ $amt_charged </strong></p>\n";
echo "			<a class=\"btn gradient\" href=\"http://www.hjta.org\">Return to Home Page</a>\n";
echo "		</div>\n";
echo "		<!-- /Main Content -->\n";

include('../global/ui-components/footer.php');

exit();
?>