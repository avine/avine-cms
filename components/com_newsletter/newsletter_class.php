<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



/////////
// Class

class comNewsletter_tmpl
{

	// General keywords for all parts of the template
	static public function generalsKeywords()
	{
		$general =
			array(
				'site_url',
				'site_name',
				'newsletter_url',
				'unsubscribe'
			);

		return $general;
	}



	static public function generalsReplacements( $tmpl_html, $newsletter_id = false, $replacements_addons = array() )
	{
		// Protocol
		global $g_protocol;

		// Site infos
		comConfig_getInfos($site_name, $system_email);

		// Newsletter url (when available)
		if ($newsletter_id)
		{
			$newsletter_url = siteUrl()."/components/com_newsletter/online.php?id=$newsletter_id";
		} else {
			$newsletter_url = '';
		}

		// Fill replacements
		$replacements =
			array(
				'site_url'			=>	siteUrl(),
				'site_name'			=>	$site_name,
				'newsletter_url'	=>	$newsletter_url,
				'unsubscribe'		=>	comMenu_rewrite('com=newsletter&amp;page=unsubscribe', true, $g_protocol)
			);

		// Addons
		$replacements = array_merge($replacements, $replacements_addons);

		// Check compatibility
		if (array_diff(self::generalsKeywords(), array_keys($replacements))) {
			trigger_error('In the array <b>$replacements</b>, the keys do not match the values in the array returned by the <b>self::generalsKeywords()</b> method.');
		}

		// Process template
		$template = new templateManager();
		return $template->setTmplHtml($tmpl_html)->setReplacements($replacements)->process();
	}



	// Specific keywords to each part of the template
	static public function keywords()
	{
		$keywords['header'	] = array('subject', 'date');
		$keywords['footer'	] = array('user_track', 'date');
		$keywords['item'	] = array('title', 'title_alias', 'text_intro', 'image_thumb', 'element_link');

		// Add generals keywords
		$general = self::generalsKeywords();
		reset($keywords);
		foreach ($keywords as $k => $v) {
			$keywords[$k] = array_merge($keywords[$k], $general);
		}

		return $keywords;
	}



	/**
	 * @param int $newsletter_id 'id' of the 'newsletter' table
	 * @param int $send_id 'id' of the 'newsletter_send' table (or 0 if unknown)
	 */
	static public function getMessage( $newsletter_id, $send_id = 0 )
	{
		global $db;
		$newsletter = $db->selectOne("newsletter, *, where: id=$newsletter_id");

		if (!$newsletter) {
			return false;
		}

		// Newsletter template
		$tmpl = $db->selectOne('newsletter_tmpl, header,footer, where: id='.$newsletter['tmpl_id']);

		// Infos
		$site_url = siteUrl();
		comConfig_getInfos($site_name, $system_email);
		$time = mb_strtolower(getTime('', 'format=long;time=no'));

		// Replacements addons (check the self::keywords() function to list them)
		$rpl_addons_header = array(
			'subject'		=>	"$site_name : ".$newsletter['subject'],
			'date'			=>	$time
		);
		$rpl_addons_footer = array(
			'user_track'	=>	$send_id ? "<div><img src=\"$site_url/components/com_newsletter/ut.php?id=$send_id\" alt=\"\" /></div>" : '',
			'date'			=>	$time
		);

		// Generals replacements
		$tmpl['header'] = self::generalsReplacements($tmpl['header'], $newsletter_id, $rpl_addons_header);
		$tmpl['footer'] = self::generalsReplacements($tmpl['footer'], $newsletter_id, $rpl_addons_footer);
		$newsletter['message'] = self::generalsReplacements($newsletter['message'], $newsletter_id);

		return $tmpl['header'].$newsletter['message'].$tmpl['footer'];
	}

}



/////////
// Class

class comNewsletter
{
	const	FORM_ID_	= 'newsletter_subscribe_',
			INPUT_NAME	= 'email';



	public function __construct()
	{
		// Resolve conflicts
		self::deletedUserConflict();
		self::subscriberToUserConflict();
	}



	/**
	 * Remove registered-user subscription wich his associated user account :
	 * 
	 * Delete a "registered-user subscription" whose user account :
	 *		- was deleted
	 *		- or has no longer an email
	 *
	 * Known issue :
	 * If duplicate-email is allowed, and someone has many user-accounts with the same email, then the Newsletter subscription is associated with only one of these accounts.
	 * Because of that, if this account is deleted (or it's email have changed), then the Newsletter is no longer sent to the other accounts !
	 */
	static private function deletedUserConflict()
	{
		global $db;

		// Select "users" who have an email
		$sub_query = " SELECT id FROM {table_prefix}user WHERE email != '' ";

		// Select "registered-users subscribers" who do not fulfill the above $sub_query
		$subscriber_conflict =
			$db->fetchMysqlResults(
				$db->sendMysqlQuery(
					"SELECT id FROM {table_prefix}newsletter_subscriber WHERE user_id IS NOT NULL AND user_id NOT IN ($sub_query)"
				)
			);

		// Delete invalid subscribers
		for ($i=0; $i<count($subscriber_conflict); $i++)
		{
			$db->delete("newsletter_subscriber; where: id=".$subscriber_conflict[$i]['id']);
		}
	}



