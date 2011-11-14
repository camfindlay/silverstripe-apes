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
				
				)				
		);
 	
 }
 
public function updateCMSFields(FieldSet &$fields) {
 		$siteconfig = $this->owner;
 		 		
 		$fields->addFieldToTab('Root.APES', new HeaderField('MailChimp Setup'));
 		
         $fields->addFieldToTab("Root.APES", new TextField("MailchimpApiKey", 'MailChimp API Key'));
	     $fields->addFieldToTab("Root.APES", new TextField("MailchimpListId", 'MailChimp List ID'));

 		
 		$installed = new CheckboxField("MailchimpInstalled",'Is MailChimp Installed?');
		$installed = $installed->transform(new ReadonlyTransformation());
		
		
		$fields->addFieldToTab('Root.APES',$installed);
 			
 }

 

 
	public function onBeforeWrite(){
		$siteconfig = $this->owner;
		if($siteconfig->MailchimpApiKey && $siteconfig->MailchimpListId){
			$siteconfig->MailchimpInstalled = true;
			} else {
			$siteconfig->MailchimpInstalled = false;
			}
	
	}
	
 
	public function onAfterWrite(){
 	
 		//only run the mailchimp extensions if they have set a API key, List ID and some sync fields
 		$siteconfig = $this->owner;
 		if($siteconfig->MailchimpApiKey && $siteconfig->MailchimpListId && APES::getSyncFields()){
 	
 	
 			$api = new MCAPI($siteconfig->MailchimpApiKey);
 	
 		 	
 		 	
 		 	$fields = APES::getSyncFields();
 	 		
 	 		
 	 		
 	 		
 	 		
 	 		$mergefields = array();
 	
 			//check to see if they have set up extra merge fields, if not - set them up
 			foreach($api->listMergeVars($siteconfig->MailchimpListId) as $merge){
 				$mergefields[] = $merge['name'];
 				}
 	
 			foreach($fields as $field){
 			
 			//make sure there is no whitespace
 			$field = trim($field);
 			
 				if(!in_array($fields, $mergefields)){
 					$tag = strtoupper(substr($field,0,8));
 					
 					//find the type of data in the field - text, number, radio, dropdown, date, address, phone, url, imageurl
 					$datatype = DataObject::database_fields('Member');
 					 						
 					switch($datatype[$field]){
 					
 						case 'Date':
 							$field_type = 'date';
 							break;
 						
 						case 'SS_Datetime':
 							$field_type = 'date';
 							break;
 							
 						case 'Int':
 							$field_type = 'number';
 							break;
 						
 						case 'Currency':
 							$field_type = 'number';
 							break;
 							
 						case 'Decimal':
 							$field_type = 'number';
 							break;
 							
 						case 'Percentage':
 							$field_type = 'number';
 							break;
 						
 						default:
 							$field_type = 'text';
 							break;
 					
 						}
 							
 					$api->listMergeVarAdd($siteconfig->MailchimpListId, $tag, $field, array('field_type'=>$field_type) );
 					
 					
					
 					
 					}
 		
 	
 				}

			}	
	
		}
}