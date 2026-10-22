<?php 

$_root  =$_SERVER['DOCUMENT_ROOT'];
if ($_root == 'C:/wamp/www' ) : // WAMP
   // localhost
else :
  // LIVE site
  // St Paddys Reply USA only
  //  require_once($_root."/DataReply/HJTA_SUBDOM/geosource/ip_front.php"); 
endif ;


//require_once($_root."/DataReply/HJTA_SUBDOM/common/utils.php");
//require_once($_root."/DataReply/HJTA_SUBDOM/common/ezMysql_i.php");
require_once($_root."/common/utils.php");
require_once($_root."/common/ezMysql_i.php");

/// integration of xclasses
// auto load class function //// propagate THIS !
function __autoload($class_name) {
   $_root = $_SERVER['DOCUMENT_ROOT'] ;
//   require_once(  $_root. '/DataReply/HJTA_SUBDOM/classes/' . $class_name . '/' . $class_name . '.class.php' ) ;
   require_once(  $_root. '/classes/' . $class_name . '/' . $class_name . '.class.php' ) ;
 } // eof: __autoload


  // REAL vs FAKE setup
  $whereami = trim(getcwd()) ;
  if ( strtoupper(substr($whereami,-5)) == '_FAKE'  ) :
//     require_once($_root."/DataReply/HJTA_SUBDOM/common/Z_HJTFglobals_FAKE.php");
//     require_once($_root."/DataReply/HJTA_SUBDOM/common/connect_FAKE.php");
     require_once($_root."/common/Z_HJTFglobals_FAKE.php");
     require_once($_root."/common/connect_FAKE.php");
     // LINKS
     if ($_root == 'C:/wamp/www' ) : // WAMP - LOCALHOST
           define( 'DONATE_LINK', 'http://localhost/DataReply/HJTA_SUBDOM/Zdon2HJTA_FAKE/ZZ_0_HJTA_donations_form.php?event=0027-0201' ) ;
           define( 'RENEW_LINK' , 'http://localhost/DataReply/HJTA_SUBDOM/Zdon2HJTA_FAKE/ZZ_0_HJTA_renewals_form.php?event=0027-0201' ) ;           
     else :  // LIVE SITE 
           define( 'DONATE_LINK', 'https://donations.hjta.org/Zdon2HJTA_FAKE/ZZ_0_HJTA_donations_form.php?event=0027-0201' ) ;
           define( 'RENEW_LINK' , 'https://donations.hjta.org/Zdon2HJTA_FAKE/ZZ_0_HJTA_renewals_form.php?event=0027-0201' ) ;
     endif ;
  else :  // "REAL" SITES
//      require_once($_root."/DataReply/HJTA_SUBDOM/common/Z_HJTFglobals.php");
//      require_once($_root."/DataReply/HJTA_SUBDOM/common/connect_REAL.php");
      require_once($_root."/common/Z_HJTFglobals.php");
      require_once($_root."/common/connect_REAL.php");

      /// LINKS
     if ($_root == 'C:/wamp/www' ) : // WAMP - LOCALHOST
           define( 'DONATE_LINK', 'http://localhost/DataReply/HJTA_SUBDOM/Zdon2HJTA/ZZ_0_HJTA_donations_form.php?event=0027-0201' ) ;
           define( 'RENEW_LINK' , 'http://localhost/DataReply/HJTA_SUBDOM/Zdon2HJTA/ZZ_0_HJTA_renewals_form.php?event=0027-0201' ) ;           
     else :  // LIVE SITE 
           define( 'DONATE_LINK', 'https://donations.hjta.org/Zdon2HJTA/ZZ_0_HJTA_donations_form.php?event=0027-0201' ) ;
           define( 'RENEW_LINK' , 'https://donations.hjta.org/Zdon2HJTA/ZZ_0_HJTA_renewals_form.php?event=0027-0201' ) ;
     endif ;      
  endif ;


  // REAL vs FAKE setup
  $whereami = trim(getcwd()) ;
  if ( strtoupper(substr($whereami,-5)) == '_FAKE'  ) :
//    require_once($_root . "/DataReply/HJTA_SUBDOM/Zdon2HJTF_FAKE/anet/config.php");
    require_once($_root . "/Zdon2HJTF_FAKE/anet/config.php");
  else :
//    require_once($_root . "/DataReply/HJTA_SUBDOM/Zdon2HJTF/anet/config.php");
    require_once($_root . "/hjtf/anet/config.php");
  endif ;



// REQUIRED email files
//require_once($_root."/DataReply/HJTA_SUBDOM/email_groups/email_groups.php");
//require_once($_root."/DataReply/HJTA_SUBDOM/common/email_funcs.php");
require_once($_root."/email_groups/email_groups.php");
require_once($_root."/common/email_funcs.php");




//
//////////////////////////////////////////////////////
//
//  set to the user defined error handler
$old_error_handler=set_error_handler("myErrorHandler");
date_default_timezone_set("America/Los Angeles");


// incoming


