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
class MailChimp extends DataExtension {

    public static $db = array(
        'MailChimpID' => 'Varchar()',
    );

    public function updateCMSFields(FieldList $fields) {
        
    }

    public function updateFrontEndFields(FieldList $fields) {
        
    }

    public function onBeforeWrite() {
        $changed = $this->owner->getChangedFields();
        //only do this if actual data has changed, not just LastEdited etc
        if (count($changed) > 3) {
            $siteconfig = SiteConfig::current_site_config();

            if ($siteconfig->MailchimpInstalled) {

                $api = new MCAPI($siteconfig->MailchimpApiKey);
                $merge_vars = array(
                    'FNAME' => $this->owner->FirstName,
                    'LNAME' => $this->owner->Surname,
                );

                //setup the fields to sync if they have been entered in to SiteConfig
                /*
                  if (APES::getSyncFields()) {
                  $fields = APES::getSyncFields();
                  foreach ($fields as $field) {
                  $field = trim($field);
                  $tag = strtoupper(substr($field, 0, 8));
                  $merge_vars[$tag] = $this->owner->$field;
                  }
                  } */

                //make sure the member did't unsubscribe 
                if (!$this->isUnsubscribed($this->owner->Email)) {
                    $doubleoptin = ($siteconfig->APESDoubleOptIn) ? true : false;

                    //add them or update them in the list.
                    $api->listSubscribe($siteconfig->MailchimpListId, $this->owner->Email, $merge_vars, 'html', $doubleoptin, true, true, false);

                    //return the unique MC id for this member - may look at using this later to do 2 way sync.
                    $memberinfo = $api->listMemberInfo($siteconfig->MailchimpListId, $this->owner->Email);
                    $this->owner->MailChimpID = $memberinfo['id'];
                }
            }
        }
    }

    public function onAfterWrite() {
        
    }

    public function onBeforeDelete() {
        //remove the member from mailchimp if they ask to be deleted
        $siteconfig = SiteConfig::current_site_config();
        $api = new MCAPI($siteconfig->MailchimpApiKey);
        $api->listUnsubscribe($siteconfig->MailchimpListId, $this->owner->Email);
    }

    public function onAfterDelete() {
        
    }

    /**
     * Checks to see if a given user has already unsubscribed from the list we want to add them to... if they have DO NOT re-subscribe them!
     */
    protected function isUnsubscribed($email_address) {
        $siteconfig = SiteConfig::current_site_config();
        $api = new MCAPI($siteconfig->MailchimpApiKey);
        $info = $api->listMemberInfo($siteconfig->MailchimpListId, $email_address);

        //now check the status variable.
        if ($info['status'] == "unsubscribed") {
            return true;
        } else {
            return false;
        }
    }

}
