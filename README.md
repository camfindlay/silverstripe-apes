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

##Config

### Step 1 - Mail Service Specific
In the future, APES will have multiple email services to choose from, each service will require you to extend your Member and SiteConfig in the /mysites/_config.php file. Below is a working example for the current MailChimp mail service.
Note: you must include a service specific extension in order for APES to work.

<code>
Object::add_extension('Member', 'MailChimp');
</code>
<code>
Object::add_extension('SiteConfig', 'MailChimpSiteConfig');
</code>


Once you have your mail service set, you can add API key information in the SiteConfig.

By default if you do not specify any Sync Fields your Member's FirstName, Surname and Email fields will Sync with the mail service.

You have 2 options for setting Sync Fields, Hardcoded or SiteConfig

### Step 2a - Sync Fields - Hardcoded

The hardcoded method of Sync Fields sets an array in your '/mysite/_config.php'.
This is handy if you intend to create a site in which you would rather the end user cannot change the Sync Fields you have set up.
Note: FirstName, Surname and Email are automatically included, no need to add these.

<code>
APES::setSyncFields(array('Birthday','Age','CustomField'));
</code>

APES will automatically detect the data type of the Sync Field and create the appropriate custom fields in your mail service once you have added your API key etc and saved the SiteConfig.

###Step 2b - SiteConfig
The alternative way of setting up Sync Fields in APES is to add the following to your /mysite/_config.php file:

<code php>
APES::setSyncFields('SiteConfig');
</code>

This will move the setting up of the Sync Fields to the APES tab in your SiteConfig in the CMS panel.

You will need to supply a comma separated list of the Sync Fields you want setup and save the SiteConfig.

This is handy if you are running your own site or have a client that wants to on occasion change the Sync Fields that are being pushed into your mail service.

You also have the option to turn on "Double Opt In" if you use the SiteConfig method so your Member has to confirm that they wish to be part of your mail list (Setting up the emails and confirmation pages are mail service specific and beyond the scope of this documentation, I can however post a how to on request if you would like to get in touch via email (<cam@camfindlay.com>) ).



###Setting the API connection
Once the initial config has been set up you will now have a tab named with your mail service in your SiteConfig e.g. APES

Add your API key or any other credentials required, Save and the APES module will check with the third party mail service, and add in the extra custom fields to your mail list if they haven't already been added.


##Extra Mail List Tools
There is a basic MailChimp Sign Up form page type included in this module to allow for email list sign up outside of the Members object, to collect newsletter sign ups for instance.
It pulls the API key and list ID direct from the APES SiteConfig but does not affect the Member object.
Please add a similar function if you fork and add new email services to the APES module :)


##TODO
*  Add a bunch more service providers and make the whole thing less MailChimp centric.
*  Continue to tidy and comment code.
*  Double Opt In setting for people using the hardcoded method.