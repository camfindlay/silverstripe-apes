# Automated Provision for Email Services (APES) Module

The APES module will allow you to set up an automated sync mechanism between the Member
object and a 3rd party mail service such as MailChimp.

When a Member logs in and changes details about themselves (object properties attached
to the Member object) these will be pushed to the 3rd party mail service to make sure
when performing mail-outs you always have the most up to date information to run segmentation on.

## Maintainer Contacts

* Cam Findlay (<cam@camfindlay.com>)
* Shea Dawson (<shea@livesource.co.nz>)

## Requirements

* SilverStripe 3.1
* Mailchimp PHP API Wrapper 2.0
* API credentials (MailChimp API Key and List ID)

## Project Links

* [GitHub Project Page](https://github.com/camfindlay/apes)
* [Issue Tracker](https://github.com/camfindlay/apes/issues)

##Usage

Install with composer

`composer require camfindlay/apes 1.0.x-dev`

Setup the following in your _ss_environment.php

```php
define('SS_MAILCHIMP_API_KEY', 'abcabcabcabcabcabc-ab5');
define('SS_MAILCHIMP_LIST_ID', 'a3298473984');
```

APES automates the process of setting up custom variables stored in third party email
services and makes use of their APIs

You can choose which Member object properties are sync'd even if you have decorated your
Member object with additional information i.e. address, phone numbers etc
