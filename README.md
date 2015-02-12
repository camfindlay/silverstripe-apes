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

# License
Copyright (c) 2015, Cam Findlay
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.

2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

3. Neither the name of the copyright holder nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
