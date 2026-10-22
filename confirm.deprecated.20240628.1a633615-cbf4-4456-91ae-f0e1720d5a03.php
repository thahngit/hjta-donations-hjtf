<?php
// ZZ_01_HJTF_confirmation_form.php

$_root  =$_SERVER['DOCUMENT_ROOT'];

///// THIS SEEMS TO INTERFERE WITH PAYPAL OPTION
//if ($_root == 'C:/wamp/www' ) : // WAMP
//   // localhost
//else :
//  // LIVE site
//  // St Paddys Reply USA only
//    require_once($_root."/DataReply/HJTF_SUBDOM/geosource/ip_front.php"); 
//endif ;

//require_once($_root."/DataReply/HJTA_SUBDOM/common/utils.php");
//require_once($_root."/DataReply/HJTA_SUBDOM/common/ezMysql_i.php");
require_once($_root."/common/utils.php");
require_once($_root."/common/ezMysql_i.php");

  // REAL vs FAKE setup
  $whereami = trim(getcwd()) ;
  if ( strtoupper(substr($whereami,-5)) == '_FAKE'  ) :
//     require_once($_root."/DataReply/HJTA_SUBDOM/common/Z_HJTFglobals_FAKE.php");
//     require_once($_root."/DataReply/HJTA_SUBDOM/common/connect_FAKE.php");
     require_once($_root."/common/Z_HJTFglobals_FAKE.php");
     require_once($_root."/common/connect_FAKE.php");

  else :
//    require_once($_root."/DataReply/HJTA_SUBDOM/common/Z_HJTFglobals.php");
//    require_once($_root."/DataReply/HJTA_SUBDOM/common/connect_REAL.php");
    require_once($_root."/common/Z_HJTFglobals.php");
    require_once($_root."/common/connect_REAL.php");

  endif ;



// REQUIRED email files
//require_once($_root."/DataReply/HJTA_SUBDOM/email_groups/email_groups.php");
//require_once($_root."/DataReply/HJTA_SUBDOM/common/email_funcs.php");
require_once($_root."/email_groups/email_groups.php");
require_once($_root."/common/email_funcs.php");



//  set to the user defined error handler
$old_error_handler=set_error_handler("myErrorHandler");
date_default_timezone_set("America/Los_Angeles");

session_start();
// HJTF is now different - make it line all others
// //$sid =trim($_POST['sid']) ;  // why it is _POST here when already in _SESSIONS 
// $sid =trim($_SESSION['sid']) ;
//$_POST['sid'] = $sid ;
// - end CO 
$sid =trim($_POST['sid']) ; // back to org method 



// get incoming via POST
// $visitorArgs
$visitor_Args=array();
// personal
$visitor_Args['sid']     =trim($_POST['sid']);
$visitor_Args['fname']   =ucfirst(strtolower(trim($_POST['FN'])));
$visitor_Args['lname']   =ucfirst(strtolower(trim($_POST['LN'])));
$visitor_Args['addr']    =trim($_POST['ADDR']);
$visitor_Args['addr2']   =trim($_POST['addr2']);
$visitor_Args['city']    =trim($_POST['CTY']);
$visitor_Args['state']   =trim($_POST['ST']);
$visitor_Args['zip']     =trim($_POST['ZIP']);
$visitor_Args['email']   =trim($_POST['EM']);
$visitor_Args['comments']=trim($_POST['COMMENTS']);
$visitor_Args['visitor_details']="Y";
// charge stuff
/// modification to accomodate Radio Buttons for Extra Donation  SAVE!!!
if (isset($_POST["donateRadioGroup"])):
    if ($_POST["donateRadioGroup"] == 'other'):
        $visitor_Args['contrib'] = $_POST['CONAMT'];
    else:
        $visitor_Args['contrib'] = $_POST["donateRadioGroup"];
    endif;
else:
    $visitor_Args['contrib'] = 0;
endif;
$visitor_Args['cc_name']   = $_POST['CNAME'];
$visitor_Args['cc_number'] = $_POST['CN'];
$visitor_Args['cc_type']   = strtoupper($_POST['CT']);
$visitor_Args['cc_last5']  = substr($_POST['CN'], -4); // changed to last 4
$visitor_Args['cc_month']  = $_POST['CD1'];
$visitor_Args['cc_year']   = $_POST['CD2'];
$visitor_Args['cc_cvv']    = trim($_POST['CVV']);




