<?php

class MCSubscriptionPage extends Page{

}

class MCSubscriptionPage_Controller extends Page_Controller{
	
	static $allowed_actions = array(
		'SubscribeForm'
	);
	
	public function SubscribeForm()
	{	 	
        $fields = new FieldSet(
            new TextField('Name'),
            new TextField('Email')
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
     	$name = $data['Name'];
     	
     	$siteconfig = SiteConfig::current_site_config();
       	$api = new MCAPI($siteconfig->MailchimpApiKey);
       	
       	$merge_vars = array(
			'FNAME'=>$name
		);
			
       	if($api->listSubscribe(
       		$siteconfig->MailchimpListId, 
       		$email, 
       		$merge_vars, 
       		'html', 
       		false, true, true, false)
       	){
       		$this->setMessage('Success', "Thank you $name, you are now subscribed. Chur!");
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



	
