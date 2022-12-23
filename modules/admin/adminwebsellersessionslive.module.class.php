<?php

class cAdminwebsellersessionslive extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINWEBSELLERSESSIONSLIVE;
		var $navbar_id = 0;
		var $errors = array();
		var $info_messages = array();
		var $success_messages = array();
		
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				//If the user is not logged in..
				if(!isset($_SESSION['user_id'])) {
						header('Location: index.php/login/');
						die;
				}
				
				//check the rights..
				if(false === cAccount::adminrightCheck('cAdminwebsellersessionslive', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=103');
						die;
				}
				
				//We use the Admin module for output.
				cAdmin::setSmallBodyExecutionalHooks();	
				
				//Now set our own hooks below the CMS hooks.
				$core = core();
				core()->setHook('cCore|process', 'process');
				core()->setHook('cRenderer|content', 'content');
				core()->setHook('cRenderer|footer', 'footer');
		}
	
	
		///////////////////////////////////////////////////////////////////
		// processData
		///////////////////////////////////////////////////////////////////
		function process() {
				$this->checkUser();
				$this->checkSession();
			
				$this->action = core()->getGetVar('action');
				$cAdmin = core()->getInstance('cAdmin');
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINWEBSELLERSESSIONSLIVE, 'index.php?s=cAdminwebsellersessionslive' . '&amp;user_id=' . (int)$this->data['data']['user_id'] . '&amp;webseller_sessions_id=' . (int)$this->data['data']['webseller_sessions_id']);
				
				switch($this->action) {
						case 'end':
								$this->endSession();		//Expected to end in this script with a redirect.
								break;
						
						default:
								$this->getList();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// End Session
		///////////////////////////////////////////////////////////////////
		private function endSession() {
				$webseller_sessions_live_id = (int)core()->getGetVar('webseller_sessions_live_id');
				
				//TODO: Could check that this session really belongs to this live session - but  not really needed for now..
				cWebsellersessionslive::endLiveSession($webseller_sessions_live_id);
				
				header('Location: index.php?s=cAdminwebsellersessionslive&user_id=' . $this->data['data']['user_id'] . '&webseller_sessions_id=' . $this->data['data']['webseller_sessions_id']);
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Check Session.
		///////////////////////////////////////////////////////////////////
		private function checkSession() {
				$webseller_sessions_id = (int)core()->getGetVar('webseller_sessions_id');
				$tmp = cWebsellersessions::loadSessionById((int)$webseller_sessions_id);
				
				$this->data['data']['webseller_sessions_id'] = $webseller_sessions_id;
				$this->data['data']['webseller_sessions_data'] = $tmp;

				if(false === $tmp) {		//SEssion not found - redirect..
						header('Location: index.php?s=cAdminaccounts&error=104');
						die;
				}
		}
		
		
		///////////////////////////////////////////////////////////////////
		// Check User.
		///////////////////////////////////////////////////////////////////
		private function checkUser() {
				$user_id = (int)core()->getGetVar('user_id');
				$tmp = cAdminaccounts::getValidUser((int)$user_id);
				
				$this->data['data']['user_id'] = $user_id;
				$this->data['data']['user_data'] = $tmp;

				if(false === $tmp) {		//User not found - redirect..
						header('Location: index.php?s=cAdminaccounts&error=63');
						die;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Prepare data for the editor.
		///////////////////////////////////////////////////////////////////
		function initData() {
		}
		
		///////////////////////////////////////////////////////////////////
		// getList
		///////////////////////////////////////////////////////////////////
		function getList() {
				$this->data['list'] = $this->loadList();
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page content.
		///////////////////////////////////////////////////////////////////
		public function content() {
				switch($this->action) {
						default:
								$this->drawList();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the list view.
		///////////////////////////////////////////////////////////////////
		function drawList() {
				//Collect messages..
				$info_message = core()->getGetVar('info_message');
				$error = core()->getGetVar('error');
				$success = core()->getGetVar('success');
				
				if(NULL !== $info_message) {
						$this->info_messages[] = $info_message;
				}
				
				if(NULL !== $error) {
						$this->errors[] = $error;
				}
				
				if(NULL !== $success) {
						$this->success_messages[] = $success;
				}
				
				//Render page..
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('INFO_MESSAGES', $this->info_messages);
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('SUCCESS_MESSAGES', $this->success_messages);
				$renderer->render('site/adminwebsellersessionslive/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Loads a list of data..
		///////////////////////////////////////////////////////////////////
		public function loadList() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('webseller_sessions_live') . ' ' .
						'WHERE webseller_sessions_id = :webseller_sessions_id ' . 
						'ORDER BY session_started_on DESC;'
				);
				$db->bind(':webseller_sessions_id', (int)$this->data['data']['webseller_sessions_id']);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						$tmp['seller'] = cAccount::loadUserData($tmp['seller_id']);
						
						$retval[] = $tmp;
				}
				
				return $retval;
		}
}
?>