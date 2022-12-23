<?php

class cWssellerhandler extends cModule {
		var $template = 'blitz2016';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
		var $action = '';
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				$state = cWsseller::checkSellerStatus();		//Redirects to another page, if an error occures.
				
				if($state != 'loggedin') {
						die('Seller is not logged in');
				}
				
				$live_session_id = (int)core()->getGetVar('live_session_id');
				
				if(empty($live_session_id)) {
						cWebsellersessionslive::writeLog('No live session id provided.', array('id' => $live_session_id), array('SESSION: ' . print_r($_SESSION, true)));
						header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/session_setup.html?nosessionidprovided=1');
						die;
				}
				
				$live_session_data = cWebsellersessionslive::loadLiveData($live_session_id);
						
				//Live-Session-Data not found. Reload sessions screen.
				if(false === $live_session_data) {
						cWebsellersessionslive::writeLog('Live Session not found.', $live_session_data, array('SESSION: ' . print_r($_SESSION, true)));
						header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/session_setup.html?err=NOLIVESESSIONDATA');
						die;
				}
				
				//Make sure that user is connected to this live session!
				if((int)$_SESSION['seller_id'] !== (int)$live_session_data['seller_id']) {
						cWebsellersessionslive::writeLog('Seller is not allowed to use this session.', $live_session_data, array('SESSION: ' . print_r($_SESSION, true)));
						header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/session_setup.html?err=NOTUSERSSESSION');
						die;
				}
				
				//We use the CMS module for output.
				cCMS::setExecutionalHooks();		
				
				$core = core();
				
				//Now set our own hooks below the CMS hooks.
				//core()->setHook('cCMS|init', 'init');
				core()->setHook('cCore|process', 'process');
				core()->setHook('cCMS|footer', 'footer');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$this->data['live_session_id'] = (int)core()->getGetVar('live_session_id');
				$this->data['live_session_data'] = cWebsellersessionslive::loadLiveData($this->data['live_session_id']);
				$this->data['seller'] = cAccount::loadUserData($this->data['live_session_data']['seller_id']);
				$this->data['session_data'] = cWebsellersessions::loadSessionById($this->data['live_session_data']['webseller_sessions_id']);
				$this->data['webseller_sessions_id'] = $this->data['live_session_data']['webseller_sessions_id'];
				$this->data['customer'] = cAccount::loadUserData($this->data['session_data']['user_id']);
				$this->data['webseller_machines_data'] = cWebsellermachines::loadEntryById( $this->data['session_data']['webseller_machines_id']);
				
				$this->action = core()->getGetVar('action');
				
