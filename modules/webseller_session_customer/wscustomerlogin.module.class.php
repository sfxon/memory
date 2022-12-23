<?php

class cWscustomerlogin extends cModule {
		var $template = 'blitz2016';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				//If the user is logged in..
				if(isset($_SESSION['wscustomer_session_id_live'])) {
						header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'mein-logo-shop.html');
						die;
				}
				
				//We use the CMS module for output.
				cCMS::setExecutionalHooks();		
				
				$core = core();
				
				//Now set our own hooks below the CMS hooks.
				//core()->setHook('cCMS|init', 'init');
				core()->setHook('cCore|process', 'process');
				core()->setHook('cRenderer|content', 'content');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$errormessage = '';
				$action = core()->getGetVar('action');
				$live_session_id = (int)core()->getGetVar('wcs');		//ID of the customer_webseller_session_live table entry.
				$session_key = '';
				
				if(0 === $live_session_id) {
						header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . '?infomessage=NOSESSIONPROVIDED&error=90');
						die;
				}
				
				//Load live session data.
				$live_session_data = cWebsellersessionslive::loadLiveData((int)$live_session_id);
				
				if(false === $live_session_data) {
						header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'infomessage=NOSESSIONPROVIDED&error=100');
						die;
				}
				
				$this->data['live_session_id'] = $live_session_id;
				$this->data['live_session_data'] = $live_session_data;
				
				//Check if this live session is started - but not ended!
				if(empty($live_session_data['session_started_on'])) {
						header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'infomessage=NOSESSIONPROVIDED&error=101');
						die;
				}
				
				//Check if this live session is already ended!
				if($live_session_data['session_ended_on'] !== NULL) {
						header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'infomessage=NOSESSIONPROVIDED&error=102');
						die;
				}
				
				//Get the session id and check the session in the next steps
				$session_id = $live_session_data['webseller_sessions_id'];
				
				//Check the existence of this session!
				if(false === cWscustomer::checkSessionSetupExists($session_id)) {
						header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . '?infomessage=NOSESSIONPROVIDED&error=91');
						die;
				}
				
				$this->initData($session_id);
				
				//Processing input
				if($action == 'process') {
						$session_key = core()->getPostVar('session_key');	
						
						if(false === $this->doWsLogin($live_session_id, $session_id, $session_key)) {
								//$renderer = core()->getInstance('cRenderer');
								//$renderer->setTemplate($this->template);
								//$renderer->assign('ERRORMESSAGE', ERROR_TEXT_LOGIN_INCORRECT);
								$errormessage = 'Der eingegebene Session-Key ist falsch.<br />Bitte versuchen Sie es noch einmal.';
								$this->logLoginAttempt('wrong_login_key');
						}
				} else {
						$this->logLoginAttempt('login_page_opened');
				}
				
				//Set the site url. We need this for the form to have the right action url!
				$login_form_url = cSeourls::loadSeourlByQueryString('s=cWscustomerlogin');
				$login_form_url = ltrim($login_form_url, '/');
				$login_form_url .= '?wcs=' . $live_session_id . '&amp;action=process';
				$login_form_url = '//' . cSite::loadSiteUrl(core()->get('site_id')) . $login_form_url;
				
				$webseller_template = $this->data['cws']['webseller_machines_data']['template_folder'];

				//Load the CMS Entry for the login page.
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $webseller_template);
				$renderer->assign('LOGIN_FORM_URL', $login_form_url);
				$renderer->assign('SESSION_KEY', $session_key);
				$renderer->assign('ERRORMESSAGE', $errormessage);
				$tmp_content = $renderer->fetch('site/wscustomer/login.html');
				
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('WSCUSTOMER_LOGIN');
				$content['text'] = $tmp_content;
				$cms->setContentData($content);
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Processing a login.
		/////////////////////////////////////////////////////////////////////////////////
		public function doWsLogin($live_session_id, $session_id, $webseller_session_key) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT id FROM ' . $db->table('webseller_sessions') . ' ' .
						'WHERE ' .
								'id = :id AND ' .
								'webseller_session_key = :webseller_session_key ' .
						'LIMIT 1');
				$db->bind(':id', (int)$session_id);
				$db->bind(':webseller_session_key', $webseller_session_key);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['id'])) {
						$_SESSION['wscustomer_session_id_live'] = (int)$live_session_id;
						$this->logLoginAttempt('login_successful');
						$this->setLoggedInSession();
						
						header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'mein-logo-shop.html');
						die;
				}
				
				core()->set('wscustomer_session_id_live', '');
				return false;
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Init all needed data.
		/////////////////////////////////////////////////////////////////////////////
		public function initData($session_id) {
				$this->data['cws'] = array(
						'id' => $session_id,
						'session_id' => $session_id,
						'webseller_session' => cWscustomer::loadSessionData($session_id)
				);
				
				$this->data['cws']['customers_data'] = cAccount::loadUserData($this->data['cws']['webseller_session']['user_id']);
				$this->data['cws']['logo_image_url'] = cWscustomer::getLogoImageUrl($this->data['cws']);
				$this->data['cws']['webseller_machines_data'] = cWebsellermachines::loadEntryById( $this->data['cws']['webseller_session']['webseller_machines_id'] );
				
				//if this is not a seller - do a log entry and update the status.
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Init all needed data.
		/////////////////////////////////////////////////////////////////////////////
		public function logLoginAttempt($action) {
				if(!isset($_SESSION['seller_id'])) {		//Only log, if this is not a seller!
						$state_json = cWebsellersessionslive::makeStateJson('customer_login', $action);
						cWebsellersessionslive::updateByCustomer($this->data['live_session_id'], $state_json);
				}
		}
		
		/////////////////////////////////////////////////////////////////////////////
		// Init all needed data.
		/////////////////////////////////////////////////////////////////////////////
		public function setLoggedInSession() {
				$state_json = cWebsellersessionslive::makeStateJson('customer_logged_in', array());
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('webseller_sessions_live') . ' SET ' .
								'state = :state ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':state', $state_json);
				$db->bind(':id', (int)$this->data['live_session_id']);
				$result = $db->execute();
		}
}

?>