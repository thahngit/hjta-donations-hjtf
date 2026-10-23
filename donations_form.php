<?php 
//golive:
$_root  =$_SERVER['DOCUMENT_ROOT'];
if (session_status() === PHP_SESSION_NONE){
	session_start();
	if (!isset($_COOKIE['session_name()'])) setcookie(session_name(), session_id(), 0, NULL, NULL, 0);
}
//Remove the LIVE_TESTING indicator if exists in this session because this is a live page; not a test page:
if (isset($_SESSION['LIVE_TESTING'])) { unset($_SESSION['LIVE_TESTING']); }

require_once($_SERVER['DOCUMENT_ROOT']."/common/monologger.php");
//get_logger()->info('monologger verification', array('Called from' => __FILE__));

// used to resolve IE https IFRAME issue
header('P3P: CP="CAO PSA OUR"'); // test 4/12/2011

$_root  =$_SERVER['DOCUMENT_ROOT'];

require_once($_root."/common/utils.php");
require_once($_root."/common/ezMysql_ntw.php");

//////////////////////////////////
//
//	Get passed parameters
//
if (isset($_REQUEST['event'])) :
 list($cust, $event)=explode("-", $_REQUEST['event']);
else :
  // redirect to HJTA.org 
  header("Location: http://www.hjta.org/take-action/");
  exit(502);  
endif;

// REQUIRED email files
require_once($_root."/email_groups/email_groups.php");
require_once($_root."/common/email_funcs.php");

require_once($_root."/common/Z_HJTFglobals.php");
require_once($_root."/common/connect_REAL.php");


///////////////////////////////
//
//  Manage error reporting
//   added php 5 support for  E_STRICT
ini_set("error_reporting", E_ALL | E_STRICT & ~E_NOTICE & ~E_DEPRECATED  );


// init stuff
//dbine.ntw:  Replaced with monologger.php $error_handler=set_error_handler("myErrorHandler");
date_default_timezone_set("America/Los_Angeles");

$mytime  = time();
$now     = date("Y-m-d H:i:s", $mytime);
$ip      = $_SERVER["REMOTE_ADDR"];

////////////////// session id

session_regenerate_id();
session_destroy();
if(isset($_SESSION['sid'])) :
  $_SESSION = array();
  unset($_SESSION);
endif ;
if (session_status() === PHP_SESSION_NONE){
	session_start();
}
$sid   = session_id();
$sid = $sid . '----' . $mytime ;
$_SESSION['sid'] = $sid ;


//////////////////////////////////////////////////////////////////////////////////////
////// NEW Event & cust by database lookup
////// JUN 2017
$ska         = 0 ;
//$event      = ;
$visittype   = 'donate' ;      // because this is DONATE page
//////////////////////////////////////////////////////////////////////////////////////

/// tracer
hjtaorgLog( 'BEGIN Stage 0' , "Init write & form $sid" , __FILE__ . ' Line: ' . __LINE__ , "HJTF");

//	Do Database stuff
/// SET DATA CONNECTION
$ez    =new ezMysql_ntw();

// ARE THEY APPROVED EVENT & CUSTOMER CODES?
$search = "event='" . $event . "'" ;
$result=$ez->ezSelect_i("*", 'don2hjta_approved_codes' , $search );
$numb_rows = mysqli_num_rows($result);

// IF NOT APPROVED 
// REDIRECT 
if ( $numb_rows == 0): // search does not exist in database
   /// tracer
   // log code re-direct tracer not working ? 6/29/21 ???? debug another time
//   hjtaorgLog( 'BEGIN Stage 00' , "Redirect. Failed event code of: ( $event ) tried" , __FILE__ . ' Line: ' . __LINE__ , "HJTF");
  // redirect to HJTA.org 
  header("Location: https://www.hjta.org/take-action/");
  //exit(502);
  exit(0) ;
endif ;

