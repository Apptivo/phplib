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
 
//Only execute the api calls when data is being submitted.  Will either print a standard message, or return the response from API call.
if(isset($_POST['lead_firstname']))
{		
	//Create array of common fields for this lead
	$lead_data = Array (
		'firstName' => urlencode($_POST['lead_firstname']),
		'lastName' => urlencode($_POST['lead_lastname']),
		'jobTitle' => urlencode($_POST['lead_job_title']),
		'companyName' => urlencode($_POST['lead_company']),
		'description' => urlencode($_POST['lead_description']),
		'firstName' => urlencode($_POST['lead_firstname'])
	);
	
	//These are some mandatory values that won't come from the web form.  You can hard-code this, or apply some logic.  An example would be to change the assignee of a lead, based on the address submitted in the form, so you can distribute leads based on region.
	$lead_data['assigneeObjectRefId'] = '18767';
	$lead_data['assigneeObjectRefName'] = urlencode('Kenny Clark');
	$lead_data['referredById'] = '18767';
	$lead_data['referredByName'] = urlencode('Kenny Clark');
	$lead_data['leadStatus'] = '6826705';
	$lead_data['leadStatusMeaning'] = 'New';
	$lead_data['leadSource'] = '6827230';
	$lead_data['leadSourceMeaning'] = 'Other';
	$lead_data['leadRank'] = '6826692';	
	$lead_data['leadRankMeaning'] = 'High';
	
	
	//Now we'll build Arrays for each type of grouped data: phone numbers, emails, addresses, and custom fields
	
		//Phone Fields 
		$phone_numbers = Array(
			Array(
				'phoneType' => $_POST['phone_type_1'],
				'phoneNumber' => $_POST['phone_number_1']
			),
			Array(
				'phoneType' => $_POST['phone_type_2'],
				'phoneNumber' => $_POST['phone_number_2']
			)
		);
		
		//Email Fields 		
		$emails = Array(
			Array(
				'emailType' => $_POST['email_type_1'],
				'emailAddress' => $_POST['email_address_1']
			),
			Array(
				'emailType' => $_POST['email_type_2'],
				'emailAddress' => $_POST['email_address_2']
			)
		);
		
		//Address Fields
		$state_arr = explode(',', $_POST['address_state']); //Address State is a comma separated value with [State ID, State Name]		
		$addresses = Array (
			Array (
				'addressTypeCode' => 1,
				'addressType' => urlencode('Billing Address'),
				'addressLine1' => urlencode($_POST['address_1']),
				'addressLine2' => urlencode($_POST['address_2']),
				'city' => urlencode($_POST['address_city']),
				'stateCode' => $state_arr[0],
				'state' => urlencode($state_arr[1]),
				'zipCode' => $_POST['address_zip'],
				'countryId' => 176,
				'countryName' => urlencode('United States')
			),
			//Hard-coding to shipping address as well, for an example of multi-address usage
			Array (
				'addressTypeCode' => 1,
				'addressType' => urlencode('Shipping Address'),
				'addressLine1' => urlencode($_POST['address_1']),
				'addressLine2' => urlencode($_POST['address_2']),
				'city' => urlencode($_POST['address_city']),
				'stateCode' => $state_arr[0],
				'state' => urlencode($state_arr[1]),
				'zipCode' => $_POST['address_zip'],
				'countryId' => 176,
				'countryName' => urlencode('United States')
			)
		);
		
		//Custom Fields.  The attribute IDs need to be hard-coded.  Find attribute ID's by inspecting element inside of the Apptivo App.
		$custom_attributes = Array(
			Array (
				'customAttributeType' => 'select',
				'id' => 'attr_11756_8834_select_6950d1d89a0a96715e5a350129e90346',
				'customAttributeValue' => $_POST['lead_isp']
			),
			Array (
				'customAttributeType' => 'input',
				'id' => 'attribute_input_1390553045821_8872',
				'customAttributeValue' => $_POST['lead_speed']
			)
		);
	
	//Finally, we call the method to create a lead.  Returns a success/failure message
	$form_message = $apptivo->create_lead($lead_data, $phone_numbers, $addresses, $emails, $custom_attributes);
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
					<input type="text" name="lead_firstname" /><br />
					<label>Last Name: </label>
					<input type="text" name="lead_lastname" /><br />
					<label>Company: </label>
					<input type="text" name="lead_company" /><br />
					<label>Job Title: </label>
					<input type="text" name="lead_job_title" /><br />
					<label>Email: </label>
					'.$apptivo->get_email_type_dropdown_html('1').'<br />
					<label>Alternate Email: </label>
					'.$apptivo->get_email_type_dropdown_html('2').'<br />
					<label>Phone: </label>
					'.$apptivo->get_phone_type_dropdown_html('1').'<br />
					<label>Alternate Phone: </label>
					'.$apptivo->get_phone_type_dropdown_html('2').'<br />
					<label>ISP: </label>
					<select name="lead_isp" id="lead_isp">
						<option name="Comcast">Comcast</option>
						<option name="AT&T">AT&T</option>
						<option name="Fios">Fios</option>
						<option name="Google Fiber">Google Fiber</option>
						<option name="Time Warner">Time Warner</option>
					</select><br />
					<label>Internet Speed: </label>
					<input type="text" name="lead_speed" /><br />
					<label>Comments: </label>
					<textarea name="lead_description"></textarea><br />
					<label>Address 1: </label>
					<input type="text" name="address_1" /><br />
					<label>Address 2: </label>
					<input type="text" name="address_2" /><br />
					<label>City: </label>
					<input type="text" name="address_city" /><br />
					<label>State: </label>
					'.$apptivo->get_state_dropdown_html('176').'<br />
					<label>Zip: </label>
					<input type="text" name="address_zip" /><br />
					<input type="submit" value="Submit" />
				</form>
			');
			/* END Web Form HTML*/
		?>
	</body>
</html>
