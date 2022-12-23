<?php

class cAdmingamedata extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINGAMEDATA;
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
				if(false === cAccount::adminrightCheck('cAdmingamedata', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=INSUFFICIENT_RIGHTS_FOR_MODULE_ADMINGAMEDATA');
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
		function process() {
				$this->datalanguages = cDatalanguages::loadActivated();
				$this->action = core()->getGetVar('action');
				$cAdmin = core()->getInstance('cAdmin');
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINGAMEDATA, 'index.php?s=cAdmingamedata');
				
				switch($this->action) {
						case 'delete':
								$this->initData();
								$this->getContent();
								$this->delete();
								break;
						case 'confirm_delete':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdmingamedata&amp;action=delete&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINGAMEDATA_CONFIRM_DELETE, '');
								$this->navbar_title = TEXT_ADMINGAMEDATA_CONFIRM_DELETE;
								break;
						case 'edit':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdmingamedata&amp;action=update&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINGAMEDATA_EDIT, '');
								$this->navbar_title = TEXT_ADMINGAMEDATA_EDIT;
								break;
						case 'update':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdmingamedata&amp;action=update&amp;id=' . (int)$this->data['data']['id'];
								$this->update();
								$cAdmin->appendBreadcrumb(TEXT_ADMINGAMEDATA_EDIT, '');
								$this->navbar_title = TEXT_ADMINGAMEDATA_EDIT;
								break;
						case 'create':
								$this->create();
								$this->data['url'] = 'index.php?s=cAdmingamedata&amp;action=create';
								$cAdmin->appendBreadcrumb(TEXT_ADMINGAMEDATA_NEW, '');
								$this->navbar_title = TEXT_ADMINGAMEDATA_NEW;
								break;
						case 'new':
								$this->initData();
								$this->data['url'] = 'index.php?s=cAdmingamedata&amp;action=create';
								$cAdmin->appendBreadcrumb(TEXT_ADMINGAMEDATA_NEW, '');
								$this->navbar_title = TEXT_ADMINGAMEDATA_NEW;
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
				$this->data['data']['status'] = 1;
				$this->data['data']['right_answer'] = 0;
				
				$titles = array();
				
				foreach($this->datalanguages as $tmplang) {
						$this->data['data']['titles'] = array(
								array(
										'gamedata_cards_id' => 0,
										'language_id' => $tmplang['id'],
										'title1' => '',
										'title2' => '',
										'language_title' => $tmplang['title']
								)
						);
				}
				
				$this->data['data']['titles'] = $titles;
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
				if(false === cAccount::adminrightCheck('cAdmingamedata', 'DELETE_CARD', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=NOT_ALLOWED_TO_DELETE_ADMINGAMEDATA');
						die;
				}
				
				//check if user wants to delete or abort the action
				$button_do_not_delete = core()->getPostVar('button_do_not_delete');
				$button_delete = core()->getPostVar('button_delete');
				
				//abort button..
				if($button_do_not_delete !== NULL && $button_do_not_delete === 'not_delete') {
						header('Location: index.php?s=cAdmingamedata&info_message=1');
						die;
				}
				
				//delete button
				if($button_delete !== NULL && $button_delete === 'delete') {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery('DELETE FROM ' . $db->table('memory_gamedata_cards') . ' WHERE id = :id;');
						$db->bind(':id', (int)$this->data['data']['id']);
						$result = $db->execute();
						
						header('Location: index.php?s=cAdmingamedata&success=CREATED_SUCCESSFULLY');
						die;
				}
				
				//unknown operation (we didn't get proper input).
				header('Location: index.php?s=cAdmin&error=WRONG_INPUT');
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
				$renderer->render('site/admingamedata/confirm_delete_dialog.html');
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
				$renderer->assign('DATALANGUAGES', $this->datalanguages);
				$renderer->assign('CARDS', cMemorygamedata::loadCardsList());
				$renderer->render('site/admingamedata/editor.html');
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
				$renderer->assign('ADMINRIGHT_DELETE_GAMEDATA', cAccount::adminrightCheck('cAdmingamedata', 'DELETE_ACCOUNT', (int)$_SESSION['user_id']));				
				$renderer->assign('INFO_MESSAGES', $this->info_messages);
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('SUCCESS_MESSAGES', $this->success_messages);
				$renderer->assign('CURRENT_LANGUAGE_ID', 1);
				$renderer->assign('CARDS', cMemorygamedata::loadCardsList());
				$renderer->render('site/admingamedata/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// get expected post values for editor
		///////////////////////////////////////////////////////////////////
		public function getExpectedPostValuesForEditor() {
				$data = array();
				
				$data['status'] = (int)core()->getPostVar('status');
				$data['right_answer'] = (int)core()->getPostVar('right_answer');
				
				return $data;
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdmingamedata&error=NO_ID_PROVIDED_FOR_UPDATE');
						die;
				}
				
				//get input values
				$data = $this->getExpectedPostValuesForEditor();
				$data['id'] = (int)$id;				
				
				$this->data['data'] = $data;
				
				//Check input values
				//1. check email and email existence..
				if(false === cAdmingamedata::checkGamedataExistenceById((int)$data['id'])) {
						header('Location: index.php?s=cAdmingamedata&error=GAME_DATA_NOT_FOUND_BY_ID');
						die;
				}
				
				//Save general data.
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdmingamedata&error=44');
						die;
				}
				
				$this->saveTitlesByPost($id);
				$this->saveImagesByPost($id);
				$this->saveAnimationsByPost($id);
				$this->saveWrongAnimationsByPost($id);
				
				//Done. Redirect to success page.	
				header('Location: index.php?s=cAdmingamedata&action=edit&id=' . $id . '&success=15');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Save titles by post.
		///////////////////////////////////////////////////////////////////
		private function saveTitlesByPost($id) {
				foreach($this->datalanguages as $lang) {
						$title1 = core()->getPostVar('title1_' . $lang['id']);
						$title2 = core()->getPostVar('title2_' . $lang['id']);
						
						if(false === $title1) {
								$title1 = '';
						}
						
						if(false === $title2) {
								$title2 = '';
						}
						
						cMemorygamedata::saveTitle($id, $lang['id'], $title1, $title2);
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Save the images by post.
		///////////////////////////////////////////////////////////////////
		private function saveImagesByPost($id) {
				$this->saveImageByPost($id, 'image_card1', 1);
				$this->saveImageByPost($id, 'image_card2', 2);
				$this->saveImageByPost($id, 'image_card3', 3);
		}
		
		///////////////////////////////////////////////////////////////////
		// Save the images by post.
		///////////////////////////////////////////////////////////////////
		private function saveAnimationsByPost($id) {
				$this->saveAnimationByPost($id, 'animation_card1', 1);
				$this->saveAnimationByPost($id, 'animation_card2', 2);
		}
		
		///////////////////////////////////////////////////////////////////
		// Save the images by post.
		///////////////////////////////////////////////////////////////////
		private function saveWrongAnimationsByPost($id) {
				$this->saveWrongAnimationByPost($id, 'wrong_animation_card1', 1);
				$this->saveWrongAnimationByPost($id, 'wrong_animation_card2', 2);
		}
		
		///////////////////////////////////////////////////////////////////
		// Saves one image by post.
		// Accepts an image post name as paramter, to make enhancements easy.
		///////////////////////////////////////////////////////////////////
		private function saveImageByPost($id, $image_name, $card_number) {
				if(isset($_FILES[$image_name])) {
						$destination_path = 'data/gamedata/cards/';
						$destination_filename =  $id . '_' . $card_number;		//File extension is added by upload function!
						
						//upload and get file type..
						$result = cImage::upload($image_name, $destination_path, $destination_filename);
						
						if(isset($result['file_extension'])) {
								//update in database..
								cMemorygamedata::saveCardsImagesDataInDb($id, $card_number, $result['file_extension']);
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Saves one animation by post.
		// Accepts an animation post name as paramter, to make enhancements easy.
		///////////////////////////////////////////////////////////////////
		private function saveAnimationByPost($id, $image_name, $card_number) {
				if(isset($_FILES[$image_name])) {
						$destination_path = 'data/gamedata/cards/';
						$destination_filename =  'animation_' . $id . '_' . $card_number;		//File extension is added by upload function!
						
						//upload and get file type..
						$result = cAnimation::upload($image_name, $destination_path, $destination_filename);
						
						if(isset($result['file_extension'])) {
								//update in database..
								cMemorygamedata::saveCardsAnimationsDataInDb($id, $card_number, $result['file_extension']);
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Saves one animation by post.
		// Accepts an animation post name as paramter, to make enhancements easy.
		///////////////////////////////////////////////////////////////////
		private function saveWrongAnimationByPost($id, $image_name, $card_number) {
				if(isset($_FILES[$image_name])) {
						$destination_path = 'data/gamedata/cards/';
						$destination_filename =  'wrong_animation_' . $id . '_' . $card_number;		//File extension is added by upload function!
						
						//upload and get file type..
						$result = cAnimation::upload($image_name, $destination_path, $destination_filename);
						
						if(isset($result['file_extension'])) {
								//update in database..
								cMemorygamedata::saveCardsWrongAnimationsDataInDb($id, $card_number, $result['file_extension']);
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Check if the entry exists (by id)
		///////////////////////////////////////////////////////////////////
		public static function checkGamedataExistenceById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('memory_gamedata_cards') . ' WHERE id = :id LIMIT 1;');
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
				$data['status'] = (int)core()->getPostVar('status');
				$data['right_answer'] = (int)core()->getPostVar('right_answer');
				$this->data['data'] = $data;
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdmingamedata&error=45');
						die;
				}
				
				$this->saveTitlesByPost($id);
				$this->saveImagesByPost($id);
				$this->saveAnimationsByPost($id);
				$this->saveWrongAnimationsByPost($id);
	
				header('Location: index.php?s=cAdmingamedata&action=edit&id=' . $id . '&success=16');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Loads a list of data..
		///////////////////////////////////////////////////////////////////
		public function loadList() {
				return cMemorygamedata::loadCardsList();
		}
	
		/////////////////////////////////////////////////////////////////////////////////
		// Save content data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function save($data) {
				$id = (int)$data['id'];
		
				if(0 === $id) {
						return $this->createInDB($data);
				}
				
				$this->updateInDB($id, $data);
				
				return $data['id'];
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Create content data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function createInDB($data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('memory_gamedata_cards') . ' ' .
								'(status, right_answer) ' .
						'VALUES ' .
								'(:status, :right_answer)'
				);
				$db->bind(':status', (int)$data['status']);
				$db->bind(':right_answer', (int)$data['right_answer']);
				$db->execute();
				
				return $db->insertId();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Update navbar data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function updateInDB($id, $data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('memory_gamedata_cards') . ' SET ' .
								'status = :status, ' .
								'right_answer = :right_answer ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':status', (int)$data['status']);
				$db->bind(':right_answer', (int)$data['right_answer']);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a content entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function getContent() {
				$id = (int)core()->getGetVar('id');
				
				$this->data['data'] = cMemorygamedata::loadCardById($id);
		}
}
?>