<?php
$path = '../plugins/merchant-sdk-php-3.9.1/lib';
set_include_path ( get_include_path () . PATH_SEPARATOR . $path );
use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\EBLBaseComponents\AddressType;
use PayPal\EBLBaseComponents\PaymentDetailsItemType;
use PayPal\EBLBaseComponents\PaymentDetailsType;
use PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType;
use PayPal\EBLBaseComponents\BillingAgreementDetailsType;
use PayPal\PayPalAPI\SetExpressCheckoutRequestType;
use PayPal\PayPalAPI\SetExpressCheckoutReq;
use PayPal\Service\PayPalAPIInterfaceServiceService;

// Starting the session
session_name ( 'ssLogin' );

// Making the cookie live for 2 weeks
session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );

// Start our session
session_start ();
require_once ("../plugins/merchant-sdk-php-3.9.1/samples/PPBootStrap.php");

// this will hold our results/response
$response = array ();

// get our user information
include_once "../php/user.php";
$user = new user ();
if (! $user->isLoggedIn ()) {
    $response ['error'] = "User must be logged in to create an account";
    echo json_encode ( $response );
    exit ();
} else {
    $user = $user->getId ();
}

// retrieve all of our sent information
$IP = $_SERVER ['REMOTE_ADDR'];
$user_details = $order_details = $coupon = "";
if (isset ( $_POST ['user'] )) {
    $user_details = $_POST ['user'];
} else {
    $response ['error'] = "User details not set";
    echo json_encode ( $response );
    exit ();
}
if (isset ( $_POST ['order'] )) {
    $order_details = $_POST ['order'];
} else {
    $response ['error'] = "Order details not set";
    echo json_encode ( $response );
    exit ();
}
if (isset ( $_POST ['coupon'] )) {
    $coupon = $_POST ['coupon'];
}

// setup our urls for success or failure
$url = $_SERVER ['HTTP_REFERER'];
$returnUrl = "$url?paypal=success";
$cancelUrl = "$url?paypal=failure";

// setup our payment details class to hold everything
$PaymentDetails = new PaymentDetailsType ();

// setup our client address
$address = new AddressType ();
$address->CityName = $user_details ['city'];
$address->Name = $user_details ['name'];
$address->Street1 = $user_details ['address'];
$address->StateOrProvince = $user_details ['state'];
$address->PostalCode = $user_details ['zip'];
$address->Country = "US";
$address->Phone = $user_details ['phone'];
$address->ExternalAddressID = $user_details ['email'];

// setup our general pricing information fees
$currencyCode = "USD";
$taxPercent = 0.06;
$itemTotalValue = 0;
$taxTotalValue = 0;

// generate our items to order
require_once "../php/sql.php";
$conn = new sql ();
$conn->connect ();
$sql = "SELECT * FROM `cart` JOIN `album_images` ON `cart`.`image` = `album_images`.`sequence` AND `cart`.`album` = `album_images`.`album` JOIN `products` ON `cart`.`product` = `products`.`id` JOIN `product_types` ON `products`.`product_type` = `product_types`.`id` WHERE `cart`.`user` = '$user';";
$result = mysqli_query ( $conn->db, $sql );
$counter = 0;
while ( $item = mysqli_fetch_assoc ( $result ) ) {
    $itemAmount = new BasicAmountType ( $currencyCode, $item ['price'] );
    $itemTotalValue += $item ['price'] * $item ['count'];
    $taxTotalValue += $item ['price'] * $item ['count'] * $taxPercent;
    
    $itemDetails = new PaymentDetailsItemType ();
    $itemDetails->Name = $item ['name'] . " - " . $item ['size'];
    $itemDetails->Amount = $itemAmount;
    $itemDetails->Quantity = $item ['count'];
    if ($item ['name'] === "Negatives") {
        $itemDetails->ItemCategory = "Digital";
    } else {
        $itemDetails->ItemCategory = "Physical";
    }
    $itemDetails->Tax = new BasicAmountType ( $currencyCode, $item ['price'] * $taxPercent );
    
    $PaymentDetails->PaymentDetailsItem [$counter] = $itemDetails;
    $counter ++;
}
$conn->disconnect ();

