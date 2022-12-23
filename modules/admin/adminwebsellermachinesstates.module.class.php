<?php

class cAdminwebsellermachinesstates extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINWEBSELLERMACHINESSTATES;
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
				if(false === cAccount::adminrightCheck('cAdminwebsellermachinesstates', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=109');
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
				$this->getWebsellerMachine();
			
				$this->action = core()->getGetVar('action');
				$cAdmin = core()->getInstance('cAdmin');
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINWEBSELLERMACHINES, 'index.php?s=cAdminwebsellermachinesstates');
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINWEBSELLERMACHINESSTATES, 'index.php?s=cAdminwebsellermachinesstates&amp;webseller_machines_id=' . $this->data['webseller_machines_id']);
				
				switch($this->action) {
						case 'edit':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminwebsellermachinesstates&amp;action=update&amp;id=' . (int)$this->data['data']['id'] . '&amp;webseller_machines_id=' . $this->data['webseller_machines_id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINWEBSELLERMACHINESSTATES_EDIT, '');
								$this->navbar_title = TEXT_ADMINWEBSELLERMACHINESSTATES_EDIT;
								break;
						case 'update':
								$this->initData();
								$this->getContent();
								$this->update();
								$cAdmin->appendBreadcrumb(TEXT_ADMINWEBSELLERMACHINESSTATES_EDIT, '');
								$this->navbar_title = TEXT_ADMINWEBSELLERMACHINESSTATES_EDIT;
								break;
						case 'create':
								$this->create();
								$cAdmin->appendBreadcrumb(TEXT_ADMINWEBSELLERMACHINESSTATES_NEW, '');
								$this->navbar_title = TEXT_ADMINWEBSELLERMACHINESSTATES_NEW;
								break;
						case 'new':
								$this->initData();
								$this->data['url'] = 'index.php?s=cAdminwebsellermachinesstates&amp;action=create&amp;webseller_machines_id=' . $this->data['webseller_machines_id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINWEBSELLERMACHINESSTATES_NEW, '');
								$this->navbar_title = TEXT_ADMINWEBSELLERMACHINESSTATES_NEW;
								break;
						default:
								$this->getList();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Get webseller machine.
		// Verifies also, that the GET param for the machine is valid!
		///////////////////////////////////////////////////////////////////
		public function getWebsellerMachine() {
				$webseller_machines_id = (int)core()->getGetVar('webseller_machines_id');
				
				if(0 === $webseller_machines_id) {
						header('Location: index.php?s=cAdminwebsellermachines&error=110');
						die;
				}
				
				$webseller_machines_data = cWebsellermachines::loadEntryById($webseller_machines_id);
				
				if(false === $webseller_machines_data) {
						header('Location: index.php?s=cAdminwebsellermachines&error=111');
						die;
				}
				
				//check the existence of the template folder..
				if(false === cWebsellermachines::checkTemplateFolder($webseller_machines_data['template_folder'])) {
						header('Location: index.php?s=cAdminwebsellermachines&error=112');
						die;
				}
				
				$this->data['webseller_machines_id'] = $webseller_machines_id;
				$this->data['webseller_machines_data'] = $webseller_machines_data;
		}
		
		///////////////////////////////////////////////////////////////////
		// Prepare data for the editor.
		///////////////////////////////////////////////////////////////////
		public function initData() {
				$this->data['data']['id'] = 0;
				$this->data['data']['state_machines_id'] = $this->data['webseller_machines_id'];
				$this->data['data']['level'] = 0;
				$this->data['data']['parent_state_id'] = 0;
				$this->data['data']['status'] = 0;
				$this->data['data']['title'] = '';
				$this->data['data']['template_file'] = '';
				$this->data['data']['template_folder'] = 'data/templates/' . $this->data['webseller_machines_data']['template_folder'] . '/state_pages';
				$this->data['data']['template_files'] = $this->loadTemplateFiles($this->data['data']['template_folder']);
		}
		
		///////////////////////////////////////////////////////////////////
		// Load template folders (scan template folder..)
		///////////////////////////////////////////////////////////////////
		public function loadTemplateFiles($template_folder) {
				$content = scandir($template_folder);
				$retval = array();

				foreach($content as $c) {
						if($c == '.' || $c == '..' || !is_file($template_folder . '/' . $c)) {
								continue;
						}
						
						$retval[] = $c;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////
		// getList
		///////////////////////////////////////////////////////////////////
		function getList() {
				$this->data['list'] = cWebsellermachinesstates::loadList();
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
						case 'create':
						case 'new':
								$this->drawEditor();
								break;
						case 'edit':
								$this->drawEditor();
								break;
						default:
								$this->drawList();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the editor.
		///////////////////////////////////////////////////////////////////
		function drawEditor() {			
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('SITE_URLS', cSite::loadSiteUrls());
				$renderer->assign('NAVBARS', cAdminnavbaredit::loadNavbarList());
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('DATA', $this->data);
				$renderer->render('site/adminwebsellermachinesstates/editor.html');
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
				$renderer->render('site/adminwebsellermachinesstates/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminwebsellermachinesstates&error=113');
						die;
				}
				
				//get input values
				$data['id'] = (int)$id;
				$data['state_machines_id'] = (int)core()->getPostVar('state_machines_id');
				$data['level'] = (int)core()->getPostVar('level');
				$data['parent_state_id'] = (int)core()->getPostVar('parent_state_id');
				$data['status'] = (int)core()->getPostVar('status');
				$data['title'] = core()->getPostVar('title');
				$data['template_file'] = core()->getPostVar('template_file');
				$this->data['data'] = $data;
				
				//Save general data.
				$id = cWebsellermachinesstates::updateInDB($id, $data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminwebsellermachinesstates&error=114');
						die;
				}
				
				//Done. Redirect to success page.	
				header('Location: index.php?s=cAdminwebsellermachinesstates&action=edit&id=' . $id . '&webseller_machines_id=' . $this->data['webseller_machines_id'] . '&success=34');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Crate a content entry.
		///////////////////////////////////////////////////////////////////
		function create() {
				$data['id'] = 0;
				$data['state_machines_id'] = (int)core()->getPostVar('state_machines_id');
				$data['level'] = (int)core()->getPostVar('level');
				$data['parent_state_id'] = (int)core()->getPostVar('parent_state_id');
				$data['status'] = (int)core()->getPostVar('status');
				$data['title'] = core()->getPostVar('title');
				$data['template_file'] = core()->getPostVar('template_file');
				$this->data['data'] = $data;
				
				//Check input values
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminwebsellermachinesstates&error=115');
						die;
				}
	
				header('Location: index.php?s=cAdminwebsellermachinesstates&action=edit&id=' . $id . '&webseller_machines_id=' . $this->data['webseller_machines_id'] . '&success=37');
				die;
		}
	
		/////////////////////////////////////////////////////////////////////////////////
		// Save content data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function save($data) {
				$id = (int)$data['id'];
		
				if(0 === $id) {
						$id =  cWebsellermachinesstates::createInDB($data);
						$data['id'] = (int)$id;
				} else {
						cWebselelrmachinesstates::updateInDB($id, $data);
				}
				
				return $data['id'];
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a content entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function getContent() {
				$id = (int)core()->getGetVar('id');
				$tmp = cWebsellermachinesstates::loadEntryById($id);
				$this->data['data'] = array_merge($this->data['data'], $tmp);
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page footer.
		///////////////////////////////////////////////////////////////////
		public function footer() {
				/*
				$additional_output = 	
						"\n" . '<script src="data/templates/' . $this->template . '/js/mv_file_upload.jquery.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/websellersessions_products.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
				*/
		}
}
?>