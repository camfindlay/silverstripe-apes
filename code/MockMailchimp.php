<?php
/*
 * MockMailchimp
 * 
 * Use to mock the responses from Mailchimp when running unit tests
 */
class MockMailchimp {
    
    
    public function __construct(){

        $this->lists = $this;

    }


    public function subscribe($id, $email, $merge_vars=null, $email_type='html', $double_optin=true, $update_existing=false, $replace_interests=true, $send_welcome=false){
            return array('email' => $email, 'euid' => '12345', 'leid' => '67890');
    }

    public function memberInfo(){}
    
}