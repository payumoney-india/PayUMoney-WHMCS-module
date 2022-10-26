<?php

include("../../../init.php");
include("../../../includes/functions.php");
include("../../../includes/gatewayfunctions.php");
include("../../../includes/invoicefunctions.php");

HEADER("refresh:1 url=.$redirect");
$redirect = $GATEWAY['systemurl'].'viewinvoices.php?id=' . $invoiceid;

$gatewaymodule = "payumoney"; #Gateway Module Name here.

$GATEWAY = getGatewayVariables($gatewaymodule);
if (!$GATEWAY["type"]) die("Module Not Activated"); #If Gateway is not activated then do not accept callback request.

$response = array();
$response = $_POST;

#Get Returned variables 
$status = $response["status"];
$fee = $response['amount'];
$amount = $response["amount"];
$invoiceid = $response["txnid"];
$transid = $response["txnid"];

#Check if invoice ID is a valid invoice number and transaction id is not already exist.
$invoiceid = checkCbInvoiceID($invoiceid, 'payumoney');
checkCbTransID($transid); 

#Make sure that Payumoney response is successfull else do not mark invoice as paid.


if($response['status']==='success' AND $response['unmappedstatus']==='captured' AND $response['country']==='IN' AND $response['field9'] !== 'Cancelled by user') {
    # Successful
    
    addInvoicePayment($invoiceid, $transid, $amount, $gatewaymodule, null); # Apply Payment to Invoice: invoiceid, transactionid, amount paid, fees, modulename
	logTransaction($GATEWAY["name"],$response,"Successful"); # Save to Gateway Log: name, data array, status
	$redirect_url = $systemurl.'viewinvoice.php?id='.$invoiceid;
$filename = $GATEWAY['systemurl'].'/viewinvoice.php?id='.$invoiceid.'&paymentsuccess=true';
HEADER("location:$filename");	
} 

else {
	#Unsuccessful
    logTransaction($GATEWAY["name"],$response,"Unsuccessful"); # Save to Gateway Log: name, data array, status
    $redirect_url = $systemurl.'viewinvoice.php?id='.$invoiceid.'&paymentfailed=true';

$filename = $GATEWAY['systemurl'].'/viewinvoice.php?id='.$invoiceid.'&paymentfailed=true';    // path of your viewinvoice.php
HEADER("location:$filename");
}


?>
