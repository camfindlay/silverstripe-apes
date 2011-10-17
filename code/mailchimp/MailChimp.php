<?php
/**
 * MailChimp
 * 
 * Decorates the member to auto integrate with the mailchimp API
 *
 * PHP version 5
 *	
 * listSubscribe($id, $email_address, $merge_vars, $email_type='html', $double_optin=true, $update_existing=false, $replace_interests=true, $send_welcome=false) 
 * 
 * @package    apes
 * @author     Cam Findlay <cam@camfindlay.com>
 * @copyright  2011 Cam Findlay Consulting
 * @version    SVN: $Id$      
 * @uses	   MCAPI
 */
 class MailChimp extends DataObjectDecorator{
 
 public function extraStatics(){
 	return array(
			'db' => array(
				'MailChimpID'=>'Varchar()',
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
 
 public function onBeforeWrite(){}
 
 
 public function onAfterWrite(){
 	
	$siteconfig = SiteConfig::current_site_config();
	
	if($siteconfig->MailchimpInstalled){
	
		$api = new MCAPI($siteconfig->MailchimpApiKey);
 		$merge_vars = array(
			'FNAME'=>$this->owner->FirstName,
			'LNAME'=>$this->owner->Surname,
			);
	
	
		
	
		foreach(APES::$syncFields as $field){
			$tag = strtoupper(substr($field,0,8));
		
			
	
			$merge_vars[$tag] = $this->owner->$field;
		
		
			}		
	
			
			
			
		//add custom syncfields here
	//@todo use statics to set the double optin etc
			if(!$this->isUnsubscribed($this->owner->Email)){
					$api->listSubscribe( $siteconfig->MailchimpListId, $this->owner->Email,$merge_vars,'html',false,true,true,false);
					
					$memberinfo = $api->listMemberInfo($siteconfig->MailchimpListId, $this->owner->Email);
					
					
					$this->owner->MailChimpID = $memberinfo['id'];
					}
	
	
	
	}
	
}
 
 public function onBeforeDelete(){
 //remove the member from mailchimp if they ask to be deleted
 $siteconfig = SiteConfig::current_site_config();
 $api = new MCAPI($siteconfig->MailchimpApiKey);
 $api->listUnsubscribe($siteconfig->MailchimpListId, $this->owner->Email);
 
 }
 
 public function onAfterDelete(){}
 
 /**
  * Checks to see if a given user has already unsubscribed from the list we want to add them to... if they have DO NOT re-subscribe them!
  */
 protected function isUnsubscribed($email_address){
	$siteconfig = SiteConfig::current_site_config();
 	$api = new MCAPI($siteconfig->MailchimpApiKey);
 	$info = $api->listMemberInfo($siteconfig->MailchimpListId, $email_address);
 	
 	//now check the status variable.
 	if($info['status'] == "unsubscribed"){
 		return true;
 		} else {
 		return false;
 	}
 	
 }
 
 
 }