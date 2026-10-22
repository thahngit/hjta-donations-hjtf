<?php
require '../vendor/autoload.php';
use PayPal\Http\Environment\ProductionEnvironment;
use PayPal\Http\PayPalClient;

use PayPal\Checkout\Requests\OrderCreateRequest;
use PayPal\Checkout\Orders\AmountBreakdown;
use PayPal\Checkout\Orders\Item;
use PayPal\Checkout\Orders\Order;
use PayPal\Checkout\Orders\PurchaseUnit;

require_once 'ANetConstants.php';

if (session_status() === PHP_SESSION_NONE){
    session_start();
    if (!isset($_COOKIE['session_name()'])) setcookie(session_name(), session_id(), 0, NULL, NULL, 0);
}

require_once($_SERVER['DOCUMENT_ROOT']."/common/monologger.php");
//get_logger()->info('monologger verification', array('Called from' => __FILE__));

$folder = dirname( __FILE__, 3 ) . '/Logs/ANet';
$logfile = $folder . '/' . date('Y-m') . '.anet.hjtf.log' ;
define("AUTHORIZENET_LOG_FILE", $logfile);

$_root  =$_SERVER['DOCUMENT_ROOT'];

require_once($_root."/common/utils.php");
require_once($_root."/common/ezMysql_ntw.php");

require_once($_root."/common/Z_HJTFglobals.php");
require_once($_root."/common/connect_REAL.php");

require_once($_root."/email_groups/email_groups.php");
require_once($_root."/common/email_funcs.php");

date_default_timezone_set("America/Los_Angeles");

$content = trim(file_get_contents("php://input"));
$joinform = json_decode($content, true);

$PP_Args=array();
$PP_Args['sid']      =trim($joinform['sid']);
$PP_Args['fname']    =trim($joinform['FN']);
$PP_Args['lname']    =trim($joinform['LN']);
$PP_Args['addr']     =trim($joinform['ADDR']);
$PP_Args['addr2']    =trim($joinform['addr2']);
$PP_Args['city']     =trim($joinform['CTY']);
$PP_Args['state']    =trim($joinform['ST']);
$PP_Args['zip']      =trim($joinform['ZIP']);
$PP_Args['email']    =trim($joinform['EM']);
$PP_Args['comments'] = trim($joinform['COMMENTS']) ;
$PP_Args['cc_last5'] = 'PYPAL';
$PP_Args['visitor_details']="Y";
$sid =trim($PP_Args['sid']) ;

if (isset($joinform["donateRadioGroup"])):
    if ($joinform["donateRadioGroup"] == 'other'):
        $PP_Args['contrib'] = $joinform['CONAMT'];
    else:
        $PP_Args['contrib'] = $joinform["donateRadioGroup"];
    endif;
else:
    $PP_Args['contrib'] = $joinform['CONAMT'];
endif;

$ez    =new ezMysql_ntw();

//  Update Visitor Infomation to the database
$this_query = $ez->ezUpdate_i(VISITOR_TBL, $PP_Args, "sid='" . $sid . "'"); // project z 10/02/2014

// TEST IF ROW ADDED - EXCEPTION MAILED IF NOT
if ($this_query):
    // OK
else:
    $errmsg = "Failed INSERT to " . VISITOR_TBL . ' $sid= ' . $sid;
    $src    = __FILE__ . ' Line: ' . __LINE__;
    zEmail('blakester555@gmail.com', 'PAYPAL FAILED INSERT ' , $errmsg . '  ' . $src );
    // DEBUG TRACER
    hjtaorgDBUG( 'ERROR' , "Failed INSERT within PayPal branch $sid" , __FILE__ . ' Line: ' . __LINE__ , "HJTF");            
    // do NOT proceed
    $strErrorDesc = $e->getMessage() . 'Error.';
    $strErrorHeader = 'HTTP/1.1 500 Error (1)';
    sendOutput(
        json_encode(array('error' => $strErrorDesc)),
        array('Content-Type: application/json', $strErrorHeader)
    );      
endif;

$_SESSION['sid'] = $sid ;
$strErrorDesc = '';

try {

    //HJTA PROD (HJTA Website Checkout Integration):
    $clientId = 'AeFdgUPkSwzkJ5z0Ct2_e6Nkp5ZZ22Qi6s2UhiUNWixfptI06pflguIPp3SwLzrf9RoMS9khOnw0SYQU';
    $clientSecret = 'EHopEUuSYMZK0X2yejsQIKeB_tMZLlK0SvggEMczzE6zlR_Or6vOlwt9-4LXcz-YkhW_GuCSYwrNKxMl';

    // create a new production environment
    $environment = new ProductionEnvironment($clientId, $clientSecret);

    // create a new client
    $client = new PayPalClient($environment);

    // Create a purchase unit with the total amount
    $donationAmt = $PP_Args['contrib'];
    $purchase_unit = new PurchaseUnit(AmountBreakdown::of($donationAmt));

    // Create & add item to purchase unit
    $purchase_unit->addItem(Item::create('HJTF Donation', $donationAmt, 'USD', 1));     

    // Create a new order with intent to capture a payment
    $order = new Order();

    // Add a purchase unit to order
    $order->addPurchaseUnit($purchase_unit);

    // Create an order create http request
    $request = new OrderCreateRequest($order);

    // Send request to PayPal
    $response = $client->send($request);

    // Parse result
    $result = json_decode((string) $response->getBody());

    // echo $result->id; // id of the created order
    // echo $result->intent; // CAPTURE
    // echo $result->status; // CREATED
    /**
     * JFYI RE: Item Category Type:
     * The possible values are:
     *     - DIGITAL_GOODS. Goods that are stored, delivered, and used in their electronic format.
     *     - PHYSICAL_GOODS. A tangible item that can be shipped with proof of delivery.
     * Per \vendor\phpjuice\paypal-checkout-sdk\src\Orders\Item.php
     */

    $_SESSION['transactionId']=$result->id;
    $transaction_id = $_SESSION['transactionId'];

} catch (Error $e) {
    $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support.';
    $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
}

// send output 
if (!$strErrorDesc) {

    sendOutput(
        $result,
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

?>