/// SET session for future links
$_SESSION['cust']   = $cust   ;
$_SESSION['event']  = $event ;


//	Do Database stuff
//
$sid_condition = "sid='".$sid."'" ;
$result=$ez->ezSelect_i("*", VISIT_TBL , $sid_condition );
$numb_rows = mysqli_num_rows($result);
  
if ( $numb_rows == 0): // SID does not exist in database
    $mytime             =time();
    $now                =date("Y-m-d H:i:s", $mytime);
    $myArgs=array();
    $myArgs['sid']      =$sid;
    $myArgs['customer'] =$cust;
    $myArgs['event']    =$event;
    $myArgs['visittype']=$visittype;
    $myArgs['visitdate']=$now;
    $myArgs['ska']      =$ska ;
    $myArgs['myIP']     = $_SERVER['REMOTE_ADDR'];
    $this_query         =$ez->ezInsert_i( VISIT_TBL , $myArgs, "SET");
    if ($this_query) :
      // OK
    else :
       // no INSERT
       $errmsg =  "Failed INSERT into " . VISIT_TBL ;
       // Send all people individually until looping / array method
       $toList = $DR_ADM1 ;
       $lName  = $toList[0]['name'];
       $lEmail = $toList[0]['email'];
       $sendto =  $lEmail  ;
       // The SEND
       $rvOK = TRUE ;
       if ($rvOK) :
          //$rvOK =  vEmail ($sendto, 'DR' , "NOTE - Failed INSERT into " . VISIT_TBL , $errmsg ) ;  
          $rvOK =  zEmail ($sendto,  "NOTE - Failed INSERT into " . VISIT_TBL , $errmsg ) ;  
       endif ;
       // DEBUG TRACER
       hjtaorgDBUG( 'STAGE 0 SQL err ' , "Failed INSERT"  . ' ' . $errmsg , __FILE__ . ' Line: ' . __LINE__ , "HJTF");
       // EXCEPTION PATH TODO :???
       // BETTER GO ELSEWHERE
       header("Location: https://www.hjta.org/take-action/");
       exit();       
    endif ;

else : //  SID already exists in the database
    $mess=" Duplicate SID $sid found";
    $src = __FILE__ . ' Line: ' . __LINE__ ;
    //Logit( $mess, '', $src ) ;
    // err_email( $mess , $src ) ; // todo convert to extended zEmail()
    //
    session_regenerate_id();
    session_destroy();
    if (session_status() === PHP_SESSION_NONE){
        session_start();
    }
    $sid   = session_id();
    $sid   = $mytime.'----'.$sid."----".$mytime ;
    $_SESSION['sid'] = $sid ;

    $myArgs=array();
    $myArgs['sid']       = $sid ;
    $myArgs['sid_stat']  = "duplicate";
    $myArgs['customer']  = $cust;
    $myArgs['event']     = $event;
    $myArgs['visittype'] = $visittype;
    $myArgs['visitdate'] = $now;
    $myArgs['ska']       = $ska;
    $myArgs['myIP']      = $ip;

    $this_query = $ez->ezInsert_i( VISIT_TBL , $myArgs, "SET");
    if ($this_query) :
      // OK
    else :
       // no INSERT
       $errmsg =  "Failed INSERT into " . VISIT_TBL  ;
       // Send all people individually until looping / array method
       $toList = $DR_ADM1 ;
       $lName  = $toList[0]['name'];
       $lEmail = $toList[0]['email'];
       $sendto =  $lEmail  ;
       // The SEND
       $rvOK = TRUE ;
       if ($rvOK) :
          //$rvOK =  vEmail ($sendto, 'DR' , "NOTE - Failed INSERT into " . VISIT_TBL , $errmsg ) ;
          $rvOK =  zEmail ($sendto,  "NOTE - Failed INSERT into " . VISIT_TBL , $errmsg ) ;          
       endif ;
       // DEBUG TRACER
       hjtaorgDBUG( 'STAGE 0 SQL err ' , "Failed INSERT"  . ' ' . $errmsg , __FILE__ . ' Line: ' . __LINE__ , "HJTF");
       // EXCEPTION PATH TODO :???

       // do NOT proceed
       // redirect to HJTA.org 
       header("Location: https://www.hjta.org/take-action/");
       exit(502) ;
    endif ;