// calculate our item total values
$shippingTotal = 0;
$handlingTotal = 0;
$insuranceTotal = 0;
$orderTotalValue = $shippingTotal + $handlingTotal + $insuranceTotal + $itemTotalValue + $taxTotalValue;

// accumulate all calculated information
$PaymentDetails->ShipToAddress = $address;
$PaymentDetails->ItemTotal = new BasicAmountType ( $currencyCode, $itemTotalValue );
$PaymentDetails->OrderTotal = new BasicAmountType ( $currencyCode, $orderTotalValue );
$PaymentDetails->TaxTotal = new BasicAmountType ( $currencyCode, $taxTotalValue );
$PaymentDetails->PaymentAction = "Sale";

$PaymentDetails->HandlingTotal = new BasicAmountType ( $currencyCode, $handlingTotal );
$PaymentDetails->InsuranceTotal = new BasicAmountType ( $currencyCode, $insuranceTotal );
$PaymentDetails->ShippingTotal = new BasicAmountType ( $currencyCode, $shippingTotal );

$setECReqDetails = new SetExpressCheckoutRequestDetailsType ();
$setECReqDetails->PaymentDetails [0] = $PaymentDetails;
$setECReqDetails->CancelURL = $cancelUrl;
$setECReqDetails->ReturnURL = $returnUrl;

// Shipping details
$setECReqDetails->NoShipping = "2";
$setECReqDetails->AddressOverride = "1";
$setECReqDetails->ReqConfirmShipping = "0";

// Billing agreement
$billingAgreementDetails = new BillingAgreementDetailsType ( "None" );
$billingAgreementDetails->BillingAgreementDescription = "Processing will begin once payment is recieved. Images should be recieved within 2 weeks of order process.";
$setECReqDetails->BillingAgreementDetails = array (
        $billingAgreementDetails 
);
$setECReqDetails->SolutionType = "Sole";

// Display options
$setECReqDetails->cppheaderimage = "https://saperstonestudios.com/Includes/images/2014websitelogo250px.png";
$setECReqDetails->cppheaderbordercolor = "9DCB3B";
$setECReqDetails->cppheaderbackcolor = "9DCB3B";
$setECReqDetails->cpppayflowcolor = "9DCB3B";
$setECReqDetails->cppcartbordercolor = "9DCB3B";
$setECReqDetails->cpplogoimage = "";
$setECReqDetails->PageStyle = "";
$setECReqDetails->BrandName = "Saperstone Studios";

// Advanced options
$setECReqDetails->AllowNote = "1";

$setECReqType = new SetExpressCheckoutRequestType ();
$setECReqType->SetExpressCheckoutRequestDetails = $setECReqDetails;
$setECReq = new SetExpressCheckoutReq ();
$setECReq->SetExpressCheckoutRequest = $setECReqType;

$paypalService = new PayPalAPIInterfaceServiceService ( Configuration::getAcctAndConfig () );
try {
    /* wrap API method calls on the service object with a try catch */
    $setECResponse = $paypalService->SetExpressCheckout ( $setECReq );
} catch ( Exception $ex ) {
    include_once ("../plugins/merchant-sdk-php-3.9.1/samples/Error.php");
    exit ();
}
if (isset ( $setECResponse )) {
    if ($setECResponse->Ack == 'Success' || $setECResponse->Errors[0]->LongMessage == 'This transaction cannot be processed. The amount to be charged is zero.') {
        // TODO - do stuff
        
        // set our success token
        $token = $setECResponse->Token;
        
        // clear out our shopping cart
        $conn = new sql ();
        $conn->connect ();
        $sql = "DELETE FROM `cart` WHERE `user` = '$user';";
        mysqli_query ( $conn->db, $sql );
        $conn->disconnect ();
        
        // markup purchases items as purchases
        // TODO
        
        // set our response
        if ($setECResponse->Ack == 'Success') {
            $response ['url'] = "https://www.paypal.com/webscr?cmd=_express-checkout&token=$token\n";
        }
        
        // send a success email
        // TODO
    } else {
        // send a failure email
        // TODO
    }

    // gather our response information from paypal, and return it
    $response ['response'] = get_object_vars( $setECResponse );
    $response ['payment'] = get_object_vars( $PaymentDetails );
    echo json_encode ( $response );
    exit ();
}