<?php
/**
 * GetResponse
 * 
 * Decorates the member to auto integrate with the GetResponse API
 *
 * PHP version 5
 *	
 *  
 * 
 * @package    apes
 * @author     Cam Findlay <cam@camfindlay.com>
 * @copyright  2011 Cam Findlay Consulting
 * @version    SVN: $Id$      
 * @uses	   GetResponseAPI
 */
 class GetResponse extends DataObjectDecorator{
 
 public function extraStatics(){
 	return array(
			'db' => array(
				'GetResponseID'=>'Varchar()',
				),
			
			'has_one' => array(
				),
				
			'has_many' => array(
				),
				
			'summary_fields' => array(
				),

			'searchable_fields' => array( 
				),
	
			'field_labels' => array(
				)

				
		);
 	
 }
 
 public function updateCMSFields(FieldSet &$fields) {}
 
 public function updateFrontEndFields($fields) {}
 
 public function onBeforeWrite(){
 
 	$siteconfig = SiteConfig::current_site_config();
	
	if($siteconfig->GetResponseInstalled){
	
		$api = new GetResponseAPI($siteconfig->GetResponseApiKey);
 	
 		$name = $this->owner->FirstName." ".$this->owner->Surname;
 		$email = $this->owner->Email;	
		$customs = array();
	
		//setup the fields to sync if they have been entered in to SiteConfig
		if(APES::getSyncFields()){
			$fields = APES::getSyncFields();
			
			foreach($fields as $field){
				$value = trim($value);
				$customs[$field] = $this->owner->$field;
				
				}
			}		
	
			
		//make sure the member did't unsubscribe 
	 	if(!$this->isUnsubscribed($this->owner->Email)){
	 		
	 		//GetResponse is always Double OptIn
			//$doubleoptin = ($siteconfig->APESDoubleOptIn) ? true : false;
					
			//add them or update them in the list. Check if we already have a stored id... if true then update overwise insert them.
			$api->addContact($siteconfig->GetResponseCampaignID, $name, $email, 'standard', 0, $customs);
			
			//return the unique id for this member - may look at using this later to do 2 way sync.
			$contactid =  key( (array)$api->getContacts($siteconfig->GetResponseCampaignID,'EQUALS',$email) );
			$this->owner->GetResponseID = $contactid;
					
			}
	
	
	
	}
	

 
 
 }
 
 
 public function onAfterWrite(){}
 
 public function onBeforeDelete(){}
 
 public function onAfterDelete(){}
 
 /**
  * Checks to see if a given user has already unsubscribed from the list we want to add them to... if they have DO NOT re-subscribe them!
  * @todo check the contacts status in GetResponse
  */
 protected function isUnsubscribed($email_address){
	$siteconfig = SiteConfig::current_site_config();
 	
 	return false; 
 
 	}
 
 }