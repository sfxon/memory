<?php

class cAdminwebsellercustomersimport extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINWEBSELLERCUSTOMERSIMPORT;
		var $navbar_id = 0;
		var $errors = array();
		var $info_messages = array();
		var $success_messages = array();
		var $data = array();
		
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
				if(false === cAccount::adminrightCheck('cAdminwebsellercustomersimport', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=125');
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
				$this->action = core()->getGetVar('action');
				$cAdmin = core()->getInstance('cAdmin');
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINWEBSELLERCUSTOMERSIMPORT, 'index.php?s=cAdminwebsellercustomersimport');
				$this->data['url'] = 'index.php?s=cAdminwebsellercustomersimport&amp;action=import';
				$this->initData();
				
				switch($this->action) {
						default:
								$this->import();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Daten initialisieren.
		///////////////////////////////////////////////////////////////////
		private function initData() {
		}
		///////////////////////////////////////////////////////////////////
		// Start the import (if data is provided).
		///////////////////////////////////////////////////////////////////
		private function import() {
				if($this->action == 'import') {
						$customers_id = $this->saveCustomer();
						$sessions_id = $this->saveSession($customers_id);
						$live_sessions_id = $this->saveLiveSession($sessions_id);
						$this->saveSessionProducts($sessions_id, $customers_id);
						
						header('Location: index.php?s=cAdminwebsellercustomersimport&success=' . urlencode('Die Daten wurden erfolgreich importiert'));
						die;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Save customer.
		///////////////////////////////////////////////////////////////////
		private function saveCustomer() {
				$customers_number = core()->getPostVar('customers_number');
				$company = core()->getPostVar('company');
				$firstname = core()->getPostVar('firstname');
				$lastname = core()->getPostVar('lastname');
				$email = core()->getPostVar('email');
				$phone = core()->getPostVar('phone');
				$gender = core()->getPostVar('gender');
				
				//check if customer exists
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('accounts') . ' WHERE customers_number = :customers_number');
				$db->bind(':customers_number', $customers_number);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false !== $tmp) {
						return $tmp['id'];
				}
				
				//If no customer with this number was found - create him.				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('accounts') . ' ' .
								'(account_type, username, email, phone, password, email_language, firstname, ' .
								'lastname, company, notice, created_on, gender, customers_number) ' .
						'VALUES ' .
								'(:account_type, :username, :email, :phone, :password, :email_language, :firstname, ' .
								':lastname, :company, :notice, NOW(), :gender, :customers_number) '
				);
				$db->bind(':account_type', (int)1);
				$db->bind(':username', $company);
				$db->bind(':email', $email);
				$db->bind(':phone', $phone);
				$db->bind(':password', cAdminwebsellersessions::randomString(12));
				$db->bind(':email_language', (int)1);
				$db->bind(':firstname', $firstname);
				$db->bind(':lastname', $lastname);
				$db->bind(':company', $company);
				$db->bind(':notice', 'Durch das System angelegt (Webseller-Kunden Import)');
				$db->bind(':gender', (int)$gender);
				$db->bind(':customers_number', $customers_number);				
				$db->execute();
				
				return $db->insertId();
		}
		
		///////////////////////////////////////////////////////////////////
		// Save session
		///////////////////////////////////////////////////////////////////
		private function saveSession($customers_id) {
				$session_type = (int)core()->getPostVar('session_type');
				$session_key = core()->getPostVar('session_key');
				$webseller_machines_id = (int)core()->getPostVar('webseller_machines_id');
				
				$session_id = cWebsellersessions::createInDB($session_key, $session_type, $customers_id, $webseller_machines_id);
				//Session Logo
				
				cWebsellersessions::prepareForSavingFiles($customers_id, $session_id);
				$this->saveSessionLogo($session_id, $customers_id);
				
				return $session_id;
		}
		
		///////////////////////////////////////////////////////////////////
		// Save session logo.
		///////////////////////////////////////////////////////////////////
		private function saveSessionLogo($session_id, $customers_id) {
				$customers_number = core()->getPostVar('customers_number');
				
				$src = 'import_images/customers/' . $customers_number . '/logo.jpg';
				$dst = 'data/webseller/sessions/' . $customers_id . '/' . $session_id . '/logo.jpg';
				copy($src, $dst);
				
				cWebsellersessions::updateLogoInDB($session_id, 'logo.jpg', '.jpg');
		}
		
		///////////////////////////////////////////////////////////////////
		// Save live session.
		///////////////////////////////////////////////////////////////////
		private function saveLiveSession($sessions_id) {
				//do nothing - live sessions are created by the salesmen now!
		}
		
		///////////////////////////////////////////////////////////////////
		// Save session products.
		///////////////////////////////////////////////////////////////////
		private function saveSessionProducts($sessions_id, $customers_id) {
				//get all products
				$index = 0;
				$products_per_iteration = 10;
				
				while($products = cProducts::loadListWithoutAttributeProducts($index, $products_per_iteration, array('id'))) {
						//save all products to sessions entries
						foreach($products as $product) {
								cWebsellersessionsproducts::save('', $sessions_id, $product['id']);
								$this->saveSessionProductsImages($sessions_id, $product['id'], $customers_id);
								$this->saveSessionProductsFeaturesImages($sessions_id, $product['id'], $customers_id);
						}
						$index += $products_per_iteration;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Save session features images..
		///////////////////////////////////////////////////////////////////
		private function saveSessionProductsFeaturesImages($sessions_id, $products_id, $customers_id) {
				$customers_number = core()->getPostVar('customers_number');
				$src_path = 'import_images/standardbilder/';
				$src_path_customer = 'import_images/customers/' . $customers_number . '/';
				$dst_path = 'data/webseller/sessions/' . $customers_id . '/' . $sessions_id . '/';
				
				$products_data = cProducts::loadComplexProductData($products_id, 1);		//TODO: fix default language..
				
				foreach($products_data['products_data']['products_features'] as $feature) {
						$category_name = $products_data['products_data']['products_categories'][0][0]['categories_string'];
						$features_name = $feature['products_featuresets_values_title'];
						$products_name = $products_data['products_data']['titles'][1][0][1];
						
						//Skip entries we dont have images for.
						if($category_name == "Werbetasche") {
										continue;
						}
						
						
						
						$category_name_clean = $this->makeClearFilesystemName(utf8_decode($category_name));
						$products_name_clean = $this->makeClearFilesystemName(utf8_decode($products_name));
						$features_name_clean = $this->makeClearFilesystemName($features_name);
						
						
						if($feature['products_featuresets_title'] == 'Details') {
								$src_file_location = $src_path . $category_name_clean . '/' . $products_name_clean . '/' . $features_name_clean . '/feature.jpg';
						} else if($feature['products_featuresets_title'] == 'Druck') {
								$src_file_location = $src_path_customer . 'standboegen/' . $products_name_clean . '.jpg';
						}
						
						if(!file_exists($src_file_location)) {
								die('Datei wurde nicht gefunden: ' . $src_file_location);
						}
						
						//Artikelbild-Image-Id abrufen
						$image_id = $feature['id'];
						
						//Original Artikelbild ID laden..
						$dst_filename = 'img-feature-' . $products_id . '-' . $image_id . '.jpg';
		
						//Bild kopieren.
						$dst_file_location = $dst_path . $dst_filename;
						copy($src_file_location, $dst_file_location);
						
						cWebsellersessionsproductsimages::create($sessions_id, $products_id, $image_id, 'feature', $features_name_clean . '.jpg', '.jpg');
				}
		}
				
		///////////////////////////////////////////////////////////////////
		// Save session products images.
		///////////////////////////////////////////////////////////////////
		private function saveSessionProductsImages($sessions_id, $products_id, $customers_id) {
				$customers_number = core()->getPostVar('customers_number');
				$src_path = 'import_images/customers/' . $customers_number . '/';
				$dst_path = 'data/webseller/sessions/' . $customers_id . '/' . $sessions_id . '/';
				
				$products_data = cProducts::loadComplexProductData($products_id, 1);		//TODO: fix default language..
				
				$category_name = $products_data['products_data']['products_categories'][0][0]['categories_string'];
				$products_name = $products_data['products_data']['titles'][1][0][1];
				
				$category_name_clean = $this->makeClearFilesystemName(utf8_decode($category_name));
				$products_name_clean = $this->makeClearFilesystemName(utf8_decode($products_name));
				
				//Skip some products we do not have images for.
				if($category_name_clean == 'bindemappen') {
						return;
				}
				
				$src_file_location = $src_path . $category_name_clean . '/' . $products_name_clean . '.jpg';
				
				if(!file_exists($src_file_location)) {
						die('Datei wurde nicht gefunden: ' . $src_file_location);
				}
				
				//Artikelbild-Image-Id abrufen
				$image_id = $products_data['products_images'][0]['id'];
				
				//Original Artikelbild ID laden..
				$dst_filename = 'img-product-' . $products_id . '-' . $image_id . '.jpg';

				//Bild kopieren.
				$dst_file_location = $dst_path . $dst_filename;
				copy($src_file_location, $dst_file_location);
				
				cWebsellersessionsproductsimages::create($sessions_id, $products_id, $image_id, 'product', $products_name_clean . '.jpg', '.jpg');
		}
		
		///////////////////////////////////////////////////////////////////
		// Save products default images.
		///////////////////////////////////////////////////////////////////
		private function saveProductDefaultImages($products_id, $row) {
				$import_images_folder = 'import_images/standardbilder/';
				$import_images_folder .= $this->makeClearFilesystemName(utf8_decode($this->getRowsValue($row, 'Kategorie 2')));
				$import_images_folder .= '/';
				
				//Attribute (Farben)
				$item_name = $this->makeClearFilesystemName(utf8_decode($this->getRowsValue($row, 'Bezeichnung')));
				
				$subfolder = 'farben/';
				$subfolder .= $item_name;
				$subfolder .= '/';
				
				//Skip some attributes, that get real images!
				switch($item_name) {
						case 'bueroclipjohn':
						case 'bueroclipjohnny':
						case 'register':
						//case 'bindemappen':
								return;
				}
				
				
				
				foreach($this->indexes['Attribute'] as $attribute) {
						$options_value = $row[$attribute[utf8_decode('Ausprägung')]['col_index']];
						
						if($options_value == '') {
								continue;
						}
						
						
						$filename = $this->makeClearFilesystemName($options_value) . '.jpg';
						$options_value = utf8_encode($options_value);
						$path = $import_images_folder . $subfolder . $filename;
						
						if(!file_exists($path)) {
								echo 'Bild wurde nicht gefunden: ';
								echo $path;
								die;
						}
						
						
						$documents_id = cDocument::create();												//Dokument anlegen
						cDocument::saveFileData($documents_id, 'jpg');							//Dokumentdaten anlegen
						
						//get options id
						$option = utf8_encode($row[$attribute['Atribut']['col_index']]);
						$options_data = cProductoptions::loadByTitle(1, $option);
						$options_id = $options_data['products_options_id'];
						
						//get attributes products id.
						$options_values_data = cProductoptionsvalues::loadByTitleAndOptionsId(1, $options_id, $options_value);
						$options_values_id = $options_values_data['products_options_values_id'];
						
						//Load attribute by products id and options values id
						$slave_products_data = cProductattributes::loadByProductsIdAndOptionsValuesId($products_id, $options_values_id);
						$slave_products_id = $slave_products_data['products_id_slave'];
						
						//Save the attributes image
						$images_id = cProductimages::saveData($slave_products_id, $documents_id, 0);		//Artikel-Bild Daten anlegen
						
						//Move the file
						$dst = 'data/images/product_images/' . $documents_id . '.jpg';
						copy($path, $dst);
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Fehlerfreien Namen für Dateisystem erzeugen.
		///////////////////////////////////////////////////////////////////
		private function makeClearFilesystemName($name) {
				$name = utf8_encode($name);
				$name = strtolower($name);
				
				//replace umlauts and sz
				$name = str_replace('ä', 'ae', $name);
				$name = str_replace('Ä', 'ae', $name);
				$name = str_replace('ö', 'oe', $name);
				$name = str_replace('Ö', 'oe', $name);
				$name = str_replace('ü', 'ue', $name);
				$name = str_replace('Ü', 'ue', $name);
				
				$name = str_replace('ß', 'ss', $name);
				
				//Einige andere nervige Zeichen..
				$name = str_replace(' ', '', $name);
				$name = str_replace('-', '', $name);
				$name = str_replace('_', '', $name);
				$name = str_replace('/', '', $name);
				
				return $name;
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page content.
		///////////////////////////////////////////////////////////////////
		public function content() {
				switch($this->action) {
						default:
								$this->drawDialog();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the list view.
		///////////////////////////////////////////////////////////////////
		function drawDialog() {
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
				$renderer->assign('SESSION_KEY', cAdminwebsellersessions::randomString(12));
				$renderer->assign('WEBSELLER_MACHINES', cWebsellermachines::loadList());
				$renderer->render('site/adminwebsellercustomersimport/dialog.html');
		}
}
?>