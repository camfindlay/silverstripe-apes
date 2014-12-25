<?php

/**
 * Automated Provision for Email Services (APES)
 * 
 * When provided credentials and an array of Member fields to sync this will keep SilverStripe and
 * Mailchimp in sync if a user has opted in to recevie emails.
 *
 * @package    apes
 * @author     Cam Findlay <cam@silverstripe.com>
 */
class APES extends DataExtension {

	/**
	 * Unsubscribe users on delete?
	 *
	 * @var boolean
	 * @config
	 */
	private static $mailchimp_unsubscribe_on_delete = false;

	/**
	 * Require double-opt in for users (check email)
	 *
	 * @var type
	 */
	private static $double_optin = true;

	/**
	 * Mailchimp list ID
	 *
	 * Override by defining SS_MAILCHIMP_LIST_ID
	 *
	 * @var string
	 * @config
	 */
	private static $mailchimp_list_id;

	/**
	 * Field used for mailchimp options
	 *
	 * @var string
	 * @config
	 */
	private static $mailchimp_form_prefix = 'MailChimp';

	/**
	 * Get the list ID to use
	 *
	 * @return string
	 */
	public static function get_mailchimp_list_id() {
		if(defined('SS_MAILCHIMP_LIST_ID')) return SS_MAILCHIMP_LIST_ID;
		return $this->owner->config()->mailchimp_list_id;
	}

	/**
	 * Lis of member fields to synchronise
	 *
	 * Map of local field to mailchimp field
	 *
	 * @var array
	 * @config
	 */
	private static $sync_member_fields = array(
		'FirstName' => 'FNAME',
		'Surname' => 'LNAME',
		'Email' => 'EMAIL'
	);
	
	private static $db = array(
		'MailchimpID' => 'Varchar'
	);

	public function updateCMSFields(FieldList $fields) {
		$fields->addFieldsToTab(
			'Root.Mailchimp',
			array(
				ReadonlyField::create('MailchimpID', 'Mailchimp ID'),
				ReadonlyField::create('ListStatus',  'List Status')
			)
		);
	}

	/**
	 * Gets an array of data identifying this user with mailchimp
	 *
	 * @return array
	 */
	public function getMailchimpRef() {
		if($this->owner->MailchimpID) {
			return array('leid' => $this->owner->MailchimpID);
		} else {
			return array('email' => $this->owner->Email);
		}
	}

	/**
	 * Get selected groups for this user in a format suitable for sending to list->updateSubscriber
	 *
	 * @return array
	 */
	public function getMailChimpUserGroupings() {
		// Unsubscribed users are treated as completely unsubscribed
		if($this->isUnsubscribed()) return array();

		// Map synchronised fields to mailchimp tags, ensuring to retrieve full profile
		// prior to safely merge updated fields with existing ones
		$memberInformation = $this->getMailChimpInformation();
		if(empty($memberInformation['merges']['GROUPINGS'])) return array();

		// Map get group format to set group format
		$userGroupings = array();
		foreach($memberInformation['merges']['GROUPINGS'] as $groupings) {
			$selectedOptions = array();
			foreach($groupings['groups'] as $option) {
				if($option['interested']) $selectedOptions[] = $option['name'];
			}
			$userGroupings[] = array(
				'id' => $groupings['id'],
				'name' => $groupings['name'],
				'groups' => $selectedOptions
			);
		}
		return $userGroupings;
	}

	/**
	 * Get selected options for a given group ID
	 *
	 * @param int $id Group ID to get options for
	 * @return array
	 */
	public function getMailChimpUserGroupingOptions($id) {
		// Find member data for this group
		$memberData = $this->getMailChimpUserGroupings();
		foreach($memberData as $grouping) {
			if($grouping['id'] == $id) return $grouping['groups'];
		}
		return array();
	}

	/**
	 * Update this user on mailchimp
	 *
	 * @param array $groups Specify group data for merge, or null to leave unchanged
	 */
	public function subscribeMailChimpUser($groups = null) {
		// Map synchronised fields to mailchimp tags, ensuring to retrieve full profile
		// prior to safely merge updated fields with existing ones
		$memberInformation = $this->getMailChimpInformation();
		$mergeTags = $memberInformation && isset($memberInformation['merges'])
			? $memberInformation['merges']
			: array();
		foreach ($this->owner->config()->sync_member_fields as $field => $tag) {
			$mergeTags[$tag] = $this->owner->$field;
		}

		// Map get group format to set group format
		$mergeTags['groupings'] = isset($groups)
			? $groups
			: $this->getMailChimpUserGroupings();
		unset($mergeTags['GROUPINGS']);

		//@todo add error handling
		// Subscribe this user
		try {
			$api = SS_Mailchimp::instance();
			$subscribe = $api->lists->subscribe(
				static::get_mailchimp_list_id(),
				$this->getMailchimpRef(),
				$mergeTags,
				'html',
				$this->owner->config()->double_optin,
				true, // update
				true, // replace interests
				false // dont send welcome
			);

			// Store the unique Mailchimp ID for this member if user doesn't already have one
			//@todo look at using this later to do 2 way sync
			if (!$this->owner->MailchimpID) {
				$this->owner->MailchimpID = $subscribe['leid'];
			}
		} catch (Mailchimp_Error $exception) {
			SS_Log::log($exception, SS_Log::ERR);
		}
	}

