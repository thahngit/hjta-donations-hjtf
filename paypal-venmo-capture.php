<?php
require '../vendor/autoload.php';
use PayPal\Http\Environment\ProductionEnvironment;
use PayPal\Http\PayPalClient;

use PayPal\Checkout\Requests\OrderCaptureRequest;

require_once 'ANetConstants.php';

if (session_status() === PHP_SESSION_NONE){
    session_start();
    if (!isset($_COOKIE['session_name()'])) setcookie(session_name(), session_id(), 0, NULL, NULL, 0);
}

require_once($_SERVER['DOCUMENT_ROOT']."/common/monologger.php");

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

require_once($_root."/email_groups/email_groups.php");
require_once($_root."/common/email_funcs.php");

require_once($_root."/common/Z_HJTFglobals.php");
require_once($_root."/common/connect_REAL.php"); //PRODUCTION DB VARS

$strErrorDesc = '';
$order_id = $_GET["orderId"];
$sid = $_GET["sid"];

try {
    //HJTA PROD (HJTA Website Checkout Integration):
    $clientId = 'AeFdgUPkSwzkJ5z0Ct2_e6Nkp5ZZ22Qi6s2UhiUNWixfptI06pflguIPp3SwLzrf9RoMS9khOnw0SYQU';
    $clientSecret = 'EHopEUuSYMZK0X2yejsQIKeB_tMZLlK0SvggEMczzE6zlR_Or6vOlwt9-4LXcz-YkhW_GuCSYwrNKxMl';

    // create a new production environment
    $environment = new ProductionEnvironment($clientId, $clientSecret);

    // create a new client
    $client = new PayPalClient($environment);

    // Create an order capture http request
    $request = new OrderCaptureRequest($order_id);

    // Send request to PayPal
    $response = $client->send($request);

    // Parse result
    $paypalResult = json_decode((string) $response->getBody());
    // WATCH:  json_encode($paypalResult, JSON_PRETTY_PRINT)

    // Three cases to handle:
    //   (1) Recoverable INSTRUMENT_DECLINED -> call actions.restart() on the front-end
    //   (2) Other non-recoverable errors -> Show a failure message on the front-end
    //   (3) Successful transaction -> Show confirmation or thank you message on the front-end

    if (isset($paypalResult->details) && isset($paypalResult->details[0]) && isset($paypalResult->details[0]->issue) && $paypalResult->details[0]->issue === "INSTRUMENT_DECLINED") 
    {       
        hjtaorgLog( 'PAYPAL INSTRUMENT_DECLINED ' , "PayPal declined $_AMT for $sid" , __FILE__ . ' Line: ' . __LINE__ , "HJTF");
        //will be handled on the front-end
        
        // DATABASE STUFF
        $ez    =new ezMysql_ntw();
        $sid_condition = "sid='". $sid  . "'";
        $fname = '';
        $email = '';

        // GET FNAME & EMAIL
        $result=$ez->ezSelect_i("*", VISIT_TBL , $sid_condition );
        $numb_rows = mysqli_num_rows($result);
        if ( $numb_rows == 1) :
            $data       = mysqli_fetch_object($result);
            $visittype  = trim(strtoupper($data->visittype)) ;
            $fname      = trim(strtoupper($data->fname)) ;
            $email      = trim(strtoupper($data->email)) ;
        else :
            $visittype  = '' ;
            $fname      = 'Donor' ;
            $email      = '' ;
        endif ;

        // EMAIL stuff
        $ebody = Donation_Sorry($fname, "INSTRUMENT_DECLINED" ) ;
        zEmail( trim($visitor_Args['email'])  , "Your HJTF Transaction Attempt Failed", $ebody ) ;

        $strErrorDesc = 'PAYPAL INSTRUMENT_DECLINED. Your account was not charged.';
        $strErrorHeader = 'HTTP/1.1 500 Error';

    } 
    else if (isset($paypalResult->details) && isset($paypalResult->details[0]) && isset($paypalResult->details[0]->issue)) 
    {
        hjtaorgLog( 'PAYPAL OTHER NON-RECOVERABLE ERROR ' , "PayPal error processing $_AMT for $sid: " + $paypalResult->details[0]->issue , __FILE__ . ' Line: ' . __LINE__ , "HJTF");
        //will be handled on the front-end

        // DATABASE STUFF
        $ez    =new ezMysql_ntw();
        $sid_condition = "sid='". $sid  . "'";
        $fname = '';
        $email = '';

        // GET FNAME & EMAIL
        $result=$ez->ezSelect_i("*", VISIT_TBL , $sid_condition );
        $numb_rows = mysqli_num_rows($result);
        if ( $numb_rows == 1) :
            $data       = mysqli_fetch_object($result);
            $visittype  = trim(strtoupper($data->visittype)) ;
            $fname      = trim(strtoupper($data->fname)) ;
            $email      = trim(strtoupper($data->email)) ;
        else :
            $visittype  = '' ;
            $fname      = 'Donor' ;
            $email      = '' ;
        endif ;

        // EMAIL stuff
        $ebody = Donation_Sorry($fname, "NON-RECOVERABLE ERROR" ) ;
        zEmail( trim($email)  , "Your HJTF Transaction Attempt Failed", $ebody ) ;
        
    } 
    else if (isset($paypalResult->purchase_units) && (!isset($paypalResult->purchase_units[0]) || !isset($paypalResult->purchase_units[0]->amount) || !isset($paypalResult->purchase_units[0]->amount->value)))
    {
        hjtaorgLog( 'PAYPAL RESPONSE PAYLOAD ERROR ' , "PayPal response missing purchase_units[0]->amount->value for $_AMT for $sid", __FILE__ . ' Line: ' . __LINE__ , "HJTF");
        //will be handled on the front-end

        // DATABASE STUFF
        $ez    =new ezMysql_ntw();
        $sid_condition = "sid='". $sid  . "'";
        $fname = '';
        $email = '';

        // GET FNAME & EMAIL
        $result=$ez->ezSelect_i("*", VISIT_TBL , $sid_condition );
        $numb_rows = mysqli_num_rows($result);
        if ( $numb_rows == 1) :
            $data       = mysqli_fetch_object($result);
            $visittype  = trim(strtoupper($data->visittype)) ;
            $fname      = trim(strtoupper($data->fname)) ;
            $email      = trim(strtoupper($data->email)) ;
        else :
            $visittype  = '' ;
            $fname      = 'Donor' ;
            $email      = '' ;
        endif ;

        // EMAIL stuff
        $ebody = Donation_Sorry($fname, "NON-RECOVERABLE ERROR" ) ;
        zEmail( trim($email)  , "Your HJTF Transaction Attempt Failed", $ebody ) ;
    } 
    else
    {
        $_AMT = $paypalResult->purchase_units[0]->amount->value;

        // DATABASE STUFF
        $ez    =new ezMysql_ntw();
        $sid_condition = "sid='". $sid  . "'";
        $fname = '';
        $lname = '';
        $email = '';

        // GET FNAME & EMAIL
        $result=$ez->ezSelect_i("*", VISIT_TBL , $sid_condition );
        $numb_rows = mysqli_num_rows($result);
        if ( $numb_rows == 1) :
            $data       = mysqli_fetch_object($result);
            $visittype  = trim(strtoupper($data->visittype)) ;
            $fname      = trim(strtoupper($data->fname)) ;
            $lname      = trim(strtoupper($data->lname)) ;
            $email      = trim(strtoupper($data->email)) ;
        else :
            $visittype  = '' ;
            $fname      = 'Donor' ;
            $email      = '' ;
        endif ;

        // build reply values for form
        $a_ReplyVals = array() ;
        $a_ReplyVals["trans_id"]    = $sid ;
        $a_ReplyVals["amt_charged"] = $_AMT ;
        $a_ReplyVals["sku"]         = $visittype  ;
        $a_ReplyVals["visittype"]   = $visittype ;

        hjtaorgLog( 'PAYPAL COMPLETE ' , "PayPal Approved $_AMT for $sid" , __FILE__ . ' Line: ' . __LINE__ , "HJTF");

        // update database
        ///////////////// DATABASE  begin
        $mytime = time();
        $now    = date("Y-m-d H:i:s", $mytime);
        $a_updateArgs = array() ;
        $a_updateArgs['contribyn']       =  $_AMT ;
        $a_updateArgs['contrib']         =  $_AMT;
        $a_updateArgs['cc_type']         =  'PAYPAL' ;
        $a_updateArgs['cc_last5']        =  'PYPAL' ;
        $a_updateArgs['visitor_details'] =  'Y' ;
        $a_updateArgs['cc_server_stat']  =  'SUCCESS';
        $a_updateArgs['cc_respText']     =  'APPROVAL PAYPAL' ;
        $a_updateArgs['completed']       =  'Y' ;
        $a_updateArgs['completedate']    =  $now ;
        $a_updateArgs['cc_authorization']=  'Y' ;

        //  Update Visitor Infomation to the database
        $this_query = $ez->ezUpdate_i(VISITOR_TBL, $a_updateArgs, "sid='" . $sid . "'"); // project z 10/02/2014
        if ( $this_query ) :
            // OK
        else :
            $errmsg = "Table did NOT UPDATE PAYPAL APPROVED $_AMT for $sid" ;
            $src    =  __FILE__ . ' Line: ' . __LINE__ ;
            hjtaorgLog( 'PAYPAL ERROR!!! ' , $errmsg , $src , "HJTF");
            hjtaorgDBUG('PAYPAL ERROR!!! ' , $errmsg , $src , "HJTF");
            zEmail('blakester555@gmail.com', 'PAYPAL FAILED UPDATE ! ' , $errmsg . '  ' . $src );
        endif ;

        // broadcast approved email
        $ebody = Donation_Thanks($fname ) ;
        zEmail( $email  , "Thank You For Your Howard Jarvis Taxpayers Foundation Donation", $ebody ) ;      

        // high donor alert
        if ( $_AMT >= 500 ) :
            $amt_charged = $a_ReplyVals["amt_charged"] ;
            $donor       = $fname . ' ' . $lname ;
            $body        = " Amount of $amt_charged was just made to HJTF. By $donor. See: $sid" ;
            zEmail( 'hkelly32@gmail.com'  , "HIGH DONOR ALERT - HJTF", $body ) ;
            hjtaorgLog( 'HIGH DONOR ALERT ' , $body , __FILE__ . ' Line: ' . __LINE__ , "HJTF");
        endif;

        $_SESSION['a_ReplyVals'] = $a_ReplyVals;
    }

} catch (Error $e) {
    $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support.';
    $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
}

