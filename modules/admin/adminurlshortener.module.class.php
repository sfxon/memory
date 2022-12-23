<?php

class cAdminurlshortener extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINURLSHORTENER;
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
				if(false === cAccount::adminrightCheck('cAdminurlshortener', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=126');
						die;
				}
				
				//We use the Admin module for output.
				cAdmin::setSmallBodyExecutionalHooks();	
				
				//Now set our own hooks below the CMS hooks.
				$core = core();
				core()->setHook('cCore|process', 'process');
				core()->setHook('cRenderer|content', 'content');
		}
	
	
		///////////////////////////////////////////////////////////////////
		// processData
		///////////////////////////////////////////////////////////////////
		public function process() {
				$this->action = core()->getGetVar('action');
				$cAdmin = core()->getInstance('cAdmin');
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINURLSHORTENER, 'index.php?s=cAdminurlshortener');
				
				switch($this->action) {
						case 'delete':
								$this->initData();
								$this->getContent();
								$this->delete();
								break;
						case 'edit':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminurlshortener&amp;action=update&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINURLSHORTENER_EDIT, '');
								$this->navbar_title = TEXT_MODULE_TITLE_ADMINURLSHORTENER_EDIT;
								break;
						case 'stats':
								$this->initData();
								$this->getContent();
								$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINURLSHORTENER_STATS, '');
								$this->navbar_title = TEXT_MODULE_TITLE_ADMINURLSHORTENER_STATS;
								break;
						case 'update':
								$this->initData();
								$this->getContent();
								$this->update();
								$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINURLSHORTENER_EDIT, '');
								$this->navbar_title = TEXT_MODULE_TITLE_ADMINURLSHORTENER_EDIT;
								break;
						case 'create':
								$this->create();
								$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINURLSHORTENER_NEW, '');
								$this->navbar_title = TEXT_MODULE_TITLE_ADMINURLSHORTENER_NEW;
								break;
						case 'new':
								$this->initData();
								$this->data['url'] = 'index.php?s=cAdminurlshortener&amp;action=create';
								$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINURLSHORTENER_NEW, '');
								$this->navbar_title = TEXT_MODULE_TITLE_ADMINURLSHORTENER_NEW;
								break;
						default:
								$this->getList();
								break;
				}
		}

		///////////////////////////////////////////////////////////////////
		// Prepare data for the editor.
		///////////////////////////////////////////////////////////////////
		function initData() {
				$this->data['data']['id'] = 0;
				$this->data['data']['referrer_accounts_id'] = 0;
				$this->data['data']['final_url'] = '';
				$this->data['data']['created_on'] = '0000-00-00 00:00:00';
				$this->data['data']['link_data'] = '';
				$this->data['data']['link_type'] = 0;
				$this->data['data']['status'] = 0;
		}
		
		///////////////////////////////////////////////////////////////////
		// getList
		///////////////////////////////////////////////////////////////////
		function getList() {
				$this->data['list'] = $this->loadList();
		}
		
		///////////////////////////////////////////////////////////////////
		// Suche
		///////////////////////////////////////////////////////////////////
		function search() {
				die( 'search' );
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page content.
		///////////////////////////////////////////////////////////////////
		public function content() {
				switch($this->action) {
						case 'confirm_delete':
								$this->drawConfirmDeleteDialog();
								break;
						case 'create':
						case 'new':
								$this->drawEditor();
								break;
						case 'edit':
								$this->drawEditor();
								break;
						case 'stats':
								$this->drawStats();
								break;
						default:
								$this->drawList();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// "Delete" an entry..
		// We do not really delete any entry. We just flag it,
		// so it does not appear anymore.
		///////////////////////////////////////////////////////////////////
		private function delete() {
				//check if user got rights to delete an entry.
				//check the rights..
				if(false === cAccount::adminrightCheck('cAdminurlshortener', 'DELETE_URLSHORTENER_LINK', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=127');
						die;
				}
				
				//check if user wants to delete or abort the action
				$button_do_not_delete = core()->getPostVar('button_do_not_delete');
				$button_delete = core()->getPostVar('button_delete');
				
				//abort button..
				if($button_do_not_delete !== NULL && $button_do_not_delete === 'not_delete') {
						header('Location: index.php?s=cAdminurlshortener&user_id=' . $this->data['data']['user_id'] . '&info_message=');
						die;
				}
				
				//delete button
				if($button_delete !== NULL && $button_delete === 'delete') {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery('UPDATE ' . $db->table('urlshortener') . ' SET status = 0 WHERE id = :id LIMIT 1;');
						$db->bind(':id', (int)$this->data['data']['id']);
						$result = $db->execute();
						
						header('Location: index.php?s=cAdminurlshortener&user_id=' . $this->data['data']['user_id'] . '&success=40');
						die;
				}
				
				//unknown operation (we didn't get proper input).
				header('Location: index.php?s=cAdmin&error=128');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the editor.
		///////////////////////////////////////////////////////////////////
		function drawConfirmDeleteDialog() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('SITE_URLS', cSite::loadSiteUrls());
				$renderer->assign('NAVBARS', cAdminnavbaredit::loadNavbarList());
				$renderer->assign('ERRORS', $this->errors);
				$renderer->render('site/adminurlshortener/confirm_delete_dialog.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the stats.
		///////////////////////////////////////////////////////////////////
		public function drawStats() {
				$site_url = cSite::loadSiteUrls();

				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('SITE_URLS', cSite::loadSiteUrls());
				$renderer->assign('NAVBARS', cAdminnavbaredit::loadNavbarList());
				$renderer->assign('ERRORS', $this->errors);			
				$renderer->assign('DATA', $this->data);
				$renderer->assign('SITE_URL', $site_url[0]['title']);
				$renderer->assign('STATS', cAdminurlshortener::loadStats($this->data['data']['id']));
				$renderer->render('site/adminurlshortener/stats.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the stats.
		///////////////////////////////////////////////////////////////////
		public function loadStats($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('urlshortener_calls') . ' WHERE urlshortener_id = :urlshortener_id'
				);
				$db->bind(':urlshortener_id', (int)$id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$retval[] = $result->fetchArrayAssoc();
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the editor.
		///////////////////////////////////////////////////////////////////
		function drawEditor() {
				$site_url = cSite::loadSiteUrls();

				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('SITE_URLS', cSite::loadSiteUrls());
				$renderer->assign('NAVBARS', cAdminnavbaredit::loadNavbarList());
				$renderer->assign('ERRORS', $this->errors);			
				$renderer->assign('DATA', $this->data);
				$renderer->assign('SITE_URL', $site_url[0]['title']);
				$renderer->assign('REFERRER_PLATFORMS', cAccount::loadReferrerPlatforms());
				$renderer->render('site/adminurlshortener/editor.html');
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
				$renderer->assign('ADMINRIGHT_DELETE_URLSHORTENER_LINK', cAccount::adminrightCheck('cAdminurlshortener', 'DELETE_URLSHORTENER_LINK', (int)$_SESSION['user_id']));				
				$renderer->assign('INFO_MESSAGES', $this->info_messages);
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('SUCCESS_MESSAGES', $this->success_messages);
				$renderer->render('site/adminurlshortener/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminurlshortener&error=129');
						die;
				}
				
				//get input values
				$data['id'] = (int)$id;
				$data['referrer_accounts_id'] = (int)core()->getPostVar('referrer_accounts_id');
				$data['final_url'] = core()->getPostVar('final_url');
				$data['link_data'] = core()->getPostVar('link_data');
				$data['link_type'] = (int)core()->getPostVar('link_type');
				$data['status'] = (int)core()->getPostVar('status');

				$this->data['data'] = $data;
				
				//Check input values
				//1. check webseller sessions existence..
				if(false === cAdminurlshortener::checkDatabaseEntriesExistenceById((int)$data['id'])) {
						header('Location: index.php?s=cAdminurlshortner&error=130');
						die;
				}
				
				//Save general data.
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminurlshortener&error=131');
						die;
				}
				
				//Done. Redirect to success page.	
				header('Location: index.php?s=cAdminurlshortener&action=edit&id=' . $id . '&success=41');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Check if the entry exists (by id)
		///////////////////////////////////////////////////////////////////
		public static function checkDatabaseEntriesExistenceById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('urlshortener') . ' WHERE id = :id LIMIT 1;');
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				$retval = array();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['id'])) {
						return true;
				}
				
				return false;
		}
		
		///////////////////////////////////////////////////////////////////
		// Crate a content entry.
		///////////////////////////////////////////////////////////////////
		function create() {
				$data['id'] = 0;
				$data['referrer_accounts_id'] = (int)core()->getPostVar('referrer_accounts_id');
				$data['final_url'] = core()->getPostVar('final_url');
				$data['link_data'] = core()->getPostVar('link_data');
				$data['link_type'] = (int)core()->getPostVar('link_type');
				$data['status'] = (int)core()->getPostVar('status');
				$this->data['data'] = $data;
				
				//Check input values
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminurlshortner&error=132');
						die;
				}
	
				header('Location: index.php?s=cAdminurlshortener&action=edit&id=' . $id . '&success=42');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Loads a list of data..
		///////////////////////////////////////////////////////////////////
		public function loadList() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT ' .
								'* ' .
						'FROM ' . $db->table('urlshortener') . ' ' .
						'ORDER BY final_url DESC;'
				);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$tmp['referrer_account'] = cAccount::loadUserData($tmp['referrer_accounts_id']);
						$retval[] = $tmp;
				}
				
				return $retval;
		}
	
		/////////////////////////////////////////////////////////////////////////////////
		// Save content data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function save($data) {
				$id = (int)$data['id'];
		
				if(0 === $id) {
						$id =  cUrlshortener::createInDB($data);
						$data['id'] = (int)$id;
				} else {
						cUrlshortener::updateInDB($id, $data);
				}
				
				return $data['id'];
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a content entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function getContent() {
				$id = (int)core()->getGetVar('id');
				
				$tmp = cUrlshortener::loadEntryById($id);
				
				$this->data['data'] = array_merge($this->data['data'], $tmp);
		}
}
?>