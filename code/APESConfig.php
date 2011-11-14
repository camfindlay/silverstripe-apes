<?php
/**
 * Automated Provision for Email Services (APES)
 * 
 * Set the fields for SiteCOnfig if you choose to interact with it via the SiteConfig
 *
 * PHP version 5
 *	
 * @package    apes
 * @author     Cam Findlay <cam@camfindlay.com>
 * @copyright  2011 Cam Findlay Consulting
 * @version    SVN: $Id$      
 */
class APESConfig extends DataObjectDecorator {

   		
   		
   	//options for field syncing now in SiteConfig	
   public function extraStatics(){
 	return array(
			'db' => array(
				'APESSyncFields' => 'Text',
				'APESDoubleOptIn' => 'Boolean'				
				),
			
				
		);
 	
 }
 
 public function updateCMSFields(FieldSet &$fields) {
 		
 		
$fields->addFieldToTab('Root.APES', new HeaderField('Sync Setup'));

         $fields->addFieldToTab("Root.APES", new TextField("APESSyncFields", 'Member Fields to Sync (Comma Separated)'));
	     $fields->addFieldToTab("Root.APES", new CheckboxField("APESDoubleOptIn", 'Double Opt-In (Recommended)'));
	     
 	
 }
 


   
   
}