	/**
	 * Unsubscribe this user from mailchimp
	 */
	public function unsubscribeMailChimpUser() {
		try {
			$api = SS_Mailchimp::instance();
			$api->lists->unsubscribe(
				static::get_mailchimp_list_id(),
				$this->getMailchimpRef()
			);
		} catch (Mailchimp_Error $exception) {
			SS_Log::log($exception, SS_Log::ERR);
		}
	}

	public function onBeforeWrite() {
		
		// Only update ID if this record has been changed
		$changed = $this->owner->getChangedFields();
		if (empty($changed) && $this->owner->exists()) {
			return;
		}

		// Only update members which are subscribed (and not pending) or new.
		if($this->isSubscribed() || !$this->getListStatus() || !$this->isPending()){
			// Send subscription update to mailchimp
			$this->subscribeMailChimpUser();
		}
		
	}

	public function onBeforeDelete() {
		// Unsubscribe this user before deleting
		if ($this->owner->config()->mailchimp_unsubscribe_on_delete && $this->isSubscribed()) {
			$this->unsubscribeMailChimpUser();
		}
	}

	/**
	 * Determine the subscription status for this user
	 *
	 * @return string|false The list status, or false if not on the list
	 */
	public function getListStatus() {
		$data = $this->getMailChimpInformation();
		if($data) return $data['status'];
		else return false;
	}

	/**
	 * Return true if this user is on a list, and happily subscribed
	 *
	 * @return boolean
	 */
	public function isSubscribed() {
		return $this->getListStatus() === 'subscribed';
	}

	public function isPending() {
		return $this->getListStatus() === 'pending';
	}

	/**
	 * Checks to see if a given user has already unsubscribed from the list we want to add them to...
	 * If they have DO NOT re-subscribe them!
	 *
	 * @return boolean
	 */
	protected function isUnsubscribed() {
		return $this->getListStatus() === 'unsubscribed';
	}

	/**
	 * Gets group data for this list
	 *
	 * @return array
	 */
	public function getMailchimpListGroupings() {
		// Return cached value
		if(isset($this->owner->mcgroupdata)) return $this->owner->mcgroupdata;

		// Request from API
		try {
			$groupData = SS_Mailchimp::instance()
				->lists
				->interestGroupings(static::get_mailchimp_list_id());
		} catch (Mailchimp_Error $exception) {
			SS_Log::log($exception, SS_Log::ERR);
			return array();
		}
		// Sort data
		return $this->owner->mcgroupdata = $this->sortData($groupData);
	}

	/**
	 * Get data on this user's subscription status, or false if not subscribed
	 *
	 * @return array|false
	 */
	public function getMailChimpInformation() {
		// Skip singletons
		if(empty($this->owner->Email)) return false;

		// Return cached value
		if(isset($this->owner->mcdata)) return $this->owner->mcdata;

		try {
			$api = SS_Mailchimp::instance();
			$result = $api->lists->memberInfo(
				static::get_mailchimp_list_id(),
				array(
					$this->getMailchimpRef()
				)
			);
		} catch (Mailchimp_Error $exception) {
			SS_Log::log($exception, SS_Log::ERR);
			return $this->owner->mcdata = false;
		}

		// If not on list, not subscribed
		if($result && $result['success_count'] > 0) {

			// If on lit, check the status variable.
			foreach($result['data'] as $memberData) {
				return $this->owner->mcdata = $memberData;
			}
		}

		// Non-member
		return $this->owner->mcdata = false;
	}

	/**
	 * Helper function for sorting mailchimp standard lists
	 *
	 * @param array $data
	 * @return array
	 */
	protected function sortData($data) {
		usort($data, function($left, $right) {
			return $left['display_order'] - $right['display_order'];
		});
		return $data;
	}

