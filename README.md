# Automated Provision for Email Services (APES) Module

The APES module will allow you to set up an automated sync mechanism between the Member object and a 3rd party mail service such as MailChimp.

When a Member logs in and changes details about themselves (object properties attached to the Member object) these will be pushed to the 3rd party mail service to make sure when performing mail-outs you always have the most up to date information to run segmentation on.

## Maintainer Contacts
*  Cam Findlay (<cam@camfindlay.com>)
*  Shea Dawson (<shea@livesource.co.nz>)

## Requirements
*  SilverStripe 2.4+.
*  API Keys for mail services (At present a MailChimp API Key and List ID (http://mailchimp.com) )

## Project Links
*  [GitHub Project Page](https://github.com/cam-findlay/apes)
*  [Issue Tracker](https://github.com/cam-findlay/apes/issues)

##Usage

Make sure to name your module folder "apes" (all lowercase).

APES automates the process of setting up custom variables stored in third party email services and makes use of their APIs

You can choose which Member object properties are sync'd even if you have decorated your Member object with additional information i.e. address, phone numbers etc

###Config
In your '/mysite/_config.php'

Add the following to set up the properties of your Member you wish to have pushed to your mail service.
FirstName, Surname and Email are automatically included, no need to add these.


<code php>
//APES - Mailchimp
Object::add_extension('Member', 'MailChimp');
Object::add_extension('SiteConfig', 'MailChimpSiteConfig');
APES::setSyncFields(array('Birthday','Interests'));
</code>

###Setting the API connection
Once the initial config has been set up you will now have a tab named with your mail service in your SiteConfig e.g. MailChimp

Add your API key or any other credentials required, Save and the APES module will check with the third party mail service, and add in the extra custom fields to your mail list if they haven't already been added.


##Extra Mail List Tools
There is a basic MailChimp Sign Up form page type included in this module to allow for email list sign up outside of the Members object, to collect newsletter sign ups for instance.
It pulls the API key and list ID direct from the APES SiteConfig but does not affect the Member object.
Please add a similar function if you fork and add new email services to the APES module :)


##TODO
*  Add a bunch more service providers and make the whole thing less MailChimp centric.
*  Add the ability to set which email service you want in the SiteConfig.
*  Detect the type of object property is being set up on install and create the correct custom variable type in the 3rd party service. Example if you are trying to sync a Birthday, on install it will set the 3rd party service's custom field to a 'date' type, currently only creates 'text' type.