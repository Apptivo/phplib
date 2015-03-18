<?php
/* ABOUT THIS FILE 
   This file will automatically create a series of activities over the next week for the supplied business.
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

//How many activities should we create?
$max_count = 2;

//Keeping this simple & slow.  Randomly select an object, then we'll create a random type of activity for it
for ($i = 1; $i <= $max_count; $i++) {
	print 'starting activity #'.$i.'<br />';
	//Enter a loop which will select a random object type, check for results, and grab the ID number from a random result
	$object_ready = false;
	Do {
		$random = rand(1,1);
		switch($random) {
			Case 1:
				//Get a list of all contacts
				$all_contact_data = $apptivo->get_all_contacts(0);
				$contact_count = $all_contact_data->countOfRecords;
				if($contact_count > 0) {
					$object_id = $all_contact_data->contacts[0]->contactId;
					print 'contact id='.$object_id.'<br>';
					$object_ready = true;
				}
			break;
		}
	} while ($object_ready == false);
	
	//Now let us pick a random activity type (event, task, follow up) and create it
	$random = rand(1,1);
	switch($random) {
		Case 1:
			//Create an event
			$apptivo->create_event($eventData);
		break;
	}
	
	//We will have a default start date of today, but you can ovverride it if you want to generate activities in the past.  It will generate activities from start date + 5 days into future.
	//$start_date = now();
	
	print 'ending activity #'.$i.'<br />';

}





?>
