<?php
//Single meeting creation with curl.  URL encode the subject, and start/end time date format should be: MM/DD/YYYYTHH:MM:SS (use 24 hour format).  Also need to pass in your g2m account credentials.

/* Sample function call
	$subject = urlencode('Proposal for Deerfield');
	$g2mStartTime = '03/15/2015T14:30:00';
	$g2mEndTime = '03/15/2015T16:00:00';

	$new_g2m_object = create_g2m_mtg($subject,$g2mStartTime,$g2mEndTime, 'gotomeeting@apptivo.com','SecurePassword','W3GHC1kRdH3T3os1Jnb4tCaWJnbHVtCaLWH3');
*/

function create_g2m_mtg($subject, $starttime, $endtime, $user_id, $password, $client_id) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_URL, 'https://api.citrixonline.com/oauth/access_token?grant_type=password&user_id='.$user_id.'&password='.$password.'client_id='.$client_id);
	
	$api_result = curl_exec($ch);
	$api_response = json_decode($api_result);

	$mtg_data = array (
		'subject' => $subject,
		'starttime' => $starttime,
		'endtime' => $endtime,
		'passwordrequired' => 'false',
		'conferencecallinfo' => 'Hybrid', // normal PSTN + VOIP options
		'timezonekey' => '',
		'meetingtype' => 'Scheduled'
	);
	$mtg_data_json = json_encode ($mtg_data);

	$url = "https://api.citrixonline.com/G2M/rest/meetings";
	$headers = array("Accept: application/json", "Content-Type: application/json", "Authorization: OAuth oauth_token=$api_response->access_token");
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $mtg_data_json);
	
	$results = curl_exec ($ch);
	curl_close ($ch);
	
	return json_decode($results);
}
?>