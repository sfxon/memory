<?php

class cMail extends cModule {
		var $data = array();
		var $mail_from = '';
		var $mail_subject = '';
		var $mail_cms_key = '';
		var $mail_reply_to = '';
		var $mail_to = '';
		var $mail_text = '';
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Set the mail to.
		/////////////////////////////////////////////////////////////////////////////////////////
		public function setMailText($mail_text) {
				$this->mail_text = $mail_text;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Set the mail to.
		/////////////////////////////////////////////////////////////////////////////////////////
		public function setMailTo($mail_to) {
				$this->mail_to = $mail_to;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Set the mail reply to field.
		/////////////////////////////////////////////////////////////////////////////////////////
		public function setMailReplyTo($reply_to) {
				$this->mail_reply_to = $reply_to;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Set the mail from field.
		/////////////////////////////////////////////////////////////////////////////////////////
		public function setMailFrom($mail_from) {
				$this->mail_from = $mail_from;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Set some data for template parsing.
		/////////////////////////////////////////////////////////////////////////////////////////
		public function addData($key, $data) {
				$this->data[$key] = $data;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Set the subject. (Overrides the heading subject).
		/////////////////////////////////////////////////////////////////////////////////////////
		public function setSubject($subject) {
				$this->mail_subject = $subject;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Set the cms key. By the cms key, it loads the content that is to be set as email text.
		/////////////////////////////////////////////////////////////////////////////////////////
		public function setCmsKey($cms_key) {
				$this->mail_cms_key = $cms_key;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Render E-Mail content.
		/////////////////////////////////////////////////////////////////////////////////////////
		public function renderContent() {
				$content_data = cCMS::loadContentDataByCmsKeyStatic($this->mail_cms_key);
				
				//Render content
				$renderer = core()->getInstance('cRenderer');
				$renderer->assign('DATA', $this->data);
				$this->mail_text = $renderer->fetchFromString($content_data['text']);
				
				//Render subject
				$this->mail_subject = $renderer->fetchFromString($this->mail_subject);
				
				//Decode. This is, because mail in utf8 is shown wrong in some clients.
				$this->mail_text = utf8_decode($this->mail_text);				
				$this->mail_subject = utf8_decode($this->mail_subject);
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Send the contact email.
		/////////////////////////////////////////////////////////////////////////////////////////
		public function send() {			
				$this->renderContent();
				
				//Build the headers..
				$mail_header = 'From: ' . $this->mail_from . "\r\n";
				
				if($this->mail_from != '') {		//Ad reply to address, if the address is different from our setup address..
						$mail_header .= 'Reply-To: ' . $this->mail_from . "\r\n";
				}
				
				$mail_header .= 'X-Mailer: PHP/' . phpversion();
				
				//Send the email
				mail($this->mail_to, $this->mail_subject, $this->mail_text, $mail_header);
		}
		
		///////////////////////////////////////////////////////////////////
		// validate the email address.
		// pregmatch as of http://stackoverflow.com/questions/201323/using-a-regular-expression-to-validate-an-email-address
		//
		// Context:
		//	A valid e-mail address is a string that matches the ABNF production […].
		//	Note: This requirement is a willful violation of RFC 5322, which defines a syntax for e-mail addresses that is simultaneously too strict (before the "@" character), too vague (after the "@" character), and too lax (allowing comments, whitespace characters, and quoted strings in manners unfamiliar to most users) to be of practical use here.
		//	The following JavaScript- and Perl-compatible regular expression is an implementation of the above definition.
		//
		// TODO:
		//	There is a copy of this function in cAdminaccounts.
		//	All calls of the other function should be changed to be done by this module (cMail).
		//	Then remove the function in the other module.
		///////////////////////////////////////////////////////////////////
		public static function validateEmailAddress($email_address) {
				$output_array = array();
				preg_match("/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/", $email_address, $output_array);
				
				if(count($output_array) > 0) {
						return true;
				}
				
				return false;
		}
}

?>