// sid & SESSION 
session_start();
$sid             = $_POST['sid'] ;
$_SESSION["sid"] = $sid; // this was recently absent from some degugging flaw- putting it back 


  // Update Args
  $visitor_Args = array() ;
  $visitor_Args['sid']             = $_POST['sid'] ;
  $visitor_Args['fname']           = ucfirst(strtolower($_POST['fname']));
  $visitor_Args['lname']           = ucfirst(strtolower($_POST['lname']));
  $visitor_Args['addr']            = $_POST['addr'] ;
  $visitor_Args['addr2']           = $_POST['addr2'];
  $visitor_Args['city']            = $_POST['city'] ;
  $visitor_Args['state']           = $_POST['state'];
  $visitor_Args['zip']             = $_POST['zip'] ;
  $visitor_Args['email']           = $_POST['email']; 
  $visitor_Args['contrib']         = $_POST['contrib'] ;       
  $visitor_Args['cc_last5']        = $_POST['cc_last5'] ;  
//  $visitor_Args['occupation']      = $_POST['occupation'];    
//  $visitor_Args['employer']        = $_POST['employer']   ;    
//  $visitor_Args['corp_card']       = $_POST['corp_card'] ;     
//  $visitor_Args['corp_name']       = $_POST['corp_name'] ;     
//  $visitor_Args['corp_address']    = $_POST['corp_address'] ;   
//  $visitor_Args['corp_city']       = $_POST['corp_city'] ;     
//  $visitor_Args['corp_state']      = $_POST['corp_state'];
//  $visitor_Args['corp_zip']        = $_POST['corp_zip']; 
  $visitor_Args['cc_type']         = $_POST['cc_type'] ;  
  
  /// charging only DONT SAVE
  $charge_Args = array() ;
  $charge_Args['contrib']         = $_POST['contrib'] ;   
  $charge_Args['cc_name']         = $_POST['cc_name'];
  $charge_Args['cc_number']       = $_POST['cc_number'];
  $charge_Args['cc_type']         = $_POST['cc_type'] ;  // yes we do save
  $charge_Args['cc_month']        = $_POST['cc_month'] ;
  $charge_Args['cc_year']         = $_POST['cc_year'] ;
  $charge_Args['cc_cvv']          = $_POST['cc_cvv'] ;

/// tracer
hjtaorgLog( 'Stage 1 ' , "Process $sid" , __FILE__ . ' Line: ' . __LINE__ , "HJTF");

