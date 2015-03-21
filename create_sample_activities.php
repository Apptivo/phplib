<?php
/* ABOUT THIS FILE 
   This file will automatically create a series of activities over the next week for the supplied business.
   For details and the most recent code see here: https://github.com/Apptivo/phplib/wiki/Library-Documentation
*/

// *****START CONFIGURATION*****
	include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'config.php');
	$configData = getConfig();

	//Apptivo API credentials
	$api_key = $configData['api_key'];
	$access_key = $configData['access_key'];
	$user_name = $configData['user_name'];
// *****END CONFIGURATION*****

// Initialize the apptivo_toolset object
include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'apptivo_toolset.php');
$apptivo = new apptivo_toolset($api_key, $access_key);

//How many activities should we create?
$max_count = 5;

//Keeping this simple & slow.  Randomly select an object, then we'll create a random type of activity for it
for ($i = 1; $i <= $max_count; $i++) {
	print 'starting activity #'.$i.'<br />';
	//Enter a loop which will select a random object type, check for results, and grab the ID number from a random result
	$object_ready = false;
	Do {
		$random = rand(1,1);
		$r = rand(0,49);
		switch($random) {
			Case 1:
				//Get a list of all contacts
				$all_contact_data = $apptivo->get_all_contacts($r);
				$contact_count = $all_contact_data->countOfRecords;
				if($contact_count > 0) {
					$object_id = $all_contact_data->contacts[$r]->contactId;
					print 'contact id='.$object_id.'<br>';
					$object_ready = true;
					
					$associated_object = Array (
						'objectId' => 2,
						'objectRefId' => $all_contact_data->contacts[$r]->contactId,
						'objectRefName' => urlencode($all_contact_data->contacts[$r]->firstName.' '.$all_contact_data->contacts[$r]->lastName),
						'objectName' => 'Contact'
					);
				}
			break;
		}
	} while (!$associated_object);
	
	//Now let us pick a random activity type (event, task, follow up) and create it.  2 Random numbers so we can randomize content for activities.
	$random1 = rand(1,1);
	$random2 = rand(1,6);

	
	print_r($day);
	switch($random1) {
		Case 1:
			//Event
			switch($random2) {
				Case 1:
					$subject = 'Lunch meeting with '.urldecode($associated_object['objectRefName']);
					$location = 'PF Changs @ Market Place';
					$startTime = date("m/d/Y",strtotime('+'.$random2.' days')).' 12:00';
					$endTime = date("m/d/Y",strtotime('+'.$random2.' days')).' 14:30';
				break;
				Case 2:
					$subject = 'Software demo for '.urldecode($associated_object['objectRefName']);
					$location = 'Online';
					$startTime = date("m/d/Y",strtotime('+'.$random2.' days')).' 10:00';
					$endTime = date("m/d/Y",strtotime('+'.$random2.' days')).' 11:30';
				break;
				Case 3:
					$subject = 'Review proposal with '.urldecode($associated_object['objectRefName']);
					$location = 'G2M';
					$startTime = date("m/d/Y",strtotime('+'.$random2.' days')).' 7:00';
					$endTime = date("m/d/Y",strtotime('+'.$random2.' days')).' 7:45';
				break;
				Case 4:
					$subject = 'Breakfast with '.urldecode($associated_object['objectRefName']);
					$location = 'Hobees Sunnyvale';
					$startTime = date("m/d/Y",strtotime('+'.$random2.' days')).' 6:00';
					$endTime = date("m/d/Y",strtotime('+'.$random2.' days')).' 6:30';
				break;
				Case 5:
					$subject = 'Weekly review call - '.urldecode($associated_object['objectRefName']);
					$location = 'Conference Room A-4';
					$startTime = date("m/d/Y",strtotime('+'.$random2.' days')).' 15:00';
					$endTime = date("m/d/Y",strtotime('+'.$random2.' days')).' 15:45';
				break;
				Case 6:
					$subject = 'Dinner meeting with - '.urldecode($associated_object['objectRefName']);
					$location = 'Shenanigans';
					$startTime = date("m/d/Y",strtotime('+'.$random2.' days')).' 18:00';
					$endTime = date("m/d/Y",strtotime('+'.$random2.' days')).' 19:30';
				break;
			}

			$start_arr = explode(' ',$startTime);
			$start_time_arr = explode(':',$start_arr[1]);
			$end_arr = explode(' ',$endTime);
			$end_time_arr = explode(':',$end_arr[1]);
			$apptivoStartDate = $start_arr[0];
			$apptivoEndDate = $end_arr[0];
			$apptivoStartTimeMinute = $start_time_arr[1];
			$apptivoEndTimeMinute = $end_time_arr[1];
			if(intval($start_time_arr[0]) > 12) {
				$apptivoStartTimeMeridian = 1;
				$apptivoStartTimeHour = intval($start_time_arr[0])-12;
			}elseif(intval($start_time_arr[0]) == 12){
				$apptivoStartTimeMeridian = 1;
				$apptivoStartTimeHour = '12';
			}else{
				$apptivoStartTimeMeridian = 0;
				$apptivoStartTimeHour = $start_time_arr[0];
			}
			if(intval($end_time_arr[0]) > 12) {
				$apptivoEndTimeMeridian = 1;
				$apptivoEndTimeHour = intval($end_time_arr[0])-12;
			}elseif(intval($end_time_arr[0]) == 12){
				$apptivoEndTimeMeridian = 1;
				$apptivoEndTimeHour = 12;
			}else{
				$apptivoEndTimeMeridian = 0;
				$apptivoEndTimeHour = $end_time_arr[0];
			}
			
			print '<br><br>'.$apptivoStartDate.'<br><br>';
			//Create an event
			$eventData = Array (
				'subject' => urlencode($subject),
				'location' => urlencode($location),
				'assigneeDetails' => Array (
					Array (
						'objectId' => 8,
						'objectRefId' => 18767,
					)
				),
				'associatedObjects' => Array (
					$associated_object
				),
				'startDate' => urlencode($apptivoStartDate),
				'endDate' => urlencode($apptivoEndDate),
				'allDayEvent' => 'N',
				'startTimeHour' => $apptivoStartTimeHour,
				'startTimeMinute' => $apptivoStartTimeMinute,
				'startTimeMeridian' => $apptivoStartTimeMeridian,
				'endTimeHour' => $apptivoEndTimeHour,
				'endTimeMinute' => $apptivoEndTimeMinute,
				'endTimeMeridian' => $apptivoEndTimeMeridian,
			);
			$created_event = $apptivo->create_event($eventData);
			if($created_event) {
				print 'Just successfully created an event<br>';
			}else{
				print 'Just FAILED to created an event<br>';
			}
		break;
	}
	
	//We will have a default start date of today, but you can ovverride it if you want to generate activities in the past.  It will generate activities from start date + 5 days into future.
	//$start_date = now();
	
	print 'ending activity #'.$i.'<br />';

}





?>
