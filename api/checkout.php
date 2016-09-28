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
    $response ['error'] = "User must be logged in to submit their order.";
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
$returnUrl = "$url&paypal=success";
$cancelUrl = "$url&paypal=failure";

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
$PaymentDetails->ShipToAddress = $address;

// setup our user/shipping information to email
$user_text = "\t\t" . $user_details ['name'] . "\t" . $user_details ['email'] . "\t" . $user_details ['phone'] . "\n";
$user_text .= "\t\t" . $user_details ['address'] . "\n";
$user_text .= "\t\t" . $user_details ['city'] . ", " . $user_details ['state'] . " " . $user_details ['zip'] . "\n";

$user_HTML = "<table><tr><td>" . $user_details ['name'] . "</td><td>" . $user_details ['email'] . "</td><td>" . $user_details ['phone'] . "</td></tr>";
$user_HTML .= "<tr><td colspan=3>" . $user_details ['address'] . "</td></tr>";
$user_HTML .= "<tr><td colspan=2>" . $user_details ['city'] . ", " . $user_details ['state'] . "</td><td>" . $user_details ['zip'] . "</td></tr></table>";

// setup our general pricing information fees
$currencyCode = "USD";
$taxPercent = 0.06;
$itemTotalValue = 0;
$taxTotalValue = 0;

// our basic email information
$order_text = "";
$order_HTML = "<table><tr><th>Name</th><th>Album</th><th>Preview</th><th>Product</th><th>Size</th><th>Item Option</th><th>Price</th></tr>";

// generate our items to order
require_once "../php/sql.php";
$conn = new sql ();
$conn->connect ();
$sql = "SELECT `cart`.*,`album_images`.*,`products`.*,`product_types`.*,`albums`.`name` AS album_title FROM `cart` JOIN `album_images` ON `cart`.`image` = `album_images`.`sequence` AND `cart`.`album` = `album_images`.`album` JOIN `products` ON `cart`.`product` = `products`.`id` JOIN `product_types` ON `products`.`product_type` = `product_types`.`id` JOIN `albums` ON `cart`.`album` = `albums`.`id` WHERE `cart`.`user` = '$user';";
$result = mysqli_query ( $conn->db, $sql );
$counter = 0;
while ( $item = mysqli_fetch_assoc ( $result ) ) {
    $options = getOptions ( $order_details, $item ['product'], $item ['image'] );
    for($i = 0; $i < $item ['count']; $i ++) {
        $itemAmount = new BasicAmountType ( $currencyCode, $item ['price'] );
        $itemTotalValue += $item ['price'];
        $taxTotalValue += $item ['price'] * $taxPercent;
        
        $itemDetails = new PaymentDetailsItemType ();
        $itemDetails->Name = $item ['name'] . " - " . $item ['size'];
        $itemDetails->Number = $item ['product'];
        $itemDetails->ProductCategory = ucwords ( $item ['category'] );
        $itemDetails->Amount = $itemAmount;
        $itemDetails->Quantity = 1;
        if ($item ['name'] === "Negatives") {
            $itemDetails->ItemCategory = "Digital";
        } else {
            $itemDetails->ItemCategory = "Physical";
        }
        $itemDetails->Tax = new BasicAmountType ( $currencyCode, $item ['price'] * $taxPercent );
        
        $PaymentDetails->PaymentDetailsItem [$counter] = $itemDetails;
        $counter ++;
        
        // our information about the product for the email
        $option = array_pop ( $options );
        $preview = "<img src='http" . (isset ( $_SERVER ['HTTPS'] ) ? 's' : '') . "://" . $_SERVER ['HTTP_HOST'] . $item ['location'] . "' style='max-width:100px;max-height:100px;' alt='" . $item ['title'] . "' />";
        $order_text .= "\t\t" . $item ['title'] . "\t" . $item ['album_title'] . "\t" . $item ['name'] . "\t" . $item ['size'] . "\t$option\t\t$" . $item ['price'] . "\n";
        $order_HTML .= "<tr><td>" . $item ['title'] . "</td><td>" . $item ['album_title'] . "</td><td>$preview</td><td>" . $item ['name'] . "</td><td>" . $item ['size'] . "</td><td>$option</td><td>$" . number_format ( $item ['price'], 2 ) . "</td></tr>";
    }
}
$conn->disconnect ();