				switch($this->action) {
						case 'ajax_logout_customer':
								$this->ajaxLogoutCustomer();
								break;
						
						case 'ajax_sync':
								$this->ajaxSync();
								break;
						
						default:
								$this->drawHandlerScreen();
								break;
				}
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Logout the customer.
		/////////////////////////////////////////////////////////////////////////////////
		public function ajaxLogoutCustomer() {
				unset($_SESSION['wscustomer_session_id_live']);
				$state_json = cWebsellersessionslive::makeStateJson('customer_login', array());
				
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
				die;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function drawHandlerScreen() {
				$site_url = cSeourls::loadSeourlByQueryString('s=cWssellersessionsetup');
				$site_url = ltrim($site_url, '/');
				
				//Load the CMS Entry for the login page.
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $this->template);
				$renderer->assign('SITE_URL', $site_url);
				$renderer->assign('SESSION_HANDLER_URL', '//' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/index.html');
				$renderer->assign('SESSIONS_LIVE_URL', '//' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/sessions_live/');
				$renderer->assign('SESSION_QUIT_URL', '//' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/sessions_live/QUIT');
				
				$tmp_content = $renderer->fetch('site/wsseller/handler.html');
				
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('WSSELLER_HANDLER');
				
				$content['text'] = $tmp_content;
				$cms->setContentData($content);
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page footer.
		///////////////////////////////////////////////////////////////////
		public function footer() {
				$additional_output = 
						"\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/wsseller_handler_sync.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
		
		///////////////////////////////////////////////////////////////////
		// ajax Sync
		///////////////////////////////////////////////////////////////////
		public function ajaxSync() {
				$state_array = json_decode($this->data['live_session_data']['state'], true);
				
				switch($state_array['site']) {
						case 'customer_login':				//When we are waiting for the customer to login
								$this->returnAjaxCustomerLogin();
								break;
						case 'customer_logged_in':		//When the customer just logged in!
								$this->returnAjaxCustomerLoggedIn();
								break;
						default:
								$this->handleState();
								break;
				}
				
				echo 'do the default stuff..';
				die;
		}
	
		///////////////////////////////////////////////////////////////////
		// When the customer just logged in.
		///////////////////////////////////////////////////////////////////
		public function returnAjaxCustomerLoggedIn() {
				//set the customer session id..
				$_SESSION['wscustomer_session_id_live'] = $this->data['live_session_id'];
				
				//Load the first screen of the session..
				$customer_url = cSeourls::loadSeourlByQueryString('s=cWscustomerhandler');
				$customer_url = ltrim($customer_url, '/');
				/*$customer_login_url .= '?wcs=' . $this->data['live_session_id'];*/
				$customer_url = '//' . cSite::loadSiteUrl(core()->get('site_id')) . $customer_url;
				
				$tmp_live_session_data = $this->data['live_session_data'];
				$tmp_live_session_data['state'] = json_decode( $tmp_live_session_data['state'] );
				
				$retval = array(
						'customer_url' => $customer_url,
						'live_session_id' => $this->data['live_session_id']/*,
						'live_session_data' => $tmp_live_session_data/*,
						'user_screen_width' => 300,
						'user_screen_height' => 600
						*/
				);
				
				cAjax::returnSuccessAndQuit('customer_logged_in', $retval);
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// ajax Sync
		///////////////////////////////////////////////////////////////////
		public function getPage() {
				//1. Instanciate all needed objects. --------------------------
				//Load current state
				$ws_machines_state =  cWebsellermachinesstates::loadStateByJson( $this->data['live_session_data']['state'], $this->data['webseller_machines_data']['id'] );
				
				//Load objects
				$ws_machines_states_objects = cWebsellermachinesstatesobjects::instanciateObjectsByWebsellerMachinesStatesId($ws_machines_state['id'], $this->data);
			
				//1. Render the page ------------------------------------------
				$webseller_template = $this->data['webseller_machines_data']['template_folder'];
				
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($webseller_template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('MACHINES_STATES_OBJECTS', $ws_machines_states_objects);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $webseller_template);
				$renderer->assign('SITE_URL', cSite::loadSiteUrl(core()->get('site_id')));
				$renderer->assign('SESSION_HANDLER_URL', '//' . cSite::loadSiteUrl(core()->get('site_id')) . 'mein-logo-shop.html');
				
				if(isset($_SESSION['seller_id'])) {
						$renderer->assign('IS_SELLER', 1);
				} else {
						$renderer->assign('IS_SELLER', 0);
				}
				
				$tmp_content = $renderer->fetch('state_pages/' . $ws_machines_state['template_file']);
				
				echo $tmp_content;
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Waiting for the customer to login.
		///////////////////////////////////////////////////////////////////
		public function returnAjaxCustomerLogin() {
				//$_SESSION['wscustomer_session_id_live'] = $this->data['live_session_id'];
				
				//Load the login screen: We load it in an iframe.
				$customer_login_url = cSeourls::loadSeourlByQueryString('s=cWscustomerlogin');
				$customer_login_url = ltrim($customer_login_url, '/');
				$customer_login_url .= '?wcs=' . $this->data['live_session_id'];
				$customer_login_url = '//' . cSite::loadSiteUrl(core()->get('site_id')) . $customer_login_url;
				
				$tmp_live_session_data = $this->data['live_session_data'];
				$tmp_live_session_data['state'] = json_decode( $tmp_live_session_data['state'] );
				
				$retval = array(
						'customer_login_url' => $customer_login_url,
						'live_session_id' => $this->data['live_session_id']/*,
						'live_session_data' => $tmp_live_session_data/*,
						'user_screen_width' => 300,
						'user_screen_height' => 600
						*/
				);
				
				cAjax::returnSuccessAndQuit('customer_login', $retval);
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// ajax Sync
		///////////////////////////////////////////////////////////////////
		public function handleState() {
				$ws_machines_state =  cWebsellermachinesstates::loadStateByJson( $this->data['live_session_data']['state'], $this->data['webseller_machines_data']['id'] );
				
				$retval = array(
						/*'session_url' => $session_url,*/
						'live_session_id' => $this->data['live_session_id'],
						'state' => $ws_machines_state['id']
						/*
						'live_session_data' => $tmp_live_session_data/*,
						'user_screen_width' => 300,
						'user_screen_height' => 600
						*/
				);
				cAjax::returnSuccessAndQuit('state', $retval);
		}
		
		
		
		
}

?>