<?php

class cMydata extends cModule {
		var $template = 'blitz2016';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
		var $errors = array();
		var $successes = array();
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				if(!empty($_SESSION['user_id'])) {
						cCMS::setExecutionalHooks();		//We use the CMS module for output.
						
						$core = core();
						core()->setHook('cCore|process', 'process');
				}
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
				
				$userdata = cAccount::loadUserData($_SESSION['user_id']);
				
				if(false === $userdata) {
						header('Location: ' . $this->siteUrl . 'index.php?error=25');
						die;
				}
				
				$email_address = $userdata['email'];
				$email_address_confirm = $userdata['email'];
				$password = '';
				$password_confirm = '';
				$email_language = $userdata['email_language'];
				
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
						
						//check E-Mail existence..
						$email_account_id = cAdminaccounts::getAccountIdByEmail($email_address);
						
						if($email_account_id !== false  && $email_account_id != core()->get('user_id')) {
								$this->errors['email_exists'] = true;
						}
						
						if(strlen($password) > 0) {
								if(6 > strlen($password)) {
										$this->errors['password'] = 1;
								}
								
								if(6 > strlen($password_confirm)) {
										$this->errors['password_confirm'] = 1;
								}
						
								if($password != $password_confirm) {
										$this->errors['password_missmatch'] = 1;
								}
						}
						
						if($email_language != 1 && $email_language != 2) {
								$email_language = 1;
						}
						

						
						if(count($this->errors) == 0) {
								cAccount::saveCustomersEmailAddress($_SESSION['user_id'], $email_address);
								
								if(strlen($password) > 0) {
										cAccount::saveCustomersPassword($_SESSION['user_id'], $password);
								}
								cAccount::saveCustomersEmailLanguage($_SESSION['user_id'], $email_language);
								header('Location: //' . $this->siteUrl . 'mydata/index.html?success=6');
								die;
						}
				}

				//Load the CMS Entry for the login page.
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('MYDATA');
				
				//Render the content (if there are set some CMS variables..)
				$renderer = core()->getInstance('cRenderer');
				$renderer->assign('SUCCESSES', $this->successes);
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('EMAIL_ADDRESS', htmlentities($email_address));
				$renderer->assign('EMAIL_ADDRESS_CONFIRM', htmlentities($email_address_confirm));
				$renderer->assign('PASSWORD', '');
				$renderer->assign('PASSWORD_CONFIRM', '');
				$renderer->assign('EMAIL_LANGUAGE', htmlentities($email_language));
				
				$this->contentData['text'] = $renderer->fetchFromString($content);
				$cms->setContentData($content);
				
				//Check SEO Url if this module was called directly..
				$s = core()->getGetVar('s');
	
				//Check SEO Url
				$process = core()->getGetVar('process');
				$params = array('s' => 'cMydata', 'process' => $process);
				cSeourls::checkSeoUrl($this->siteUrl, 'cCMS', $params);
		}
}

?>