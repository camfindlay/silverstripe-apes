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
 * @uses	   Member
 */
class APES extends DataObjectDecorator {
  
   
   public static $syncFields = array(

   );
      
   
   public static function setSyncFields($fields){
   
   		self::$syncFields = $fields;
     
   		}
   
   
   
}