	/**
	 * Gets the list of groups, and their subscriptions
	 *
	 * @link https://apidocs.mailchimp.com/api/1.2/listinterestgroups.func.php
	 *
	 * @return array List of groups as an associative array, each item containing the following keys:
	 * <ul>
	 * <li>id - ID of this group</li>
	 * <li>name - Name of this group</li>
	 * <li>type - One of checkboxes, radio, select, hidden</li>
	 * <li>options - List of options, each with 'id', 'name', and 'selected' fields</li>
	 * <li>selected - Helper list of selected option ids for this group</li>
	 * <li>values - Helper array mapping of id to option names</li>
	 * </dl>
	 */
	public function getMailChimpGroups() {
		$groups = array();
		$groupData = $this->getMailchimpListGroupings();
		foreach($groupData as $groupDataItem) {
			$groupID = $groupDataItem['id'];
			$selectedOptions = $this->getMailChimpUserGroupingOptions($groupID);

			// Save all options
			$options = array();
			$optionValues = array();
			$optionData = $this->sortData($groupDataItem['groups']);
			foreach($optionData as $optionDataItem) {
				// Add option
				$options[] = array(
					'id' => $optionDataItem['id'],
					'name' => $optionName = $optionDataItem['name'],
					'selected' => in_array($optionName, $selectedOptions)
				);
				$optionValues[$optionName] = $optionName;
			}

			// Build the group
			$groups[] = array(
				'id' => $groupID,
				'name' => $groupDataItem['name'],
				'type' => $groupDataItem['form_field'],
				'options' => $options,
				'selected' => $selectedOptions, // selected options
				'values' => $optionValues // all options
			);
		}
		return $groups;
	}

	/**
	 * Get the composite field necessary for generating the subscription options
	 *
	 * @return FormField
	 */
	public function getMailChimpField() {
		// Add subscription settings for mailchimp
		// Get list of available groups
		// @see APES::getAvailableGroups
		$prefix = $this->owner->config()->mailchimp_form_prefix;

		// Show notice if pending
		if($this->isPending()) {
			return LiteralField::create(
				"{$prefix}[Pending]",
				"<p class='mailchimp-pending'>Your subscription is currently pending email verification.<br />".
					"Please check your email and verify to confirm.</p>"
			);
		}

		$field = CompositeField::create(
			// Flag to indicate these fields should be processed by the response handler
			HiddenField::create("{$prefix}[Enabled]", false, 1)
		)->addExtraClass('mailchimp-options');

		// Generate fields for all groups
		$groups = $this->getMailChimpGroups();
		foreach($groups as $mailGroup) {
			$groupID = $mailGroup['id'];

			// Determine field to use
			if($mailGroup['type'] === 'checkboxes') {
				$field->push(
					CheckboxSetField::create(
						"{$prefix}[Groups][{$groupID}]",
						$mailGroup['name'],
						$mailGroup['values'],
						$mailGroup['selected']
					)->addExtraClass('mailchimp-option')
				);
			} else {
				// Not implemented
				user_error('Mailchimp group type '.$mailGroup['type'].' not implemented', E_USER_WARNING);
			}
		}

		// If there are no groups, replace all subscriptions with a single checkbox
		if(empty($groups)) {
			$field->push(
				CheckboxField::create(
					"{$prefix}[Subscribed]",
					"I wish to subscribe for email updates",
					$this->isSubscribed()
				)->addExtraClass('mailchimp-subscribe')
			);
		}

		return $field;
	}

	/**
	 * Update subscription details from a form containing the field generated by
	 * {@see APES::getSubscriptionFields()}
	 *
	 * @param SS_HTTPRequest $request
	 */
	public function updateMailChimpSubscription($request) {
		$prefix = $this->owner->config()->mailchimp_form_prefix;
		$postedData = $request->postVar($prefix);

		// Abort if no data from MailChimpField
		if(empty($postedData['Enabled'])) return;

		// Check if subscribing or not
		$subscribed = !empty($postedData['Subscribed']);

		// Generate list of groups
		$groups = array();
		$groupData = $this->getMailchimpListGroupings();
		foreach($groupData as $groupDataItem) {
			// Skip fully unselected groups
			if(empty($postedData['Groups'][$groupDataItem['id']])) continue;
			$subscribed = true;
			$groups[] = array(
				'id' => $groupDataItem['id'],
				'name' => $groupDataItem['name'],
				'groups' => array_values($postedData['Groups'][$groupDataItem['id']])
			);
		}

		if($subscribed) {
			$this->subscribeMailChimpUser($groups);
		} else {
			$this->unsubscribeMailChimpUser();
		}
	}

}
