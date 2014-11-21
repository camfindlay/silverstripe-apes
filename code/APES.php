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

    private static $sync_member_fields = array();

    private static $double_optin = true;

    private static $db = array(
        'MailchimpID' => 'Varchar'
    );

    public function updateCMSFields(FieldList $fields) {
        $fields->addFieldToTab('Root.Mailchimp', ReadonlyField::create('MailchimpID'));
    }

    public function onBeforeWrite() {
        //don't ping the API is the logged in user is an ADMIN
        //if(Permission::check('ADMIN')){
          //  return false;
       // }

        $changed = $this->owner->getChangedFields();
        
        //only do this if actual data has changed, not just LastEdited etc
        //@todo test to see if adding a new rego from the CMS will trigger this?
        //@todo if email has changed add this to the API call for changed email in MCAPI2
        //if (count($changed) > 3) {

            try {
                $this->mc = new Mailchimp(Config::inst()->get('APES','mailchimp_api_key'));
            } catch (Mailchimp_Error $e) {
                user_error($e->getMessage(), E_USER_ERROR);
            }

            //@todo make this a SS ArrayList or DataList
            $merge_tags = array(
                'FNAME' => $this->owner->FirstName,
                'LNAME' => $this->owner->Surname,
                'EMAIL' => $this->owner->Email
            );

            //@todo document how this works
            if (Config::inst()->get('APES','sync_member_fields')) {
                foreach ($fields as $field) {
                  $field = trim($field);
                  $tag = strtoupper(substr($field, 0, 8));
                  $merge_tags[$tag] = $this->owner->$field;
                }
            }

            //make sure the member did't unsubscribe 
            //if (!$this->isUnsubscribed($this->owner->Email)) {
                
                //add them or update them in the list. 
                //@todo add error handling
                //@todo if the member has a mailchimp id use this over the email address.
                $subscribe = $this->mc->lists->subscribe(
                    Config::inst()->get('APES','mailchimp_list_id'), 
                    array('email' => $this->owner->Email),
                    $merge_tags, 
                    'html', 
                    $double_optin, 
                    true, 
                    true, 
                    false
                );

                //return the unique Mailchimp ID for this member if user doesn't already have one
                //@todo look at using this later to do 2 way sync
                if(!$this->owner->MailchimpID){
                    
                    $this->owner->MailchimpID = $subscribe['leid']; 
                }
            //}
            
        //}
       // parent::onBeforeWrite();
    }

    public function onBeforeDelete() {
        //remove the member from mailchimp if they ask to be deleted
      //  $siteconfig = SiteConfig::current_site_config();
      //  $api = new MCAPI($siteconfig->MailchimpApiKey);
      //  $api->listUnsubscribe($siteconfig->MailchimpListId, $this->owner->Email);
    }


    /**
     * Checks to see if a given user has already unsubscribed from the list we want to add them to... if they have DO NOT re-subscribe them!
     */
    protected function isUnsubscribed($email_address) {
            $memberinfo = $this->mc->lists->memberInfo(
                            Config::inst()->get('APES','mailchimp_list_id'), 
                            $this->owner->Email
            );

        //now check the status variable.
        if ($memberinfo['status'] == 'unsubscribed') {
            return true;
        } else {
            return false;
        }
    }

}