endif ;

$PAGE_TITLE = "HJTF Donations | Howard Jarvis Taxpayers Foundation";
$HEADER_TITLE = "HJTF Secure Online Donation Form";

ob_start();

include('../global/ui-components/header.php');
$SHOW_SUBSCRIPTION_INFO = true;

//
/////////////////////////////////////
//
//	Invoke the Donations Form  - NEW for 04/01/2018
?>

<script lang="JavaScript">
	function ntwSet() {
		if (jQuery("#buttons").length && jQuery("#donation-submit").length) {
			jQuery("#buttons #donation-submit").prop('disabled',true);
			jQuery("#buttons #donation-submit").prop('value','Processing');
			jQuery("#buttons #donation-submit").css('cursor','default');
			jQuery("#buttons #donation-submit").css('opacity','0.5');
		}
		return true;
	}
	function ntwReset() {
		if (jQuery("#buttons").length && jQuery("#donation-submit").length) {
			jQuery("#buttons #donation-submit").prop('disabled',false);
			jQuery("#buttons #donation-submit").prop('value','SUBMIT');
			jQuery("#buttons #donation-submit").css('cursor','default');
			jQuery("#buttons #donation-submit").css('opacity','1.0');
		}
		return false;
	}

	function ToggleCT()
	{
		var ct = document.getElementById('CT');

		selectedCt = ct.options[ct.selectedIndex].text;

		if (selectedCt == "PayPal or Venmo")
		{
			if ( (ntwSet() && FrontPage_Form2_Validator(this) ? true : ntwReset()) == true )
			{
					
				jQuery("#NameOnCard").css("display","none");
				jQuery("#CCN").css("display","none");
				jQuery("#ExpDate").css("display","none");
				jQuery("#CVV").css("display","none");
				jQuery("#Important").css("display","none");
				jQuery("#buttons").css("display","none");
				if (PayPalDisplayed == false) 
				{
					//paypal.Buttons().render('#paypal-button-container'); 

					var APP_SCRIPT = '../global/scripts/paypal-app.js?rand=' + Math.random();
					var appscript = document.createElement('script');
					var appscript = document.createElement('script');
					appscript.setAttribute('src', APP_SCRIPT);
					document.head.appendChild(appscript);
					PayPalDisplayed = true;
				}						
				jQuery("#PayPalPanelNotes").css("display","block");
				document.querySelector('#paypal-button-container').style.display = 'block';	
			}
			else
			{
				ct.value = "Select one:";
				ct.dispatchEvent(new Event('change'));
			}

		}
		else
		{			
			jQuery("#NameOnCard").css("display","block");
			jQuery("#CCN").css("display","block");
			jQuery("#ExpDate").css("display","block");
			jQuery("#CVV").css("display","block");	
			jQuery("#Important").css("display","block");
			jQuery("#buttons").css("display","block");
			jQuery("#PayPalPanelNotes").css("display","none");
			document.querySelector('#paypal-button-container').style.display = 'none';	
		}
	}

	var PAYPAL_SCRIPT = 'https://www.paypal.com/sdk/js?client-id=AeFdgUPkSwzkJ5z0Ct2_e6Nkp5ZZ22Qi6s2UhiUNWixfptI06pflguIPp3SwLzrf9RoMS9khOnw0SYQU&components=buttons&enable-funding=venmo&disable-funding=paylater,card&currency=USD';
	var script = document.createElement('script');
	script.setAttribute('src', PAYPAL_SCRIPT);
	document.head.appendChild(script);
	var PayPalDisplayed = false;
