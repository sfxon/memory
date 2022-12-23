<?php

class cAdminproductcategories extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINPRODUCTCATEGORIES;
		var $navbar_id = 0;
		var $errors = array();
		var $info_messages = array();
		var $success_messages = array();
		
		var $channelSelected = false;
		var $categorySelected = false;
		var $categories_parent_category_id = false;
		var $default_datalanguage = 1;		//TODO: Make this editable in the future.
		
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
				if(false === cAccount::adminrightCheck('cAdminproductcategories', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=79');
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
				$this->data['products'] = array();				
				$this->paths = array();
				
				$this->action = core()->getGetVar('action');
				$cAdmin = core()->getInstance('cAdmin');
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINPRODUCTCATEGORIES, 'index.php?s=cAdminproductcategories');
				
				switch($this->action) {
					case 'ajax_load_channel_view':
								$this->ajaxLoadChannelView();
								break;						
						case 'ajax_add_category':
								$this->ajaxAddCategory();
								break;								
						case 'ajax_delete_image':
								$this->ajaxDeleteImage();
								break;						
						case 'ajax_upload_image':
								$this->ajaxUploadImage();
								break;
						case 'delete':
								$this->initData();
								$this->getContent();
								$this->delete();
								break;
						case 'confirm_delete':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminproductcategories&amp;action=delete&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINACCOUNTS_CONFIRM_DELETE, '');
								$this->navbar_title = TEXT_ADMINACCOUNTS_CONFIRM_DELETE;
								break;
								
						case 'edit':
								$this->initData();
								$this->getChannels();
								$this->getActiveChannel();
								$this->getActiveCategory();
								$this->getCategories();
								$this->initCategoriesEditorData();
								$this->getExtendedCategoriesData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminproductcategories&amp;action=update&amp;channel_id=' . (int)$this->data['channel_id'] . '&amp;category_id=' . (int)$this->data['category_id'];

								$cAdmin->appendBreadcrumb(TEXT_ADMINPRODUCTCATEGORIES_EDIT, '');
								$this->navbar_title = TEXT_ADMINPRODUCTCATEGORIES_EDIT;
								break;
								
						case 'update':
								$this->initData();
								$this->getChannels();
								$this->getActiveChannel();
								$this->getContent();
								$this->update();
								$cAdmin->appendBreadcrumb(TEXT_ADMINPRODUCTCATEGORIES_EDIT, '');
								$this->navbar_title = TEXT_ADMINPRODUCTCATEGORIES_EDIT;
								break;
								
						case 'create':
								$this->getChannels();
								$this->getActiveChannel();
								$this->create();
								$cAdmin->appendBreadcrumb(TEXT_ADMINPRODUCTCATEGORIES_NEW, '');
								$this->navbar_title = TEXT_ADMINPRODUCTCATEGORIES_NEW;
								break;
								
						case 'new':
								$this->initData();
								$this->getChannels();
								$this->getActiveChannel();
								$this->getActiveCategory();
								$this->getCategories();
								$this->initCategoriesEditorData();
								$this->data['url'] = 'index.php?s=cAdminproductcategories&amp;action=create&amp;channel_id=' . (int)$this->data['channel_id'] . '&amp;category_id=' . (int)$this->data['category_id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINPRODUCTCATEGORIES_NEW, '');
								$this->navbar_title = TEXT_ADMINPRODUCTCATEGORIES_NEW;
								break;
								
						default:
								$this->getChannels();
								$this->getActiveChannel();
								$this->getActiveCategory();
								$this->getCategories();
								$this->getList();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Prepare data for the editor.
		///////////////////////////////////////////////////////////////////
		private function initData() {
		}
		
		///////////////////////////////////////////////////////////////////
		// Get content..
		///////////////////////////////////////////////////////////////////
		public function getContent() {
			
		}
		
		///////////////////////////////////////////////////////////////////
		// Prepare data for the editor.
		///////////////////////////////////////////////////////////////////
		private function initCategoriesEditorData() {
				$this->data['data']['id'] = 0;
				
				if(!isset($this->data['category_data'])) {
						$this->data['category_data'] = array();
				}
				
				if(!isset($this->data['category_data']['id'])) {
						$this->data['category_data']['id'] = 0;
				}
				
				if(!isset($this->data['category_data']['sort_order'])) {
						$this->data['category_data']['sort_order'] = 0;
				}
				
				if(!isset($this->data['category_data']['status'])) {
						$this->data['category_data']['status'] = 0;
				}
				
				//prepare datalanguage
				$this->datalanguages = cDatalanguages::loadActivated();
				$this->data['category_data']['descriptions'] = array();
				
				foreach($this->datalanguages as $lang) {
						$this->data['category_data']['descriptions'][] = array(
								'languages_id' => $lang['id'],
								'title' => '',
								'heading_title' => '',
								'description' => '',
								'meta_title' => '',
								'meta_description' => '',
								'meta_keywords' => '',
								'rewrite_url' => ''
						);
				}
				
				//and some more initialisations..
				$this->data['data']['tmp_product_categories_id'] = cAdminproductCategories::generateTmpProductCategoriesId('PC.' . $_SESSION['user_id'] . '.');
				$this->data['images'] = array();
		}
		
		///////////////////////////////////////////////////////////////////
		// Generate tmp product categories id.
		///////////////////////////////////////////////////////////////////
		public static function generateTmpProductCategoriesId($prefix) {
				$retval = $prefix . uniqid('', true);
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////
		// delete an Image via an ajax request (temporary..
		// real delete action is done in submit function for product)
		///////////////////////////////////////////////////////////////////
		public function ajaxDeleteImage() {
				$tmp_product_categories_id = core()->getGetVar('tmp_product_categories_id');
				$tmp_product_categories_image = core()->getGetVar('tmp_product_categories_image');
				$documents_id = (int)core()->getGetVar('documents_id');
				cAdmintmpproductcategoriesimages::delete($tmp_product_categories_id, $tmp_product_categories_image, $documents_id);
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// upload an Image via an ajax request
		///////////////////////////////////////////////////////////////////
		public function ajaxUploadImage() {
				$this->datalanguages = cDatalanguages::loadActivated();
		
				$tmp_product_categories_id = core()->getGetVar('tmp_product_categories_id');
				$uuid = uniqid('img', true);
				
				//get all images alt tags as an array
				$alt_tags = array();
				
				foreach($this->datalanguages as $tmp) {
						$alt_tags[$tmp['id']] = core()->getPostVar('product_categories_image_alt_tag_' . $tmp['id']);
				}
				
				//get all images title tags as an array
				$title_tags = array();
				
				foreach($this->datalanguages as $tmp) {
						$title_tags[$tmp['id']] = core()->getPostVar('product_categories_image_title_tag_' . $tmp['id']);
				}
				
				//get the image data			
				$destination_filename = $tmp_product_categories_id . $uuid;
				$destination_filename = str_replace('.', '', $destination_filename);		//remove dots - so there is no chance it results in .php (chance is small- but possible attack with flooding.. :\)
				$destination_path = 'data/tmp/tmpuploads/';
				
				//upload the image
				$result = cImage::upload('0', $destination_path, $destination_filename);
				
				//set paths for output..
				$result['destination_path'] = $destination_path;
				$result['server'] = '';
				
				//get additional values from post
				$sort_order = core()->getPostVar('sort_order');
				$file_source = core()->getPostVar('image_source');
				$license_type = core()->getPostVar('image_license_type');
				$qualifier = core()->getPostVar('image_qualifier');
				$tmp_product_categories_image = core()->getPostVar('tmp_product_categories_image');
				$documents_id = core()->getPostVar('documents_id');
				
				//if this is an update of an existing image, without upload of a new image.. set the data for it..
				if(isset($result['error']) && !empty($tmp_product_categories_image)) {
						$result['original_filename'] = '';
						$result['file_extension'] = '';
						unset($result['error']);
				}
				
				//if this is an existing document image and no new image has been submitted - we have to return some values...
				if(isset($result['error']) && !empty($documents_id)) {
						$result['original_filename'] = '';
						$result['file_extension'] = cDocument::getFileExtensionById($documents_id);
						$result['destination_path'] = 'data/images/categories_images/';
						unset($result['error']);
				}
				
				//save the image data
				if(!isset($result['error'])) {
						cAdmintmpproductcategoriesimages::save(
								$tmp_product_categories_id,
								$tmp_product_categories_image,
								$destination_filename  . $result['file_extension'],
								$result['original_filename'], $result['file_extension'],
								$alt_tags,
								$title_tags,
								$sort_order,
								$file_source,
								$license_type,
								$qualifier,
								$documents_id
						);
				}		
				
				$result = json_encode($result);
				echo $result;
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// @INFO		Erweiterte Kategoriedaten laden
		///////////////////////////////////////////////////////////////////
		public function getExtendedCategoriesData() {
				$this->data['images'] = cProductcategoriesimages::loadByCategoryId($this->data['category_id']);
				$this->data['data']['id'] = $this->data['category_id'];
				$this->data['category_data']['descriptions'] = cProductcategoriesdescriptions::loadByCategoryId($this->data['category_id']);
		}
		
		///////////////////////////////////////////////////////////////////
		// get active category data
		// the active category is identified by a parameter that is passed
		// by a get parameter
		///////////////////////////////////////////////////////////////////
		public function getActiveCategory() {
				if(false === $this->channelSelected) {
						return;
				}
				
				$category_id = (int)core()->getGetVar('category_id');
				$category_data = cProductcategories::loadByIdAndDefaultLanguage($category_id, $this->default_datalanguage);
				
				if(false !== $category_data || 0 == $category_id) {
						$this->data['category_id'] = $category_id;
						$this->data['category_data'] = $category_data;
						$this->categorySelected = true;
				}
				
				if(false !== $category_data) {
						$tmp = cProductcategories::loadById($category_id);
						$this->categories_parent_category_id = $tmp['parent_id'];
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Get Categories as an array.
		///////////////////////////////////////////////////////////////////
		public function getCategories() {
				if(false === $this->categorySelected) {
						return;
				} else {
						$this->data['parent_category_id'] = $this->data['category_id'];
				}
				
				$this->data['categories_list'] = cProductcategories::loadByChannelIdAndParentCategoryId($this->data['channel_id'], $this->data['parent_category_id'], $this->default_datalanguage);	
				
				$catpath = array();
				cProductcategories::loadTreeReverse($this->data['parent_category_id'], $this->default_datalanguage, $catpath);
				
				foreach($catpath as $cat) {
						$this->paths[] = array(
								'type' => 'category',
								'id' => $cat['id'],
								'title' => $cat['title']
						);
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// get active channel (the one that is selected and offered via
		// get in the url
		///////////////////////////////////////////////////////////////////
		public function getActiveChannel() {
				$channel_id = NULL;
				
				if(isset($_GET['channel_id'])) {
					$channel_id = $_GET['channel_id'];
				}
				
				$channel_data = cChannel::loadByIdAndDefaultLanguage($channel_id, $this->default_datalanguage);
				
				if(0 === $channel_id || "0" === $channel_id) {
						$channel_id = (int)0;
						$channel_data = cChannel::getDefaultChannel();
				}
				
				if(false !== $channel_data) {
						$this->data['channel_id'] = $channel_id;
						$this->data['channel_data'] = $channel_data;
						$this->channelSelected = true;
						$this->paths[] = array(
								'type' => 'channel',
								'id' => $channel_id,
								'title' => $channel_data['title']
						);
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Channel View laden
		///////////////////////////////////////////////////////////////////
		public function ajaxAddCategory() {
				$channel_id = (int)core()->getGetVar('channel_id');
				$parent_id = (int)core()->getGetVar('parent_id');
				$title = core()->getGetVar('title');
				
				//save channel data
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('product_categories') . ' (channel_id, status, parent_id) ' .
						'VALUES(:channel_id, 1, :parent_id)'
				);
				$db->bind(':channel_id', (int)$channel_id);
				$db->bind(':parent_id', (int)$parent_id);
				$db->execute();
				
				$id = $db->insertId();
				
				//save channel description data
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('product_categories_description') . ' (product_categories_id, languages_id, title) ' .
						'VALUES(:product_categories_id, :languages_id, :title)'
				);
				$db->bind(':product_categories_id', $id);
				$db->bind(':languages_id', $this->default_datalanguage);
				$db->bind(':title', $title);
				$db->execute();
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Channel View laden
		///////////////////////////////////////////////////////////////////
		public function ajaxLoadChannelView() {
				$channel_id = (int)core()->getGetVar('channel_id');
				
				//load channel data
				if($channel_id == 0) {
						$channel_data = cChannel::getDefaultChannel();
				} else {
						$channel_data = cChannel::loadByIdAndDefaultLanguage($channel_id, $this->default_datalanguage);
				}
				
				if($channel_data === false) {
						echo 'error: no channel data..';
						die;
				}
				
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('SITE_URLS', cSite::loadSiteUrls());
				$renderer->assign('NAVBARS', cAdminnavbaredit::loadNavbarList());
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('CHANNEL_DATA', $channel_data);
				$renderer->assign('DELETE_BUTTON', 'FALSE');
				$renderer->assign('PRODUCT_CATEGORIES', cProductcategories::loadByChannelId($channel_id, $this->default_datalanguage));
				echo $renderer->fetch('site/adminproductcategories/treeview.html');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// get channels
		///////////////////////////////////////////////////////////////////
		function getChannels() {
				//set default channel??
				$this->data['channels'] = cChannel::loadActiveChannels($this->default_datalanguage);
				$this->data['result_count'] = count($this->data['channels']);
		}
		
		///////////////////////////////////////////////////////////////////
		// Suche
		///////////////////////////////////////////////////////////////////
		function search() {
				die( 'search' );
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// getList
		///////////////////////////////////////////////////////////////////
		function getList() {
				if($this->categorySelected !== false) {
						$this->data['products'] = cProducts::loadProductsByCategory($this->data['category_id'], $this->default_datalanguage);
				}
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
		// Draw the page footer.
		///////////////////////////////////////////////////////////////////
		public function footer() {
				$additional_output = 
						"\n" . '<script src="data/templates/' . $this->template . '/js/products_categories.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/mv_file_upload.jquery.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the editor.
		//	TODO: Set real value for current_datalanguage..
		///////////////////////////////////////////////////////////////////
		function drawEditor() {
				$this->current_datalanguage = 1;
				$this->datalanguages = cDatalanguages::loadActivated();
				
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('CURRENT_DATALANGUAGE_ID', $this->current_datalanguage);
				$renderer->assign('DATALANGUAGES', $this->datalanguages);
				$renderer->assign('CATEGORY_PATHS', $this->paths);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('SITE_URLS', cSite::loadSiteUrls());
				$renderer->assign('NAVBARS', cAdminnavbaredit::loadNavbarList());
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('TAB_GENERAL', $renderer->fetch('site/adminproductcategories/edit_categories_tab_general.html'));
				$renderer->assign('TAB_IMAGES', $renderer->fetch('site/adminproductcategories/edit_categories_tab_images.html'));
				$renderer->render('site/adminproductcategories/editor.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the list view.
		///////////////////////////////////////////////////////////////////
		function drawList() {
				if(true === $this->categorySelected) {
						$this->drawListCategories();
				} else if(true === $this->channelSelected) {
						$this->drawListCategories();
				} else {
						$this->drawListChannels();
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// draw list of the categories
		///////////////////////////////////////////////////////////////////
		function drawListCategories() {
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
				$renderer->assign('CATEGORIES_PARENT_CATEGORY_ID', $this->categories_parent_category_id);
				$renderer->assign('CATEGORY_PATHS', $this->paths);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('ADMINRIGHT_DELETE_PRODUCT_CATEGORIE', cAccount::adminrightCheck('cAdminproductcategories', 'DELETE_PRODUCT_CATEGORIE', (int)$_SESSION['user_id']));
				$renderer->assign('INFO_MESSAGES', $this->info_messages);
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('SUCCESS_MESSAGES', $this->success_messages);
				$renderer->render('site/adminproductcategories/list_categories.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// draw list of the channels
		///////////////////////////////////////////////////////////////////
		function drawListChannels() {
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
				$renderer->assign('CATEGORY_PATHS', $this->paths);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('ADMINRIGHT_DELETE_PRODUCT_CATEGORIE', cAccount::adminrightCheck('cAdminproductcategories', 'DELETE_PRODUCT_CATEGORIE', (int)$_SESSION['user_id']));
				$renderer->assign('INFO_MESSAGES', $this->info_messages);
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('SUCCESS_MESSAGES', $this->success_messages);
				$renderer->render('site/adminproductcategories/list_channels.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// "Delete" an entry..
		// We do not really delete any entry. We just flag it,
		// so it does not appear anymore.
		///////////////////////////////////////////////////////////////////
		private function delete() {
				die('Not supported yet');
				//check if user got rights to delete an entry.
				//check the rights..
				/*if(false === cAccount::adminrightCheck('cAdminproductcategories', 'DELETE_PRODUCT_CATEGORY', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=70');
						die;
				}
				
				//check if user wants to delete or abort the action
				$button_do_not_delete = core()->getPostVar('button_do_not_delete');
				$button_delete = core()->getPostVar('button_delete');
				
				//abort button..
				if($button_do_not_delete !== NULL && $button_do_not_delete === 'not_delete') {
						header('Location: index.php?s=cAdminproductcategories&info_message=3');
						die;
				}
				
				//delete button
				if($button_delete !== NULL && $button_delete === 'delete') {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery('UPDATE ' . $db->table('product_categories') . ' SET status = 0 WHERE id = :id LIMIT 1;');
						$db->bind(':id', (int)$this->data['data']['id']);
						$result = $db->execute();
						
						header('Location: index.php?s=cAdminproductcategories&success=28');
						die;
				}
				
				//unknown operation (we didn't get proper input).
				header('Location: index.php?s=cAdmin&error=71');
				die;*/
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the editor.
		///////////////////////////////////////////////////////////////////
		function drawConfirmDeleteDialog() {
				die('Not supported yet.');
				/*$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('SITE_URLS', cSite::loadSiteUrls());
				$renderer->assign('NAVBARS', cAdminnavbaredit::loadNavbarList());
				$renderer->assign('ERRORS', $this->errors);
				$renderer->render('site/adminproductcategories/confirm_delete_dialog.html');*/
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$categories_data = core()->getAllPosts();
				
				if(!isset($categories_data['id'])) {
						header('Location: index.php?s=cAdminproductcategories&error=76&channel_id=' . $this->data['channel_id'] . '&category_id=' . $this->data['category_id']);
						die;
				}
				
				if(false === cProductcategories::checkForExistence((int)$categories_data['id'])) {
						header('Location: index.php?s=cAdminproductcategories&error=77&channel_id=' . $this->data['channel_id'] . '&category_id=' . $this->data['category_id']);
						die;
				}
				
				$id = cProductcategories::save($categories_data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminproductcategories&error=78');
						die;
				}
	
				header('Location: index.php?' .
						's=cAdminproductcategories&action=edit&channel_id=' . $this->data['channel_id'] . 
						'&category_id=' . $id . '&success=29'
				);
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Crate a content entry.
		///////////////////////////////////////////////////////////////////
		function create() {
				$categories_data = core()->getAllPosts();				
				$categories_data['parent_id'] = (int)core()->getGetVar('category_id');
				
				$categories_data['id'] = 0;				
				$id = cProductcategories::save($categories_data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminproductcategories&error=75');
						die;
				}
	
				header('Location: index.php?' .
						's=cAdminproductcategories&action=edit&channel_id=' . $this->data['channel_id'] . 
						'&category_id=' . $id . '&success=30'
				);
				die;
		}
}
?>