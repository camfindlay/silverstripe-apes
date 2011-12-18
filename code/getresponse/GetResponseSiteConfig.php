<?php
/**
 * GetResponse SiteConfig
 * 
 * Decorates the Site Config to auto integrate with the GetResponse API if the api key is set.
 *
 * PHP version 5
 * 
 * @package    getresponse
 * @author     Cam Findlay <cam@camfindlay.com>
 * @copyright  2011 Cam Findlay Consulting
 * @version    SVN: $Id$      
 * @uses	   GetResponseAPI
 */
 class GetResponseSiteConfig extends DataObjectDecorator{
 
 public function extraStatics(){
 	return array(
			'db' => array(
				'GetResponseApiKey' => 'Text',
				'GetResponseCampaign' => 'Text',
				'GetResponseCampaignID' => 'Varchar(50)',
				'GetResponseInstalled' => 'Boolean',
				
				)				
		);
 	
 }
 
public function updateCMSFields(FieldSet &$fields) {
 		$siteconfig = $this->owner;
 		 		
 		$fields->addFieldToTab('Root.APES', new HeaderField('GetResponse Setup'));
 		
         $fields->addFieldToTab("Root.APES", new TextField("GetResponseApiKey", 'GetResponse API Key'));
         
         $fields->addFieldToTab("Root.APES", new TextField("GetResponseCampaign", 'GetResponse Campaign Name'));
 		
 		//campaign id
 		$campaignid = new TextField("GetResponseCampaignID",'Stored Campaign ID');
		$campaignid = $campaignid->transform(new ReadonlyTransformation());		
		$fields->addFieldToTab('Root.APES',$campaignid);
 		
 		
 		//is it installed?
 		$installed = new CheckboxField("GetResponseInstalled",'Is GetResponse Installed?');
		$installed = $installed->transform(new ReadonlyTransformation());
		$fields->addFieldToTab('Root.APES',$installed);
 			
 }

 

 	//use the ping method to see if we can reach the API
	public function onBeforeWrite(){
		$siteconfig = $this->owner;
		
		$api = new GetResponseAPI($siteconfig->GetResponseApiKey);
		
		
		if($api->ping() == "pong"){
			
			//marks as being installed
			$siteconfig->GetResponseInstalled = true;
			
			//set the campaign ID
			$siteconfig->GetResponseCampaignID = key( (array)$api->getCampaigns('EQUALS',$siteconfig->GetResponseCampaign) );
			
			} else {
			//could not reach the API mark as not installed
			$siteconfig->GetResponseInstalled = false;
			}
			
		
				
		
	}
	
 	/**
 	 * no need to pre-setup the custom fields, GetResponse does thison the fly during the addContact call.
 	 */
	public function onAfterWrite(){
 	
 				}
}