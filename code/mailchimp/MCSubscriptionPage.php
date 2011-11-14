<?php
/**
 * MailChimp Sign Up Form
 * 
 * Quick and dirty implementation of a mailchimp sign up for outside of the APES system but providing a useful form to create leads.
 *
 * PHP version 5
 *	
 * @package    apes
 * @author     Shea Dawson <shea@livesource.co.nz>
 * @author     Cam Findlay <cam@camfindlay.com>
 * @copyright  2011 Cam Findlay Consulting
 * @version    SVN: $Id$      
 * @uses	   MCAPI
 */
class MCSubscriptionPage extends Page{

}

class MCSubscriptionPage_Controller extends Page_Controller{
	
	static $allowed_actions = array(
		'SubscribeForm'
	);
	
	public function SubscribeForm()
	{	 	
        $fields = new FieldSet(
            new TextField('FirstName','First Name'),
            new TextField('Surname','Last Name'),
            new TextField('Email','Email Address')
        );
         
        $actions = new FieldSet(
            new FormAction('doSubscribe', 'Submit')
        );
        
        $validator = new RequiredFields(array(
			'Name', 'Email'	
		));
     
        return new Form($this, 'SubscribeForm', $fields, $actions, $validator);
    }


    function doSubscribe($data, $form) 
    {
     	$email = $data['Email'];
     	$fname = $data['FirstName'];
     	$lname = $data['Surname'];
     	
     	$siteconfig = SiteConfig::current_site_config();
       	$api = new MCAPI($siteconfig->MailchimpApiKey);
       	
       	$merge_vars = array(
			'FNAME'=>$fname,
			'LNAME'=>$lname
		);
			
       	if($api->listSubscribe(
       		$siteconfig->MailchimpListId, 
       		$email, 
       		$merge_vars, 
       		'html', 
       		false, true, true, false)
       	){
       		$this->setMessage('Success', "Thank you $fname, you are now subscribed.");
       		$this->redirectBack();
       	}else{
       		$this->setMessage('Error', $api->errorMessage);
			$this->redirectBack();
       	}	
    }
    
    
    public function setMessage($type, $message)
	{	
		Session::set('Message', array(
			'MessageType' => $type,
			'Message' => $message
		));
	}
	
	
	public function getMessage()
	{
		if($message = Session::get('Message')){
			Session::clear('Message');
			$array = new ArrayData($message);
			return $array->renderWith('Message');
		}
	}
}



	
