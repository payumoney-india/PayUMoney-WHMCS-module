<?php

# Required File Includes 
include("../../../AppData/dbconnect.php");
include("../../../AppData/includes/functions.php");
include("../../../AppData/includes/gatewayfunctions.php");
include("../../../AppData/includes/invoicefunctions.php");

$gatewaymodule = "payumoney"; # Enter your gateway module name here replacing template

$GATEWAY = getGatewayVariables($gatewaymodule);
if (!$GATEWAY["type"]) die("Module Not Activated"); # Checks gateway module is active before accepting callback

# Get Returned Variables - Adjust for Post Variable Names from your Gateway's Documentation
$status = $_POST["status"];
#$invoiceid = $_POST["payuMoneyId"];
$transid = $_POST["txnid"];
$amount = $_POST["amount"];

#$invoiceid = checkCbInvoiceID($invoiceid,$GATEWAY["name"]); # Checks invoice ID is a valid invoice number or ends processing

#checkCbTransID($transid); # Checks transaction number isn't already in the database and ends processing if it does

if ($status=="1") {
    # Successful
    addInvoicePayment($transid,$amount,$gatewaymodule); # Apply Payment to Invoice: invoiceid, transactionid, amount paid, fees, modulename
	logTransaction($GATEWAY["name"],$_POST,"Successful"); # Save to Gateway Log: name, data array, status
} else {
	# Unsuccessful
    logTransaction($GATEWAY["name"],$_POST,"Unsuccessful"); # Save to Gateway Log: name, data array, status
}

?>