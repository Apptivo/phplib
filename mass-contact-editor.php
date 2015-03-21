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
	$user_name = $configData['user_name'];
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
		if(strlen($current->accountName) < 2)
		{

			$created_customers = Array();
			//In this test firm, custom attribute index value is 0 for the organization field
			
			
			
			//The issue here is that the custom attribute index value will change based on how many custom fields are completed on this object.  Need to first walk through the cusomAttributes object and compare against a hard-coded ID value for our organization field.  Once we find the proper ID value, extract it and save as variable for use below.
			
			if ($current->customAttributes[0]->id == 'attribute_input_1412012251279_4458')
			{
				$organization = $current->customAttributes[0]->customAttributeValue;
			}elseif ($current->customAttributes[1]->id == 'attribute_input_1412012251279_4458')
			{
				$organization = $current->customAttributes[1]->customAttributeValue;
			}elseif ($current->customAttributes[2]->id == 'attribute_input_1412012251279_4458')
			{
				$organization = $current->customAttributes[2]->customAttributeValue;
			}elseif ($current->customAttributes[3]->id == 'attribute_input_1412012251279_4458')
			{
				$organization = $current->customAttributes[3]->customAttributeValue;
			}elseif ($current->customAttributes[4]->id == 'attribute_input_1412012251279_4458')
			{
				$organization = $current->customAttributes[4]->customAttributeValue;
			}
			print ('#'.$counter.'    first/last='.$current->firstName.' '.$current->lastName.'     org name='.$organization.'<br>');

			if(strlen($organization) > 2)
			{

				//This is the processing that will run per business logic.
				
				//First, check if a customer already exists (only bother checking first 3 results)
				$results = $apptivo->search_customers_by_name(urlencode($organization));
				print $results->customers[0]->customerName;
				if($results->customers[0]->customerName == $organization)
				{
					$customer_name = $results->customers[0]->customerName;
					$customer_id = $results->customers[0]->customerId;
				}elseif($results->customers[1]->customerName == $organization)
				{
					$customer_name = $results->customers[1]->customerName;
					$customer_id = $results->customers[1]->customerId;
				}elseif($results->customers[2]->customerName == $organization)
				{
					$customer_name = $results->customers[2]->customerName;
					$customer_id = $results->customers[2]->customerId;
				}else{
					//No customer found, create a new one!
				
					//Create array of common fields for this customer
					$customer_data = Array (
						'customerNumber' => 'Auto+generated+number',
						'customerName' => urlencode($organization),
						'customerCategory' => 'Associated+Companies',
						'customerCategoryId' => '23318',
						'assigneeObjectRefId' => '42655',
						'assigneeObjectRefName' => 'Beverly+Love',
						'paymentTermId' => '47717'
					);		

					//Finally, we call the method to create a customer.  Returns the customer object
					$new_customer = $apptivo->create_customer($customer_data, $phone_numbers, $addresses, $emails, $custom_attributes);
					
					//Now we're going to extract the ID info			
					$customer_name = $new_customer->customer->customerName;
					$customer_id = $new_customer->customer->id;
				}
				
				//Now that we have the desired customer name & ID, let's update the contact
				$contact_data = Array (
					'accountName' => urlencode($customer_name),
					'accountId' => $customer_id,
					'isNewCustomer' => '',
				);	
				$attributeName = Array (
					'accountName'
				);	
				//Next we're going to update the accountName (customer) field on the contact	
				$updated_contact = $apptivo->update_contact($current->contactId, json_encode($attributeName), json_encode($contact_data));
			}
		}
	}	
	// Now let's pull the next 50 contacts to process
	$startIndex = $startIndex + 50;
	sleep(5);
} while ($startIndex > $contact_count);



?>
