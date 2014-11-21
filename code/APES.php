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
    private static $sync_member_fields = array('FNAME', 'Email');
    private static $db = array(
        'MailChimpID' => 'Varchar'
    );

    public static function getSyncFields() {
        $siteconfig = SiteConfig::current_site_config();
        if (self::$sync_member_fields <> null && is_array(self::$sync_member_fields)) {
            $array = self::$sync_member_fields;
        } else {
            $array = explode(',', $siteconfig->APESSyncFields);
        }
        if (empty($array[0])) {
            return false;
        } else {
            return $array;
        }
    }

}