///////////////// REGULAR CREDIT CARD CHARGING PROCESSING STARTS HERE 
setlocale(LC_MONETARY, 'en_US');
$to_charge = '$ ' . sprintf("%01.2f", $visitor_Args['contrib'] ) ;

/// tracer
hjtaorgLog( 'Stage 01 ' , "Confirm form $sid" , __FILE__ . ' Line: ' . __LINE__ , "HJTF");

////////////////// END OF FILE

$MAIN_SITE_ENV = "DEV";

$MAIN_SITE_URL = "https://hjta.org";

if ($MAIN_SITE_ENV == "DEV")
{
	$MAIN_SITE_URL = "https://hjtadev.wpengine.com";
}

$PAGE_TITLE = "HJTF Donations | Howard Jarvis Taxpayers Foundation";
$HEADER_TITLE = "HJTF Secure Online Donation Form";

ob_start();

include('../global/ui-components/header.php');

?>

<div class="siteorigin-widget-tinymce textwidget">
			
       <!-- WORK AREA -->
		<form name = "confirm_charge_form" action="https://donations.HJTA.org/hjtf/complete.php" method="post" onsubmit="return process(this)">
		<fieldset id="totals" name="totals">
					<div id="totals-wrap">
					    <h2 class="pageTitle">Please Confirm</h2>
						<h3 style="padding-bottom:6px;">Your card will be charged the following:</h3>
						<strong id="totals-label"><span><?php echo( $to_charge ) ; ?></span> <span class="tLabel">Donation</span></strong>
						<p class="warning"><span class="important">IMPORTANT:</span><br/>Authorizing might take 30 seconds.<br/>Please press "Confirm" <u>ONLY</u> once.</p>
					</div>
				</fieldset>

        <fieldset id="buttons" name="buttons">
        <input type="hidden" id="sid"        name="sid"        value="<?php echo $visitor_Args['sid'] ?>">
        <input type="hidden" id="fname"      name="fname"      value="<?php echo $visitor_Args['fname'] ?>">
        <input type="hidden" id="lname"      name="lname"      value="<?php echo $visitor_Args['lname'] ?>">
        <input type="hidden" id="addr"       name="addr"       value="<?php echo $visitor_Args['addr'] ?>">
        <input type="hidden" id="addr2"      name="addr2"      value="<?php echo $visitor_Args['addr2'] ?>">
        <input type="hidden" id="city"       name="city"       value="<?php echo $visitor_Args['city'] ?>">
        <input type="hidden" id="state"      name="state"      value="<?php echo $visitor_Args['state'] ?>">
        <input type="hidden" id="zip"        name="zip"        value="<?php echo $visitor_Args['zip'] ?>">
        <input type="hidden" id="email"      name="email"      value="<?php echo $visitor_Args['email'] ?>">
        <input type="hidden" id="comments"   name="comments"   value="<?php echo $visitor_Args['comments'] ?>">        
        <input type="hidden" id="cc_name"    name="cc_name"    value="<?php echo $visitor_Args['cc_name'] ?>">
        <input type="hidden" id="cc_number"  name="cc_number"  value="<?php echo $visitor_Args['cc_number'] ?>">
        <input type="hidden" id="cc_type"    name="cc_type"    value="<?php echo $visitor_Args['cc_type'] ?>">
        <input type="hidden" id="cc_last5"   name="cc_last5"   value="<?php echo $visitor_Args['cc_last5'] ?>">
        <input type="hidden" id="cc_month"   name="cc_month"   value="<?php echo $visitor_Args['cc_month'] ?>">
        <input type="hidden" id="cc_year"    name="cc_year"    value="<?php echo $visitor_Args['cc_year'] ?>">
        <input type="hidden" id="cc_cvv"     name="cc_cvv"     value="<?php echo $visitor_Args['cc_cvv']  ?>">
        <input type="hidden" id="contrib"    name="contrib"    value="<?php echo $visitor_Args['contrib'] ?>">
          
       
         <!-- buttons -->
		<input id="donation-submit" name="submit" value="Confirm" type="submit" />
		
		</fieldset>
        </form>
        <!-- END WORK AREA -->
 
		</div>
<!-- end form -->
<?php include('../global/ui-components/footer.php'); ?>

<?php ob_flush(); ?>
