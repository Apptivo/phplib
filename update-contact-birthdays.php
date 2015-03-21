<?php
/* ABOUT THIS FILE 
   This is a web to lead form that collects details from the user, and submits to the Apptivo REST API to generate a new sales lead.
   This form does not include any complex validation, use it as an example to build one on your website!
   For details and the most recent code see here: https://github.com/Apptivo/phplib/wiki/Library-Documentation
*/
  
// *****START CONFIGURATION*****
	include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'config.php');
	$configData = getConfig();

	//Apptivo API credentials
	$api_key = $configData['api_key'];
	$access_key = $configData['access_key'];
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