</script>

	<div class="siteorigin-widget-tinymce textwidget">		
			<!--<h3>Donations are currently unavailable</h3>-->
			<h2>Make your tax-deductible donation to HJTF today!</h2>
			<p>The <strong>Howard Jarvis Taxpayers Foundation</strong> (HJTF) is the research, education and legal arm of the Jarvis organization.
			HJTF advances the interests of taxpayers in the courtroom. Given the high cost of litigation, an ordinary taxpayer is rarely in a position to challenge the validity of an illegal tax levy. In appropriate cases, HJTF provides legal representation without cost to other taxpayer advocacy organizations, homeowners and individual taxpayers where the precedence established confers a broad public benefit to other citizens.</p>
			<p>NOTE: Contributions or gifts to the Howard Jarvis Taxpayers Foundation are deductible from your federal income tax and may also be deductible from your state income tax.</p> 
			<p>Howard Jarvis Taxpayers Foundation Tax ID: 52-1155794</p>
			
			<form action="confirm.php" method="post" class="joinform" id="joinform" onsubmit="return (ntwSet() && FrontPage_Form2_Validator(this) ? true : ntwReset())" name="FrontPage_Form2">
			    <input type="hidden" name="sid" id="sid" value="<?php echo($sid); ?>" />
				<div class="formgroup">
					<fieldset id="donateAmount" name="donateAmount">
						<h3>Choose Donation Amount:</h3>
						<div>
							<label class="option"><input type="radio" name="donateRadioGroup" id="donateRadioGroup10" value="10" />$10</label>
							<label class="option"><input type="radio" name="donateRadioGroup" id="donateRadioGroup15" value="15" checked="checked" />$15</label>
							<label class="option"><input type="radio" name="donateRadioGroup" id="donateRadioGroup25" value="25" />$25</label>
						</div>
						<div>
							<label class="option"><input type="radio" name="donateRadioGroup" id="donateRadioGroup50" value="50" />$50</label>
							<label class="option"><input type="radio" name="donateRadioGroup" id="donateRadioGroup100" value="100" />$100</label>
							<label class="option"><input type="radio" name="donateRadioGroup" id="donateRadioGroup250" value="250" />$250</label>
						</div>
						<div class="last">
							<label class="option"><input type="radio" name="donateRadioGroup" id="donateRadioGroup500" value="500" />$500</label>
							<label class="option"><input type="radio" name="donateRadioGroup" id="donateRadioGroup1000" value="1000" />$1,000</label>
							<span class="other">
								<label class="option other"><input type="radio" name="donateRadioGroup" id="otherRadio" value="other"  />Other Amount: $&nbsp;</label>
								<input type="text" id="CONAMT" name="CONAMT" size="12" class="formfield" value="" data-min="5" />
								<span class="disclaim">*Minimum $5 donation</span>
							</span>
						</div>
					</fieldset>
				</div>
				<div class="formgroup">
					<fieldset id="contactInfo" name="contactInfo">
						<h3>My Contact Information: <span class="disclaim">*required fields</span></h3>
						<div class="formitem">
							<label for="FN">First Name:<span class="form-required" title="This field is required.">*</span></label>
							<input type="text" id="FN" name="FN" size="35" maxlength="64" class="formfield" value="" />
						</div>
						<div class="formitem">
							<label for="LN">Last Name:<span class="form-required" title="This field is required.">*</span></label>
							<input type="text" id="LN" name="LN" size="35" maxlength="64" class="formfield" value="" />
						</div>
						<div class="formitem">
							<label for="ADDR">Address 1:<span class="form-required" title="This field is required.">*</span></label>
							<input type="text" id="ADDR" name="ADDR" size="35" class="formfield" value="" />
						</div>
						<div class="formitem">
							<label for="addr2">Address 2:</label>
							<input type="text" id="addr2" name="addr2" size="35" class="formfield" value="" />
						</div>
						<div class="formitem">
							<label for="CTY">City:<span class="form-required" title="This field is required.">*</span></label>
							<input type="text" id="CTY" name="CTY" size="35" class="formfield" value="" />
						</div>
						<div class="formitem">
							<label for="ST">State:<span class="form-required" title="This field is required.">*</span></label>
							<select id="ST" name="ST" size="1" class="formfield" value="">
								<option value="AL">Alabama</option>
								<option value="AK">Alaska</option>
								<option value="AS">American Samoa</option>
								<option value="AZ">Arizona</option>
								<option value="AR">Arkansas</option>
								<option value="CA" selected="selected">California</option>
								<option value="CO">Colorado</option>
								<option value="CT">Connecticut</option>
								<option value="DE">Delaware</option>
								<option value="DC">District of Comumbia</option>
								<option value="FL">Florida</option>
								<option value="GA">Georgia</option>
								<option value="GU">Guam</option>
								<option value="HI">Hawaii</option>
								<option value="ID">Idaho</option>
								<option value="IL">Illinois</option>
								<option value="IN">Indiana</option>
								<option value="IA">Iowa</option>
								<option value="KS">Kansas</option>
								<option value="KY">Kentucky</option>
								<option value="LA">Louisiana</option>
								<option value="ME">Maine</option>
								<option value="MD">Maryland</option>
								<option value="MA">Massachusetts</option>
								<option value="MI">Michigan</option>
								<option value="MN">Minnesota</option>
								<option value="MO">Missouri</option>
								<option value="MS">Mississippi</option>
								<option value="MT">Montana</option>
								<option value="NE">Nebraska</option>
								<option value="NV">Nevada</option>
								<option value="NH">New Hampshire</option>
								<option value="NJ">New Jersey</option>
								<option value="NM">New Mexico</option>
								<option value="NY">New York</option>
								<option value="NC">North Carolina</option>
								<option value="ND">North Dakota</option>
								<option value="OH">Ohio</option>
								<option value="OK">Oklahoma</option>
								<option value="OR">Oregon</option>
								<option value="PA">Pennsylvania</option>
								<option value="PR">Puerto Rico</option>
								<option value="RI">Rhode Island</option>
								<option value="SC">South Carolina</option>
								<option value="SD">South Dakota</option>
								<option value="TN">Tennessee</option>
								<option value="TX">Texas</option>
								<option value="UT">Utah</option>
								<option value="VT">Vermont</option>
								<option value="VA">Virginia</option>
								<option value="VI">Virgin islands</option>
								<option value="WA">Washington</option>
								<option value="WV">West Virginia</option>
								<option value="WI">Wisconsin</option>
								<option value="WY">Wyoming</option>
							</select>
						</div>
						<div class="formitem">
							<label for="ZIP">ZIP Code:<span class="form-required" title="This field is required.">*</span></label>
							<input type="text" id="ZIP" name="ZIP" size="12" class="formfield" value="" />
						</div>
						<div class="formitem">
							<label for="EM">Email Address:<span class="form-required" title="This field is required.">*</span></label>
							<input type="text" id="EM" name="EM" size="35" class="formfield" value="" />
						</div>
						<div class="formitem">
							<label for="DP">Phone Number:</label>
							<input type="text" id="DP" name="DP" size="35" class="formfield" value="" />
						</div>
						<div class="formitem">
							<label class="option"><input type="checkbox" id="ASUB" name="ASUB" value="ON" class="form-checkbox" checked="checked" />Sign me up for the email list for HJTA updates.</label>
						</div>
					</fieldset>
					<fieldset id="comment" name="comment">
						<div class="formitem">
							<label for="COMMENTS">Comments:</label>
							<textarea rows="10" id="COMMENTS" name="COMMENTS" cols="27" class="formfield" value=""></textarea>
							<span class="description">(For security reasons, only letters, commas and periods can be entered in the comment field. Other characters will cause an error message to appear.)</span>
						</div>
					</fieldset>
					<fieldset id="billingInfo" name="billingInfo">
						<h3>My Billing Information: <span class="disclaim">*required fields</span></h3>
						<div class="formitem">
							<label for="CT">Payment Type:<span class="form-required" title="This field is required.">*</span></label>
							<select size="1" id="CT" name="CT" class="formfield" onchange="ToggleCT()">
								<option selected="selected">Select one:</option>
								<option>Mastercard</option>
								<option>Visa</option>
								<option>Discover</option>
								<option>American Express</option>
								<option>PayPal or Venmo</option>							
							</select>
						</div>
						<div class="formitem" id="NameOnCard">
							<label for="CNAME">Name on Card:<span class="form-required" title="This field is required.">*</span></label>
							<input type="text" id="CNAME" name="CNAME" size="35" value="" maxlength="64" class="formfield" />
						</div>
						<div class="formitem" id="CCN">
							<label for="CN">Card Number:<span class="form-required" title="This field is required.">*</span></label>
							<input type="text" id="CN" name="CN" size="35" maxlength="32" class="formfield" />
							<span class="description">Do not use dashes or spaces between numbers.</span>
						</div>
						<div class="formitem" id="ExpDate">
							<label for="CD1">Exp. Date:<span class="form-required" title="This field is required.">*</span></label>
							<select size="1" id="CD1" name="CD1" class="formfield">
								<option selected="selected">Month</option>
								<option>01</option>
								<option>02</option>
								<option>03</option>
								<option>04</option>
								<option>05</option>
								<option>06</option>
								<option>07</option>
								<option>08</option>
								<option>09</option>
								<option>10</option>
								<option>11</option>
								<option>12</option>
							</select> &nbsp;
							<select size="1" id="CD2" name="CD2" class="formfield">
								<option selected="selected">Year</option>								
								<?php include('../uilib/ccExpirationYear.php'); ?>
							</select>
						</div>
						<div class="formitem" id="CVV">
							<label for="CVV">CVV Code:<span class="form-required" title="This field is required.">*</span></label>
							<input type="text" id="CVV" name="CVV" size="3" value="" maxlength="4" class="formfield" />
							<img src="<?php echo(HJTF_SDS_ROOT); ?>images/cvv_generic.jpg" width="120" height="65" alt="cvv" />
							
							
						</div>
					</fieldset>
				</div>
				<fieldset id="totals" name="totals">
					<div id="totals-wrap">
						<h3 style="padding-bottom:6px;">Your card will be charged the following:</h3>
						<strong id="totals-label">$<span id="donationValue">0</span> <span class="tLabel">Donation</span></strong>
						<p class="warning" id="Important"><span class="important">IMPORTANT:</span><br/>Authorizing might take 30 seconds.<br/>Please press "Submit" <u>ONLY</u> once.</p>
					</div>
				</fieldset>
				<fieldset id="buttons" name="buttons">
					<input id="donation-submit" name="submit" value="Submit" type="submit" />
					<input id="donation-cancel" name="cancel" value= "Cancel" type="reset" />										
				</fieldset>				
				
				<div id="PayPayPanel" style="height: auto; width: 500px;  margin: auto; ">	
				<br/>
				<div id="paypal-button-container"></div>
				<span class="description" id="PayPalPanelNotes" style="display:none">
				You will see an additional option to pay by Venmo if you're on a desktop computer,<br/>or if you're on a mobile device with the VenMo app installed <b><i>and</i></b><br/>you're using Safari (iPhone) or Chrome (Android).</span>
				<br/><p id="result-message" style="color:red"></p>
				</div>

			</form>
		</div>
<!-- end form -->
<?php include('../global/ui-components/footer.php'); ?>

<?php ob_flush(); ?>