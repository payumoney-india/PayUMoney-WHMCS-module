<?php

function payumoney_config() {
    $configarray = array(
     "FriendlyName" => array("Type" => "System", "Value"=>"PayUMoney"),
     "MERCHANT_KEY" => array("FriendlyName" => "Merchant Key", "Type" => "text", "Size" => "20", "Description" => "Test Key: JBZaLc, Merchant key here as provided by PayUMoney", ),
     "SALT" => array("FriendlyName" => "Merchant Salt", "Type" => "text", "Size" => "20", "Description" => "Test Salt: GQs7yium, Merchant Salt as provided by PayUMoney", ),
     "PAYU_BASE_URL" => array("FriendlyName" => "PayUMoney Base URL", "Type" => "textarea", "Rows" => "1", "Description" => "https://test.payu.in for TEST mode, https://secure.payu.in for LIVE mode", ),
     "service_provider" => array("FriendlyName" => "Service Provider", "Type" => "text", "Size" => "20", "Description" => "Eg: payu_paisa", ),
     "surl" => array("FriendlyName" => "Success URL", "Type" => "textarea", "Rows" => "1", "Description" => "Please Enter surl, Eg: http://<yourdomain.com>/clientarea.php", ),
     "furl" => array("FriendlyName" => "Failure URL", "Type" => "textarea", "Rows" => "1", "Description" => "Please Enter furl, Eg: http://<yourdomain.com>/clientarea.php", ),
	 );

	return $configarray;
}

function payumoney_link($params) {

	# Gateway Specific Variables 
	$MERCHANT_KEY = $params['MERCHANT_KEY'];
	$SALT = $params['SALT'];
	$PAYU_BASE_URL = $params['PAYU_BASE_URL'];
	$service_provider = $params['service_provider'];
	$surl = $params['surl'];
	$furl = $params['furl'];
	
	# Invoice Variables
	$invoiceid = $params['invoiceid'];   	
	$productinfo = $params['description'];	
	$amount = $params['amount']; # Format: ##.##
	$currency = $params['currency']; # Currency Code

	
	# Client Variables
	$firstname = $params['clientdetails']['firstname'];
	$lastname = $params['clientdetails']['lastname'];
	$email = $params['clientdetails']['email'];
	$address1 = $params['clientdetails']['address1'];
	$address2 = $params['clientdetails']['address2'];
	$city = $params['clientdetails']['city'];
	$state = $params['clientdetails']['state'];
	$postcode = $params['clientdetails']['postcode'];
	$country = $params['clientdetails']['country'];
	$phone = $params['clientdetails']['phonenumber'];

	# System Variables
	$companyname = $params['companyname'];
	$systemurl = $params['systemurl'];
	$currency = $params['currency'];
	
	
	$action = '';

	$posted = array();
	if(!empty($_POST)) {
		//print_r($_POST);
	  foreach($_POST as $key => $value) {    
		$posted[$key] = $value; 
		
	  }
	}
	
	$formError = 0;
	
	if(empty($posted['txnid'])) {
	  // Generate random transaction id
	  $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
	} else {
	  $txnid = $posted['txnid'];
	}
	$hash = '';
	// Hash Sequence
	$hashSequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
	if(empty($posted['hash']) && sizeof($posted) > 0) {
	  if(
			  empty($posted['key'])
			  || empty($posted['txnid'])
			  || empty($posted['amount'])
			  || empty($posted['firstname'])
			  || empty($posted['email'])
			  || empty($posted['phone'])
			  || empty($posted['productinfo'])
			  || empty($posted['surl'])
			  || empty($posted['furl'])
			  || empty($posted['service_provider'])
	  ) {
		$formError = 1;
	  } else {
		//$posted['productinfo'] = json_encode(json_decode('[{"name":"tutionfee","description":"","value":"500","isRequired":"false"},{"name":"developmentfee","description":"monthly tution fee","value":"1500","isRequired":"false"}]'));
		$hashVarsSeq = explode('|', $hashSequence);
		$hash_string = '';	
		foreach($hashVarsSeq as $hash_var) {
		  $hash_string .= isset($posted[$hash_var]) ? $posted[$hash_var] : '';
		  $hash_string .= '|';
		}
	
		$hash_string .= $SALT;
	
		$hash = strtolower(hash('sha512', $hash_string));
		$action = $PAYU_BASE_URL . '/_payment';
	  }
	} elseif(!empty($posted['hash'])) {
	  $hash = $posted['hash'];
	  $action = $PAYU_BASE_URL . '/_payment';
	}
	

	$code = '
	<script>
		var hash = '.$hash.';
		function submitPayuForm() {
		  if(hash == "") {
			return;
		  }
		  var payuForm = document.forms.payuForm;
		  payuForm.submit();
		}
	</script>

	  <form action="'.$action.'" method="post" name="payuForm" onload="submitPayuForm()" >
		  <input type="hidden" name="key" value="'.$MERCHANT_KEY.'" />
		  <input type="hidden" name="hash" value="'.$hash.'"/>
		  <input type="hidden" name="txnid" value="'.$txnid.'" />
		  <input type="hidden" name="amount" value="'.$amount.'" />
		  <input type="hidden" name="firstname" value="'.$firstname.'" />
		  <input type="hidden" name="email" value="'.$email.'" />
		  <input type="hidden" name="phone" value="'.$phone.'" />
		  <input type="hidden" name="productinfo" value="'.$productinfo.'" />
		  <input type="hidden" name="surl" value="'.$surl.'" />
		  <input type="hidden" name="furl" value="'.$furl.'" />
		  <input type="hidden" name="service_provider" value="'.$service_provider.'" />
      	  <input type="submit" value="Pay Now" />
      </form>
      ';
	  
	  return $code;
}

?>