<?php
/**
 * Automated Provision for Email Services (APES)
 * 
 * When provided credentials and an array of Member fields to sync this will keep SilverStripe and Mailchimp in sync if a user has opted in to recevie emails.
 *
 * @package    apes
 * @author     Cam Findlay <cam@silverstripe.com>
 */
class APES extends DataExtension {

  private static $mailchimp_api_key;

  private static $mailchimp_list_id;
  
  private static $sync_member_fields = array('FirstName', 'Email');

  private static $db = array(
      'MailChimpID'=>'Varchar'
  );
	   
}