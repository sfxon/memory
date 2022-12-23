<?php

class cAdminwebsellermachinesstatesobjects extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINWEBSELLERMACHINESSTATESOBJECTS;
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
				if(false === cAccount::adminrightCheck('cAdminwebsellermachinesstatesobjects', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=116');
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
				$this->getWebsellerMachinesState();
			
				$this->action = core()->getGetVar('action');
				$cAdmin = core()->getInstance('cAdmin');
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINWEBSELLERMACHINES, 'index.php?s=cAdminwebsellermachinesstates');
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINWEBSELLERMACHINESSTATES, 'index.php?s=cAdminwebsellermachinesstates&amp;webseller_machines_id=' . $this->data['webseller_machines_id']);
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINWEBSELLERMACHINESSTATESOBJECTS, 'index.php?s=cAdminwebsellermachinesstatesobjects&amp;webseller_machines_id=' . $this->data['webseller_machines_id'] . '&amp;webseller_machines_states_id=' . $this->data['webseller_machines_states_id']);
				
				switch($this->action) {
						case 'edit':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminwebsellermachinesstatesobjects&amp;action=update&amp;id=' . (int)$this->data['data']['id'] . '&amp;webseller_machines_id=' . $this->data['webseller_machines_id'] . '&amp;webseller_machines_states_id=' . $this->data['webseller_machines_states_id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINWEBSELLERMACHINESSTATESOBJECTS_EDIT, '');
								$this->navbar_title = TEXT_ADMINWEBSELLERMACHINESSTATESOBJECTS_EDIT;
								break;
						case 'update':
								$this->initData();
								$this->getContent();
								$this->update();
								$cAdmin->appendBreadcrumb(TEXT_ADMINWEBSELLERMACHINESSTATESOBJECTS_EDIT, '');
								$this->navbar_title = TEXT_ADMINWEBSELLERMACHINESSTATESOBJECTS_EDIT;
								break;
						case 'create':
								$this->create();
								$cAdmin->appendBreadcrumb(TEXT_ADMINWEBSELLERMACHINESSTATESOBJECTS_NEW, '');
								$this->navbar_title = TEXT_ADMINWEBSELLERMACHINESSTATESOBJECTS_NEW;
								break;
						case 'new':
								$this->initData();
								$this->data['url'] = 'index.php?s=cAdminwebsellermachinesstatesobjects&amp;action=create&amp;webseller_machines_id=' . $this->data['webseller_machines_id'] . '&amp;webseller_machines_states_id=' . $this->data['webseller_machines_states_id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINWEBSELLERMACHINESSTATESOBJECTS_NEW, '');
								$this->navbar_title = TEXT_ADMINWEBSELLERMACHINESSTATESOBJECTS_NEW;
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
		// Get webseller machines state.
		// Verifies also, that the GET param for the machine state is valid!
		///////////////////////////////////////////////////////////////////
		public function getWebsellerMachinesState() {
				$webseller_machines_states_id = (int)core()->getGetVar('webseller_machines_states_id');
				
				if(0 === $webseller_machines_states_id) {
						header('Location: index.php?s=cAdminwebsellermachinesstates&error=117');
						die;
				}
				
				$webseller_machines_states_data = cWebsellermachinesstates::loadEntryById($webseller_machines_states_id);
				
				if(false === $webseller_machines_states_data) {
						header('Location: index.php?s=cAdminwebsellermachinesstates&error=118');
						die;
				}
				
				//check the existence of the template folder..
				if(false === cWebsellermachinesstates::checkTemplateFile($this->data['webseller_machines_data']['template_folder'], $webseller_machines_states_data['template_file'])) {
						header('Location: index.php?s=cAdminwebsellermachines&error=119');
						die;
				}
				
				$this->data['webseller_machines_states_id'] = $webseller_machines_states_id;
				$this->data['webseller_machines_states_data'] = $webseller_machines_states_data;
		}
		
		///////////////////////////////////////////////////////////////////
		// Prepare data for the editor.
		///////////////////////////////////////////////////////////////////
		public function initData() {
				$this->data['data']['id'] = 0;
				$this->data['data']['webseller_machines_states_id'] = (int)$this->data['webseller_machines_states_id'];
				$this->data['data']['object_identifier'] = '';
		}
		
		///////////////////////////////////////////////////////////////////
		// getList
		///////////////////////////////////////////////////////////////////
		function getList() {
				$this->data['list'] = cWebsellermachinesstatesobjects::loadListByWebsellerMachinesStatesId($this->data['webseller_machines_states_id']);
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
				$renderer->render('site/adminwebsellermachinesstatesobjects/editor.html');
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
				$renderer->render('site/adminwebsellermachinesstatesobjects/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminwebsellermachines&error=120');
						die;
				}
				
				//get input values
				$data['id'] = (int)$id;
				$data['webseller_machines_states_id'] = (int)core()->getPostVar('webseller_machines_states_id');
				$data['object_identifier'] = core()->getPostVar('object_identifier');
				$this->data['data'] = $data;
				
				//Save general data.
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminwebsellermachines&error=121');
						die;
				}
				
				//Done. Redirect to success page.	
				header('Location: index.php?s=cAdminwebsellermachinesstatesobjects&action=edit&id=' . $id . '&webseller_machines_id=' . $this->data['webseller_machines_id'] . '&webseller_machines_states_id=' . $this->data['webseller_machines_states_id'] . '&success=38');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Crate a content entry.
		///////////////////////////////////////////////////////////////////
		function create() {
				$data['id'] = 0;
				$data['webseller_machines_states_id'] = (int)core()->getPostVar('webseller_machines_states_id');
				$data['object_identifier'] = core()->getPostVar('object_identifier');
				$this->data['data'] = $data;
				
				//Check input values
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminwebsellermachines&error=122');
						die;
				}
	
				header('Location: index.php?s=cAdminwebsellermachinesstatesobjects&action=edit&id=' . $id . '&webseller_machines_id=' . $this->data['webseller_machines_id'] . '&webseller_machines_states_id=' . $this->data['webseller_machines_states_id'] . '&success=39');
				die;
		}
	
		/////////////////////////////////////////////////////////////////////////////////
		// Save content data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function save($data) {
				$id = (int)$data['id'];
		
				if(0 === $id) {
						$id =  cWebsellermachinesstatesobjects::createInDB($data);
						$data['id'] = (int)$id;
				} else {
						cWebsellermachinesstatesobjects::updateInDB($id, $data);
				}
				
				return $data['id'];
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a content entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function getContent() {
				$id = (int)core()->getGetVar('id');
				$tmp = cWebsellermachinesstatesobjects::loadEntryById($id);
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