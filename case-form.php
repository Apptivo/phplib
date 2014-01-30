<?php
/* ABOUT THIS FILE 
   This is a web to lead form that collects details from the user, and submits to the Apptivo REST API to generate a new sales lead.
   This form does not include any complex validation, use it as an example to build one on your website!
   For details and the most recent code see here: https://github.com/Apptivo/phplib/wiki/Library-Documentation
*/
  
// *****START CONFIGURATION*****
	//Supply the API & Access keys for your Apptivo account
	$api_key = 'cb83cbc3-7efc-4457-9beb-a72871187cea'; // Replace this with your business api key
	$access_key = 'grxPZSZKvEtB-eIArCNDnLNXl-0910a13e-651b-4e63-8175-86cb8f243b2a';  //Replace this with your business access key
// *****END CONFIGURATION*****

// Initialize the apptivo_toolset object
include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'apptivo_toolset.php');
$apptivo = new apptivo_toolset($api_key, $access_key);

if(isset($_POST['caseSummary']))
{	
	//Create array of common fields for this lead
	$case_data = Array (
		'caseSummary'=> urlencode($_POST['caseSummary']),
		'description'=> urlencode($_POST['description']),
		'caseCustomer'=> 'maxitron',
		'caseCustomerId'=> '416958',
		'caseContact'=> 'Todd+Miner',
		'caseContactId'=> '852122',
		'caseEmail'=> urlencode('tod3252345d@apptivo.com')
	);
	//These are some mandatory values that won't come from the web form.  You can hard-code this, or apply some logic.  An example would be to change the assignee of a lead, based on the address submitted in the form, so you can distribute leads based on region.
	$case_data['assignedObjectRefId'] = '18767';
	$case_data['assignedObjectRefName'] = urlencode('Kenny Clark');
	$case_data['caseNumber'] = 'Auto+generated+number';
	
	//temp hard-code, need to dynamically produce
	$case_data['caseType'] = 'Other';
	$case_data['caseTypeId'] = '6826995';
	$case_data['caseStatus'] = 'New';
	$case_data['caseStatusId'] = '6826985';
	$case_data['casePriority'] = 'Medium';
	$case_data['casePriorityId'] = '6826984';
	
	//Check if this customer exists in the system
	/*  Just hard-coding the customer value for now to ensure the create_case method works OK.  Will introduce this as common method next.
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
	*/
	
	//Finally, we call the method to create a case.  Returns a success/failure message
	$form_message = $apptivo->create_case($case_data, $phone_numbers, $addresses, $emails, $custom_attributes);
}else{
	$form_message = 'Please complete the form below to proceed';
}
?>
<html>
	<body>
		<?php
			/* START Web Form HTML*/
			echo('
				<div class="form_message">'.$form_message.'</div>
				<form class="apptivo_form" action="'.$_SERVER['REQUEST_URI'].'" method="post" name="form">
					<label>First Name: </label>
					<input type="text" name="firstName" /><br />
					<label>Last Name: </label>
					<input type="text" name="lastName" /><br />
					<label>Company: </label>
					<input type="text" name="companyName" /><br />
					<label>Email: </label>
					'.$apptivo->get_email_type_dropdown_html('1').'<br />
					<label>Quick Summary: </label>
					<input type="text" name="caseSummary" /><br />
					<label>Additional Details: </label>
					<textarea name="description"></textarea><br />
					<input type="submit" value="Submit" />
				</form>
			');
			/* END Web Form HTML*/
		?>
	</body>
</html>

