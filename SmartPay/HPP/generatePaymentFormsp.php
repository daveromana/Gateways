<?php
/*
   =========================================================================================
   =========================================================================================
   =This PHP code provides a payment form for the Barclaycard SmartPay Hosted Payment Pages=
   ====== Version 0.1					                                            ========
   =========================================================================================
   =========================================================================================
*/

/* ========================================================================================
   ==================================  account details  ===================================
   ========================================================================================
	$skinCode
		The skin to be used
	$merchantAccount
		The merchant account we want to process this payment with.
	$sharedSecret
		The shared HMAC key.
*/
	$skinCode           = "SkinCode";
	$merchantAccount    = "AccountName";
	$sharedSecret       = "HMACKey";         // shared HMAC secret for TEST environment
    //  $sharedSecret       = "liveKeyx";            // shared HMAC secret for LIVE environment


/* ========================================================================================
   ===========================  payment-specific details  ============================
   ========================================================================================
	$merchantReference
		This reference will be used in all communication to you about the status of the payment.
	$paymentAmmount
		The payment amount is specified in minor units.
	$currencyCode
		The currency in which the payment is processed.
	$shipBefore
		The date by which the goods or services specified in order must be shipped or rendered.
	$orderDataRaw
		Order Data is a fragment of HTML which will be displayed to the customer on a 
        'review payment' page just before final confirmation of the payment
	$sessionValidity
		The final time by which a payment needs to have been made.
    $shopperReference
        The unique reference to this (registered?) shopper
    $shopperEmail
        The email of the shopper
*/

	$merchantReference = "TestOrder12345"; // The transaction reference you assign to the payment
	$paymentAmount     = 10000;  // Amount in minor units (10000 for 100.00 EUR)
	$currencyCode      = "EUR";  // 3 Digit ISO Currency Code  (e.g. GBP, USD)
	$shipBeforeDate    = date("Ymd" , mktime(date("H"), date("i"), date("s"), date("m"), date("j")+5, date("Y"))); // example: ship in 5 days
	$shopperLocale     = "en_GB"; // Locale (language) to present to shopper (e.g. en_US, nl, fr, fr_BE)
	$orderDataRaw	   = "1 usb MP3 Player"; // A description of the payment which is displayed to shoppers
	$sessionValidity   = date(DATE_ATOM	, mktime(date("H")+1, date("i"), date("s"), date("m"), date("j"), date("Y"))); // example: shopper has one hour to complete
	$shopperReference  = "shopper123"; // the shopper id in our system 
	$shopperEmail      = "shopper@null.com"; // the shopper's email address

/* ========================================================================================
   ==================================  process fields  ====================================
   ========================================================================================
	$merchantSig
		The signature in Base64 encoded format. The is generated by concatenating the values of above fields and computing HMAC over this using the shared secret
		(the shared secret is configured in the Skin in the backoffice)
	$orderData
		The $orderDataRaw which is GZIP compressed and base64 encoded
*/
	//GZIP and base64 encode the orderData
	$orderData = base64_encode(gzencode($orderDataRaw));
	
    // concatenate all the data needed to calculate the HMAC-string in the correct order
    // (please refer to Appendix B in the Integration Manual for more details)
    $hmacData = $paymentAmount . $currencyCode . $shipBeforeDate . $merchantReference . $skinCode 
                . $merchantAccount . $sessionValidity . $shopperEmail . $shopperReference;
	
	// base64 encode the binary result of the HMAC computation. If you use a PHP version < 5.0.12 you
    // you may need to use a different HMAC implementation. Please refer to "Computing the HMAC in PHP"
    // example from the downloads section on https://support.barclaycardsmartpay.com/ 
    $merchantSig = base64_encode(hash_hmac('sha1',$hmacData,$sharedSecret,true));
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Barclaycard SmartPay Payment</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
</head>
<body>
	<form name="smartForm" action="https://test.barclaycardsmartpay.com/hpp/pay.shtml" method="post">
		<input type="hidden" name="merchantReference" value="<?php echo $merchantReference?>" />
		<input type="hidden" name="paymentAmount"     value="<?php echo $paymentAmount?>" />
		<input type="hidden" name="currencyCode"      value="<?php echo $currencyCode?>" />
		<input type="hidden" name="shipBeforeDate"    value="<?php echo $shipBeforeDate?>" />
		<input type="hidden" name="skinCode"          value="<?php echo $skinCode?>" />
		<input type="hidden" name="merchantAccount"   value="<?php echo $merchantAccount?>" />
		<input type="hidden" name="shopperLocale"     value="<?php echo $shopperLocale?>" />
		<input type="hidden" name="orderData"         value="<?php echo $orderData?>" />
		<input type="hidden" name="sessionValidity"   value="<?php echo $sessionValidity?>" />
		<input type="hidden" name="merchantSig"       value="<?php echo $merchantSig?>" />
		<input type="hidden" name="shopperEmail"      value="<?php echo $shopperEmail?>" />
		<input type="hidden" name="shopperReference"  value="<?php echo $shopperReference?>" />
                <input type="hidden" name="allowedMethods"    value="paypal" />
		<input type="submit" name="submit" value="Submit" />
	</form>
</body>
</form>
