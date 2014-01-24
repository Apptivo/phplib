<?php

class apptivo_toolset
{
	public $api_key = 'null';
	public $access_key = 'null';
	public $user_name = 'null';
	public $ch;
	public $custom_attributes = true;

	function get_countries()
	{
		$api_url = 'https://api.apptivo.com/app/commonservlet?a=getAllCountries&apiKey='.$this->api_key.'&accessKey='.$this->access_key.'&userName='.$this->user_name;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);

		$dat_result = curl_exec($this->ch);
		$api_response = json_decode($dat_result);

		return $api_response;
	}
	
	function get_states_by_country($input_country_id)
	{
		$api_url = 'https://api.apptivo.com/app/commonservlet?a=getAllStatesByCountryId&countryId='.$input_country_id.'&api_key='.$this->api_key.'&accessKey='.$this->access_key.'&userName='.$this->user_name;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);

		$dat_result = curl_exec($this->ch);
		$api_response = json_decode($dat_result);

		return $api_response;
	}
	
	function get_state_dropdown_html($input_country_id)
	{
		$states = $this->get_states_by_country($input_country_id);
		$output_html = '<select id="address_state" name="address_state">';
		foreach ($states->responseObject as $cur_state)
		{
			$output_html .= '<option name="'.$cur_state->stateCode.','.$cur_state->stateName.'" value="'.$cur_state->stateCode.','.$cur_state->stateName.'">'.$cur_state->stateName.'</option>';	
		}
		$output_html .= '</select>';
		
		return $output_html;
	}
	
	function create_lead($input_first_name, $input_last_name, $input_phone, $input_job_title, $input_email, $input_company_name, $input_assignee_id, $input_description, $input_address_1, $input_address_2, $input_address_city, $input_address_state, $input_address_country, $input_address_zip)
	{
		// Uncommon Assumed/Empty values, these could be abstracted and populated if desired
		$title = 'Mr.';
		$mobile = '';
		$fax = '';
		$easy_way_to_contact = 'EMAIL';
		$referred_by_name = '';
		$referred_by_id = '';
		$country_id = '176';
		$lead_rank_id = '';  //I need to add comments with the three default values.  Let's set to "Normal" out of the box.
		$customer_name = '';  //Need to run API method to get customer details before submitting this value
		$customer_id = '';	//Need to run API method to get customer details before submitting this value
		$assignee_name = '';
		
		$lead_status_id = 'I NEED TO GET THIS VALUE';
		$lead_source_id = 'I NEED TO GET THIS VALUE';
		$assignee_id = 'NEED TO GET THIS';
		$assignee_type = 'Employee'; //This value must be changed if we are assigning to a team
		
		// Sanitize the inputs, doing 1-by-1 in case we want to add individual processing later.  Could refactor this into a single data array to clean up.
		$first_name = urlencode($input_first_name);
		$last_name = urlencode($input_last_name);
		$phone = urlencode($input_phone);
		$job_title = urlencode($input_job_title);
		$email = urlencode($input_email);
		$company_name = urlencode($input_company_name);
		$description = urlencode($input_description);
		// Address fields
		$address_1 = urlencode($input_address_1);
		$address_2 = urlencode($input_address_2);
		$address_city = urlencode($input_address_city);
		//Address State is a comma seperated value with [State Name, State ID]
		$state_arr = explode(',', $input_address_state);		
		$address_state_id = $state_arr[0];
		$address_state = $state_arr[1];
		$address_zip = urlencode($input_address_zip);
		
		if(strlen($address_1) > 0 || strlen($address_state) > 0)
		{			
			$address_line = ',"address_country":"'.$country_id.'"}&addressData1=["-1","'.$country_id.'","'.$address_1.'","'.$address_2.'","'.$address_city.'","'.$address_state.'","'.$address_state_id.'","'.$address_zip.'","'.$country_id.'"]';
		}else{
			$address_line = '}';
		}
		
		if($this->custom_attributes == true)
		{
			$custom_attr = '&customAttributes=[{"id":"attribute_input_1390553045821_8872","customAttributeType":"input","customAttributeName":"attribute_input_1390553045821_8872","customAttributeValue":"dfsgsdfg"}]';
		
		}
		
		// Temporary hard-coded values
		$lead_status_id = '6826705';
		$lead_source_id = '13067790';
		$assignee_id = '30389';
		$lead_rank_id = '6826692';		
	
		$api_url = 'https://api.apptivo.com/app/dao/lead?a=createLead&leadData={"title":"'.$title.'","firstName":"'.$first_name.'","phone":"'.$phone.'","lastName":"'.$last_name.'","mobile":"'.$mobile.'","jobTitle":"'.$job_title.'","fax":"'.$fax.'","easyWayToContact":"'.$easy_way_to_contact.'","emailId":"'.$email.'","leadStatus":"'.$lead_status_id.'","leadSourceType":"'.$lead_source_id.'","refferedByName":"'.$referred_by_name.'","refferedById":"'.$referred_by_id.'","assigneeName":"'.$assignee_name.'","assignedToId":"'.$assignee_id.'","assigneeType":"'.$assignee_type.'","leadRank":"'.$lead_rank_id.'","accountName":"'.$customer_name.'","accountId":"'.$customer_id.'","description":"'.$description.'"'.$address_line.$custom_attr.'&apiKey='.$this->api_key.'&accessKey='.$this->access_key.'&userName='.$this->user_name;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);

		$dat_result = curl_exec($this->ch);
		
		$api_response = json_decode($dat_result);
		
		
		print $api_url;
		
		if($api_response)
		{
			return 'Thank you, your submission has been received!';
		}else{
			return 'Sorry, there seems to have been an error.  Please check with the administrator.';
		}
		
	}

	function __construct($input_apikey, $input_accesskey, $input_username) {
				
		$this->api_key = $input_apikey;
		$this->access_key = $input_accesskey;
		$this->user_name = $input_username;
		
		// Basic curl implementation.  This can be further secured in future.
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_HEADER, 0);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER,   0);
		curl_setopt($this->ch, CURLOPT_SSLVERSION, 3);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST,   0);

	}
	
	function __destruct()
	{
		curl_close($this->ch);
	}

}

?>