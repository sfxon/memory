<?php

class cRegister extends cModule {
		var $template = 'blitz2016';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				//If the user is logged in..
				if(isset($_SESSION['user_id'])) {
						header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'myaccount/index.html');
						die;
				}
				
				//We use the CMS module for output.
				cCMS::setExecutionalHooks();
				$core = core();
				
				//Now set our own hooks below the CMS hooks.
				core()->setHook('cCore|process', 'process');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$success = core()->getGetVar('success');
				
				if(6 == $success) {
						$this->successes['saved'] = 1;
				}
				
				$errormessage = '';
				$action = core()->getGetVar('action');
				$this->siteUrl = cSite::loadSiteUrl(core()->get('site_id'));
				
				$email_address = '';
				$email_address_confirm = '';
				$password = '';
				$password_confirm = '';
				$email_language = 1;
				
				if($action == 'process') {
						$email_address = trim(core()->getPostVar('email_address'));
						$email_address_confirm = trim(core()->getPostVar('email_address_confirm'));
						$password = trim(core()->getPostVar('password'));
						$password_confirm = trim(core()->getPostVar('password_confirm'));
						$email_language = (int)core()->getPostVar('email_language');
												
						if(5 > strlen($email_address)) {
								$this->errors['email_address'] = 1;
						}
						
						if(5 > strlen($email_address_confirm)) {
								$this->errors['email_address_confirm'] = 1;
						}
						
						if($email_address != $email_address_confirm) {
								$this->errors['email_address_missmatch'] = 1;
						}
						
						if(6 > strlen($password)) {
								$this->errors['password'] = 1;
						}
								
						if(6 > strlen($password_confirm)) {
								$this->errors['password_confirm'] = 1;
						}
				
						if($password != $password_confirm) {
								$this->errors['password_missmatch'] = 1;
						}
						
						if($email_language != 1 && $email_language != 2) {
								$email_language = 1;
						}
						
						//check E-Mail existence..
						$email_account_id = cAdminaccounts::getAccountIdByEmail($email_address);
						
						if($email_account_id !== false) {
								$this->errors['email_exists'] = true;
						}
						
						//Save if there are no errors!						
						if(count($this->errors) == 0) {
								$email_validation_code = md5(time());
								
								$user_id = $this->createInDB($email_address, $password, $email_language, $email_validation_code);
								$this->sendValidationEmail($email_address, $email_validation_code, $email_language, $user_id);
								header('Location: //' . $this->siteUrl . 'registration_success/');
								die;
						} else {
								$this->data['data']['email_address'] = htmlspecialchars($email_address);
								$this->data['data']['email_address_confirm'] = htmlspecialchars($email_address_confirm);
								$this->data['data']['password'] = htmlspecialchars($password);
								$this->data['data']['password_confirm'] = htmlspecialchars($password_confirm);
								$this->data['data']['email_language'] = (int)$email_language;
						}
							
				}
				
				//Load the CMS Entry for the login page.
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('REGISTRATION');
				
				//Render the content (if there are set some CMS variables..)
				$renderer = core()->getInstance('cRenderer');
				$renderer->assign('SUCCESSES', $this->successes);
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('EMAIL_ADDRESS', htmlentities($email_address));
				$renderer->assign('EMAIL_ADDRESS_CONFIRM', htmlentities($email_address_confirm));
				$renderer->assign('PASSWORD', '');
				$renderer->assign('PASSWORD_CONFIRM', '');
				$renderer->assign('EMAIL_LANGUAGE', htmlentities($email_language));
				$renderer->assign('REGISTER_DATA', $this->data);
				
				$this->contentData['text'] = $renderer->fetchFromString($content);
				$cms->setContentData($content);
				
				//Check SEO Url if this module was called directly..
				$s = core()->getGetVar('s');
	
				//Check SEO Url
				$process = core()->getGetVar('process');
				$params = array('s' => 'cRegister', 'process' => $process);
				cSeourls::checkSeoUrl($this->siteUrl, 'cCMS', $params);
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		// Create an account in the database.
		/////////////////////////////////////////////////////////////////////////////////////////////
		public function createInDB($email_address, $password, $email_language, $email_validation_code) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('accounts') . ' ' .
								'(account_type, username, email, password, email_language, notice, created_on, mqllock_count, demo_accounts, email_validation_code, email_validated) ' .
						'VALUES ' .
								'(2, :username, :email, :password, :email_language, :notice, NOW(), 0, 5, :email_validation_code, 0) '
				);
				$db->bind(':username', $email_address);
				$db->bind(':email', $email_address);
				$db->bind(':password', password_hash($password, PASSWORD_BCRYPT));
				$db->bind(':email_language', (int)$email_language);
				$db->bind(':email_validation_code', $email_validation_code);
				$db->bind(':notice', 'Erstellt über das Frontend / Registrierung durch Kunde auf Webseite.');
				$result = $db->execute();
				return $db->insertId();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////
		// Send a validation email.
		///////////////////////////////////////////////////////////////////////////////////////////
		public function sendValidationEmail($email_address, $email_validation_code, $email_language, $user_id) {
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('REGISTRATION_VALIDATION_EMAIL');
				
				//Render the content (if there are set some CMS variables..)
				$renderer = core()->getInstance('cRenderer');
				$renderer->assign('EMAIL_VALIDATION_CODE', htmlentities($email_validation_code));
				$renderer->assign('VALIDATION_URL', '//' . cSite::loadSiteUrl(core()->get('site_id')) . 'validate_mail/?vc=' . urlencode($email_validation_code) . '&user_id=' . $user_id);
				$this->contentData['text'] = $renderer->fetchFromString($content['text']);
				
				//Send email
				$to = utf8_decode($email_address);
				$subject = utf8_decode(TEXT_REGISTRATION_EMAIL_HEADING);
				$txt = utf8_decode($this->contentData['text']);
				$headers  = "MIME-Version: 1.0\r\n";
				$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
				$headers .= "From: " . "\r\n" .
				"BCC: ";
				mail($to,$subject,$txt,$headers);
				
				
		}
		
		
}

?>