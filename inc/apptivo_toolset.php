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
	
	function create_lead($lead_data, $input_phone_numbers, $input_addresses, $input_emails, $input_custom_attributes)
	{
		//Phone Numbers
		if(Count($input_phone_numbers > 0))
		{
			$counter = 1;
			foreach($input_phone_numbers as $cur_phone)
			{
				if($counter == 1)
				{
					$add_comma = '';
					$counter_text = ''; //Don't change the ID for the first number, or else we get a blank field.
				}else{
					$add_comma = ',';
					$counter_text = $counter;
				}
				$phone_numbers .= $add_comma.'{"phoneNumber":"'.$cur_phone['phoneNumber'].'","phoneTypeCode":"'.$cur_phone['phoneType'].'","phoneType":"'.$cur_phone['phoneType'].'","id":"lead_phone_input'.$counter_text.'"}';
				$counter = $counter + 1;
			}
		}
		
		//Email Addresses
		if(Count($input_emails > 0))
		{
			$counter = 1;
			foreach($input_emails as $cur_email)
			{
				if($counter == 1)
				{
					$add_comma = '';
					$counter_text = ''; //Don't change the ID for the first number, or else we get a blank field.
				}else{
					$add_comma = ',';
					$counter_text = $counter;
				}
				$emails .= $add_comma.'{"emailAddress":"'.$cur_email['emailAddress'].'","emailType":"'.$cur_email['emailType'].'","emailTypeCode":"'.$cur_email['emailType'].'","id":"cont_email_input'.$counter_text.'"}';
				$counter = $counter + 1;
			}
		}
		
		// Address fields
		if(Count($input_addresses > 0))
		{
			$counter = 1;
			foreach($input_addresses as $cur_addr)
			{
				$phone_type = explode(',', $cur_addr[0]);
				if($counter == 1)
				{
					$add_comma = '';
				}else{
					$add_comma = ',';
				}
				$addresses .= $add_comma.'{"addressAttributeId":"address_section_attr_id'.$counter.'","addressTypeCode":"'.$cur_addr['addressTypeCode'].'","addressType":"'.$cur_addr['addressType'].'","addressLine1":"'.$cur_addr['addressLine1'].'","addressLine2":"'.$cur_addr['addressLine2'].'","city":"'.$cur_addr['city'].'","stateCode":"'.$cur_addr['stateCode'].'","state":"'.$cur_addr['state'].'","zipCode":"'.$cur_addr['zipCode'].'","countryId":'.$cur_addr['countryId'].',"countryName":"'.$cur_addr['countryName'].'"}';
				$counter = $counter + 1;
			}
		}
		
		//Check to see if we passed in an array of custom attributes.  This array contains one or more attributes, and each attribute should have 3 values comma separated (attribute type, attribute id, attribute value)
		if(Count($input_custom_attributes > 0))
		{
			$counter = 1;
			foreach($input_custom_attributes as $cur_attr)
			{
				if($counter == 1)
				{
					$add_comma = '';
				}else{
					$add_comma = ',';
				}
				$custom_attr .= $add_comma.'{"customAttributeType":"'.$cur_attr['customAttributeType'].'","id":"'.$cur_attr['id'].'","customAttributeName":"'.$cur_attr['id'].'","customAttributeId":"'.$cur_attr['id'].'","customAttributeValue":"'.$cur_attr['customAttributeValue'].'"}';
				$counter = $counter + 1;
			}
		}
		
		// These are mandatory fields that we cannot set a default for.  You must pass in these fields, or we'll return an error message.
		$required_fields = Array('assigneeObjectRefId','assigneeObjectRefName','referredById','referredByName','leadStatus','leadStatusMeaning','leadSource','leadSourceMeaning','leadRank','leadRankMeaning');
		foreach ($required_fields as $cur_field)
		{
			if(!$lead_data[$cur_field])
			{
				$form_message .= 'Error: '.$cur_field.' is empty.  This is a required field.  Please report this error to the website admin.<br />';
			}
		}
		
		// If we missed any required fields, $form_message will contain the errors.  Check for value and just return the message & exit if one is found.
		if($form_message)
		{
			return $form_message;
		}
		
		// Some attributes require a value.  But a0re not commonly used.  We'll check if a value as given, if not set to a default
		if(!$lead_data['title']){$lead_data['title'] = 'Mr.';}
		if(!$lead_data['easyWayToContact']){$lead_data['easyWayToContact'] = 'EMAIL';}
		if(!$lead_data['wayToContact']){$lead_data['wayToContact'] = 'Email';}
		if(!$lead_data['currencyCode']){$lead_data['currencyCode'] = 'USD';}
		if(!$lead_data['leadTypeId']){$lead_data['leadTypeId'] = -1;}
		
		// Some attributes need to have "null" passed in, if there is no value.  We'll check if a value was given, if not set to null.
		if(!$lead_data['potentialAmount']){$lead_data['potentialAmount'] = 'null';}
		if(!$lead_data['campaignId']){$lead_data['campaignId'] = 'null';}
		if(!$lead_data['territoryId']){$lead_data['territoryId'] = 'null';}
		if(!$lead_data['marketId']){$lead_data['marketId'] = 'null';}
		if(!$lead_data['marketName']){$lead_data['marketName'] = 'null';}
		if(!$lead_data['segment_id']){$lead_data['segment_id'] = 'null';}
		if(!$lead_data['segmentName']){$lead_data['segmentName'] = 'null';}
		if(!$lead_data['followUpDate']){$lead_data['followUpDate'] = 'null';}
		if(!$lead_data['followUpDescription']){$lead_data['followUpDescription'] = 'null';}
		if(!$lead_data['accountId']){$lead_data['accountId'] = 'null';}
		if(!$lead_data['employeeRangeId']){$lead_data['employeeRangeId'] = 'null';}
		if(!$lead_data['employeeRange']){$lead_data['employeeRange'] = 'null';}
		if(!$lead_data['annualRevenue']){$lead_data['annualRevenue'] = 'null';}
		if(!$lead_data['potentialAmount']){$lead_data['potentialAmount'] = 'null';}
		
		/* These are other possible values that could be passed in
			$lead_data['referredByName']
			$lead_data['referredById']
			$lead_data['leadTypeId']
			$lead_data['leadTypeName']
			$lead_data['skypeName']
			$lead_data['estimatedCloseDate']
			$lead_data['campaignName']
			$lead_data['territoryName']
			$lead_data['lastUpdatedByName']
			$lead_data['createdByName']
			$lead_data['lastUpdateDate']
			$lead_data['creationDate']
			$lead_data['accountName']
			$lead_data['industry']
			$lead_data['industryName']
			$lead_data['ownership']
			$lead_data['website']
			$lead_data['faceBookURL']
			$lead_data['twitterURL']
			$lead_data['linkedInURL']
		*/
			
		$api_url = 'https://api.apptivo.com/app/dao/leads?a=createLead&leadData={"title":"'.$lead_data['title'].'","firstName":"'.$lead_data['firstName'].'","lastName":"'.$lead_data['lastName'].'","jobTitle":"'.$lead_data['jobTitle'].'","easyWayToContact":"'.$lead_data['easyWayToContact'].'","wayToContact":"'.$lead_data['wayToContact'].'","leadStatus":'.$lead_data['leadStatus'].',"leadStatusMeaning":"'.$lead_data['leadStatusMeaning'].'","leadSource":'.$lead_data['leadSource'].',"leadSourceMeaning":"'.$lead_data['leadSourceMeaning'].'","leadTypeName":"'.$lead_data['leadTypeName'].'","leadTypeId":'.$lead_data['leadTypeId'].',"referredByName":"'.$lead_data['referredByName'].'","referredById":'.$lead_data['referredById'].',"assigneeObjectRefName":"'.$lead_data['assigneeObjectRefName'].'","assigneeObjectRefId":'.$lead_data['assigneeObjectRefId'].',"assigneeObjectId":8,"description":"'.$lead_data['description'].'","skypeName":"'.$lead_data['skypeName'].'","potentialAmount":'.$lead_data['potentialAmount'].',"currencyCode":"'.$lead_data['currencyCode'].'","estimatedCloseDate":"'.$lead_data['estimatedCloseDate'].'","leadRank":'.$lead_data['leadRank'].',"leadRankMeaning":"'.$lead_data['leadRankMeaning'].'","campaignName":"'.$lead_data['campaignName'].'","campaignId":'.$lead_data['campaignId'].',"territoryName":"'.$lead_data['territoryName'].'","territoryId":'.$lead_data['territoryId'].',"marketId":'.$lead_data['marketId'].',"marketName":'.$lead_data['marketName'].',"segmentId":'.$lead_data['segment_id'].',"segmentName":'.$lead_data['segmentName'].',"followUpDate":'.$lead_data['followUpDate'].',"followUpDescription":'.$lead_data['followUpDescription'].',"createdByName":"'.$lead_data['createdByName'].'","lastUpdatedByName":"'.$lead_data['lastUpdatedByName'].'","creationDate":"'.$lead_data['creationDate'].'","lastUpdateDate":"'.$lead_data['lastUpdateDate'].'","accountName":"'.$lead_data['accountName'].'","accountId":'.$lead_data['accountId'].',"companyName":"'.$lead_data['companyName'].'","employeeRangeId":'.$lead_data['employeeRangeId'].',"employeeRange":'.$lead_data['employeeRange'].',"annualRevenue":'.$lead_data['annualRevenue'].',"industry":"'.$lead_data['industry'].'","industryName":"'.$lead_data['industryName'].'","ownership":"'.$lead_data['ownership'].'","website":"'.$lead_data['website'].'","faceBookURL":"'.$lead_data['faceBookURL'].'","twitterURL":"'.$lead_data['twitterURL'].'","linkedInURL":"'.$lead_data['linkedInURL'].'","phoneNumbers":['.$phone_numbers.'],"addresses":['.$addresses.'],"emailAddresses":['.$emails.'],"labels":[],"customAttributes":['.$custom_attr.'],"createdBy":null,"lastUpdatedBy":null}&apiKey='.$this->api_key.'&accessKey='.$this->access_key;
		
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
	
	function create_case($case_data, $input_phone_numbers, $input_addresses, $input_emails, $input_custom_attributes)
	{
		//Phone Numbers
		if(Count($input_phone_numbers > 0))
		{
			$counter = 1;
			foreach($input_phone_numbers as $cur_phone)
			{
				if($counter == 1)
				{
					$add_comma = '';
					$counter_text = ''; //Don't change the ID for the first number, or else we get a blank field.
				}else{
					$add_comma = ',';
					$counter_text = $counter;
				}
				$phone_numbers .= $add_comma.'{"phoneNumber":"'.$cur_phone['phoneNumber'].'","phoneTypeCode":"'.$cur_phone['phoneType'].'","phoneType":"'.$cur_phone['phoneType'].'","id":"case_phone_input'.$counter_text.'"}';
				$counter = $counter + 1;
			}
		}
		
		//Email Addresses
		if(Count($input_emails > 0))
		{
			$counter = 1;
			foreach($input_emails as $cur_email)
			{
				if($counter == 1)
				{
					$add_comma = '';
					$counter_text = ''; //Don't change the ID for the first number, or else we get a blank field.
				}else{
					$add_comma = ',';
					$counter_text = $counter;
				}
				$emails .= $add_comma.'{"emailAddress":"'.$cur_email['emailAddress'].'","emailType":"'.$cur_email['emailType'].'","emailTypeCode":"'.$cur_email['emailType'].'","id":"cont_email_input'.$counter_text.'"}';
				$counter = $counter + 1;
			}
		}
		
		// Address fields
		if(Count($input_addresses > 0))
		{
			$counter = 1;
			foreach($input_addresses as $cur_addr)
			{
				$phone_type = explode(',', $cur_addr[0]);
				if($counter == 1)
				{
					$add_comma = '';
				}else{
					$add_comma = ',';
				}
				$addresses .= $add_comma.'{"addressAttributeId":"address_section_attr_id'.$counter.'","addressTypeCode":"'.$cur_addr['addressTypeCode'].'","addressType":"'.$cur_addr['addressType'].'","addressLine1":"'.$cur_addr['addressLine1'].'","addressLine2":"'.$cur_addr['addressLine2'].'","city":"'.$cur_addr['city'].'","stateCode":"'.$cur_addr['stateCode'].'","state":"'.$cur_addr['state'].'","zipCode":"'.$cur_addr['zipCode'].'","countryId":'.$cur_addr['countryId'].',"countryName":"'.$cur_addr['countryName'].'"}';
				$counter = $counter + 1;
			}
		}
		
		//Check to see if we passed in an array of custom attributes.  This array contains one or more attributes, and each attribute should have 3 values comma separated (attribute type, attribute id, attribute value)
		if(Count($input_custom_attributes > 0))
		{
			$counter = 1;
			foreach($input_custom_attributes as $cur_attr)
			{
				if($counter == 1)
				{
					$add_comma = '';
				}else{
					$add_comma = ',';
				}
				$custom_attr .= $add_comma.'{"customAttributeType":"'.$cur_attr['customAttributeType'].'","id":"'.$cur_attr['id'].'","customAttributeName":"'.$cur_attr['id'].'","customAttributeId":"'.$cur_attr['id'].'","customAttributeValue":"'.$cur_attr['customAttributeValue'].'"}';
				$counter = $counter + 1;
			}
		}
		
		// These are mandatory fields that we cannot set a default for.  You must pass in these fields, or we'll return an error message.
		$required_fields = Array('caseNumber','assignedObjectRefId','assignedObjectRefName','caseStatus','caseStatusId','caseType','caseTypeId','casePriority','casePriorityId');
		foreach ($required_fields as $cur_field)
		{
			if(!$case_data[$cur_field])
			{
				$form_message .= 'Error: '.$cur_field.' is empty.  This is a required field.  Please report this error to the website admin.<br />';
			}
		}
		
		// If we missed any required fields, $form_message will contain the errors.  Check for value and just return the message & exit if one is found.
		if($form_message)
		{
			return $form_message;
		}
		
		// Some attributes require a value.  But a0re not commonly used.  We'll check if a value as given, if not set to a default
		if(!$case_data['assignedObjectId']){$case_data['assignedObjectId'] = 8;}
		
		// Some attributes need to have "null" passed in, if there is no value.  We'll check if a value was given, if not set to null.
		if(!$case_data['caseItemId']){$case_data['caseItemId'] = 'null';}
		if(!$case_data['caseProjectId']){$case_data['caseProjectId'] = 'null';}
		
		/* These are other possible values that could be passed in
			$case_data['caseItem']
			$case_data['needByDate']
			$case_data['caseProject']
			$case_data['dateResolved']
		*/
			
		$api_url = 'https://api.apptivo.com/app/dao/case?a=createCase&caseData={"caseNumber":"'.$case_data['caseNumber'].'","caseStatus":"'.$case_data['caseStatus'].'","caseStatusId":"'.$case_data['caseStatusId'].'","caseType":"'.$case_data['caseType'].'","caseTypeId":"'.$case_data['caseTypeId'].'","casePriority":"'.$case_data['casePriority'].'","casePriorityId":"'.$case_data['casePriorityId'].'","assignedObjectRefName":"'.$case_data['assignedObjectRefName'].'","assignedObjectId":"'.$case_data['assignedObjectId'].'","assignedObjectRefId":"'.$case_data['assignedObjectRefId'].'","caseSummary":"'.$case_data['caseSummary'].'","description":"'.$case_data['description'].'","caseItem":"'.$case_data['caseItem'].'","caseItemId":'.$case_data['caseItemId'].',"needByDate":"'.$case_data['needByDate'].'","caseProject":"'.$case_data['caseProject'].'","caseProjectId":'.$case_data['caseProjectId'].',"dateResolved":"'.$case_data['dateResolved'].'","caseCustomer":"'.$case_data['caseCustomer'].'","caseCustomerId":'.$case_data['caseCustomerId'].',"caseContact":"'.$case_data['caseContact'].'","caseContactId":'.$case_data['caseContactId'].',"caseEmail":"'.$case_data['caseEmail'].'","customAttributes":['.$custom_attr.']}&apiKey='.$this->api_key.'&accessKey='.$this->access_key;

		
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
		
	function get_email_type_dropdown_html($field_number='1')
	{
		$output_html = '
			<select name="email_type_'.$field_number.'" id="email_type_'.$field_number.'">
				<option value="Business">Business</option>
				<option value="Home">Home</option>
				<option value="Other">Other</option>
			</select>	
			<input type="text" name="email_address_'.$field_number.'" id="email_address_'.$field_number.'" />
		';
		return $output_html;
	}
	
	function get_phone_type_dropdown_html($field_number='1')
	{
		$output_html = '
			<select name="phone_type_'.$field_number.'" id="phone_type_'.$field_number.'">
				<option value="Business">Business</option>
				<option value="Mobile">Mobile</option>
				<option value="Home">Home</option>
				<option value="PFax">Fax</option>
				<option value="Other">Other</option>
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