///////////////// DATABASE  begin
$ez    =new ezMysql_i(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

//	Do Database stuff
$sid_condition = "sid='". $visitor_Args['sid']   . "'" ;



// Need to determine what VISITTYPE is for later use
$visittype = 'DONATE' ;
$result=$ez->ezSelect_i("*", VISIT_TBL , $sid_condition );
$numb_rows = mysqli_num_rows($result);
if ( $numb_rows == 1) : 
     $data       = mysqli_fetch_object($result);
     $visittype  = trim(strtoupper($data->visittype)) ;
endif ;


// trap to see if there is ALREADY a finalized trasaction with that SID
$already_condition = $sid_condition . " AND completed = 'Y' " ;
$result=$ez->ezSelect_i("*", VISIT_TBL , $already_condition );
$numb_rows = mysqli_num_rows($result);
  
if ( $numb_rows == 1) : // ALREAD DONE !!!
  // go to decline function
  DeclinedForm('Transaction has already completed. Do NOT press BACK ARROW.') ;
  // logit
  hjtaorgLog( 'HALT Stage 1 ' , "Duplicate charge attempt found. STOPPED. $sid"  , __FILE__ . ' Line: ' . __LINE__ , "HJTF");
  // debug
  // DEBUG TRACER
  hjtaorgDBUG( 'STAGE 1 HALTED ' , "DUPLICATE COMPLETED TRANS FOUND $sid" , __FILE__ . ' Line: ' . __LINE__ , "HJTF");
  // EXCEPTION PATH TODO :???
  exit() ;
else :
  // ok to proceed
endif ;



//
//UPDATE trivial/clerical fields
$visitor_updated = $ez->ezUpdate_i(VISITOR_TBL, $visitor_Args, $sid_condition ); //

///  low ball testing
if ( trim($charge_Args['cc_name']) == 'UMA GUMA JR'  ):
  $charge_Args['contrib']   =  '1.01' ;
  $totalContrib             =  '1.01' ;
endif ;


///// ***************************************************
/////  BEGIN AUTHORIZE.NET CHARGE 06/20/2012
///// ***************************************************

$sale = new AuthorizeNetAIM;
$sale->amount     = $charge_Args["contrib"];
$sale->card_num   = $charge_Args['cc_number'];
$sale->exp_date   = $charge_Args["cc_month"] . '/' . $charge_Args["cc_year"] ;
$sale->card_code  = $charge_Args['cc_cvv'] ; 

// added 05/10/2013
$sale->first_name =  $visitor_Args['fname'] ;
$sale->last_name  =  $visitor_Args['lname'] ;
$sale->address    =  $visitor_Args['addr'] ;
$sale->city       =  $visitor_Args['city'] ;
$sale->state      =  $visitor_Args['state'] ;
$sale->zip        =  $visitor_Args['zip'] ;
$sale->email      =  $visitor_Args['email'] ;
$sale->company    =  $charge_Args['cc_name'] ;
//
//if ( $visitor_Args['corp_card'] == "1"  ) :
// $sale->description     =  'COPRORATE' ;
//else :
// $sale->description     =  'PERSONAL' ;
//endif ;
//  added 10/20/2014
$sale->description  = $visitor_Args['sid'] ;
$sale->customer_ip  = $_SERVER['REMOTE_ADDR'];
// end

// go to A.Net
$response = $sale->authorizeAndCapture();

// eval ANET replies

$r_approved              = $response->approved ;
$r_declined              = $response->declined ;
$r_error                 = $response->error ;
$r_error_message         = $response->error_message ;
$r_response_reason_text  = $response->response_reason_text ;
$r_authorization_code    = trim($response->authorization_code );
$r_amount                = $response->amount ;
$transaction_id          = $response->transaction_id;

if ($r_approved) :

  // convert new vars into old DRCC vars
  $serverRet    = 'SUCCESS' ;
  $respText     = 'APPROVAL ' . $r_authorization_code . ' TRANS ID: ' . $transaction_id ;
  $respErrNum   = '' ;
  $respErrText  = '' ;
  $auth         = 'APPROVAL ' . $r_authorization_code ;
  
  // build reply values for form
  $a_ReplyVals = array() ;
  $a_ReplyVals["trans_id"]    = $response->transaction_id;
  $a_ReplyVals["amt_charged"] = $response->amount ;
  $a_ReplyVals["sku"]         = 'HJTF'  ;
  $a_ReplyVals["visittype"]   = 'HJTF' ;
  

  /// APPROVED CHARGE
  /// tracer
  hjtaorgLog( 'END Stage 1 ' , "Successful charge. $sid" , __FILE__ . ' Line: ' . __LINE__ , "HJTF");

  // CHARGED SUCCEED !!!
  $rv = authStatusUpdate("Y");

  
  // EMAIL STUFF
  $ebody = HJTF_Donation_Thanks($visitor_Args['fname']) ;

  zEmail( trim($visitor_Args['email'])  , "Thank You For Your Howard Jarvis Taxpayers Foundation Donation", $ebody ) ;
  
  // high donor alert
  if ( $response->amount  >= 500 ) :
     $amt_charged = $a_ReplyVals["amt_charged"] ;
     $sid         = $visitor_Args['sid'] ;
     $donor       = $visitor_Args['fname'] . ' ' . $visitor_Args['lname']  ;
     $body        = " Amount of $amt_charged was just made to HJTF. By $donor. See: $sid" ;
     zEmail( 'blakester555@gmail.com'  , "HIGH DONOR ALERT - HJTF", $body ) ;
     hjtaorgLog( 'HIGH DONOR ALERT ' , $body , __FILE__ . ' Line: ' . __LINE__ , "HJTF");
  endif ;

  
  // NEW PATH 2014 PROJECT Z
  $rv = ApprovedForm( $a_ReplyVals) ;
  
  
else :  // FAILED - DECLINED

   // convert new vars into old DRCC vars
   $serverRet    = '' ;
   $respText     = $r_response_reason_text  ;
   $respErrNum   = '' ;
   $respErrText  = '' ;
   $auth         = '' ;


  /// tracer
  hjtaorgLog( 'END Stage 1' , "DECLINED charge. $sid" . " $respText . ' '  . $r_error_message " , __FILE__ . ' Line: ' . __LINE__ , "HJTF");



  // DECLINED
  $rv   = authStatusUpdate("N");
  
  // new path 2014 Project Zip 
  $rv = DeclinedForm($respText) ;
  
  // EMAIL stuff
  $ebody = HJTF_Donation_Sorry($visitor_Args['fname'], $respText ) ;
  zEmail( trim($visitor_Args['email'])  , "Your HJTF Transaction Attempt Failed", $ebody ) ;

endif ; // $r_approved


///// ***************************************************
/////  END EVERYTHING 
///// ***************************************************

$MAIN_SITE_ENV = "DEV";

$MAIN_SITE_URL = "https://hjta.org";

if ($MAIN_SITE_ENV == "DEV")
{
	$MAIN_SITE_URL = "https://hjtadev.wpengine.com";
}

$PAGE_TITLE = "HJTF Donations | Howard Jarvis Taxpayers Foundation";
$HEADER_TITLE = "HJTF Secure Online Donation Form";

function authStatusUpdate($auth = "N"){
    //  Add final information to the visit table, showing completion
    //  and authorization status
    //
    global $sid;
    global $serverRet;
    global $respText;
    global $respErrNum;
    global $respErrText;

    //

    // LAST BITS OF COMPLETION
    $mytime    = time();
    $now       = date("Y-m-d H:i:s", $mytime);
    $myArgs['cc_server_stat']    =  $serverRet;
    $myArgs['cc_authorization']  =  $auth;
    $myArgs['cc_respText']       =  $respText;
    $myArgs['cc_respErrNum']     =  $respErrNum;
    $myArgs['cc_respErrText']    =  $respErrText;
    $myArgs['completed']         =  "Y";
    $myArgs['completedate']      =  $now;
    
    $ez    =new ezMysql_i(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);    
    $ez->ezUpdate_i( VISIT_TBL , $myArgs, "sid='".$sid."'"); 
    
    return($auth) ;
} // eof: authStatusUpdate()


// HTML FORM
function ApprovedForm( $a_ReplyVals ) {

$vHJTF_SDS_ROOT = HJTF_SDS_ROOT ;
$vHJTF_SD_ROOT  = HJTF_SD_ROOT ;

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
echo "			<p>Your card was charged the following: </p>\n";
echo "			<p><strong><span class=\"Label\">Amount </span> \$ $amt_charged </strong></p>\n";
echo "			<a class=\"btn gradient\" href=\"http://www.hjta.org\">Return to Home Page</a>\n";
echo "		</div>\n";
echo "		<!-- /Main Content -->\n";

include('../global/ui-components/footer.php');

return(TRUE) ;

} // eof: ApprovedForm()


// HTML FORM
function DeclinedForm($ccFailReason) {

$vHJTF_SDS_ROOT = HJTF_SDS_ROOT ;
$vHJTF_SD_ROOT  = HJTF_SD_ROOT ;

$vRENEW_LINK  = RENEW_LINK ;
$vDONATE_LINK = DONATE_LINK ;

global $PAGE_TITLE;

include('../global/ui-components/header.php');

// failed reason
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
echo "			<h1><span>Your donation attempt failed.</span></h1>\n";
echo "                <p>Unfortunately, there was a problem with your donation attempt. Nothing has been charged.</p>\n";
echo "                <p><strong><span class=\"Label\">Reason: </span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
echo "                $ccFailReason \n";
echo "			<a class=\"btn gradient\" href=\"donations_form.php?event=0027-5555\">TRY AGAIN</a>\n";

//
echo "		</div>\n";

include('../global/ui-components/footer.php');


return(TRUE) ;

} // eof DeclinedForm()

// HTML EMAIL REPLY
function HJTF_Donation_Thanks($fname) {
$vHJTF_SDS_ROOT = HJTF_SDS_ROOT ;
$vHJTF_SD_ROOT  = HJTF_SD_ROOT ;

$ebody  = '' ;
$ebody .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
$ebody .= "<html xmlns:v=\"urn:schemas-microsoft-com:vml\">\n";
$ebody .= "<head>\n";
$ebody .= "<meta name=\"HandheldFriendly\" content=\"false\" />\n";
$ebody .= "<meta name=\"viewport\" content=\"width=device-width; initial-scale=1; maximum-scale=1; minimum-scale=1; user-scalable=no;\" />\n";
$ebody .= "<title>Thank you! Your donation helps keep taxes low.</title>\n";
$ebody .= "<style type=\"text/css\">\n";
$ebody .= ".ReadMsgBody {width:100%;}\n";
$ebody .= ".ExternalClass {width:100%;}\n";
$ebody .= "table td {border-collapse:collapse;}\n";
$ebody .= ".appleLinksGrey a {color:#5B5B5B; text-decoration: none;}\n";
$ebody .= "@media all and (max-width:540px) {\n";
$ebody .= "	table {width:100%;}\n";
$ebody .= "	table[class=\"wrapper\"] {width:100% !important;}\n";
$ebody .= "	table[class=\"hide\"], tr[class=\"hide\"], td[class=\"hide\"], img[class=\"hide\"] {display:none!important;}\n";
$ebody .= "	td[class=\"header\"] {padding:15px;font-size:12px !important;}\n";
$ebody .= "	img[class=\"fit\"] {width:100%; height:auto;}\n";
$ebody .= "	/*Spacing*/\n";
$ebody .= "	td[class=\"gutter\"] {width:10px !important;}\n";
$ebody .= "	td[class=\"padding15\"] {padding:15px;}\n";
$ebody .= "	/* CTA */\n";
$ebody .= "	table[class=\"CTA_1\"] {width:250px;}\n";
$ebody .= "}\n";
$ebody .= "</style>\n";
$ebody .= "</head>\n";
$ebody .= "<body bgcolor=\"#F6F6F6\" text=\"#000001\" link=\"#0000EE\" alink=\"#0000EE\" vlink=\"#551A8B\" style=\"margin-left:0px;margin-right:0px;margin-top:0px;padding:0px;\">\n";
$ebody .= "<div style=\"text-decoration:none;\">\n";
$ebody .= "	<div style=\"-webkit-text-size-adjust:none;\">\n";
$ebody .= "		<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" bgcolor=\"#F6F6F6\" class=\"wrapper\">\n";
$ebody .= "			<tr valign=\"top\">\n";
$ebody .= "				<td width=\"100%\" align=\"center\" valign=\"top\">\n";
$ebody .= "\n";
$ebody .= "					<table width=\"620\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" bgcolor=\"#F6F6F6\">\n";
$ebody .= "						<tr valign=\"top\">\n";
$ebody .= "							<td class=\"gutter\" width=\"10\"></td>\n";
$ebody .= "							<td align=\"center\" valign=\"top\">\n";
$ebody .= "								\n";
$ebody .= "								<!--HEADER-->\n";
$ebody .= "								<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
$ebody .= "									<tr valign=\"top\">\n";
$ebody .= "										<td height=\"45\" width=\"10\"></td>\n";
$ebody .= "										<td align=\"center\" valign=\"middle\" style=\"font:normal 11px/15px Arial, Helvetica, sans-serif;color:#5B5B5B;\" class=\"header\">\n";
$ebody .= "											Thank you for supporting HJTF with your donation.\n";
$ebody .= "										</td>\n";
$ebody .= "										<td height=\"45\" width=\"10\"></td>\n";
$ebody .= "									</tr>\n";
$ebody .= "								</table>\n";
$ebody .= "								\n";
$ebody .= "								<!--TOP-->\n";
$ebody .= "								<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#213B5D\">\n";
$ebody .= "									<tr valign=\"top\">\n";
$ebody .= "										<td align=\"center\" style=\"padding:10px;\">\n";
$ebody .= "											\n";
$ebody .= "											<!--Logo-->\n";
$ebody .= "											<table width=\"326\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\" style=\"mso-table-lspace:0;mso-table-rspace:0;\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td style=\"font:bold 12px/20px Arial, Helvetica, sans-serif;color:#FFFFFF;\" align=\"left\">\n";
$ebody .= "														<img src=\"$vHJTF_SD_ROOT" . "images/logo.gif\" width=\"326\" height=\"100\" alt=\"Howard Jarvis Taxpayers Association\" border=\"0\" style=\"display:block;\" class=\"fit\" />\n";
$ebody .= "													</td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "											\n";
$ebody .= "											<table width=\"1\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\" style=\"mso-table-lspace:0;mso-table-rspace:0;\" class=\"hide\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td width=\"1\"> </td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "											\n";
$ebody .= "											<!--CTA-->\n";
$ebody .= "											<table width=\"220\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"right\" style=\"mso-table-lspace:0;mso-table-rspace:0;\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td class=\"hide\"><img src=\"$vHJTF_SD_ROOT" . "images/shim.gif\" width=\"1\" height=\"25\" alt=\"\" border=\"0\" style=\"display:block;\" /></td>\n";
$ebody .= "												</tr>\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td style=\"font:normal 12px/20px Verdana, Geneva, sans-serif;color:#FFFFFF;padding:15px 0;\" align=\"center\">\n";
$ebody .= "														<a href=\"http://www.hjta.org/e-mail-updates/\" target=\"_blank\" style=\"outline:none;text-decoration:none;\"><span style=\"color:#FFFFFF;text-decoration:underline;\">Sign up for FREE email updates</span></a>\n";
$ebody .= "													</td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "											\n";
$ebody .= "										</td>\n";
$ebody .= "									</tr>\n";
$ebody .= "								</table>\n";
$ebody .= "								\n";
$ebody .= "								<!--Main Body: start-->\n";
$ebody .= "								<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#FFFFFF\">\n";
$ebody .= "									<tr valign=\"top\">\n";
$ebody .= "										<td style=\"padding:20px 20px 0;\">\n";
$ebody .= "										\n";
$ebody .= "											<!--IMAGE-->\n";
$ebody .= "											<table width=\"90\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\" style=\"mso-table-lspace:0;mso-table-rspace:0;\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td style=\"font:bold 12px/20px Arial, Helvetica, sans-serif;color:#FFFFFF;\" align=\"left\">\n";
$ebody .= "														<img src=\"$vHJTF_SD_ROOT" . "images/HJTF_graphic.jpg\" width=\"79\" height=\"79\" alt=\"Gavel\" border=\"0\" style=\"display:block;\" />\n";
$ebody .= "													</td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "											\n";
$ebody .= "											<table width=\"20\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\" style=\"mso-table-lspace:0;mso-table-rspace:0;\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td width=\"20\"><img src=\"$vHJTF_SD_ROOT" . "images/shim.gif\" width=\"20\" height=\"20\" alt=\"\" border=\"0\" style=\"display:block;\" /></td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "											\n";
$ebody .= "											<!--TEXT-->\n";
$ebody .= "											<table width=\"420\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\" style=\"mso-table-lspace:0;mso-table-rspace:0;\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td style=\"font:normal 14px/20px Verdana, Geneva, sans-serif;color:#5B5B5B;\" align=\"left\">\n";
$ebody .= "														Dear $fname,\n";
$ebody .= "														<br /><br />\n";
$ebody .= "														On behalf of everyone at the Howard Jarvis Taxpayers Association, I would like to express my heartfelt thanks for your financial Support to the Howard Jarvis Taxpayers Foundation.\n";
$ebody .= "													</td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "										\n";
$ebody .= "										</td>\n";
$ebody .= "									</tr>\n";
$ebody .= "									<tr valign=\"top\">\n";
$ebody .= "										<td style=\"padding:20px;\">\n";
$ebody .= "										\n";
$ebody .= "											<!--Copy-->\n";
$ebody .= "											<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td style=\"font:normal 14px/20px Verdana, Geneva, sans-serif;color:#5B5B5B;padding:0 0 25px;\" align=\"left\">\n";
$ebody .= "														We will use your donation wisely as we continue to defend Proposition 13 at the court level.\n";
$ebody .= "														Your contribution is fully tax-deductible and your Foundation donation funds the HJTA Office of Legal Affairs.\n";
$ebody .= "														\n";
$ebody .= "														<br /><br />\n";
$ebody .= "														I invite you to keep tabs on our progress, and follow important news affecting California taxpayers, at our\n";
$ebody .= "														<a href=\"http://www.hjta.org/\" target=\"_blank\" style=\"outline:none;text-decoration:none;\"><span style=\"color:#0062A6;text-decoration:underline;\">official website</span></a>.\n";
$ebody .= "														You can also\n";
$ebody .= "														<a href=\"http://www.hjta.org/e-mail-updates/\" target=\"_blank\" style=\"outline:none;text-decoration:none;\"><span style=\"color:#0062A6;text-decoration:underline;\">click here</span></a>\n";
$ebody .= "														to sign up for our free weekly email updates.\n";
$ebody .= "														<br /><br />\n";
$ebody .= "														Once again, thank you for your support.\n";
//$ebody .= "														<a href=\"http://www.hjta.org/\" target=\"_blank\" style=\"outline:none;text-decoration:none;\"><span style=\"color:#0062A6;text-decoration:underline;\">HJTA</span></a>.\n";
$ebody .= "														<br /><br />\n";
$ebody .= "														Sincerely,<br />\n";
$ebody .= "														Craig Mordoh<br />\n";
$ebody .= "														HJTA Chairman of the Board\n";
$ebody .= "													</td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "											\n";
$ebody .= "											<!--CTA-->\n";
$ebody .= "											<table width=\"250\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"CTA_1\">\n";
$ebody .= "												<tr valign=\"top\" align=\"center\">\n";
$ebody .= "													<td align=\"center\" valign=\"middle\" bgcolor=\"#0062A6\" style=\"font:bold 16px/20px Arial, Helvetica, sans-serif;color:#FFFFFF;padding:6px;border-radius:6px;-webkit-border-radius:6px;-moz-border-radius:6px;text-shadow:0 1px 2px #000000;\">\n";
$ebody .= "														<a href=\"http://www.hjta.org/e-mail-updates/\" target=\"_blank\" style=\"outline:none;text-decoration:none;\"><span style=\"color:#FFFFFF;text-decoration:none;\">FREE Weekly email updates</span></a>\n";
$ebody .= "													</td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "											\n";
$ebody .= "										</td>\n";
$ebody .= "									</tr>\n";
$ebody .= "								</table>\n";
$ebody .= "								<!--Main Body: end-->\n";
$ebody .= "								\n";
$ebody .= "								<!--Legal: start-->\n";
$ebody .= "								<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
$ebody .= "									<tr valign=\"top\">\n";
$ebody .= "										<td style=\"font:normal 11px/15px Arial, Helvetica, sans-serif;color:#5B5B5B;padding:20px;\" align=\"left\">\n";
$ebody .= "											Copyright &copy; ".date("Y")." Howard Jarvis Taxpayers Association.\n";
$ebody .= "											All rights reserved. Howard Jarvis Taxpayers Association, <span class=\"appleLinksGrey\">621 South Westmoreland Avenue, Suite 200, Los Angeles, CA 90005</span>\n";
$ebody .= "											<br /><br />\n";
$ebody .= "										</td>\n";
$ebody .= "									</tr>\n";
$ebody .= "								</table>\n";
$ebody .= "											\n";
$ebody .= "								\n";
$ebody .= "							</td>\n";
$ebody .= "							<td class=\"gutter\" width=\"10\"></td>\n";
$ebody .= "						</tr>\n";
$ebody .= "					</table>\n";
$ebody .= "					\n";
$ebody .= "				</td>\n";
$ebody .= "			</tr>\n";
$ebody .= "		</table>\n";
$ebody .= "	</div>\n";
$ebody .= "</div>\n";
$ebody .= "</body>\n";
$ebody .= "</html>\n";



return($ebody)	 ;
} // HJTF_Donation_Thanks()




// HTML EMAIL REPLY
function HJTF_Donation_Sorry($fname, $ccFailReason) {

$vHJTF_SDS_ROOT = HJTF_SDS_ROOT ;
$vHJTF_SD_ROOT  = HJTF_SD_ROOT ;

$ebody = '' ;

$ebody .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
$ebody .= "<html xmlns:v=\"urn:schemas-microsoft-com:vml\">\n";
$ebody .= "<head>\n";
$ebody .= "<meta name=\"HandheldFriendly\" content=\"false\" />\n";
$ebody .= "<meta name=\"viewport\" content=\"width=device-width; initial-scale=1; maximum-scale=1; minimum-scale=1; user-scalable=no;\" />\n";
$ebody .= "<title>Sorry, there has been an error with your donation attempt</title>\n";
$ebody .= "<style type=\"text/css\">\n";
$ebody .= ".ReadMsgBody {width:100%;}\n";
$ebody .= ".ExternalClass {width:100%;}\n";
$ebody .= "table td {border-collapse:collapse;}\n";
$ebody .= ".appleLinksGrey a {color:#5B5B5B; text-decoration: none;}\n";
$ebody .= "@media all and (max-width:540px) {\n";
$ebody .= "	table {width:100%;}\n";
$ebody .= "	table[class=\"wrapper\"] {width:100% !important;}\n";
$ebody .= "	table[class=\"hide\"], tr[class=\"hide\"], td[class=\"hide\"], img[class=\"hide\"] {display:none!important;}\n";
$ebody .= "	td[class=\"header\"] {padding:15px;font-size:12px !important;}\n";
$ebody .= "	img[class=\"fit\"] {width:100%; height:auto;}\n";
$ebody .= "	/*Spacing*/\n";
$ebody .= "	td[class=\"gutter\"] {width:10px !important;}\n";
$ebody .= "	td[class=\"padding15\"] {padding:15px;}\n";
$ebody .= "	/* CTA */\n";
$ebody .= "	table[class=\"CTA_1\"] {width:250px;}\n";
$ebody .= "}\n";
$ebody .= "</style>\n";
$ebody .= "</head>\n";
$ebody .= "<body bgcolor=\"#F6F6F6\" text=\"#000001\" link=\"#0000EE\" alink=\"#0000EE\" vlink=\"#551A8B\" style=\"margin-left:0px;margin-right:0px;margin-top:0px;padding:0px;\">\n";
$ebody .= "<div style=\"text-decoration:none;\">\n";
$ebody .= "	<div style=\"-webkit-text-size-adjust:none;\">\n";
$ebody .= "		<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" bgcolor=\"#F6F6F6\" class=\"wrapper\">\n";
$ebody .= "			<tr valign=\"top\">\n";
$ebody .= "				<td width=\"100%\" align=\"center\" valign=\"top\">\n";
$ebody .= "\n";
$ebody .= "					<table width=\"620\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" bgcolor=\"#F6F6F6\">\n";
$ebody .= "						<tr valign=\"top\">\n";
$ebody .= "							<td class=\"gutter\" width=\"10\"></td>\n";
$ebody .= "							<td align=\"center\" valign=\"top\">\n";
$ebody .= "\n";
$ebody .= "								<!--HEADER-->\n";
$ebody .= "								<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
$ebody .= "									<tr valign=\"top\">\n";
$ebody .= "										<td height=\"45\" width=\"10\"></td>\n";
$ebody .= "										<td align=\"center\" valign=\"middle\" style=\"font:normal 12px/16px Arial, Helvetica, sans-serif;color:#5B5B5B;\" class=\"header\">\n";
$ebody .= "											Processing error with your HJTF donation.\n";
$ebody .= "										</td>\n";
$ebody .= "										<td height=\"45\" width=\"10\"></td>\n";
$ebody .= "									</tr>\n";
$ebody .= "								</table>\n";
$ebody .= "\n";
$ebody .= "								<!--TOP-->\n";
$ebody .= "								<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#213B5D\">\n";
$ebody .= "									<tr valign=\"top\">\n";
$ebody .= "										<td align=\"center\" style=\"padding:10px;\">\n";
$ebody .= "\n";
$ebody .= "											<!--Logo-->\n";
$ebody .= "											<table width=\"326\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\" style=\"mso-table-lspace:0;mso-table-rspace:0;\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td style=\"font:bold 12px/20px Arial, Helvetica, sans-serif;color:#FFFFFF;\" align=\"left\">\n";
$ebody .= "														<img src=\"$vHJTF_SD_ROOT" . "images/logo.gif\" width=\"326\" height=\"100\" alt=\"Howard Jarvis Taxpayers Association\" border=\"0\" style=\"display:block;\" class=\"fit\" />\n";
$ebody .= "													</td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "\n";
$ebody .= "											<table width=\"1\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\" style=\"mso-table-lspace:0;mso-table-rspace:0;\" class=\"hide\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td width=\"1\"> </td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "\n";
$ebody .= "											<!--CTA-->\n";
$ebody .= "											<table width=\"200\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"right\" style=\"mso-table-lspace:0;mso-table-rspace:0;\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td class=\"hide\"><img src=\"$vHJTF_SD_ROOT" . "images/shim.gif\" width=\"1\" height=\"25\" alt=\"\" border=\"0\" style=\"display:block;\" /></td>\n";
$ebody .= "												</tr>\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td style=\"font:normal 14px/20px Verdana, Geneva, sans-serif;color:#FFFFFF;padding:15px 0;\" align=\"center\">\n";
$ebody .= "														<a href=\"$vHJTF_SD_ROOT" . "donations_form.php?event=0027-5555" . " target=\"_blank\" style=\"outline:none;text-decoration:none;\"><span style=\"color:#FFFFFF;text-decoration:underline;\">Return to HJTF Donations</span></a>\n";
$ebody .= "													</td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "\n";
$ebody .= "										</td>\n";
$ebody .= "									</tr>\n";
$ebody .= "								</table>\n";
$ebody .= "\n";
$ebody .= "								<!--Main Body: start-->\n";
$ebody .= "								<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#FFFFFF\">\n";
$ebody .= "									<tr valign=\"top\">\n";
$ebody .= "										<td style=\"padding:20px;\">\n";
$ebody .= "\n";
$ebody .= "											<!--Copy-->\n";
$ebody .= "											<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td style=\"font:normal 14px/20px Verdana, Geneva, sans-serif;color:#5B5B5B;padding:15px 0 25px;\" align=\"left\">\n";
$ebody .= "														Dear $fname,\n";
$ebody .= "														<br /><br />\n";
$ebody .= "														We're sorry, but we were unable to complete your donation to the Howard Jarvis Taxpayers Foundation due to a problem with your credit card information.\n";
$ebody .= "														<br /><br />\n";
$ebody .= "														There are several reasons why this can happen, perhaps something as simple as mistyping a number.\n";
$ebody .= "														<br />\n";
$ebody .= "														Decline Reason : This transaction has been declined. (Code: $ccFailReason )\n";
$ebody .= "														<br /><br />\n";
$ebody .= "														Please take a moment now to enter your donation and credit card information again at\n";
$ebody .= "														<a href=\"http://www.hjta.org/donate-now\" target=\"_blank\" style=\"outline:none;text-decoration:none;\"><span style=\"color:#0062A6;text-decoration:underline;\">our website</span></a>. As you know, the Howard Jarvis Taxpayers Association relies on the Foundation to support litigations to protect Prop. 13 at the court level. We'd like to count on you to help us protect Prop. 13 and your taxpayer rights.\n";
$ebody .= "														<br /><br />\n";
$ebody .= "														To return to our website, simply click on the button below.\n";
$ebody .= "														<br /><br />\n";
$ebody .= "														Thank you,<br />\n";
$ebody .= "														Howard Jarvis Taxpayers Association\n";
$ebody .= "													</td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "\n";
$ebody .= "											<!--CTA-->\n";
$ebody .= "											<table width=\"250\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"CTA_1\">\n";
$ebody .= "												<tr valign=\"top\" align=\"center\">\n";
$ebody .= "													<td align=\"center\" valign=\"middle\" bgcolor=\"#0062A6\" style=\"font:bold 16px/20px Arial, Helvetica, sans-serif;color:#FFFFFF;padding:6px;border-radius:6px;-webkit-border-radius:6px;-moz-border-radius:6px;text-shadow:0 1px 2px #000000;\">\n";
$ebody .= "														<a href=\"http://www.hjta.org/\" target=\"_blank\" style=\"outline:none;text-decoration:none;\"><span style=\"color:#FFFFFF;text-decoration:none;\">Visit HJTA.org</span></a>\n";
$ebody .= "													</td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "\n";
$ebody .= "										</td>\n";
$ebody .= "									</tr>\n";
$ebody .= "								</table>\n";
$ebody .= "								<!--Main Body: end-->\n";
$ebody .= "\n";
$ebody .= "								<!--Legal: start-->\n";
$ebody .= "								<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
$ebody .= "									<tr valign=\"top\">\n";
$ebody .= "										<td style=\"font:normal 11px/15px Arial, Helvetica, sans-serif;color:#5B5B5B;padding:20px;\" align=\"left\">\n";
$ebody .= "											Copyright &copy; ".date("Y")." Howard Jarvis Taxpayers Association.\n";
$ebody .= "											All rights reserved. Howard Jarvis Taxpayers Association, <span class=\"appleLinksGrey\">621 South Westmoreland Avenue, Suite 200, Los Angeles, CA 90005</span>\n";
$ebody .= "											<br /><br />\n";
$ebody .= "										</td>\n";
$ebody .= "									</tr>\n";
$ebody .= "								</table>\n";
$ebody .= "\n";
$ebody .= "\n";
$ebody .= "							</td>\n";
$ebody .= "							<td class=\"gutter\" width=\"10\"></td>\n";
$ebody .= "						</tr>\n";
$ebody .= "					</table>\n";
$ebody .= "\n";
$ebody .= "				</td>\n";
$ebody .= "			</tr>\n";
$ebody .= "		</table>\n";
$ebody .= "	</div>\n";
$ebody .= "</div>\n";
$ebody .= "</body>\n";
$ebody .= "</html>";

return( $ebody)	 ;
} // eof: HJTF_Donation_Sorry()




?>


