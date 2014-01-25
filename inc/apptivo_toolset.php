<?php
/* ABOUT THIS FILE 
   This is a general class that contains methods commonly used when interacting with the Apptivo API.
   For details and the most recent code see here: https://github.com/Apptivo/phplib/wiki/Library-Documentation
*/
class apptivo_toolset
{
	public $api_key = 'null';
	public $access_key = 'null';
	public $user_name = 'null';
	public $ch;
	public $custom_attributes = true;
	
// Primary Methods: Create Lead, Create Case, Create Customer, etc
	
	//function create_lead($input_first_name, $input_last_name, $input_phone_numbers, $input_job_title, $input_email, $input_company_name, $input_assignee_id, $input_description, $input_address_1, $input_address_2, $input_address_city, $input_address_state, $input_address_country, $input_address_zip, $input_custom_attributes)
	function create_lead($input_lead_data, $input_phone_numbers, $input_addresses, $input_emails, $input_custom_attributes)
	{
		// Uncommon Assumed/Empty values, these could be abstracted and populated if desired
		$title = 'Mr.';
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
		$firstName = urlencode($firstName);
		$last_name = urlencode($input_last_name);
		$job_title = urlencode($input_job_title);
		$companyName = urlencode($input_company_name);
		$description = urlencode($description);
		
		//Phone Numbers
		if(Count($input_phone_numbers > 0))
		{
			$first_val = true;
			foreach($input_phone_numbers as $cur_phone)
			{
				$phone_type = explode(',', $cur_phone[0]);
				if($first_val == true)
				{
					$add_comma = '';
					$first_val = false;
				}else{
					$add_comma = ',';
					$counter = $counter + 1;
				}
				$phone_numbers .= $add_comma.'{"phoneNumber":"'.$cur_phone[1].'","phoneTypeCode":"'.$phone_type[0].'","phoneType":"'.$phone_type[1].'","id":"lead_phone_input'.$counter.'"}';
			}
		}
		
		// Address fields
		$address_1 = urlencode($input_address_1);
		$address_2 = urlencode($input_address_2);
		$address_city = urlencode($input_address_city);
		$address_zip = urlencode($input_address_zip);
		$state_arr = explode(',', $input_address_state); //Address State is a comma separated value with [State Name, State ID]		
		$address_state_id = $state_arr[0];
		$address_state = $state_arr[1];
		if(strlen($address_1) > 0 || strlen($address_state) > 0)
		{			
			$address_line = ',"addresses":[{"addressAttributeId":"address_section_attr_id","addressTypeCode":"1","addressType":"Billing+Address","addressLine1":"'.$address_1.'","addressLine2":"'.$address_2.'","city":"'.$address_city.'","stateCode":"'.$address_state_id.'","state":"'.$address_state.'","zipCode":"'.$address_zip.'","countryId":176,"countryName":"United+States"}]';
		}
		
		//Check to see if we passed in an array of custom attributes.  This array contains one or more attributes, and each attribute should have 3 values comma separated (attribute type, attribute id, attribute value)
		if(strlen($input_custom_attributes > 0))
		{
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
	
		$api_url = 'https://api.apptivo.com/app/dao/leads?a=createLead&leadData={"title":"'.$title.'","firstName":"'.$input_lead_data['firstName'].'","lastName":"'.$input_lead_data['lastName'].'","jobTitle":"'.$jobTitle.'","easyWayToContact":"'.$easy_way_to_contact.'","wayToContact":"'.$way_to_contact.'","leadStatus":'.$lead_status_id.',"leadStatusMeaning":"'.$lead_status_meaning.'","leadSource":'.$lead_source_id.',"leadSourceMeaning":"'.$lead_source_meaning.'","leadTypeName":"'.$lead_type_name.'","leadTypeId":'.$lead_type_id.',"referredByName":"'.$referred_by_name.'","referredById":'.$referred_by_id.',"assigneeObjectRefName":"'.$assignee_name.'","assigneeObjectRefId":'.$assignee_id.',"assigneeObjectId":8,"description":"'.$input_lead_data['description'].'","skypeName":"'.$skype_name.'","potentialAmount":'.$potential_amount.',"currencyCode":"'.$currency_code.'","estimatedCloseDate":"'.$estimated_close_date.'","leadRank":'.$lead_rank_id.',"leadRankMeaning":"'.$lead_rank_meaning.'","campaignName":"'.$campaign_name.'","campaignId":'.$campaign_id.',"territoryName":"'.$territory_name.'","territoryId":'.$territory_id.',"marketId":'.$market_id.',"marketName":'.$market_name.',"segmentId":'.$segment_id.',"segmentName":'.$segment_name.',"followUpDate":'.$follow_up_date.',"followUpDescription":'.$follow_up_description.',"createdByName":"'.$created_by_name.'","lastUpdatedByName":"'.$last_updated_by_name.'","creationDate":"'.$creation_date.'","lastUpdateDate":"'.$last_update_date.'","accountName":"'.$account_name.'","accountId":'.$account_id.',"companyName":"'.$input_lead_data['companyName'].'","employeeRangeId":'.$employee_range_id.',"employeeRange":'.$employee_range.',"annualRevenue":'.$annual_revenue.',"industry":"'.$industry.'","industryName":"'.$industry_name.'","ownership":"'.$ownership.'","website":"'.$website.'","faceBookURL":"'.$facebook.'","twitterURL":"'.$twitter.'","linkedInURL":"'.$linkedin.'","phoneNumbers":['.$phone_numbers.']'.$address_line.',"emailAddresses":[],"labels":[],"customAttributes":['.$custom_attr.'],"createdBy":null,"lastUpdatedBy":null}&apiKey='.$this->api_key.'&accessKey='.$this->access_key;
		
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

// General data utility methods - get lists of countries/states, retrieve configuration settings, retrieve employee lists, etc.
	
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
	
//HTML Utilities.  Methods to generate commonly used HTML snippets for web forms.  Things like state dropdowns, phone number types, etc
	
	function get_state_dropdown_html($input_country_id)
	{
		$states = $this->get_states_by_country($input_country_id);
		$output_html = '<select id="address_state" name="address_state">';
		foreach ($states->responseObject as $cur_state)
		{
			$output_html .= '<option value="'.$cur_state->stateCode.','.$cur_state->stateName.'">'.$cur_state->stateName.'</option>';	
		}
		$output_html .= '</select>';
		
		return $output_html;
	}
	
	function get_phone_type_dropdown_html($field_number='1')
	{
		$output_html = '
			<select name="phone_type_'.$field_number.'" id="phone_type_'.$field_number.'">
				<option value="PHONE_BUSINESS,Business">Business</option>
				<option value="PHONE_MOBILE,Mobile">Mobile</option>
				<option value="PHONE_HOME,Home">Home</option>
				<option value="PHONE_FAX,Fax">Fax</option>
				<option value="PHONE_Other,Other">Other</option>
			</select>	
			<input type="text" name="phone_number_'.$field_number.'" id="phone_number_'.$field_number.'" />
		';
		return $output_html;
	}
	
//Constructor sets the api/access keypair.  Also constructs the curl object so we can start making API requests.  Will destroy curl object on destruct.

	function __construct($input_apikey, $input_accesskey) {
				
		$this->api_key = $input_apikey;
		$this->access_key = $input_accesskey;
		
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