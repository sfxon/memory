<?php

class cWscustomerhandler extends cModule {
		var $template = 'blitz2016';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
		var $action = '';
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				$state = cWscustomer::checkCustomerStatus();		//Redirects to another page, if an error occures.
				
				if($state != 'loggedin') {
						die('Customer is not logged in');
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
				$this->data['live_session_id'] = (int)$_SESSION['wscustomer_session_id_live'];
				
				if(isset($_SESSION['seller_id'])) {
						$this->processSellersActions();
				}
				
				$this->data['live_session_data'] = cWebsellersessionslive::loadLiveData($this->data['live_session_id']);
				$this->data['seller'] = cAccount::loadUserData($this->data['live_session_data']['seller_id']);
				$this->data['session_data'] = cWebsellersessions::loadSessionById($this->data['live_session_data']['webseller_sessions_id']);
				$this->data['webseller_sessions_id'] = $this->data['live_session_data']['webseller_sessions_id'];
				$this->data['customer'] = cAccount::loadUserData($this->data['session_data']['user_id']);
				$this->data['webseller_machines_data'] = cWebsellermachines::loadEntryById( $this->data['session_data']['webseller_machines_id']);
				
				$cCMS = core()->getInstance('cCMS');
				$cCMS->setTemplate($this->data['webseller_machines_data']['template_folder']);
				$this->template = $this->data['webseller_machines_data']['template_folder'];
				$site_data = cSite::loadSiteData(core()->get('site_id'));
				//$cCMS->setTemplateUrl('//' . $site_data['url'] . '/data/templates/' . $this->data['webseller_machines_data']['template_folder']);
				
				$this->action = core()->getGetVar('action');
				
				switch($this->action) {
						case 'get_page':
								$this->getPage();
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
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function processSellersActions() {
				//Set all posts values in the data array, so we can use them later.
				$this->data['get_values'] = core()->getAllGets();
				
				//process machine state, if it was submitted!
				if(isset($this->data['get_values']['machines_state'])) {
						//Here we have the request to change the machines state - e.g. show a different page (and/or) action.
						$machines_state = core()->getGetVar('machines_state');
						
						//get main machine state..
						$main_machines_state = cWebsellermachinesstates::getMainState($machines_state);
						$current_machines_path = cWebsellermachinesstates::getSubstatesPath($machines_state, '');
						
						if($main_machines_state != $machines_state) {
								$this->data['get_values']['current_machines_path'] = $current_machines_path;
								$this->data['get_values']['machines_state'] = $main_machines_state;
								$this->data['get_values']['sub_machines_state'] = $machines_state;
								$machines_state = $main_machines_state;
								
								//Process currents substates machines_states_objects.
								//Values can be submitted with this by get.. Just set the new substates_value in the GET Array.
								cWebsellermachinesstatesobjects::instanciateObjectsByWebsellerMachinesStatesId($this->data['get_values']['sub_machines_state'], $this->data);
								
								if(isset($_GET['substates_value'])) {
										$this->data['get_values']['substates_value'] = $_GET['substates_value'];
								} else {
										$this->data['get_values']['substates_value'] = 0;
								}
						} else {
								$this->data['get_values']['current_machines_path'] = '';
								$this->data['get_values']['machines_state'] = $main_machines_state;
								$this->data['get_values']['sub_machines_state'] = 0;
								$this->data['get_values']['substates_value'] = 0;
								/*$machines_state = $main_machines_state;*/
						}
						
						$state_json = cWebsellersessionslive::makeStateJson('state', $this->data['get_values']);
						cWebsellersessionslive::updateBySeller($this->data['live_session_id'], $state_json);
				}
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function drawHandlerScreen() {
				$site_url = cSeourls::loadSeourlByQueryString('s=cWscustomerhandler');
				$site_url = ltrim($site_url, '/');
				
				//Load the CMS Entry for the login page.
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->data['webseller_machines_data']['template_folder']);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $this->template);
				$renderer->assign('SITE_URL', $site_url);
				$renderer->assign('SESSION_HANDLER_URL', '//' . cSite::loadSiteUrl(core()->get('site_id')) . 'mein-logo-shop.html');
				
				if(isset($_SESSION['seller_id'])) {
						$renderer->assign('IS_SELLER', 1);
				} else {
						$renderer->assign('IS_SELLER', 0);
				}
				
				$page = $this->renderPage();
				$renderer->assign('PAGE', $page);
				
				$tmp_content = $renderer->fetch('site/handler.html');
				
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('WSCUSTOMER_HANDLER');
				
				$content['text'] = $tmp_content;
				$cms->setContentData($content);
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page footer.
		///////////////////////////////////////////////////////////////////
		public function footer() {
				$additional_output = 
						"\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/wscustomer_handler_sync.js"></script>' .
						"\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/live_session_handler_customer_and_seller.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
		
		///////////////////////////////////////////////////////////////////
		// ajax Sync
		///////////////////////////////////////////////////////////////////
		public function ajaxSync() {
				$state = $this->data['live_session_data']['state'];
				$state = json_decode($state, true);
				
				if(isset($state['site'])) {
						if($state['site'] == 'customer_login') {
								unset($_SESSION['wscustomer_session_id_live']);
								cAjax::returnSuccessAndQuit('customer_login', array('live_session_id' => $this->data['live_session_data']['id']));
								die;
						}
				}
				
				//Load current state
				$ws_machines_state =  cWebsellermachinesstates::loadStateByJson( $this->data['live_session_data']['state'], $this->data['webseller_machines_data']['id'] );
				
				if(isset($state['actions']['current_machines_path'])) {
						$ws_machines_state['current_machines_path'] = $state['actions']['current_machines_path'];
				}
				
				if(isset($state['actions']['substates_value'])) {
						$ws_machines_state['substates_value'] = $state['actions']['substates_value'];
				}
				
				cAjax::returnSuccessAndQuit('handler', $ws_machines_state);
				//echo json_encode($ws_machines_state);
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// gets the page
		///////////////////////////////////////////////////////////////////
		public function getPage() {
				$tmp_content = $this->renderPage();
				
				echo $tmp_content;
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Render the page.
		///////////////////////////////////////////////////////////////////
		public function renderPage() {
				//1. Instanciate all needed objects. --------------------------
				//Load current state
				$ws_machines_state =  cWebsellermachinesstates::loadStateByJson( $this->data['live_session_data']['state'], $this->data['webseller_machines_data']['id'] );
				
				//Load objects
				$ws_machines_states_objects = cWebsellermachinesstatesobjects::instanciateObjectsByWebsellerMachinesStatesId($ws_machines_state['id'], $this->data);
				
				//Load all substates (recursive)
				$ws_machines_substates = array();		//the loadSubstatesByStatesIdRecursive takes an array as reference, that is enhanced.
				cWebsellermachinesstates::loadSubstatesByStatesIdRecursive($ws_machines_state['id'], '', $ws_machines_substates);
				
				//Render all substates.
				cWscustomerhandler::renderSubstates($ws_machines_states_objects, $ws_machines_substates);
				
				//1. Render the page ------------------------------------------
				$webseller_template = $this->data['webseller_machines_data']['template_folder'];
				
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($webseller_template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('MACHINES_STATES_OBJECTS', $ws_machines_states_objects);
				$renderer->assign('WS_MACHINES_SUBSTATES', $ws_machines_substates);
				$renderer->assign('LIVE_SESSION_DATA_STATE', json_decode($this->data['live_session_data']['state'], true));
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
				
				return $tmp_content;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Render the substates..
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		public function renderSubstates($ws_machines_states_objects,&$ws_machines_substates) {
				foreach($ws_machines_substates as $index => $ws_machines_substate) {
						$webseller_template = $this->data['webseller_machines_data']['template_folder'];
						
						$renderer = core()->getInstance('cRenderer');
						$renderer->setTemplate($webseller_template);
						$renderer->assign('DATA', $this->data);
						$renderer->assign('MACHINES_STATES_OBJECTS', $ws_machines_states_objects);
						$renderer->assign('WS_MACHINES_SUBSTATE', $ws_machines_substate);
						$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
						$renderer->assign('TEMPLATE', $webseller_template);
						$renderer->assign('SITE_URL', cSite::loadSiteUrl(core()->get('site_id')));
						$renderer->assign('SESSION_HANDLER_URL', '//' . cSite::loadSiteUrl(core()->get('site_id')) . 'mein-logo-shop.html');
						
						if(isset($_SESSION['seller_id'])) {
								$renderer->assign('IS_SELLER', 1);
						} else {
								$renderer->assign('IS_SELLER', 0);
						}
						
						$tmp_content = $renderer->fetch('state_pages/' . $ws_machines_substate['template_file']);
						$ws_machines_substates[$index]['content'] = $tmp_content;
				}
		}
}

?>