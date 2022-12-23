<?php

class cWssellersessionsetup extends cModule {
		var $template = 'blitz2016';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
		var $data = array();
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				$state = cWsseller::checkSellerStatus();		//Redirects to another page, if an error occures.
				
				if($state != 'loggedin') {
						die('Seller is not logged in');
				}
				
				//check if this seller got running sessions
				$running_sessions = cWsseller::getSellersLiveSessions($_SESSION['seller_id']);
				
				if(count($running_sessions) > 0) {
						header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/sessions_live/');
						die;
				}
				
				//We use the CMS module for output.
				cCMS::setExecutionalHooks();		
				
				$core = core();
				
				//Now set our own hooks below the CMS hooks.
				//core()->setHook('cCMS|init', 'init');
				core()->setHook('cCore|process', 'process');
				core()->setHook('cRenderer|content', 'content');
				core()->setHook('cCMS|footer', 'footer');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$errormessage = '';
				$action = core()->getGetVar('action');
				//$session_id = (int)core()->getGetVar('cws');		//cws = Customers webseller session
				//$session_key = ''; 
				$this->initData();
				
				//Processing input
				if($action == 'process') {
						//check the email for plausability..
						$this->initSession();
						
						
						
					
					
						/*$session_key = core()->getPostVar('session_key');	
						
						if(false === $this->doWsLogin($session_id, $session_key)) {
								//$renderer = core()->getInstance('cRenderer');
								//$renderer->setTemplate($this->template);
								//$renderer->assign('ERRORMESSAGE', ERROR_TEXT_LOGIN_INCORRECT);
								$errormessage = 'Der eingegebene Session-Key ist falsch.<br />Bitte versuchen Sie es noch einmal.';
						}
						*/
				}
				
				//Set the site url. We need this for the form to have the right action url!
				$form_url = cSeourls::loadSeourlByQueryString('s=cWssellersessionsetup');
				$form_url = ltrim($form_url, '/');
				$form_url .= '?action=process';
				$form_url = '//' . cSite::loadSiteUrl(core()->get('site_id')) . $form_url;

				//Load the CMS Entry for the login page.
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $this->template);
				$renderer->assign('FORM_URL', $form_url);
				$renderer->assign('ERRORMESSAGE', $errormessage);
				
				$seller_status = cAccount::getSellerStatusByAccountId($_SESSION['seller_id']);
				$open_sessions = cWsseller::loadOpenSessions($seller_status);
				$open_sessions = $this->addSessionInfo($open_sessions);
				$renderer->assign('SESSIONS', $open_sessions);
				$tmp_content = $renderer->fetch('site/wsseller/session_setup.html');
				
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('WSSELLER_SESSION_SETUP');
				$content['text'] = $tmp_content;
				$cms->setContentData($content);
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Add more information to a session.
		/////////////////////////////////////////////////////////////////////////////////
		private function initSession() {
				$session_id = (int)core()->getPostVar('session_id');
				$email = core()->getPostVar('email');
				
				//Check the session for plausability.
				$this->data['session_id'] = $session_id;
				$this->data['session_data'] = cWebsellersessions::loadSessionById($session_id);
				$this->data['email'] = $email;
				
				if(false === $this->data['session_data']) {
						header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/session_setup.html?error=94&session_id=' . $session_id);
						die;
				}
				
				//Check if there is a live version of this session.
				$session_live_instances = cWebsellersessionslive::loadWebsellerSessionsRunningLiveInstancesByWsId($session_id);
				
				if(count($session_live_instances) > 0) {
						header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/session_setup.html?error=95&session_id=' . $session_id);
						die;
				}
				
				//Check if this user is allowed to start this session (for example, if this is a rookie seller, but a powerseller session - he cannot start it!)
				if($this->data['seller']['account_type'] != $this->data['session_data']['session_type']) {
						header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/session_setup.html?error=96&session_id=' . $session_id);
						die;
				}
				
				//Load some customers data.
				$customers_data = cAccount::loadUserData($this->data['session_data']['user_id']);
				$customers_data['salutation'] = cAccount::getSalutationByGender($customers_data['gender']);
				
				if(false === $customers_data) {
						header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/session_setup.html?error=99&session_id=' . $session_id);
						die;
				}
				
				$this->data['customers_data'] = $customers_data;
								
				//Check the email address that was provided.
				$validation = cMail::validateEmailAddress($email);
				
				if(false === $validation) {
						header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/session_setup.html?error=97&session_id=' . $session_id);
						die;
				}
				
				//Start the session.
				$this->startSession();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Add more information to a session.
		/////////////////////////////////////////////////////////////////////////////////
		public function addSessionInfo($sessions) {
				foreach($sessions as $index => $session) {
						$sessions[$index]['account'] = cAccount::loadUserData($session['user_id']);
				}
				
				return $sessions;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Processing a login.
		/////////////////////////////////////////////////////////////////////////////////
		private function startSession() {
				//start live session
				$state = cWebsellersessionslive::makeStateJson('customer_login');
				
				$id = cWebsellersessionslive::create($this->data['session_id'], $this->data['seller']['id'], $state, date('Y-m-d H:i:s'));
				$live_session_data = cWebsellersessionslive::loadLiveData($id);
				$this->data['webseller_session_live'] = $live_session_data;
				
				cWebsellersessionslive::writeLog('Started session by seller.', $live_session_data);
				
				$this->sendSessionMailToCustomer();
				cWebsellersessionslive::writeLog('Mail sent to customer.', $live_session_data, $this->data['mailer']);
				
				//redirect to handler!
				header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/index.html?live_session_id=' . $id);
				die;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Send a mail to the customer, with all relevant data.
		/////////////////////////////////////////////////////////////////////////////////
		private function sendSessionMailToCustomer() {
				$mailer = new cMail();
				$mailer->setMailTo($this->data['email']);
				$mailer->setMailReplyTo( cSetup::loadValueByModuleAndKey('webseller', 'mail_reply_to'));
				$mailer->setMailFrom( cSetup::loadValueByModuleAndKey('webseller', 'mail_from'));
				$mailer->addData('DATA', $this->data);
				$mailer->setSubject( cSetup::loadValueByModuleAndKey('webseller', 'subject'));
				$mailer->setCmsKey('MAIL_EINLADUNG_WEBSELLER');
				$mailer->send();
				
				//We save it, to add it to some debug data later..
				$this->data['mailer'] = $mailer;
		}
		
		
		
		/////////////////////////////////////////////////////////////////////////////
		// Init all needed data.
		/////////////////////////////////////////////////////////////////////////////
		public function initData() {
				$this->data['seller'] = cAccount::loadUserData($_SESSION['seller_id']);
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page footer.
		///////////////////////////////////////////////////////////////////
		public function footer() {
				$additional_output = 
						"\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/wsseller_session_setup.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
}

?>