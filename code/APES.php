<?php
/**
 * Automated Provision for Email Services (APES)
 * 
 * Set the fields on the member that should be sync'd up with mailchimp
 *
 * PHP version 5
 *	
 * @package    apes
 * @author     Cam Findlay <cam@camfindlay.com>
 * @copyright  2011 Cam Findlay Consulting
 * @version    SVN: $Id$      
 */
class APES extends DataObjectDecorator {

	

   
   public static $syncFields = array();
   
   
    
   
   
   
   	/**
     * Sets the sync fields either with hardcoded array or sets up SiteConfig to accept comma separated list
     */      
   public static function setSyncFields($fields){
   
   		switch($fields) {
			
			case 'SiteConfig':
				self::$syncFields = null;
				Object::add_extension('SiteConfig', 'APESConfig'); 
				break;
			default: self::$syncFields = $fields;
		}

   
   		
     
   		}
   
   /**
   	 * Returns either the hardcoded or SiteCOnfig set SyncFields 
   	 * @return array the fields to be sync'd
   	 */	
   public static function getSyncFields(){
   $siteconfig = SiteConfig::current_site_config();
   if(self::$syncFields <> null && is_array(self::$syncFields)){
   		$array = self::$syncFields;  
   		} else {
   		$array = explode(',',$siteconfig->APESSyncFields);		
  		}
  		
  		if(empty($array[0])){
  			return false;
  			}else{
  			return $array;
  			}	
   
   
   } 







	   
}