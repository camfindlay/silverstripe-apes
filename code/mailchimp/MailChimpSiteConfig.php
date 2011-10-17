<?php
/**
 * MailChimp SiteConfig
 * 
 * Decorates the Site Config to auto integrate with the mailchimp API if the api and id are set.
 *
 * PHP version 5
 *	
 * listSubscribe($id, $email_address, $merge_vars, $email_type='html', $double_optin=true, $update_existing=false, $replace_interests=true, $send_welcome=false) 
 * 
 * @package    mailchimp
 * @author     Cam Findlay <cam@camfindlay.com>
 * @copyright  2011 Cam Findlay Consulting
 * @version    SVN: $Id$      
 * @uses	   MCAPI
 */
 class MailChimpSiteConfig extends DataObjectDecorator{
 
 public function extraStatics(){
 	return array(
			'db' => array(
				'MailchimpApiKey' => 'Text',
				'MailchimpListId' => 'Text',
				'MailchimpInstalled' => 'Boolean',
				
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
 
 public function updateCMSFields(FieldSet &$fields) {
 		
 		
 		/*Custom Mailchimp Settings
		 * 
		 */
         $fields->addFieldToTab("Root.MailChimp", new TextField("MailchimpApiKey", 'MailChimp API Key'));
	     $fields->addFieldToTab("Root.MailChimp", new TextField("MailchimpListId", 'MailChimp List ID'));

 		
 		$installed = new TextField("MailchimpInstalled",'Is MailChimp Installed?');
		$installed = $installed->transform(new ReadonlyTransformation());
		$fields->addFieldToTab('Root.MailChimp',$installed);
 
 }
 
 public function updateFrontEndFields($fields) {}
 
	public function onBeforeWrite(){
		$siteconfig = $this->owner;
		if($siteconfig->MailchimpApiKey && $siteconfig->MailchimpListId){
			$siteconfig->MailchimpInstalled = true;
			} else {
			$siteconfig->MailchimpInstalled = false;
			}
	
	}
	
 
	public function onAfterWrite(){
 	
 		//only run the mailchimp extensions if they have set a API ket and List ID
 		$siteconfig = $this->owner;
 		if($siteconfig->MailchimpApiKey && $siteconfig->MailchimpListId){
 	
 	
 			$api = new MCAPI($siteconfig->MailchimpApiKey);
 	
 		 	$fields = APES::$syncFields;
 	 		
 	 		$mergefields = array();
 	
 			//check to see if they have set up extra merge fields, if not - set them up
 			foreach($api->listMergeVars($siteconfig->MailchimpListId) as $merge){
 				$mergefields[] = $merge['name'];
 				}
 	
 			foreach($fields as $field){
 				if(!in_array($field, $mergefields)){
 					$tag = strtoupper(substr($field,0,8));
 							
 					$api->listMergeVarAdd($siteconfig->MailchimpListId, $tag, $field, array('field_type'=>'text') );
 					
 					
					
 					
 					}
 		
 	
 				}

			}	
	
		}
}