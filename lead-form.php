<?php
/* ABOUT THIS FILE 
   This is a web to lead form that collects details from the user, and submits to the Apptivo REST API to generate a new sales lead.
   This form does not include any complex validation, use it as an example to build one on your website!
*/

// Include the apptivo_toolset class
include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'apptivo_toolset.php');
  
// *****START CONFIGURATION*****
	//Supply the user email you want to authenticate with, and the API & Access keys for the business
	$user_name = 'admin@glocialtech.com';  //Replace this with your user account email address
	$api_key = 'cb83cbc3-7efc-4457-9beb-a72871187cea'; // Replace this with your business api key
	$access_key = 'grxPZSZKvEtB-eIArCNDnLNXl-0910a13e-651b-4e63-8175-86cb8f243b2a';  //Replace this with your business access key
// *****END CONFIGURATION*****

// Initialize the apptivo_toolset object
$apptivo = new apptivo_toolset($api_key, $access_key, $user_name);
 
//Only execute the api calls when data is being submitted.  Will either print a standard message, or return the response from API call.
if(isset($_POST['lead_firstname']))
{		
	//info collected from the web form
	$lead_firstname = $_POST['lead_firstname'];
	$lead_lastname = $_POST['lead_lastname'];
	$lead_description = $_POST['lead_description'];
	$lead_email = $_POST['lead_email'];
	$lead_company_name = $_POST['lead_company'];

	//Address Fields
	$address_1 = $_POST['address_1'];
	$address_2 = $_POST['address_2'];
	$address_city = $_POST['address_city'];
	$address_state = $_POST['address_state'];
	$address_country = '176'; //Hard-coded to United States.  Possible to add a dropdown and allow selection of this.
	$address_zip = $POST['address_zip'];
	
	$form_message = $apptivo->create_lead($lead_firstname, $lead_lastname, $input_phone, $input_job_title, $lead_email, $lead_company_name, $assignee_id, $lead_description, $address_1, $address_2, $address_city, $address_state, $address_country, $address_zip);
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
					<label>Email: </label>
					<input type="text" name="lead_email" /><br />
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
