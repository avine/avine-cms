<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


# TODO : Upgrades
#	- permettre le choix du transport (SMTP, ...)



class emailManager
{
	public	$demo 				= false;

	private	$message_txt 		= '',
			$message_html 		= '';

	private $template_html		= '{message_html}';

	private	$subject 			= '',
			$to 				= array(),
			$from 				= '',
			$reply_to 			= '',
			$return_path		= '',
			$attachment			= array();

	private	$charset 			= 'utf-8';

	private	$tool 				= 'swift';

	private	$failed_recipients	= array();



	public function __construct( $demo = false )
	{
		$demo ? $this->demo = true : '';
	}



	public function useDefaultTemplate()
	{
		$this->setTemplateHTML('default/tmpl.html');

		return $this;
	}



	/**
	 * Set the message template.
	 * <br />The template file must contain the keyword {message_html}.
	 * 
	 * @param string $tmpl_name
	 * <br />Relative path to the template file
	 * <br />path base : /libraries/lib_email/tmpl/
	 */
	public function setTemplateHTML( $tmpl_name )
	{
		$ftp = new ftpManager(sitePath().'/libraries/lib_email/tmpl/');

		if ($template_html = $ftp->read($tmpl_name))
		{
			if (count(explode('{message_html}', $template_html)) == 2)
			{
				$this->template_html = $template_html; # This template seems to be ok !
			} else {
				trigger_error('Template file is missing the keyword : "{message_html}".');
			}
		}

		return $this;
	}



	public function addMessageTXT( $message_txt, $set_also_html = true )
	{
		// Text version
		$this->message_txt .= $message_txt;

		// Html version
		if ($set_also_html) {
			$this->message_html .= '<p>'.nl2br($message_txt).'</p>'; # Add breaks and put inside a paragraph
		}

		return $this;
	}



	public function addMessageHTML( $message_html, $set_also_txt = true )
	{
		// Html version
		$this->message_html .= $message_html;

		// Text version
		if ($set_also_txt) {
			$this->message_txt .= $this->HTMLtoTXT($message_html);
		}

		return $this;
	}



	public function HTMLtoTXT( $message_html )
	{
		// Keep <body>
		$try = preg_replace('~\<body((\s)(.*))?\>|\</body\>~i', '[[[BODY]]]', $message_html);
		$try = explode('[[[BODY]]]', $try);
		if (count($try) == 3) {
			$message_html = $try[1];
		}

		// Remove <style> (wich can be inside the <body>)
		$try = preg_replace('~\<style((\s)type\="text/css")?\>|\</style\>~i', '[[[STYLE]]]', $message_html);
		$try = explode('[[[STYLE]]]', $try);
		if (count($try) == 3) {
			$message_html = $try[0].$try[2];
		}

		// Strip tags and add linebreaks
		$br = "\r\n";
		$message_html = preg_replace('~[\s\t\n\r]+~'		, ' '				, $message_html); # Remove all controls
		$message_html = preg_replace('~(\</p\>)~i'			, '</p>'.$br.$br	, $message_html); # Add space between paragraphs
		$message_html = preg_replace('~(\<br\>|\<br /\>)~i'	, $br				, $message_html); # Add breaks
		$message_txt = strip_tags($message_html);

		return $message_txt;
	}



	public function addTo( $email, $name = '' )
	{
		if (formManager_filter::isEmail($email))
		{
			$to_number = count($this->to);

			$this->to[$to_number]['email'] = $email;

			$name ? $this->to[$to_number]['name'] = $name : '';
		} else {
			# Rejected recipient
		}
		return $this;
	}



	public function setSubject( $subject )
	{
		$this->subject = $subject;

		return $this;
	}



	public function setFrom( $email, $name = '' )
	{
		if (formManager_filter::isEmail($email))
		{
			$this->from['email'] = $email;

			$name ? $this->from['name'] = $name : '';
		} else {
			# Rejected recipient
		}
		return $this;
	}



	public function setReplyTo( $email )
	{
		if (formManager_filter::isEmail($email))
		{
			$this->reply_to = $email;
		} else {
			# Rejected recipient
		}
		return $this;
	}



	public function setReturnPath( $email )
	{
		if (formManager_filter::isEmail($email))
		{
			$this->return_path = $email;
		} else {
			# Rejected recipient
		}
		return $this;
	}



	public function addAttachment( $file )
	{
		if (is_file($file))
		{
			$this->attachment[] = $file;
		} else {
			# Rejected file
		}
		return $this;
	}



	public function setCharset( $charset )
	{
		$this->charset = $charset;

		return $this;
	}