// finish the order details with the tax and item total
$order_text .= "\t\tTax\t\t\t\t\t\t\t\t\t$" . number_format ( $taxTotalValue, 2 ) . "\n";
$order_text .= "\t\t\t\t\t\t\tTotal Amount: $" . number_format ( $itemTotalValue, 2 ) . "\n";
$order_HTML .= "<tr><td></td><td></td><td></td><td></td><td></td><td></td><td>Tax:</td><td>$" . number_format ( $taxTotalValue, 2 ) . "</td></tr>";
$order_HTML .= "<tr><td></td><td></td><td></td><td></td><td></td><td></td><th>Total:</th><td><b>$" . number_format ( $itemTotalValue, 2 ) . "</b></td></tr></table>";

// group our item orders based on product ID to simplify PayPal invoice
// TODO

// calculate our item total values
$shippingTotal = 0;
$handlingTotal = 0;
$insuranceTotal = 0;
$orderTotalValue = $shippingTotal + $handlingTotal + $insuranceTotal + $itemTotalValue + $taxTotalValue;

// accumulate all calculated information
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
    $to;
    $from = "Orders <orders@saperstonestudios.com>";
    $subject;
    // setup our email body text
    $full_text = "Thank you for choosing to order your prints from Saperstone Studios. Below you will find an invoice for your order";
    $full_HTML = "<html><body><p>$full_text</p>";
    $full_text .= "\n\nShipping Details\n";
    $full_HTML .= "<h4><u>Shiping Details</u></h4>";
    $full_text .= $user_text;
    $full_HTML .= $user_HTML;
    $full_text .= "\n\nInvoice Details\n";
    $full_HTML .= "<h4><u>Invoice Details</u></h4>";
    $full_text .= $order_text;
    $full_HTML .= $order_HTML;
    
    if ($setECResponse->Ack == 'Success' || $setECResponse->Errors [0]->LongMessage == 'This transaction cannot be processed. The amount to be charged is zero.') {
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
        
        // a success email message
        $to = "Orders <orders@saperstonestudios.com>, " . $user_details ['name'] . " <" . $user_details ['email'] . ">";
        $subject = "Saperstone Studios Order Invoice";
        $full_text .= "\n\nAn express checkout for this order has been created through paypal, and can be found at https://www.paypal.com/webscr?cmd=_express-checkout&token=$token\n\nThis message was generated/sent after being submitted via $url";
        $full_HTML .= "<p>An invoice for this order has been created through paypal, and can be found <a href='https://www.paypal.com/webscr?cmd=_express-checkout&token=$token'>here</a></p><p>This message was generated/sent after being submitted via <a href='$url'>your web gallery</a></p></body></html>";
    } else {
        // a failure email message
        $to = "Orders <orders@saperstonestudios.com>";
        $subject = "Failed Saperstone Studios Order";
        $full_text .= "\n\nThis order did not go through due to an error:" . $setECResponse->Errors->LongMessage . ".\n\nYou may want to directly contact this customer.\n\nThis message was generated/sent after being submitted via $url";
        $full_HTML .= "<p>This order did not go through due to an error:" . $setECResponse->Errors->LongMessage . ".</p><p>You may want to directly contact this customer.\n\n</p><p>This message was generated/sent after being submitted via <a href='$url'>your web gallery</a></p></body></html>";
    }
    
    // actually send the email
    require_once "Mail.php";
    require_once "Mail/mime.php";
    $crlf = "\n";
    $mime = new Mail_mime ( $crlf );
    $mime->setTXTBody ( $full_text );
    $mime->setHTMLBody ( $full_HTML );
    $body = $mime->get ();
    require ('../php/email.php');
    
    // gather our response information from paypal, and return it
    $response ['response'] = get_object_vars ( $setECResponse );
    $response ['payment'] = get_object_vars ( $PaymentDetails );
    echo json_encode ( $response );
    exit ();
}
function getOptions($items, $product, $image) {
    $options = array ();
    foreach ( $items as $item ) {
        if ($item ['product'] == $product && $item ['img'] == $image) {
            if (isset ( $item ['option'] )) {
                $options [] = $item ['option'];
            } else {
                $options [] = "";
            }
        }
    }
    return $options;
}