	/**
	 * Search for potential subscriber who became a user
	 *
	 * After this task, the table 'newsletter_subscriber' contains the following information :
	 *		- "anonymous subscription" is defined by the 'email' field
	 *		- "registered-user subscription" is defined by the 'user_id' field
	 */
	static private function subscriberToUserConflict()
	{
		global $db;

		$emails_processed = array();

		$subscriber_conflict =
			$db->select(
				'newsletter_subscriber, id, email, where: email IS NOT NULL, join: email>; '.	# Here the anonymous
				'user, id AS user_id(desc), join: <email'										# Here the registered-users
			);

		for ($i=0; $i<count($subscriber_conflict); $i++)
		{
			if (!in_array($subscriber_conflict[$i]['email'], $emails_processed))
			{
				$id			= $subscriber_conflict[$i]['id'		];
				$user_id	= $subscriber_conflict[$i]['user_id'];

				// Make this anonymous subsciption a registered-user subscription
				$db->update("newsletter_subscriber; email=NULL, user_id=$user_id; where: id=$id");

				/*
				 * Notice : if duplicate-email is allowed, and someone has many user-accounts with this email,
				 * then associate the subscription to the most recent user account (biggest user_id) !
				 */
				$emails_processed[] = $subscriber_conflict[$i]['email'];
			}
		}
	}



	/**
	 * Anonymous subscription
	 *
	 * Restricted search : in the 'email' field of the 'newsletter_subscriber' table
	 */
	public function subscribedByEmail( $email )
	{
		global $db;
		if ($subscriber = $db->selectOne('newsletter_subscriber, activated, where: email='.$db->str_encode($email)))
		{
			if ($subscriber['activated']) {
				return true;	# Subscription activated !
			} else {
				return false;	# Subscription not activated !
			}
		}
		return NULL;			# No such subscription !
	}



	/**
	 * Registered-user subscription
	 *
	 * Restricted search : in the 'user_id' field of the 'newsletter_subscriber' table
	 */
	public function subscribedByUserID( $user_id )
	{
		global $db;
		if ($subscriber = $db->selectOne("newsletter_subscriber, activated, where: user_id=$user_id"))
		{
			if ($subscriber['activated']) {
				return true;	# Subscription activated !
			} else {
				return false;	# Subscription not activated !
			}
		}
		return NULL;			# No such subscription !
	}



	/**
	 * Check an 'email', and determine if the newsletter is sent to it !
	 *
	 * Full search : in both 'email' and 'user_id' fields of the 'newsletter_subscriber' table
	 */
	public function isSentToEmail( $email, &$status )
	{
		// Search an anonymous subscription
		$subscribed = $this->subscribedByEmail($email);
		if ($subscribed !== NULL)
		{
			$status = array(
				'subscription'	=> 'anonymous',
				'user_id'		=> false
			);
			return $subscribed;
		}

		// Search a registered-user subscription
		global $db;
		if ($user = $db->select('user, id(asc), where: email='.$db->str_encode($email)))
		{
			/*
			 * Notice :
			 *
			 * If duplicate-email is allowed, it's possible to find more than one user...
			 * So, many 'user_id' can have the same email !
			 *
			 * But, there's something we can be sure about :
			 * Only one of them is recorded into the 'newsletter_subscriber' table.
			 */
			for ($i=0; $i<count($user); $i++)
			{
				$subscribed = $this->subscribedByUserID($user[$i]['id']);
				if ($subscribed !== NULL)
				{
					$status = array(
						'subscription'	=> 'registered',
						'user_id'		=> $user[$i]['id']
					);
					return $subscribed;
				}
			}
		}

		// No subscription for this email at all !
		$status = array(
			'subscription'	=> false,
			'user_id'		=> false
		);
		return NULL;
	}



	/**
	 * Check a 'user', and determine if the newsletter is sent to him !
	 *
	 * How it works ? If this user have an email, then perhaps him or another user with the same email have a subscription !
	 */
	public function isSentToUser( $user_id, &$exact_subscriber )
	{
		global $db;

		if ($email = $db->selectOne("user, email, where: id=$user_id", 'email'))
		{
			if ($subscribed = $this->isSentToEmail($email, $status))
			{
				// Is this exactly the 'user_id' wich is recorded into the 'newsletter_subscriber' table ?
				$exact_subscriber = $this->subscribedByUserID($user_id);
			}
			return $subscribed;
		}

		return NULL;
	}



	/**
	 * Get the email of a subscriber
	 *
	 * @param int $subscriber_id 'id' field of the 'newsletter_subscriber' table
	 */
	public function subscriberEmail( $subscriber_id )
	{
		global $db;

		if ($subscriber = $db->selectOne("newsletter_subscriber, email, user_id, where: id=$subscriber_id"))
		{
			if ($subscriber['email'])
			{
				return $subscriber['email'];
			}
			elseif ($email = $db->selectOne("user, email, where: id=".$subscriber['user_id'], 'email'))
			{
				return $email;
			}
		}

		return false;
	}

}



?>