<?php

class cAdminwebsellersessions extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINWEBSELLERSESSIONS;
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
				if(false === cAccount::adminrightCheck('cAdminwebsellersessions', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=62');
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
			
				$this->action = core()->getGetVar('action');
				$cAdmin = core()->getInstance('cAdmin');
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINWEBSELLERSESSIONS, 'index.php?s=cAdminwebsellersessions' . '&amp;user_id=' . (int)$this->data['data']['user_id']);
				
				switch($this->action) {
						case 'ajax_remove_product':
								$this->initData();
								$this->ajaxRemoveProduct();
								break;
						case 'ajax_upload_products_image':
								$this->initData();
								$this->ajaxUploadProductsImage();
								break;
						case 'ajax_add_product':
								$this->initData();
								$this->ajaxAddProduct();
								break;
						case 'ajax_load_products_list':
								$this->initData();
								$this->ajaxLoadProductsList();
								break;
						case 'ajax_upload_logo_image':
								$this->initData();
								$this->ajaxUploadLogoImage();
								break;
						case 'delete':
								$this->initData();
								$this->getContent();
								$this->delete();
								break;
						case 'confirm_delete':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminwebsellersessions&amp;action=delete&amp;id=' . (int)$this->data['data']['id'] . '&amp;user_id=' . (int)$this->data['data']['user_id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINWEBSELLERSESSIONS_CONFIRM_DELETE, '');
								$this->navbar_title = TEXT_ADMINWEBSELLERSESSIONS_CONFIRM_DELETE;
								break;
						case 'edit':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminwebsellersessions&amp;action=update&amp;id=' . (int)$this->data['data']['id'] . '&amp;user_id=' . (int)$this->data['data']['user_id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINWEBSELLERSESSIONS_EDIT, '');
								$this->navbar_title = TEXT_ADMINWEBSELLERSESSIONS_EDIT;
								break;
						case 'update':
								$this->initData();
								$this->getContent();
								$this->update();
								$cAdmin->appendBreadcrumb(TEXT_ADMINWEBSELLERSESSIONS_EDIT, '');
								$this->navbar_title = TEXT_ADMINWEBSELLERSESSIONS_EDIT;
								break;
						case 'create':
								$this->create();
								$cAdmin->appendBreadcrumb(TEXT_ADMINWEBSELLERSESSIONS_NEW, '');
								$this->navbar_title = TEXT_ADMINWEBSELLERSESSIONS_NEW;
								break;
						case 'new':
								$this->initData();
								$this->data['url'] = 'index.php?s=cAdminwebsellersessions&amp;action=create&amp;user_id=' . (int)$this->data['data']['user_id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINWEBSELLERSESSIONS_NEW, '');
								$this->navbar_title = TEXT_ADMINWEBSELLERSESSIONS_NEW;
								break;
						default:
								$this->getList();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Remove product (from the temporary tables..)
		///////////////////////////////////////////////////////////////////
		private function ajaxRemoveProduct() {
				$products_id = (int)core()->getGetVar('products_id');
				$products_data = cProducts::loadSimpleData($products_id, 1);
				
				if(false === $products_data) {
						cAjax::returnErrorAndQuit('92', 'Product not found.');
						die;
				}
				
				$websellersessions_id = (int)core()->getGetVar('id');
				$this->data['data']['id'] = $websellersessions_id;
				
				$tmp_websellersessions_id = core()->getGetVar('tmp_websellersessions_id');
				$this->data['data']['tmp_websellersessions_id'] = $tmp_websellersessions_id;
				
				cTmpwebsellersessionsproducts::delete($this->data['data']['tmp_websellersessions_id'], $products_id);
				cAjax::returnSuccessAndQuit('1', '');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Upload products image.
		///////////////////////////////////////////////////////////////////
		private function ajaxUploadProductsImage() {
				$products_id = (int)core()->getGetVar('products_id');
				$image_id = (int)core()->getGetVar('image_id');
				$image_type = core()->getGetVar('image_type');
				
				$tmp_websellersessions_id = core()->getGetVar('tmp_websellersessions_id');
				$this->data['data']['tmp_websellersessions_id'] = $tmp_websellersessions_id;
				$uuid = uniqid('img', true);
				
				//get the image data			
				$destination_filename = $products_id . '-' . $image_type . '-' . $tmp_websellersessions_id . $uuid;
				$destination_filename = str_replace('.', '', $destination_filename);		//remove dots - so there is no chance it results in .php (chance is small- but possible attack with flooding.. :\)
				$destination_path = 'data/tmp/tmpuploads/';
				
				//upload the image
				$result = cImage::upload('0', $destination_path, $destination_filename);
				
				//set paths for output..
				$result['destination_path'] = $destination_path;
				$result['server'] = '';
				
				//save the image data
				if(!isset($result['error'])) {
						cTmpwebsellersessionsproductsimages::save(
								$tmp_websellersessions_id, 
								$destination_filename  . $result['file_extension'], 
								$result['original_filename'], 
								$result['file_extension'],
								$products_id,
								$image_id,
								$image_type
						);
				}		
				
				$result = json_encode($result);
				echo $result;
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Add product (to the temporary tables..)
		///////////////////////////////////////////////////////////////////
		private function ajaxAddProduct() {
				$products_id = (int)core()->getGetVar('products_id');
				$products_data = cProducts::loadSimpleData($products_id, 1);
				
				if(false === $products_data) {
						cAjax::returnErrorAndQuit('92', 'Product not found.');
						die;
				}
				
				$websellersessions_id = (int)core()->getGetVar('id');
				$this->data['data']['id'] = $websellersessions_id;
				
				$tmp_websellersessions_id = core()->getGetVar('tmp_websellersessions_id');
				$this->data['data']['tmp_websellersessions_id'] = $tmp_websellersessions_id;
				
				cTmpwebsellersessionsproducts::save($this->data['data']['tmp_websellersessions_id'], $products_id);
				
				$products = $this->getProductsBoxesHtml();
				
				cAjax::returnSuccessAndQuit('1', $products);
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Loads the products boxes html.
		///////////////////////////////////////////////////////////////////
		private function getProductsBoxesHtml() {
				$websellersession_products = cWebsellersessionsproducts::loadProductsBySessionsId($this->data['data']['id']);
				$tmp_websellersession_products = cTmpwebsellersessionsproducts::loadProductsByTmpSessionsId($this->data['data']['tmp_websellersessions_id']);
				
				$websellersession_products = cTmpwebsellersessionsproducts::mergeTmpWithLive($this->data['data']['id'], $websellersession_products, $tmp_websellersession_products);
				$products = '';
				
				foreach($websellersession_products as $product) {
						$products_data = cProducts::loadSimpleData($product['products_id'], 1);
						$products_images_array = cWebsellersessionsproductsimages::loadImagesArray($product['products_id']);
						$products_images_array = cWebsellersessionsproductsimages::checkImagesExistence($this->data['data']['id'], $this->data['data']['user_id'], $product['products_id'], $products_images_array);
						$products_images_array = cTmpwebsellersessionsproductsimages::checkTmpImagesExistence($this->data['data']['tmp_websellersessions_id'], $this->data['data']['user_id'], $product['products_id'], $products_images_array);
						
						//Render html output with all products and images.
						$renderer = core()->getInstance('cRenderer');
						$renderer->setTemplate($this->template);
						$renderer->assign('DATA', $websellersession_products);
						$renderer->assign('PRODUCTS', $product);
						$renderer->assign('PRODUCTS_DATA', $products_data);
						$renderer->assign('PRODUCTS_IMAGES_ARRAY', $products_images_array);
						$product = $renderer->fetch('site/adminwebsellersessions/mvbox-product.html');
						$products .= $product;
				}

				return $products;
		}
		
		///////////////////////////////////////////////////////////////////
		// Ajax: Load products list.
		///////////////////////////////////////////////////////////////////
		private function ajaxLoadProductsList() {
				$products = cProducts::loadListWithoutAttributeProducts(0, 999999, 'id');
				
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				//$renderer->assign('CURRENT_DATALANGUAGE_ID', 1);
				//$renderer->assign('DATALANGUAGES', $this->datalanguages);
				$renderer->assign('PRODUCTS', $products);
				$html_table = $renderer->fetch('site/adminwebsellersessions/product-list.html');
				
				cAjax::returnSuccessAndQuit('1', $html_table);
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Ajax upload logo image.
		///////////////////////////////////////////////////////////////////
		private function ajaxUploadLogoImage() {
				$tmp_websellersessions_id = core()->getGetVar('tmp_websellersessions_id');
				$this->data['data']['tmp_websellersessions_id'] = $tmp_websellersessions_id;
				
				$uuid = uniqid('img', true);
				
				//get the image data			
				$destination_filename = $tmp_websellersessions_id . $uuid;
				$destination_filename = str_replace('.', '', $destination_filename);		//remove dots - so there is no chance it results in .php (chance is small- but possible attack with flooding.. :\)
				$destination_path = 'data/tmp/tmpuploads/';
				
				//upload the image
				$result = cImage::upload('0', $destination_path, $destination_filename);
				
				//set paths for output..
				$result['destination_path'] = $destination_path;
				$result['server'] = '';
				
				//save the image data
				if(!isset($result['error'])) {
						cTmpwebsellersessionimages::saveLogo(
								$tmp_websellersessions_id, 
								$destination_filename  . $result['file_extension'], 
								$result['original_filename'], 
								$result['file_extension']
						);
				}		
				
				$result = json_encode($result);
				echo $result;
				die;
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
				$this->data['data']['id'] = 0;
				$this->data['data']['webseller_session_key'] = cAdminwebsellersessions::randomString(12);
				$this->data['data']['status'] = 1;
				$this->data['data']['session_type'] = 1;
				$this->data['data']['start_date'] = 'Wird gestartet';
				
				$logo = array(
						'file_exists' => false
				);
				
				$this->data['data']['websellersession_logo'] = $logo;
				$this->data['data']['tmp_websellersessions_id'] = cWebsellersessions::generateTmpId('SIDKKR');
				
				$this->data['data']['webseller_machines_id'] = 0;
		}
		
		///////////////////////////////////////////////////////////////////
		// Generate a random string.
		///////////////////////////////////////////////////////////////////		
		public static function randomString($len){
				$result = "";
				$chars = "abcdefghijklmnopqrstuvwxyz-ABCDEFGHIJKLMNOPQRSTUVWXYZ_0123456789";
				$charArray = str_split($chars);
				
				for($i = 0; $i < $len; $i++){
						$randItem = array_rand($charArray);
						$result .= "".$charArray[$randItem];
				}
				return $result;
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
				if(false === cAccount::adminrightCheck('cAdminwebsellersessions', 'DELETE_WEBSELLER_SESSION', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=64');
						die;
				}
				
				//check if user wants to delete or abort the action
				$button_do_not_delete = core()->getPostVar('button_do_not_delete');
				$button_delete = core()->getPostVar('button_delete');
				
				//abort button..
				if($button_do_not_delete !== NULL && $button_do_not_delete === 'not_delete') {
						header('Location: index.php?s=cAdminwebsellersessions&user_id=' . $this->data['data']['user_id'] . '&info_message=2');
						die;
				}
				
				//delete button
				if($button_delete !== NULL && $button_delete === 'delete') {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery('UPDATE ' . $db->table('webseller_sessions') . ' SET status = 0 WHERE id = :id LIMIT 1;');
						$db->bind(':id', (int)$this->data['data']['id']);
						$result = $db->execute();
						
						header('Location: index.php?s=cAdminwebsellersessions&user_id=' . $this->data['data']['user_id'] . '&success=25');
						die;
				}
				
				//unknown operation (we didn't get proper input).
				header('Location: index.php?s=cAdmin&error=65');
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
				$renderer->render('site/adminwebsellersessions/confirm_delete_dialog.html');
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
				
				$renderer->assign('WEBSELLERSESSIONS_EDITOR_TAB_GENERAL', $renderer->fetch('site/adminwebsellersessions/tab_general.html'));
				$renderer->assign('WEBSELLERSESSIONS_EDITOR_TAB_WEBSELLERSESSIONS_LOGO', $renderer->fetch('site/adminwebsellersessions/tab_websellersessions_logo.html'));
				
				$products_boxes_html = $this->getProductsBoxesHtml();
				$renderer->assign('PRODUCTS_BOXES_HTML', $products_boxes_html);				
				$renderer->assign('WEBSELLERSESSIONS_EDITOR_TAB_WEBSELLERSESSIONS_PRODUCTS', $renderer->fetch('site/adminwebsellersessions/tab_websellersessions_products.html'));
				
				$renderer->assign('DATA', $this->data);
				$renderer->render('site/adminwebsellersessions/editor.html');
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
				$renderer->assign('ADMINRIGHT_DELETE_WEBSELLER_SESSION', cAccount::adminrightCheck('cAdminwebsellersessions', 'DELETE_WEBSELLER_SESSION', (int)$_SESSION['user_id']));				
				$renderer->assign('INFO_MESSAGES', $this->info_messages);
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('SUCCESS_MESSAGES', $this->success_messages);
				$renderer->render('site/adminwebsellersessions/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminwebsellersessions&user_id=' . $this->data['data']['user_id'] . '&error=66');
						die;
				}
				
				//get input values
				$data['id'] = (int)$id;
				$data['user_id'] = $this->data['data']['user_id'];
				$data['webseller_session_key'] = core()->getPostVar('webseller_session_key');
				$data['status'] = (int)core()->getPostVar('status');
				$data['session_type'] = (int)core()->getPostVar('session_type');
				$data['tmp_websellersessions_id'] = core()->getPostVar('tmp_websellersessions_id');
				$data['webseller_machines_id'] = (int)core()->getPostVar('webseller_machines_id');
				$this->data['data'] = $data;
				
				//Check input values
				//1. check webseller sessions existence..
				if(false === cAdminwebsellersessions::checkWebsellerSessionExistenceById((int)$data['id'])) {
						header('Location: index.php?s=cAdminwebsellersessions&user_id=' . (int)$this->data['data']['user_id'] . '&error=67');
						die;
				}
				
				//Save general data.
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminwebsellersessions&user_id=' . (int)$this->data['data']['user_id'] . '&error=68');
						die;
				}
				
				//Done. Redirect to success page.	
				header('Location: index.php?s=cAdminwebsellersessions&user_id=' . (int)$this->data['data']['user_id'] . '&action=edit&id=' . $id . '&success=26');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Check if the entry exists (by id)
		///////////////////////////////////////////////////////////////////
		public static function checkWebsellerSessionExistenceById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('webseller_sessions') . ' WHERE id = :id LIMIT 1;');
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
				$data['user_id'] = $this->data['data']['user_id'];
				$data['webseller_session_key'] = core()->getPostVar('webseller_session_key');
				$data['status'] = (int)core()->getPostVar('status');
				$data['session_type'] = (int)core()->getPostVar('session_type');
				$data['tmp_websellersessions_id'] = core()->getPostVar('tmp_websellersessions_id');
				$data['webseller_machines_id'] = (int)core()->getPostVar('webseller_machines_id');
				$this->data['data'] = $data;
				
				//Check input values
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminwebsellersessions&user_id=' . $this->data['data']['user_id'] . '&error=69');
						die;
				}
	
				header('Location: index.php?s=cAdminwebsellersessions&user_id=' . $this->data['data']['user_id'] . '&action=edit&id=' . $id . '&success=27');
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
								'id, user_id, webseller_session_key, status, start_date, session_type, webseller_machines_id ' .
						'FROM ' . $db->table('webseller_sessions') . ' ' .
						'WHERE user_id = :user_id ' . 
						'ORDER BY start_date DESC;'
				);
				$db->bind(':user_id', (int)$this->data['data']['user_id']);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						//Load count of live sessions..
						$tmp['live_session_count'] = cWebsellersessionslive::countLiveSessionsByWebsellersessionsId($tmp['id']);
												
						//Load count of ended live sessions..
						$tmp['ended_session_count'] = cWebsellersessionslive::countEndedSessionsByWebsellersessionsId($tmp['id']);
						$tmp['webseller_machines_data'] = cWebsellermachines::loadEntryById($tmp['webseller_machines_id']);
						
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
						$id =  $this->createInDB($data);
						$data['id'] = (int)$id;
				} else {
						$this->updateInDB($id, $data);
				}
				
				cWebsellersessions::prepareForSavingFiles($data['user_id'], $data['id']);
				
				
				//save images data
				$this->saveLogoImage($data);
				$this->saveProducts($data);
				$this->saveProductImages($data);
				
				return $data['id'];
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Save logo image.
		/////////////////////////////////////////////////////////////////////////////////
		private function saveLogoImage($data) {
				//get logo image data from temp table.
				$tmp_logo_image = cTmpwebsellersessionimages::loadTmpLogoImage($data['tmp_websellersessions_id']);
				
				if(false != $tmp_logo_image && $tmp_logo_image['tmp_logo_images_filename'] != '') {
						cTmpwebsellersessionimages::copyLogoImageFromTempToLive($data['user_id'], $data['id'], $data['tmp_websellersessions_id']);
				}
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Save products.
		/////////////////////////////////////////////////////////////////////////////////
		private function saveProducts($data) {
				$products = cTmpwebsellersessionsproducts::loadProductsByTmpSessionsId($data['tmp_websellersessions_id']);	
				
				foreach($products as $tmp_product_data) {
						if($tmp_product_data['delete_flag'] != 0) {
								cWebsellersessionsproducts::delete($data['id'], $tmp_product_data['products_id']);
						} else {
								cWebsellersessionsproducts::save($data['tmp_websellersessions_id'], $data['id'], $tmp_product_data['products_id']);
						}
				}
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Save product images.
		/////////////////////////////////////////////////////////////////////////////////
		private function saveProductImages($data) {
				//get logo image data from temp table.
				$images = cTmpwebsellersessionsproductsimages::loadTmpImages($data['tmp_websellersessions_id']);
				
				foreach($images as $image) {
						$product_data = cWebsellersessionsproducts::loadProductByWsIdAndProductsId($data['id'], $image['products_id']);
						$tmp_product_data = cTmpwebsellersessionsproducts::loadByWsIdAndTmpWsIdAndProductsId($data['tmp_websellersessions_id'], $image['products_id']);
						
						if(false === $tmp_product_data && false === $product_data) {
								continue;		//No products data: skip this image.
						}
						
						if(false !== $tmp_product_data && $tmp_product_data['delete_flag'] != 0) {
								continue;		//No products data: skip this image.
						}
						
						cTmpwebsellersessionsproductsimages::copyImageFromTempToLive($data['user_id'], $data['id'], $data['tmp_websellersessions_id'], $image['products_id'], $image['image_id'], $image['image_type']);
				}
		}
		
		
		/////////////////////////////////////////////////////////////////////////////////
		// Create content data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public function createInDB($data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('webseller_sessions') . ' ' .
								'(webseller_session_key, status, start_date, session_type, user_id, webseller_machines_id) ' .
						'VALUES ' .
								'(:webseller_session_key, :status, NOW(), :session_type, :user_id, :webseller_machines_id) '
				);
				$db->bind(':webseller_session_key', $data['webseller_session_key']);
				$db->bind(':status', (int)$data['status']);
				$db->bind(':session_type', $data['session_type']);
				$db->bind(':user_id', (int)$this->data['data']['user_id']);
				$db->bind(':webseller_machines_id', (int)$data['webseller_machines_id']);
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
						'UPDATE ' . $db->table('webseller_sessions') . ' SET ' .
								'webseller_session_key = :webseller_session_key, ' .
								'status = :status, ' .
								'session_type = :session_type, ' .
								'user_id = :user_id, ' .
								'webseller_machines_id = :webseller_machines_id ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':webseller_session_key', $data['webseller_session_key']);
				$db->bind(':status', $data['status']);
				$db->bind(':session_type', $data['session_type']);
				$db->bind(':user_id', (int)$this->data['data']['user_id']);
				$db->bind(':webseller_machines_id', (int)$data['webseller_machines_id']);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a content entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function getContent() {
				$id = (int)core()->getGetVar('id');
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT ' .
								'* ' .
						'FROM ' . $db->table('webseller_sessions') . ' ' .
						'WHERE ' .
								'id = :id AND ' .
								'user_id = :user_id'
				);
				$db->bind(':id', (int)$id);
				$db->bind(':user_id', (int)$this->data['data']['user_id']);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				$this->data['data'] = array_merge($this->data['data'], $tmp);
				
				//Check if file exists
				$this->data['data']['websellersession_logo']['file_exists'] = cWebsellersessions::doesLogoExist($this->data['data']);
				$this->data['data']['websellersession_logo']['image_src'] = cWebsellersessions::getLogoFilenameFromLogoInfoArray($this->data['data']);
				$this->data['data']['webseller_machines_data'] = cWebsellermachines::loadEntryById($this->data['data']['webseller_machines_id']);
				$this->data['webseller_machines'] = cWebsellermachines::loadList();
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