	// Send email
	public function send()
	{
		if ($this->demo)
		{
			// To addresses
			$to = '';
			for ($i=0; $i < count($this->to); $i++)
			{
				if (isset($this->to[$i]['name']))
				{
					$to .= "{$this->to[$i]['name']} &lt;{$this->to[$i]['email']}&gt;; ";
				} else {
					$to .= $this->to[$i]['email'].'; ';
				}	
			}

			// From address
			if (isset($this->from['name']))
			{
				$from = "{$this->from['name']} &lt;{$this->from['email']}&gt;";
			} else {
				$from = $this->from['email'];
			}

			$demo  = '<div style="border:1px solid red;background-color:#FBE3E3;padding:1px;margin:15px 0;"><div style="background-color:red;color:white;font-weight:bold;text-align:center;">SEND MAIL - DEMO MODE</div><br />'."\n";
			$demo .= '<div style="margin:15px 8px;padding:3px 6px;background-color:#F8F8F8;border:1px solid #AAA;color:#333;"><strong>To :</strong> '.$to.'<br /><strong>Subject :</strong> '.$this->subject.'<br /><strong>From :</strong> '.$from.'</div>'."\n";
			$demo .= '<div style="margin:15px 8px;"><span style="font-weight:bold;color:red;">TXT VERSION</span><br /><textarea style="border:1px solid #AAA;background-color:white;width:100%;height:150px;">'.$this->message_txt.'</textarea></div>'."\n";
			$demo .= '<div style="margin:15px 8px;"><span style="font-weight:bold;color:red;">HTML VERSION</span><br /><div style="border:1px solid #AAA;background-color:white;height:150px;overflow:auto;">'.$this->message_html.'</div></div>'."\n";
			$demo .= '</div>'."\n";
			echo "\n\n<!-- senMail : DEMO MODE (begin) -->\n".$demo."\n<!-- senMail : DEMO MODE (end) -->\n\n";
		}


		// Check that we have at least one 'To' email and the 'From' email !
		if (!count($this->to) || !isset($this->from['email'])) {
			return false;
		}


		switch($this->tool)
		{
			/**
			 * Send email using swift class (http://swiftmailer.org)
			 */
			case 'swift':
			default:

				// Required files
				require_once(sitePath().'/plugins/php/Swift/lib/swift_required.php');


				// Create the Transport
				$transport = Swift_MailTransport::newInstance(); # Using php mail() function


				// Create the Mailer using the created Transport
				$mailer = Swift_Mailer::newInstance($transport);


				// Create the message
				$message = Swift_Message::newInstance()

					// Set charset
					->setCharset($this->charset)

					// Set line length
					#->setMaxLineLength(1000) # Optional. Comment this line to disable (default lenght = 78)

					// Give the message a subject
					->setSubject($this->subject);

				// Give it a body and optionally an alternative body
				if ($this->message_html != "")
				{
					$message->setBody(str_replace('{message_html}', $this->message_html, $this->template_html), 'text/html');

					$this->message_txt ? $message->addPart($this->message_txt, 'text/plain') : '';
				}
				else {
					$message->setBody($this->message_txt, 'text/plain');
				}

				// Set the To addresses
				for ($i=0; $i<count($this->to); $i++)
				{
					isset($this->to[$i]['name']) ? $message->addTo($this->to[$i]['email'], $this->to[$i]['name']) : $message->addTo($this->to[$i]['email']);
				}

				// Set the From address
				if (isset($this->from['name']))
				{
					$message->setFrom( array($this->from['email'] => $this->from['name']) );
				} else {
					$message->setFrom( array($this->from['email']) );
				}

				// Set Reply-to
				if ($this->reply_to)
				{
					$message->setReplyTo($this->reply_to);
				}

				// Set return-path
				if ($this->return_path)
				{
					$message->setReturnPath($this->return_path);
				}

				// Set return-path
				for ($i=0; $i<count($this->attachment); $i++)
				{
					$message->attach(Swift_Attachment::fromPath($this->attachment[$i]));
				}

				if ($this->demo)
				{
					$demo = '<div style="margin:15px 8px;"><span style="font-weight:bold;color:red;">SWIFT MAILER OUTPUT</span><br /><textarea style="border:1px solid #AAA;background-color:white;width:100%;height:150px;">'.$message->toString().'</textarea></div>'."\n";
					echo "\n\n<!-- senMail : SWIFT MAILER OUTPUT (begin) -->\n".$demo."\n<!-- senMail : SWIFT MAILER OUTPUT (end) -->\n\n";
					return true; # Demo: Stop here!
				}


				// Send the message
				$result = $mailer->batchSend($message, $failed_recipients);

				// Failures
				if (!$result) {
					//$this->failed_recipients = $failed_recipients; # FIXME - This feature don't seems to work !?!
				}

				return $result;
				break;
		}

	}



	// Get the list of failures
	public function failedRecipients()
	{
		return $this->failed_recipients;
	}



	// Design to display a summary to the user after his mail was successfully sent

	public function getMessageTXT()
	{
		return $this->message_txt;
	}

	public function getMessageHTML()
	{
		return $this->message_html;
	}

}


?>