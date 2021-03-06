<?php
/* ABOUT THIS FILE 
   This is a general class that contains methods commonly used when interacting with the Apptivo API.
   For details and the most recent code see here: https://github.com/Apptivo/phplib/wiki/Library-Documentation
*/
class apptivo_toolset
{
	public $api_key = 'null';
	public $access_key = 'null';
	public $user_name_str = 'null';
	public $ch;
	
	public $caseType;
	public $caseTypeId;
	public $caseStatus;
	public $caseStatusId;
	public $casePriority;
	public $casePriorityId;
	
	public $taskStatusId;
	public $taskStatusName;
	public $taskPriorityName;
	public $taskPriorityId;
	
	public $leadSourceTypeName;
	public $leadSourceTypeId;
	
// Get All Methods: Read All Contacts, etc
	function get_all_contacts($startIndex)
	{
		$api_url = 'https://api.apptivo.com/app/dao/contacts?a=getAllContacts&startIndex='.$startIndex.'&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);
		$api_result = curl_exec($this->ch);
		return json_decode($api_result);		
	}
	
// Get by ID Methods: Get Contact By ID, Get Customer By ID, Get Case By ID, etc.
	function get_contact_by_id($contact_id)
	{
		$api_url = 'https://api.apptivo.com/app/dao/contacts?a=getContactByContactId&contactId='.$contact_id.'&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);
		$api_result = curl_exec($this->ch);
		return json_decode($api_result);	
	}

	
// Create Methods: Create Lead, Create Case, Create Customer, etc
	
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
			
		$api_url = 'https://api.apptivo.com/app/dao/leads?a=createLead&leadData={"title":"'.$lead_data['title'].'","firstName":"'.$lead_data['firstName'].'","lastName":"'.$lead_data['lastName'].'","jobTitle":"'.$lead_data['jobTitle'].'","easyWayToContact":"'.$lead_data['easyWayToContact'].'","wayToContact":"'.$lead_data['wayToContact'].'","leadStatus":'.$lead_data['leadStatus'].',"leadStatusMeaning":"'.$lead_data['leadStatusMeaning'].'","leadSource":'.$lead_data['leadSource'].',"leadSourceMeaning":"'.$lead_data['leadSourceMeaning'].'","leadTypeName":"'.$lead_data['leadTypeName'].'","leadTypeId":'.$lead_data['leadTypeId'].',"referredByName":"'.$lead_data['referredByName'].'","referredById":'.$lead_data['referredById'].',"assigneeObjectRefName":"'.$lead_data['assigneeObjectRefName'].'","assigneeObjectRefId":'.$lead_data['assigneeObjectRefId'].',"assigneeObjectId":8,"description":"'.$lead_data['description'].'","skypeName":"'.$lead_data['skypeName'].'","potentialAmount":'.$lead_data['potentialAmount'].',"currencyCode":"'.$lead_data['currencyCode'].'","estimatedCloseDate":"'.$lead_data['estimatedCloseDate'].'","leadRank":'.$lead_data['leadRank'].',"leadRankMeaning":"'.$lead_data['leadRankMeaning'].'","campaignName":"'.$lead_data['campaignName'].'","campaignId":'.$lead_data['campaignId'].',"territoryName":"'.$lead_data['territoryName'].'","territoryId":'.$lead_data['territoryId'].',"marketId":'.$lead_data['marketId'].',"marketName":'.$lead_data['marketName'].',"segmentId":'.$lead_data['segment_id'].',"segmentName":'.$lead_data['segmentName'].',"followUpDate":'.$lead_data['followUpDate'].',"followUpDescription":'.$lead_data['followUpDescription'].',"createdByName":"'.$lead_data['createdByName'].'","lastUpdatedByName":"'.$lead_data['lastUpdatedByName'].'","creationDate":"'.$lead_data['creationDate'].'","lastUpdateDate":"'.$lead_data['lastUpdateDate'].'","accountName":"'.$lead_data['accountName'].'","accountId":'.$lead_data['accountId'].',"companyName":"'.$lead_data['companyName'].'","employeeRangeId":'.$lead_data['employeeRangeId'].',"employeeRange":"'.$lead_data['employeeRange'].'","annualRevenue":'.$lead_data['annualRevenue'].',"industry":"'.$lead_data['industry'].'","industryName":"'.$lead_data['industryName'].'","ownership":"'.$lead_data['ownership'].'","website":"'.$lead_data['website'].'","faceBookURL":"'.$lead_data['faceBookURL'].'","twitterURL":"'.$lead_data['twitterURL'].'","linkedInURL":"'.$lead_data['linkedInURL'].'","phoneNumbers":['.$phone_numbers.'],"addresses":['.$addresses.'],"emailAddresses":['.$emails.'],"labels":[],"customAttributes":['.$custom_attr.'],"createdBy":null,"lastUpdatedBy":null}&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
		
		curl_setopt($this->ch, CURLOPT_URL, $api_url);

		$api_result = curl_exec($this->ch);
		
		$api_response = json_decode($api_result);
				
