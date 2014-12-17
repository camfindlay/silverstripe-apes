<?php

class SS_Mailchimp extends Mailchimp {

	/**
	 * Mailchimp api key
	 *
	 * Override by defining SS_MAILCHIMP_API_KEY
	 *
	 * @var string
	 * @config
	 */
	private static $mailchimp_api_key;

	/**
	 * Get the API key to use
	 *
	 * @return string
	 */
	public static function get_mailchimp_api_key() {
		if(defined('SS_MAILCHIMP_API_KEY')) return SS_MAILCHIMP_API_KEY;
		return Config::inst()->get(__CLASS__, 'mailchimp_api_key');
	}

	/**
	 * Get the singleton of SS_Mailchimp api
	 *
	 * @return static
	 */
	public static function instance() {
		return Injector::inst()->get(
			__CLASS__,
			true,
			array(static::get_mailchimp_api_key())
		);
	}
	
	/**
	 * @var Mailchimp_Folders
	 */
	public $folders;

	/**
	 * @var Mailchimp_Templates
	 */
	public $templates;

	/**
	 * @var Mailchimp_Users
	 */
	public $users;

	/**
	 * @var Mailchimp_Helper
	 */
	public $helper;

	/**
	 * @var Mailchimp_Mobile
	 */
    public $mobile;

	/**
	 * @var Mailchimp_Conversations
	 */
    public $conversations;

	/**
	 * @var Mailchimp_Ecomm
	 */
    public $ecomm;
	
	/**
	 * @var Mailchimp_Neapolitan
	 */
    public $neapolitan;

	/**
	 * @var Mailchimp_Lists
	 */
    public $lists;

	/**
	 * @var Mailchimp_Campaigns
	 */
    public $campaigns;

	/**
	 * @var Mailchimp_Vip
	 */
    public $vip;

	/**
	 * @var Mailchimp_Reports
	 */
    public $reports;

	/**
	 * @var Mailchimp_Gallery
	 */
    public $gallery;

	/**
	 * @var Mailchimp_Goal
	 */
    public $goal;
}
