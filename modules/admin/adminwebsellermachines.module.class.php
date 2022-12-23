<?php

class cAdminwebsellermachines extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINWEBSELLERMACHINES;
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
				if(false === cAccount::adminrightCheck('cAdminwebsellermachines', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=105');
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
				$this->action = core()->getGetVar('action');
				$cAdmin = core()->getInstance('cAdmin');
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINWEBSELLERMACHINES, 'index.php?s=cAdminwebsellermachines');
				
				switch($this->action) {
						case 'edit':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminwebsellermachines&amp;action=update&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINWEBSELLERMACHINES_EDIT, '');
								$this->navbar_title = TEXT_ADMINWEBSELLERMACHINES_EDIT;
								break;
						case 'update':
								$this->initData();
								$this->getContent();
								$this->update();
								$cAdmin->appendBreadcrumb(TEXT_ADMINWEBSELLERMACHINES_EDIT, '');
								$this->navbar_title = TEXT_ADMINWEBSELLERMACHINES_EDIT;
								break;
						case 'create':
								$this->create();
								$cAdmin->appendBreadcrumb(TEXT_ADMINWEBSELLERMACHINES_NEW, '');
								$this->navbar_title = TEXT_ADMINWEBSELLERMACHINES_NEW;
								break;
						case 'new':
								$this->initData();
								$this->data['url'] = 'index.php?s=cAdminwebsellermachines&amp;action=create';
								$cAdmin->appendBreadcrumb(TEXT_ADMINWEBSELLERMACHINES_NEW, '');
								$this->navbar_title = TEXT_ADMINWEBSELLERMACHINES_NEW;
								break;
						default:
								$this->getList();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Prepare data for the editor.
		///////////////////////////////////////////////////////////////////
		public function initData() {
				$this->data['data']['id'] = 0;
				$this->data['data']['title'] = '';
				$this->data['data']['template_folder'] = '';
				
				$this->data['data']['template_folders'] = $this->loadTemplateFolders();
		}
		
		///////////////////////////////////////////////////////////////////
		// Load template folders (scan template folder..)
		///////////////////////////////////////////////////////////////////
		public function loadTemplateFolders() {
				$path = 'data/templates/';
				$content = scandir($path);
				$retval = array();

				foreach($content as $c) {
						if($c == '.' || $c == '..' || !is_dir($path . $c)) {
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
				$this->data['list'] = cWebsellermachines::loadList();
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
				$renderer->render('site/adminwebsellermachines/editor.html');
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
				$renderer->render('site/adminwebsellermachines/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminwebsellermachines&error=106');
						die;
				}
				
				//get input values
				$data['id'] = (int)$id;
				$data['title'] = core()->getPostVar('title');
				$data['template_folder'] = core()->getPostVar('template_folder');
				$this->data['data'] = $data;
				
				//Save general data.
				$id = cWebsellermachine::save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminwebsellermachines&error=107');
						die;
				}
				
				//Done. Redirect to success page.	
				header('Location: index.php?s=cAdminwebsellermachines&action=edit&id=' . $id . '&success=34');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Crate a content entry.
		///////////////////////////////////////////////////////////////////
		function create() {
				$data['id'] = 0;
				$data['title'] = core()->getPostVar('title');
				$data['template_folder'] = core()->getPostVar('template_folder');
				$this->data['data'] = $data;
				
				//Check input values
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminwebsellermachines&error=108');
						die;
				}
	
				header('Location: index.php?s=cAdminwebsellermachines&action=edit&id=' . $id . '&success=35');
				die;
		}
	
		/////////////////////////////////////////////////////////////////////////////////
		// Save content data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function save($data) {
				$id = (int)$data['id'];
		
				if(0 === $id) {
						$id =  cWebsellermachines::createInDB($data);
						$data['id'] = (int)$id;
				} else {
						cWebselelrmachines::updateInDB($id, $data);
				}
				
				return $data['id'];
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a content entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function getContent() {
				$id = (int)core()->getGetVar('id');
				$tmp = cWebsellermachines::loadEntryById($id);
				$this->data['data'] = array_merge($this->data['data'], $tmp);
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page footer.
		///////////////////////////////////////////////////////////////////
		public function footer() {
				$additional_output = 	
						"\n" . '<script src="data/templates/' . $this->template . '/js/mv_file_upload.jquery.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/websellersessions_products.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
}
?>