<?php

//Only execute the api calls when data is being submitted
if(isset($_POST['case_subject']))
{
	//Set the Apptivo environment.  This area requires configuration.
	//Supply the user you want to authentica with, and the API & Access keys for the business
	// *****START CONFIGURATION*****
		$api_environment = 'api.apptivo.com';
		$user_email_address = 'admin@glocialtech.com';  //Replace this with your user account email address
		$api_key = 'cb83cbc3-7efc-4457-9beb-a72871187cea'; // Replace this with your business api key
		$access_key = 'grxPZSZKvEtB-eIArCNDnLNXl-0910a13e-651b-4e63-8175-86cb8f243b2a';  //Replace this with your business access key
	// *****END CONFIGURATION*****

	//Support case info collected from the web form
		$case_subject = $_POST['case_subject'];
		$case_description = $_POST['case_description'];
		$customer_email_address = $_POST['customer_email'];
		$contact_email_address = $_POST['customer_email'];

	//Initialize cUrl
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,   0);
		curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,   0);

	//Check if this customer exists in the system
		$api_get_customer_url = 'https://'.$api_environment.'/app/dao/customer?a=getAllcustomersByEmailId&apiKey='.$api_key.'&accessKey='.$access_key.'&assigneeId=30389&assignedType=employee&emailId='.$customer_email_address.'&userName='.$user_email_address;
		curl_setopt($ch, CURLOPT_URL, $api_get_customer_url);

		$dat_result = curl_exec($ch);
		
		$api_response = json_decode($dat_result);

		if($api_response->numResults > 0)
		{
			//A customer was found, create the case and attach to customer
			$case_subject = urlencode($case_subject);
			$case_description = urlencode($case_description);

			$api_request_url = 'https://'.$api_environment.'/app/dao/cases?a=createCase&caseDetails={"caseNumber":"Auto+generated+number","subject":"'.$case_subject.'","description":"'.$case_description.'","contactEmailId":"'.$contact_email_address.'","customerEmailId":"'.$customer_email_address.'","resType":"mobile"}&apiKey='.$api_key.'&accessKey='.$access_key.'&userName='.$user_email_address;

			
			curl_setopt($ch, CURLOPT_URL, $api_request_url);
			$api_response = json_decode(curl_exec($ch));

			if($api_response && $api_response->responseCode == '0')
			{
				$form_message = 'Your case was successfully submitted.';
			}else{
				$form_message = 'Sorry, there was an error submitting your information.  Please contact us directly.';
			}
		}else{
			//No customer exists, return an error
			$form_message = 'An error occured.  This email address cannot be found in the system, please try again, or contact us directly.';
		}

	//Clean up cUrl object
		curl_close($ch);
}else{
	$form_message = 'Please enter your case details below';
}

//Display the HTML form
	echo('
		<html>
			<body>
			'.$form_message.'
			<form action="'.$_SERVER['PHP_SELF'].'" method="post" name="form">
				<label>Subject: </label>
				<input type="text" name="case_subject" />
				<label>Company Email: </label>
				<input type="text" name="customer_email" />
				<label>Description: </label>
				<textarea name="case_description"></textarea>
				<input type="submit" value="Submit" />
			</form>

			</body>
		</html>


	');

?>