		if($api_response)
		{
			return 'Thank you, your submission has been received!';
		}else{
			return 'Sorry, there seems to have been an error.  Please check with the administrator.';
		}
		
	}
	
	function create_opportunity($opportunity_data, $input_custom_attributes)
	{

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
		
		// Some attributes require a value.  But are not commonly used.  We'll check if a value as given, if not set to a default
		if(!$opportunity_data['assignedObjectId']){$opportunity_data['assignedObjectId'] = 8;}
		//These are required values, but the ID numbers change from firm to firm.  We'll use a default of one exists, or get a new one now.
		if(!$opportunity_data['salesStageName']){
			if($this->salesStageName){
				$opportunity_data['salesStageName'] = $this->salesStageName;
			}else{
				$this->get_opportunities_settings();
				$opportunity_data['salesStageName'] = $this->salesStageName;
			}
		}
		if(!$opportunity_data['salesStageId']){
			if($this->salesStageId){
				$opportunity_data['salesStageId'] = $this->salesStageId;
			}else{
				$this->get_opportunities_settings();
				$opportunity_data['salesStageId'] = $this->salesStageId;
			}
		}
		if(!$opportunity_data['leadSourceTypeName']){
			if($this->leadSourceTypeName){
				$opportunity_data['leadSourceTypeName'] = $this->leadSourceTypeName;
			}else{
				$this->get_opportunities_settings();
				$opportunity_data['leadSourceTypeName'] = $this->leadSourceTypeName;
			}
		}
		if(!$opportunity_data['leadSourceTypeId']){
			if($this->leadSourceTypeId){
				$opportunity_data['leadSourceTypeId'] = $this->leadSourceTypeId;
			}else{
				$this->get_opportunities_settings();
				$opportunity_data['leadSourceTypeId'] = $this->leadSourceTypeId;
			}
		}

		
		// These are mandatory fields that we cannot set a default for.  You must pass in these fields, or we'll return an error message.
		$required_fields = Array('opportunityName', 'opportunityCustomer', 'opportunityCustomerId', 'closeDate', 'assignedToObjectRefName', 'assignedToObjectRefId');
		foreach ($required_fields as $cur_field)
		{
			if(!$opportunity_data[$cur_field])
			{
				$form_message .= 'Error: '.$cur_field.' is empty.  This is a required field.  Please report this error to the website admin.<br />';
			}
		}
		// If we missed any required fields, $form_message will contain the errors.  Check for value and just return the message & exit if one is found.
		if($form_message)
		{
			return $form_message;
		}
		
		// Some attributes need to have "null" passed in, if there is no value.  We'll check if a value was given, if not set to null.
		if(!$opportunity_data['opportunityContactId']){$opportunity_data['opportunityContactId'] = 'null';}
		if(!$opportunity_data['territoryId']){$opportunity_data['territoryId'] = 'null';}
		
		$api_url = 'https://api.apptivo.com/app/dao/opportunities?a=createOpportunity&opportunityData={"opportunityName":"'.$opportunity_data['opportunityName'].'","salesStageName":"'.$opportunity_data['salesStageName'].'","salesStageId":"'.$opportunity_data['salesStageId'].'","":"","opportunityCustomer":"'.$opportunity_data['opportunityCustomer'].'","opportunityCustomerId":'.$opportunity_data['opportunityCustomerId'].',"opportunityContact":"'.$opportunity_data['opportunityContact'].'","opportunityContactId":'.$opportunity_data['opportunityContactId'].',"nextStep":"","territoryName":"'.$opportunity_data['territoryName'].'","territoryId":'.$opportunity_data['territoryId'].',"assignedToObjectRefName":"'.$opportunity_data['assignedToObjectRefName'].'","assignedToObjectId":8,"assignedToObjectRefId":'.$opportunity_data['assignedToObjectRefId'].',"probability":"'.$opportunity_data['probability'].'","amount":0,"currencyCode":"USD","closeDate":"'.$opportunity_data['closeDate'].'","campaignName":"","campaignId":null,"description":"","followUpDate":null,"followUpDescription":null,"marketName":"","marketId":null,"segmentName":"","segmentId":null,"leadSourceTypeName":"'.$opportunity_data['leadSourceTypeName'].'","leadSourceTypeId":"'.$opportunity_data['leadSourceTypeId'].'","opportunityTypeName":"'.$opportunity_data['opportunityTypeName'].'","opportunityTypeId":"'.$opportunity_data['opportunityTypeId'].'","createdByName":"","lastUpdatedByName":"","creationDate":"","lastUpdateDate":"","section_1423271450718_1343_attribute_radio_1423271792653_2867":"No","searchColumn":"'.$opportunity_data['searchColumn'].'","addresses":[],"customAttributes":['.$custom_attr.'],"labels":[],"opportunityId":null,"createdBy":null,"lastUpdatedBy":null,"isMultiCurrency":"Y"}&fromObjectId=null&fromObjectRefId=null&isDuplicate="N"&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);
		$api_result = curl_exec($this->ch);		
		$api_response = json_decode($api_result);
		
		print $api_url;
		print '<br><br>';

		return $api_response;
	}
	
	function create_case($case_data, $input_custom_attributes)
	{
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
		
		// Some attributes require a value.  But are not commonly used.  We'll check if a value as given, if not set to a default
		if(!$case_data['assignedObjectId']){$case_data['assignedObjectId'] = 8;}
		//These are required values, but the ID numbers change from firm to firm.  We'll use a default of one exists, or get a new one now.
		if(!$case_data['caseType']){
			if($this->caseType){
				$case_data['caseType'] = $this->caseType;
			}else{
				$this->get_cases_settings();
				$case_data['caseType'] = $this->caseType;
			}
		}
		if(!$case_data['caseTypeId']){
			if($this->caseTypeId){
				$case_data['caseTypeId'] = $this->caseTypeId;
			}else{
				$this->get_cases_settings();
				$case_data['caseTypeId'] = $this->caseTypeId;
			}
		}
		if(!$case_data['caseStatus']){
			if($this->caseStatus){
				$case_data['caseStatus'] = $this->caseStatus;
			}else{
				$this->get_cases_settings();
				$case_data['caseStatus'] = $this->caseStatus;
			}
		}
		if(!$case_data['caseStatusId']){
			if($this->caseStatusId){
				$case_data['caseStatusId'] = $this->caseStatusId;
			}else{
				$this->get_cases_settings();
				$case_data['caseStatusId'] = $this->caseStatusId;
			}
		}
		if(!$case_data['casePriority']){
			if($this->casePriority){
				$case_data['casePriority'] = $this->casePriority;
			}else{
				$this->get_cases_settings();
				$case_data['casePriority'] = $this->casePriority;
			}
		}
		if(!$case_data['casePriorityId']){
			if($this->casePriorityId){
				$case_data['casePriorityId'] = $this->casePriorityId;
			}else{
				$this->get_cases_settings();
				$case_data['casePriorityId'] = $this->casePriorityId;
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
		
		// Some attributes need to have "null" passed in, if there is no value.  We'll check if a value was given, if not set to null.
		if(!$case_data['caseItemId']){$case_data['caseItemId'] = 'null';}
		if(!$case_data['caseProjectId']){$case_data['caseProjectId'] = 'null';}
		
		/* These are other possible values that could be passed in
			$case_data['caseItem']
			$case_data['needByDate']
			$case_data['caseProject']
			$case_data['dateResolved']
		*/
			
		$api_url = 'https://api.apptivo.com/app/dao/case?a=createCase&caseData={"caseNumber":"'.$case_data['caseNumber'].'","caseStatus":"'.$case_data['caseStatus'].'","caseStatusId":"'.$case_data['caseStatusId'].'","caseType":"'.$case_data['caseType'].'","caseTypeId":"'.$case_data['caseTypeId'].'","casePriority":"'.$case_data['casePriority'].'","casePriorityId":"'.$case_data['casePriorityId'].'","assignedObjectRefName":"'.$case_data['assignedObjectRefName'].'","assignedObjectId":"'.$case_data['assignedObjectId'].'","assignedObjectRefId":"'.$case_data['assignedObjectRefId'].'","caseSummary":"'.$case_data['caseSummary'].'","description":"'.$case_data['description'].'","caseItem":"'.$case_data['caseItem'].'","caseItemId":'.$case_data['caseItemId'].',"needByDate":"'.$case_data['needByDate'].'","caseProject":"'.$case_data['caseProject'].'","caseProjectId":'.$case_data['caseProjectId'].',"dateResolved":"'.$case_data['dateResolved'].'","caseCustomer":"'.$case_data['caseCustomer'].'","caseCustomerId":'.$case_data['caseCustomerId'].',"caseContact":"'.$case_data['caseContact'].'","caseContactId":'.$case_data['caseContactId'].',"caseEmail":"'.$case_data['caseEmail'].'","customAttributes":['.$custom_attr.']}&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;

		
		curl_setopt($this->ch, CURLOPT_URL, $api_url);

		$api_result = curl_exec($this->ch);
		
		$api_response = json_decode($api_result);
		
		if($api_response)
		{
			return 'Thank you, your submission has been received!';
		}else{
			return 'Sorry, there seems to have been an error.  Please check with the administrator.';
		}
		
	}
	
	function create_contact($contactData, $input_phone_numbers, $input_addresses, $input_emails, $input_custom_attributes)
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
				$phone_numbers .= $add_comma.'{"phoneNumber":"'.$cur_phone['phoneNumber'].'","phoneTypeCode":"'.$cur_phone['phoneType'].'","phoneType":"'.$cur_phone['phoneType'].'","id":"contact_phone_input'.$counter_text.'"}';
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
		$required_fields = Array('assigneeObjectRefId','assigneeObjectRefName','lastName');
		foreach ($required_fields as $cur_field)
		{
			if(!$contactData[$cur_field])
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

		
		// Some attributes need to have "null" passed in, if there is no value.  We'll check if a value was given, if not set to null.
		if(!$contactData['campaignId']){$contactData['campaignId'] = 'null';}
		if(!$contactData['territoryId']){$contactData['territoryId'] = 'null';}
		if(!$contactData['accountId']){$contactData['accountId'] = 'null';}
		if(!$contactData['marketId']){$contactData['marketId'] = 'null';}
		if(!$contactData['marketName']){$contactData['marketName'] = 'null';}
		if(!$contactData['segment_id']){$contactData['segment_id'] = 'null';}
		if(!$contactData['segmentName']){$contactData['segmentName'] = 'null';}
		if(!$contactData['followUpDate']){$contactData['followUpDate'] = 'null';}
		if(!$contactData['followUpDescription']){$contactData['followUpDescription'] = 'null';}
		
		//These are required values, but the ID numbers change from firm to firm.  We'll use a default of one exists, or get a new one now.
		if(!$contactData['contactTypeName']){
			if($this->contactTypeName){
				$contactData['contactTypeName'] = $this->contactTypeName;
			}else{
				$this->get_contacts_settings();
				$contactData['contactTypeName'] = $this->contactTypeName;
			}
		}
		if(!$contactData['contactType']){
			if($this->contactType){
				$contactData['contactType'] = $this->contactType;
			}else{
				$this->get_contacts_settings();
				$contactData['contactType'] = $this->contactType;
			}
		}
		
		//if(!$addresses){$addresses = '{"addressAttributeId":"address_section_attr_id","addressTypeCode":"1","addressType":"Billing+Address","addressLine1":"","addressLine2":"","city":"","stateCode":"","zipCode":"","countryId":176,"countryName":"United+States","deliveryInstructions":""}';}
		
		/* These are other possible values that could be passed in
			$contactData['skypeName']
			$contactData['campaignName']
			$contactData['territoryName']
			$contactData['lastUpdatedByName']
			$contactData['createdByName']
			$contactData['lastUpdateDate']
			$contactData['creationDate']
			$contactData['industry']
			$contactData['industryName']
			$contactData['faceBookURL']
			$contactData['twitterURL']
			$contactData['linkedInURL']
		*/
		 
		$api_url = 'https://api.apptivo.com/app/dao/contacts?a=saveContact&isNewCustomer=&contactData={"title":null,"":"","firstName":"'.$contactData['firstName'].'","lastName":"'.$contactData['lastName'].'","jobTitle":"","contactTypeName":"'.$contactData['contactTypeName'].'","contactType":"'.$contactData['contactType'].'","accountName":"'.$contactData['accountName'].'","accountId":'.$contactData['accountId'].',"supplierName":"","supplierId":null,"assigneeObjectRefName":"'.$contactData['assigneeObjectRefName'].'","assigneeObjectRefId":'.$contactData['assigneeObjectRefId'].',"assigneeObjectId":8,"contactCategoryName":"","categoryId":null,"description":"","phoneNumber":"'.$contactData['phoneNumber'].'","contactEmail":"'.$contactData['contactEmail'].'","skypeName":"","languageName":null,"languageCode":null,"phoneticName":"","nickName":"","dateOfBirth":"","marketName":"","marketId":null,"segmentName":"","segmentId":null,"territoryName":"","territoryId":null,"industryName":"","industryId":null,"followUpDate":null,"followUpDescription":null,"createdByName":"","lastUpdatedByName":"","creationDate":"","lastUpdateDate":"","hobbies":"","foods":"","faceBookURL":"","twitterURL":"","linkedInURL":"","website":"","section_1423271450718_1343_attribute_radio_1423271792653_2867":"No","phoneNumbers":['.$phone_numbers.'],"emailAddresses":[{"emailAddress":"'.$contactData['contactEmail'].'","emailTypeCode":"BUSINESS","emailType":"Business","id":"cont_email_input"}],"searchColumn":"","addresses":['.$addresses.'],"labels":[],"customAttributes":['.$custom_attr.'],"createdBy":null,"lastUpdatedBy":null,"contactCategoryIds":[],"categories":[],"isNewCustomer":"","syncToGoogle":"Y"}&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
				
		curl_setopt($this->ch, CURLOPT_URL, $api_url);

		$api_result = curl_exec($this->ch);

		return json_decode($api_result);
	}
	
	function create_customer($customerData, $input_phone_numbers, $input_addresses, $input_emails, $input_custom_attributes)
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
				$phone_numbers .= $add_comma.'{"phoneNumber":"'.$cur_phone['phoneNumber'].'","phoneTypeCode":"'.$cur_phone['phoneType'].'","phoneType":"'.$cur_phone['phoneType'].'","id":"customer_phone_input'.$counter_text.'"}';
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
		$required_fields = Array('assigneeObjectRefId','assigneeObjectRefName','customerNumber','customerName');
		foreach ($required_fields as $cur_field)
		{
			if(!$customerData[$cur_field])
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

		
		// Some attributes need to have "null" passed in, if there is no value.  We'll check if a value was given, if not set to null.
		if(!$customerData['campaignId']){$customerData['campaignId'] = 'null';}
		if(!$customerData['territoryId']){$customerData['territoryId'] = 'null';}
		if(!$customerData['customerCategoryId']){$customerData['customerCategoryId'] = 'null';}
		if(!$customerData['marketId']){$customerData['marketId'] = 'null';}
		if(!$customerData['marketName']){$customerData['marketName'] = 'null';}
		if(!$customerData['segment_id']){$customerData['segment_id'] = 'null';}
		if(!$customerData['segmentName']){$customerData['segmentName'] = 'null';}
		if(!$customerData['followUpDate']){$customerData['followUpDate'] = 'null';}
		if(!$customerData['followUpDescription']){$customerData['followUpDescription'] = 'null';}
		if(!$customerData['employeeRangeId']){$customerData['employeeRangeId'] = 'null';}
		if(!$customerData['employeeRange']){$customerData['employeeRange'] = 'null';}
		if(!$customerData['annualRevenue']){$customerData['annualRevenue'] = 'null';}
		
		//These are required values, but the ID numbers change from firm to firm.  We'll use a default of one exists, or get a new one now.
		if(!$customerData['paymentTerm']){
			if($this->paymentTerm){
				$customerData['paymentTerm'] = $this->paymentTerm;
			}else{
				$this->get_customers_settings();
				$customerData['paymentTerm'] = $this->paymentTerm;
			}
		}
		if(!$customerData['paymentTermId']){
			if($this->paymentTermId){
				$customerData['paymentTermId'] = $this->paymentTermId;
			}else{
				$this->get_customers_settings();
				$customerData['paymentTermId'] = $this->paymentTermId;
			}
		}

		//if(!$addresses){$addresses = '{"addressAttributeId":"address_section_attr_id","addressTypeCode":"1","addressType":"Billing+Address","addressLine1":"","addressLine2":"","city":"","stateCode":"","zipCode":"","countryId":176,"countryName":"United+States","deliveryInstructions":""}';}
		
		/* These are other possible values that could be passed in
			$customerData['skypeName']
			$customerData['estimatedCloseDate']
			$customerData['campaignName']
			$customerData['territoryName']
			$customerData['lastUpdatedByName']
			$customerData['createdByName']
			$customerData['lastUpdateDate']
			$customerData['creationDate']
			$customerData['industry']
			$customerData['industryName']
			$customerData['ownership']
			$customerData['website']
			$customerData['faceBookURL']
			$customerData['twitterURL']
			$customerData['linkedInURL']
		*/
		 
		$api_url = 'https://api.apptivo.com/app/dao/customers?a=createCustomer&customerData={"customerName":"'.$customerData['customerName'].'","customerNumber":"'.$customerData['customerNumber'].'","customerCategory":"'.$customerData['customerCategory'].'","customerCategoryId":'.$customerData['customerCategoryId'].',"":"","assigneeObjectRefName":"'.$customerData['assigneeObjectRefName'].'","assigneeObjectId":8,"assigneeObjectRefId":'.$customerData['assigneeObjectRefId'].',"phoneNumber":"","contactEmail":"'.$customerData['contactEmail'].'","section_1423271450718_1343_attribute_checkbox_1423272399478_5351":"N","parentCustomerName":"","parentCustomerId":null,"employeeRange":"","employeeRangeId":null,"website":"","tickerSymbol":"","annualRevenue":null,"campaignName":"","campaignId":null,"creditRating":"","marketName":"","marketId":null,"segmentName":"","segmentId":null,"industryName":"","industryId":null,"territoryName":"","territoryId":null,"paymentTerm":"'.$customerData['paymentTerm'].'","paymentTermId":"'.$customerData['paymentTermId'].'","ownership":"","slaName":"","slaId":null,"followUpDate":null,"followUpDescription":null,"description":"","createdByName":"","lastUpdatedByName":"","creationDate":"","lastUpdateDate":"","isExistingCustomer":"N","isAffiliate":"N","faceBookURL":"","twitterURL":"","linkedInURL":"","phoneNumbers":['.$phone_numbers.'],"emailAddresses":[{"emailAddress":"'.$customerData['contactEmail'].'","emailTypeCode":"BUSINESS","emailType":"Business","id":"cont_email_input"}],"searchColumn":"","addresses":[{"addressAttributeId":"address_section_attr_id","addressTypeCode":"1","addressType":"Billing+Address","addressLine1":"","addressLine2":"","city":"","stateCode":"","zipCode":"","countryId":176,"countryName":"United+States","deliveryInstructions":""}],"labels":[],"customAttributes":['.$custom_attr.'],"customerId":null,"createdBy":null,"lastUpdatedBy":null}&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;	
		curl_setopt($this->ch, CURLOPT_URL, $api_url);
		$api_result = curl_exec($this->ch);
		
		return json_decode($api_result);
	}
	
// Update Methods
	function update_contact($contactId, $attributeName, $contactData)
	{
		$api_url = 'https://api.apptivo.com/app/dao/contacts?a=updateContact&objectId=2&contactId='.$contactId.'&attributeName='.$attributeName.'&contactData='.$contactData.'&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);
		$api_result = curl_exec($this->ch);
		
		return json_decode($api_result);
	}
	
	function update_lead($leadId, $attributeNames, $leadData)
	{
		$api_url = 'https://api.apptivo.com/app/dao/leads?a=updateLead&objectId=4&leadId='.$leadId.'&attributeNames='.$attributeNames.'&leadData='.$leadData.'&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);
		$api_result = curl_exec($this->ch);
		
		return json_decode($api_result);
	}
	
	function update_opportunity($opportunityId, $attributeNames, $opportunityData) {
		$api_url = 'https://api.apptivo.com/app/dao/opportunities?a=updateOpportunity&objectId=11&opportunityId='.$opportunityId.'&attributeName='.$attributeNames.'&opportunityData='.$opportunityData.'&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);
		$api_result = curl_exec($this->ch);
		
		return json_decode($api_result);
	}
	
// Search Methods (later on should abstract these to be generic advanced search methods, right now search criteria is locked in)
	function search_customers_by_name($customerName)
	{
		$api_url = 'https://api.apptivo.com/app/dao/customers?a=getAllCustomersByAdvancedSearch&objectId=3&startIndex=0&numRecords=250&sortColumn='.urlencode('customerName.sortable').'&sortDir=asc&searchData={"customerName":"'.$customerName.'","customerNumber":"","":"on","assigneeObjectRefName":null,"assigneeObjectId":null,"assigneeObjectRefId":null,"description":"","phoneType":"-1","phoneNumber":"","emailType":"-1","contactEmail":"","skypeName":"","parentCustomerName":"","parentCustomerId":null,"website":"","tickerSymbol":"","annualRevenue":null,"annualRevenueTo":"","campaignName":"","campaignId":null,"creditRating":"","territoryName":"","territoryId":null,"ownership":"","followUpDate":"","followUpDescription":"","createdByName":"","lastUpdatedByName":"","creationDate":"","lastUpdateDate":"","isExistingCustomer":"N","isAffiliate":"N","faceBookURL":"","twitterURL":"","linkedInURL":"","phoneNumbers":[{"phoneNumber":"","phoneType":"'.urlencode('Select One').'","phoneTypeCode":"-1","id":"cust_phone_input"}],"emailAddresses":[{"emailAddress":"","emailTypeCode":"-1","emailType":"","id":"cont_email_input"}],"searchColumn":"","addresses":[{"addressAttributeId":"addressAttributeId_1414492924019_8891","addressType":"","addressLine1":"","addressLine2":"","city":"","stateCode":"","state":"","zipCode":"","countryId":-1,"countryName":"'.urlencode('Select One').'","deliveryInstructions":""}],"labels":[],"customAttributes":[]}&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);
		$api_result = curl_exec($this->ch);
		return json_decode($api_result);
	}
	
	function search_leads_by_customer($customerName, $customerId)
	{
		$customerName = urlencode($customerName);
		$api_url = 'https://api.apptivo.com/app/dao/leads?a=getAllLeadsByAdvancedSearch&objectId=4&startIndex=0&numRecords=250&sortColumn=_score&sortDir=desc&searchData={"title":"-1","":"","firstName":"","lastName":"","phoneType":"-1","phoneNumber":"","emailType":"-1","contactEmail":"","jobTitle":"","companyName":"","assigneeObjectRefName":null,"assigneeObjectId":null,"assigneeObjectRefId":null,"description":"","potentialAmount":null,"potentialAmountTo":"","currencyCode":"","campaignName":null,"campaignId":null,"territoryName":null,"territoryId":null,"accountName":"'.$customerName.'","accountId":'.$customerId.',"annualRevenue":null,"annualRevenueTo":"","website":"","createdByName":"","lastUpdatedByName":"","creationDate":"","lastUpdateDate":"","estimatedCloseDate":"","estimatedCloseEndDate":"","faceBookURL":"","twitterURL":"","linkedInURL":"","labels":[],"phoneNumbers":[{"phoneNumber":"","phoneType":"Select+One","phoneTypeCode":"-1","id":"lead_phone_input"}],"emailAddresses":[{"emailAddress":"","emailTypeCode":"-1","emailType":"","id":"cont_email_input"}],"searchColumn":"","addresses":[{"addressAttributeId":"addressAttributeId_1426668077825_5367","addressType":"","addressLine1":"","addressLine2":"","city":"","stateCode":"","state":"","zipCode":"","countryId":-1,"countryName":"Select+One"}],"customAttributes":[]}&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);
		$api_result = curl_exec($this->ch);
		return json_decode($api_result);
	}
	
	function search_opportunities_by_customer($customerName, $customerId)
	{
		$customerName = urlencode($customerName);
		$api_url = 'https://api.apptivo.com/app/dao/opportunities?a=getAllOpportunitiesByAdvancedSearch&objectId=11&startIndex=0&numRecords=250&sortColumn=_score&sortDir=desc&searchData={"opportunityName":"","":"","opportunityCustomer":"'.$customerName.'","opportunityCustomerId":'.$customerId.',"probability":null,"probabilityTo":null,"opportunityContact":"","opportunityContactId":null,"closeDate":"","closeDateTo":"","nextStep":"","assignedToObjectRefName":"","amount":null,"amountTo":null,"currencyCode":null,"campaignName":"","campaignId":null,"description":"","followUpDate":"","followUpDescription":"","createdByName":"","lastUpdatedByName":"","creationDate":"","lastUpdateDate":"","territoryName":"","territoryId":null,"addresses":[],"customAttributes":[],"labels":[]}&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);
		$api_result = curl_exec($this->ch);
		return json_decode($api_result);
	}
	
	function search_contacts_by_text($searchText) {
		$contactEmail = urlencode($searchText);
		$api_url = 'https://api.apptivo.com/app/dao/contacts?a=getAllContactsBySearchText&objectId=2&startIndex=0&numRecords=250&sortColumn=_score&sortDir=desc&searchText='.$searchText.'&filterData=&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);
		$api_result = curl_exec($this->ch);
		
		return json_decode($api_result);
	}
	
	function search_contacts_by_email($contactEmail) {
		$contactEmail = urlencode($contactEmail);
		$api_url = 'https://api.apptivo.com/app/dao/contacts?a=getAllContactsByAdvancedSearch&objectId=2&startIndex=0&numRecords=250&sortColumn=_score&sortDir=desc&searchData={"title":null,"":"","firstName":"","lastName":"","phoneType":"-1","phoneNumber":"","emailType":"-1","contactEmail":"'.$contactEmail.'","jobTitle":"","accountName":"","accountId":null,"assigneeObjectRefName":null,"assigneeObjectId":null,"assigneeObjectRefId":null,"contactCategoryName":"","description":"","territoryName":"","territoryId":null,"createdByName":"","lastUpdatedByName":"","creationDate":"","lastUpdateDate":"","dateOfBirth":"","dateOfBirthTo":"","faceBookURL":"","twitterURL":"","linkedInURL":"","website":"","syncToGoogle":null,"categories":[],"phoneNumbers":[{"phoneNumber":"","phoneType":"'.urlencode('Select One').'","phoneTypeCode":"-1","id":"contact_phone_input"}],"emailAddresses":[{"emailAddress":"'.$contactEmail.'","emailTypeCode":"-1","emailType":"","id":"cont_email_input"}],"searchColumn":"","addresses":[{"addressAttributeId":"addressAttributeId_1426661106713_7286","addressType":"","addressLine1":"","addressLine2":"","city":"","stateCode":"","state":"","zipCode":"","countryId":-1,"countryName":"'.urlencode('Select One').'"}],"labels":[],"customAttributes":[]}&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);
		$api_result = curl_exec($this->ch);
		return json_decode($api_result);
	}
	
	function search_contacts_by_customer($customerName, $customerId)
	{
		$api_url = 'https://api.apptivo.com/app/dao/contacts?a=getAllContactsByAdvancedSearch&objectId=2&startIndex=0&numRecords=250&sortColumn=_score&sortDir=desc&searchData={"title":null,"":"","firstName":"","lastName":"","phoneType":"-1","phoneNumber":"","emailType":"-1","contactEmail":"","jobTitle":"","accountName":"'.$customerName.'","accountId":'.$customerId.',"assigneeObjectRefName":null,"assigneeObjectId":null,"assigneeObjectRefId":null,"contactCategoryName":"","description":"","territoryName":"","territoryId":null,"createdByName":"","lastUpdatedByName":"","creationDate":"","lastUpdateDate":"","dateOfBirth":"","dateOfBirthTo":"","faceBookURL":"","twitterURL":"","linkedInURL":"","website":"","syncToGoogle":null,"categories":[],"phoneNumbers":[{"phoneNumber":"","phoneType":"'.urlencode('Select One').'","phoneTypeCode":"-1","id":"contact_phone_input"}],"emailAddresses":[{"emailAddress":"'.$contactEmail.'","emailTypeCode":"-1","emailType":"","id":"cont_email_input"}],"searchColumn":"","addresses":[{"addressAttributeId":"addressAttributeId_1426661106713_7286","addressType":"","addressLine1":"","addressLine2":"","city":"","stateCode":"","state":"","zipCode":"","countryId":-1,"countryName":"'.urlencode('Select One').'"}],"labels":[],"customAttributes":[]}&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);
		$api_result = curl_exec($this->ch);
		return json_decode($api_result);
	}

	function search_contacts_by_name($firstName,$lastName)
	{
		$api_url = 'https://api.apptivo.com/app/dao/contacts?a=getAllContactsByAdvancedSearch&objectId=2&startIndex=0&numRecords=250&sortColumn=_score&sortDir=desc&searchData={"title":null,"":"","firstName":"'.$firstName.'","lastName":"'.$lastName.'","phoneType":"-1","phoneNumber":"","emailType":"-1","contactEmail":"","jobTitle":"","accountName":"","accountId":null,"assigneeObjectRefName":null,"assigneeObjectId":null,"assigneeObjectRefId":null,"contactCategoryName":"","description":"","territoryName":"","territoryId":null,"createdByName":"","lastUpdatedByName":"","creationDate":"","lastUpdateDate":"","dateOfBirth":"","dateOfBirthTo":"","faceBookURL":"","twitterURL":"","linkedInURL":"","website":"","syncToGoogle":null,"categories":[],"phoneNumbers":[{"phoneNumber":"","phoneType":"'.urlencode('Select One').'","phoneTypeCode":"-1","id":"contact_phone_input"}],"emailAddresses":[{"emailAddress":"'.$contactEmail.'","emailTypeCode":"-1","emailType":"","id":"cont_email_input"}],"searchColumn":"","addresses":[{"addressAttributeId":"addressAttributeId_1426661106713_7286","addressType":"","addressLine1":"","addressLine2":"","city":"","stateCode":"","state":"","zipCode":"","countryId":-1,"countryName":"'.urlencode('Select One').'"}],"labels":[],"customAttributes":[]}&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);
		$api_result = curl_exec($this->ch);
		return json_decode($api_result);
	}

	
//  Activity Management
	//Tasks Object
		function get_all_tasks($sortColumn, $sortDir)
		{
			$api_url = 'https://api.apptivo.com/app/dao/activities?a=getAllActivities&activityType=Task&isFromApp=home&sortColumn='.$sortColumn.'&sortDir='.$sortDir.'&objectStatus=0&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
			curl_setopt($this->ch, CURLOPT_URL, $api_url);	
			$api_result = curl_exec($this->ch);
			$api_response = json_decode($api_result);		
		
			return $api_response;
		}
		
		function update_task($attributeName, $activityId, $taskData)
		{
		
			//$api_url = 'https://api.apptivo.com/app/dao/activities?a=updateTask&actType=home&activityId='.$activityId.'&attributeName=["'.$attributeName.'"]&taskData={"'.$attributeName.'":"'.$taskData.'"}&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
			
			$api_url = 'https://api.apptivo.com/app/dao/activities?a=updateTask&actType=home&activityId='.$activityId.'&attributeName=["'.$attributeName.'"]&taskData='.$taskData.'&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
			
			
			curl_setopt($this->ch, CURLOPT_URL, $api_url);
			$api_result = curl_exec($this->ch);
			$api_response = json_decode($api_result);

			return $api_response;
		}
		
		function get_task_priorities()
		{
			$api_url = 'https://api.apptivo.com/app/commonservlet?a=getLookups&app_req_type=ajax&lookupType=PRIORITY&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
			curl_setopt($this->ch, CURLOPT_URL, $api_url);
			$api_result = curl_exec($this->ch);
			$api_response = json_decode($api_result);

			return $api_response;
		}
		
		function createTask ($taskData) {		
			if(!$taskData['activityTypeName']){$taskData['activityTypeName'] = 'Appointment';}
			if(!$taskData['objectId']){$taskData['objectId'] = '8';}
			if(!$taskData['objectRefId']){$taskData['objectRefId'] = null;}
			if(!$taskData['isBillable']){$taskData['isBillable'] = 'Y';}
			if(!$taskData['reminders']){$taskData['reminders'] = Array();}
			if(!$taskData['isRemindMeEnabled']){$taskData['isRemindMeEnabled'] = 'N';}
			if(!$taskData['labels']){$taskData['labels'] = Array();}
			if(!$taskData['assigneeDetails']){
				$taskData['assigneeDetails'] = Array(
					Array (
						'objectId' => 8,
						'objectRefId' => 49462,
					)
				);
			}
			
			if(!$taskData['statusId']){
				if($this->taskStatusId){
					$taskData['statusId'] = $this->taskStatusId;
				}else{
					$this->getTaskSettings();
					$taskData['statusId'] = $this->taskStatusId;
				}
			}
			if(!$taskData['statusName']){
				if($this->taskStatusName){
					$taskData['statusName'] = $this->taskStatusName;
				}else{
					$this->getTaskSettings();
					$taskData['statusName'] = $this->taskStatusName;
				}
			}
			if(!$taskData['priorityId']){
				if($this->taskPriorityId){
					$taskData['priorityId'] = $this->taskPriorityId;
				}else{
					$this->getTaskSettings();
					$taskData['priorityId'] = $this->taskPriorityId;
				}
			}
			if(!$taskData['priorityName']){
				if($this->taskPriorityName){
					$taskData['priorityName'] = $this->taskPriorityName;
				}else{
					$this->getTaskSettings();
					$taskData['priorityName'] = $this->taskPriorityName;
				}
			}
			
			// These are mandatory fields that we cannot set a default for.  You must pass in these fields, or we'll return an error message.
			$required_fields = Array('subject','startDate','endDate','startTimeHour','startTimeMinute','endTimeHour','endTimeMinute');
			foreach ($required_fields as $cur_field)
			{
				if(!$taskData[$cur_field])
				{
					print 'Error: '.$cur_field.' is empty.  This is a required field.  Please report this error to the website admin.<br />';
					return 'Error: '.$cur_field.' is empty.  This is a required field.  Please report this error to the website admin.<br />';
				}
			}
			

			$api_url = 'https://api.apptivo.com/app/dao/activities?a=createTask&actType=home&taskData='.json_encode($taskData).'&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
			
			curl_setopt($this->ch, CURLOPT_URL, $api_url);
			$api_result = curl_exec($this->ch);
			$api_response = json_decode($api_result);	
			return $api_response;
		}
	//Event Object
		function create_event($eventData)
		{			
			if(!$eventData['activityTypeName']){$eventData['activityTypeName'] = 'Appointment';}
			if(!$eventData['sourceObjectId']){$eventData['sourceObjectId'] = '6';}
			if(!$eventData['objectId']){$eventData['objectId'] = '6';}
			if(!$eventData['objectRefId']){$eventData['objectRefId'] = null;}
			if(!$eventData['isBillable']){$eventData['isBillable'] = 'Y';}
			if(!$eventData['reminders']){$eventData['reminders'] = Array();}
			if(!$eventData['isRemindMeEnabled']){$eventData['isRemindMeEnabled'] = 'N';}
			if(!$eventData['labels']){$eventData['labels'] = Array();}
			if(!$eventData['assigneeDetails']){
				$eventData['assigneeDetails'] = Array(
					Array (
						'objectId' => 8,
						'objectRefId' => 49462,
					)
				);
			}
			
			// These are mandatory fields that we cannot set a default for.  You must pass in these fields, or we'll return an error message.
			$required_fields = Array('subject','startDate','endDate','startTimeHour','startTimeMinute','endTimeHour','endTimeMinute');
			foreach ($required_fields as $cur_field)
			{
				if(!$eventData[$cur_field])
				{
					print 'Error: '.$cur_field.' is empty.  This is a required field.  Please report this error to the website admin.<br />';
					return 'Error: '.$cur_field.' is empty.  This is a required field.  Please report this error to the website admin.<br />';
				}
			}

			$api_url = 'https://api.apptivo.com/app/dao/activities?a=createEvent&actType=home&eventData='.json_encode($eventData).'&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
			curl_setopt($this->ch, CURLOPT_URL, $api_url);
			$api_result = curl_exec($this->ch);
			$api_response = json_decode($api_result);	
			
			return $api_response;
		}
	//Follow Up Object
		function createFollowUp($followUpData)
		{			
			if(!$followUpData['isRemindMeEnabled']){$followUpData['isRemindMeEnabled'] = 'N';}
			if(!$followUpData['assigneeDetails']){
				$followUpData['assigneeDetails'] = Array(
					Array (
						'objectId' => 8,
						'objectRefId' => 49462,
					)
				);
			}
			
			// These are mandatory fields that we cannot set a default for.  You must pass in these fields, or we'll return an error message.
			$required_fields = Array('description','startDate','endDate');
			foreach ($required_fields as $cur_field)
			{
				if(!$followUpData[$cur_field])
				{
					print 'Error: '.$cur_field.' is empty.  This is a required field.  Please report this error to the website admin.<br />';
					return 'Error: '.$cur_field.' is empty.  This is a required field.  Please report this error to the website admin.<br />';
				}
			}

			$api_url = 'https://api.apptivo.com/app/dao/activities?a=createFollowUpActivity&actType=home&followUpData='.json_encode($followUpData).'&b=1&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
			curl_setopt($this->ch, CURLOPT_URL, $api_url);
			$api_result = curl_exec($this->ch);
			$api_response = json_decode($api_result);	

			return $api_response;
		}

// General data utility methods - get lists of countries/states, retrieve configuration settings, retrieve employee lists, etc.
	
	function get_countries()
	{
		$api_url = 'https://api.apptivo.com/app/commonservlet?a=getAllCountries&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);

		$api_result = curl_exec($this->ch);
		$api_response = json_decode($api_result);

		return $api_response;
	}
	
	function get_states_by_country($input_country_id)
	{
		$api_url = 'https://api.apptivo.com/app/commonservlet?a=getAllStatesByCountryId&countryId='.$input_country_id.'&api_key='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);

		$api_result = curl_exec($this->ch);
		$api_response = json_decode($api_result);

		return $api_response;
	}
	
	//Get settings by App.  When this is called, we'll store some required values defaults that can be overridden later.
	function getTaskSettings()
	{
		$api_url = 'https://api.apptivo.com/app/dao/activities?a=getActivityData&b=1&objectId=6&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);

		$api_result = curl_exec($this->ch);
		$api_response = json_decode($api_result);
		
		//Take the required fields and grab the first value to set as default
		$this->taskStatusName = urlencode($api_response->statuses[1]->meaning);
		$this->taskStatusId = $api_response->statuses[1]->lookupId;
		$this->taskPriorityName = urlencode($api_response->priorities[0]->meaning);
		$this->taskPriorityId = $api_response->priorities[1]->lookupId;
		
		return $api_response;
	}

	function get_cases_settings()
	{
		$api_url = 'https://api.apptivo.com/app/dao/case?a=getCasesConfigData&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);

		$api_result = curl_exec($this->ch);
		$api_response = json_decode($api_result);
		
		//Take the required fields and grab the first value to set as default
		$this->caseType = urlencode($api_response->caseType[0]->meaning);
		$this->caseTypeId = $api_response->caseType[0]->lookupId;
		$this->caseStatus = urlencode($api_response->caseStatus[0]->meaning);
		$this->caseStatusId = $api_response->caseStatus[0]->lookupId;
		$this->casePriority = urlencode($api_response->casePriority[0]->meaning);
		$this->casePriorityId = $api_response->casePriority[0]->lookupId;
		
		return $api_response;
	}

	function get_customers_settings()
	{
		$api_url = 'https://api.apptivo.com/app/dao/customers?a=getAllCustomerConfigData&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);

		$api_result = curl_exec($this->ch);
		$api_response = json_decode($api_result);
		
		//Take the required fields and grab the first value to set as default
		$this->paymentTermId = $api_response->paymentTerms[0]->paymentTermId;
		$this->paymentTerm = urlencode($api_response->paymentTerms[0]->paymentTermCode);
		
		return $api_response;
	}
	
	function get_contacts_settings()
	{
		$api_url = 'https://api.apptivo.com/app/dao/contacts?a=getAllContactConfigData&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);

		$api_result = curl_exec($this->ch);
		$api_response = json_decode($api_result);
		
		//Take the required fields and grab the first value to set as default
		$this->contactType = $api_response->contactTypes[0]->contactTypeId;
		$this->contactTypeName = urlencode($api_response->contactTypes[0]->contactType);
		
		return $api_response;
	}
	
	function get_leads_settings()
	{
		$api_url = 'https://api.apptivo.com/app/dao/leads?a=getLeadConfigData&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);
		
		$api_result = curl_exec($this->ch);
		$api_response = json_decode($api_result);
		
		return $api_response;
	}
	
	function get_opportunities_settings()
	{
		$api_url = 'https://api.apptivo.com/app/dao/opportunities?a=getAllOpportunityConfigData&apiKey='.$this->api_key.'&accessKey='.$this->access_key.$this->user_name_str;
		curl_setopt($this->ch, CURLOPT_URL, $api_url);
		
		$api_result = curl_exec($this->ch);
		$api_response = json_decode($api_result);

		//Take the required fields and grab the first value to set as default		
		$this->leadSourceTypeName = urlencode($api_response->leadSources[0]->meaning);
		$this->leadSourceTypeId = $api_response->leadSources[0]->lookupId;
		
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

	function __construct($input_apikey, $input_accesskey, $user_name) {
				
		$this->api_key = $input_apikey;
		$this->access_key = $input_accesskey;
		if($user_name) {
			$this->user_name_str = '&userName='.$user_name;
		}
		
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