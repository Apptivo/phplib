<?php
/* ABOUT THIS FILE 
   This is a web to lead form that collects details from the user, and submits to the Apptivo REST API to generate a new sales lead.
   This form does not include any complex validation, use it as an example to build one on your website!
   For details and the most recent code see here: https://github.com/Apptivo/phplib/wiki/Library-Documentation
*/
  
// *****START CONFIGURATION*****
	//Supply the API & Access keys for your Apptivo account
	$api_key = 'OkUXDZRzRvGt-bFxBXXzSTAYWuV-1368e273-e850-489c-89f1-81c0801034c1'; // Replace this with your business api key
	$access_key = 'q89YdmEVL21R2w85';  //Replace this with your business access key
// *****END CONFIGURATION*****

// Initialize the apptivo_toolset object
include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'apptivo_toolset.php');
$apptivo = new apptivo_toolset($api_key, $access_key);

$startIndex = 0;
$all_contact_data = $apptivo->get_all_contacts($startIndex);
$contact_count = $all_contact_data->countOfRecords;

Do{
	//Cycle through 50 contacts at a time
	$all_contact_data = $apptivo->get_all_contacts($startIndex);
	
	//Cycle through the list of contacts and process them one-by-one
	foreach($all_contact_data->contacts as $current)
	{
		// This counter is to pause the script every ~100 requests
		$counter = $counter + 1;
		//Now let's perform our logic to determine if we want to process this contact.  Checks here are (1) Does it have a customer value? and (2) Does it have an "Organization" value?
		if($current->dateOfBirth == "12/31/1969")
		{
			//Now let's build a simple contact array passing in an empty birthday value
			$contact_data = Array (
				'dateOfBirth' => '',
			);	
			$attributeName = Array (
				'dateOfBirth'
			);	
			//Next we're going to update the accountName (customer) field on the contact	
			$updated_contact = $apptivo->update_contact($current->contactId, json_encode($attributeName), json_encode($contact_data));
		}
	}	

	// Now let's pull the next 50 contacts to process
	$startIndex = $startIndex + 50;

} while ($startIndex < $contact_count || $startIndex == 0);

print 'All Done';

?>