// send output 
if (!$strErrorDesc) {

    sendOutput(
        $paypalResult,
        array('Content-Type: application/json', 'HTTP/1.1 200 OK')
    );
} else {
    
    sendOutput(
        json_encode(array('error' => $strErrorDesc)),
        array('Content-Type: application/json', $strErrorHeader)
    );
}

function sendOutput($data, $httpHeaders = array())
{
    header_remove('Set-Cookie');
    if (is_array($httpHeaders) && count($httpHeaders)) {
        foreach ($httpHeaders as $httpHeader) {
            header($httpHeader);
        }
    }

    $json_data = json_encode((array) $data);
    //print_r($json_data);

    echo $json_data;
    exit;
}


// HTML EMAIL REPLY
function Donation_Thanks($fname) {
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
function Donation_Sorry($fname, $ccFailReason) {

$vHJTF_SDS_ROOT = HJTF_SDS_ROOT ;
$vHJTF_SD_ROOT  = HJTF_SD_ROOT ;

$ebody = '' ;

$ebody .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
$ebody .= "<html xmlns:v=\"urn:schemas-microsoft-com:vml\">\n";
$ebody .= "<head>\n";
$ebody .= "<meta name=\"HandheldFriendly\" content=\"false\" />\n";
$ebody .= "<meta name=\"viewport\" content=\"width=device-width; initial-scale=1; maximum-scale=1; minimum-scale=1; user-scalable=no;\" />\n";
$ebody .= "<title>Sorry, there has been an error with your donation attempt.</title>\n";
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
$ebody .= "											Your HJTF Transaction Attempt Failed\n";
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
$ebody .= "														We're sorry, but we were unable to complete your donation to the Howard Jarvis Taxpayers Foundation.\n";
$ebody .= "														<br /><br />\n";
$ebody .= "														There are several reasons why this can happen.  Please take a moment now to enter your donation and payment information again at\n";
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
} // eof: Donation_Sorry()
?>