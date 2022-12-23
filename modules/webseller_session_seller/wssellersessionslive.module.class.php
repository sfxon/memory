<?php

class cWssellersessionslive extends cModule {
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
				
				if(count($running_sessions) == 0) {
						header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/session_setup.html');
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
				if($action == 'quit_session') {
						$live_session_id = (int)core()->getGetVar('live_session_id');
						$live_session_data = cWebsellersessionslive::loadLiveData($live_session_id);
						
						//Live-Session-Data not found. Reload sessions screen.
						if(false === $live_session_data) {
								cWebsellersessionslive::writeLog('Quit attempt on not existing live-session.', $live_session_data, array('SESSION: ' . print_r($_SESSION, true)));
								header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/sessions_live/?err=NOLIVESESSIONDATA');
								die;
						}
						
						//User is not allowed to edit the session. Reload sessions screen.
						if((int)$_SESSION['seller_id'] !== (int)$live_session_data['seller_id']) {
								cWebsellersessionslive::writeLog('Quit attempt on live-session by seller.that does not own the session.', $live_session_data, array('SESSION: ' . print_r($_SESSION, true)));
								header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/sessions_live/?err=NOTUSERSSESSION');
								die;
						}
						
						//Quit session.
						cWebsellersessionslive::writeLog('Quit live session by seller in wssellersessionslive.', $live_session_data, array('SESSION: ' . print_r($_SESSION, true)));
						cWebsellersessionslive::endLiveSession($live_session_id);
						
						//Reload live sessions screen.
						header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/sessions_live/');
						die;
				}
				
				//Set the site url. We need this for the form to have the right action url!
				$site_url = cSeourls::loadSeourlByQueryString('s=cWssellersessionsetup');
				$site_url = ltrim($site_url, '/');
				
				$running_sessions = cWsseller::getSellersLiveSessions($_SESSION['seller_id']);
				
				foreach($running_sessions as $index => $session) {
						$session_data = cWebsellersessions::loadSessionById($session['webseller_sessions_id']);
						$session_data['customers_data'] = cAccount::loadUserData($session_data['user_id']);
						$running_sessions[$index]['session_data'] = $session_data;
				}

				//Load the CMS Entry for the login page.
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('TEMPLATE_URL', cCMS::loadTemplateUrl(core()->get('site_id')));
				$renderer->assign('TEMPLATE', $this->template);
				$renderer->assign('SITE_URL', $site_url);
				$renderer->assign('RUNNING_SESSIONS', $running_sessions);
				$renderer->assign('ERRORMESSAGE', $errormessage);
				$renderer->assign('SESSION_HANDLER_URL', '//' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/index.html');
				$renderer->assign('SESSIONS_LIVE_URL', '//' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/sessions_live/');
				
				$tmp_content = $renderer->fetch('site/wsseller/sessions_live.html');
				
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('WSSELLER_SESSIONS_LIVE');
				
				$content['text'] = $tmp_content;
				$cms->setContentData($content);
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
						"\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/wsseller_sessions_live.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
}
?>