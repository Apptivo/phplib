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
	
	function create_lead($input_first_name, $input_last_name, $input_phone, $input_job_title, $input_email, $input_company_name, $input_assignee_id, $input_description, $input_address_1, $input_address_2, $input_address_city, $input_address_state, $input_address_country, $input_address_zip, $input_custom_attributes)
	{
		// Uncommon Assumed/Empty values, these could be abstracted and populated if desired
		$title = 'Mr.';
		$mobile = '';
		$fax = '';
		$easy_way_to_contact = 'EMAIL';
		$way_to_contact = 'Email';
		$referred_by_name = '';
		$referred_by_id = '';
		$country_id = '176';  //Hard-coded to USA for now
		$lead_rank_id = '';  //I need to add comments with the three default values.  Let's set to "Normal" out of the box.
		$lead_type_id = '';
		$lead_type_name = '';
		$skype_name = '';
		$potential_amount = 'null';
		$currency_code = 'USD';
		$estimated_close_date = '';
		$campaign_name = '';
		$campaign_id = 'null';
		$territory_id = 'null';
		$territory_name = '';
		$market_id = 'null';
		$market_name = 'null';
		$segment_id = 'null';
		$segment_name = 'null';
		$follow_up_date = 'null';
		$follow_up_description = 'null';
		$last_updated_by_name = '';
		$created_by_name = '';
		$last_update_date = '';
		$creation_date = '';
		$account_name = '';
		$account_id = 'null';
		$employee_range_id = 'null';
		$employee_range = 'null';
		$annual_revenue = 'null';
		$industry = '';
		$industry_name = '';
		$ownership = '';
		$website = '';
		$facebook = '';
		$twitter = '';
		$linkedin = '';
		
		
		$lead_status_id = 'I NEED TO GET THIS VALUE';
		$lead_source_id = 'I NEED TO GET THIS VALUE';
		$assignee_id = 'NEED TO GET THIS';
		$assignee_name = 'NEED TO GET THIS';
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
			$address_line = ',"addresses":[{"addressAttributeId":"address_section_attr_id","addressTypeCode":"1","addressType":"Billing+Address","addressLine1":"'.$address_1.'","addressLine2":"'.$address_2.'","city":"'.$address_city.'","stateCode":"'.$address_state_id.'","state":"'.$address_state.'","zipCode":"'.$address_zip.'","countryId":176,"countryName":"United+States"}]';
		}
		
		//Check to see if we passed in an array of custom attributes.  This array contains one or more attributes, and each attribute should have 3 values comma separated (attribute type, attribute id, attribute value)
		if(strlen($input_custom_attributes > 0))
		{
			$custom_attr = ',"customAttributes":[';
			$first_val = true;
			foreach($input_custom_attributes as $cur_attr)
			{
				$attr_arr = explode(',', $cur_attr);
				if($first_val == true)
				{
					$add_comma = '';
					$first_val = false;
				}else{
					$add_comma = ',';
				}
				$custom_attr .= $add_comma.'{"customAttributeType":"'.$attr_arr[0].'","id":"'.$attr_arr[1].'","customAttributeName":"'.$attr_arr[1].'","customAttributeId":"'.$attr_arr[1].'","customAttributeValue":"'.urlencode($attr_arr[2]).'"}';
			}
			$custom_attr .= ']';
		}
		
		// Temporary hard-coded values
		$lead_status_id = '6826705';
		$lead_status_meaning = 'New';
		$lead_source_id = '6827230';
		$lead_source_meaning = 'Other';
		$assignee_id = '18767';
		$assignee_name = urlencode('Kenny Clark');
		$referred_by_id = '18767';
		$referred_by_name = urlencode('Kenny Clark');
		$lead_rank_id = '6826692';	
		$lead_rank_meaning = 'High';
		$lead_type_id = -1;
	
		$api_url = 'https://api.apptivo.com/app/dao/leads?a=createLead&leadData={"title":"'.$title.'","firstName":"'.$first_name.'","lastName":"'.$last_name.'","jobTitle":"'.$job_title.'","easyWayToContact":"'.$easy_way_to_contact.'","wayToContact":"'.$way_to_contact.'","leadStatus":'.$lead_status_id.',"leadStatusMeaning":"'.$lead_status_meaning.'","leadSource":'.$lead_source_id.',"leadSourceMeaning":"'.$lead_source_meaning.'","leadTypeName":"'.$lead_type_name.'","leadTypeId":'.$lead_type_id.',"referredByName":"'.$referred_by_name.'","referredById":'.$referred_by_id.',"assigneeObjectRefName":"'.$assignee_name.'","assigneeObjectRefId":'.$assignee_id.',"assigneeObjectId":8,"description":"'.$description.'","skypeName":"'.$skype_name.'","potentialAmount":'.$potential_amount.',"currencyCode":"'.$currency_code.'","estimatedCloseDate":"'.$estimated_close_date.'","leadRank":'.$lead_rank_id.',"leadRankMeaning":"'.$lead_rank_meaning.'","campaignName":"'.$campaign_name.'","campaignId":'.$campaign_id.',"territoryName":"'.$territory_name.'","territoryId":'.$territory_id.',"marketId":'.$market_id.',"marketName":'.$market_name.',"segmentId":'.$segment_id.',"segmentName":'.$segment_name.',"followUpDate":'.$follow_up_date.',"followUpDescription":'.$follow_up_description.',"createdByName":"'.$created_by_name.'","lastUpdatedByName":"'.$last_updated_by_name.'","creationDate":"'.$creation_date.'","lastUpdateDate":"'.$last_update_date.'","accountName":"'.$account_name.'","accountId":'.$account_id.',"companyName":"'.$company_name.'","employeeRangeId":'.$employee_range_id.',"employeeRange":'.$employee_range.',"annualRevenue":'.$annual_revenue.',"industry":"'.$industry.'","industryName":"'.$industry_name.'","ownership":"'.$ownership.'","website":"'.$website.'","faceBookURL":"'.$facebook.'","twitterURL":"'.$twitter.'","linkedInURL":"'.$linkedin.'","phoneNumbers":[]'.$address_line.',"emailAddresses":[],"labels":[]'.$custom_attr.',"createdBy":null,"lastUpdatedBy":null}&apiKey='.$this->api_key.'&accessKey='.$this->access_key;
		
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