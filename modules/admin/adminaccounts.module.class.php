<?php

class cAdminaccounts extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINACCOUNTS;
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
				if(false === cAccount::adminrightCheck('cAdminaccounts', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=41');
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
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINACCOUNTS, 'index.php?s=cAdminaccounts');
				
				switch($this->action) {
						case 'delete':
								$this->initData();
								$this->getContent();
								$this->delete();
								break;
						case 'confirm_delete':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminaccounts&amp;action=delete&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINACCOUNTS_CONFIRM_DELETE, '');
								$this->navbar_title = TEXT_ADMINACCOUNTS_CONFIRM_DELETE;
								break;
						case 'edit':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminaccounts&amp;action=update&amp;id=' . (int)$this->data['data']['id'];
								$cAdmin->appendBreadcrumb(TEXT_ADMINACCOUNTS_EDIT, '');
								$this->navbar_title = TEXT_ADMINACCOUNTS_EDIT;
								break;
						case 'update':
								$this->initData();
								$this->getContent();
								$this->data['url'] = 'index.php?s=cAdminaccounts&amp;action=update&amp;id=' . (int)$this->data['data']['id'];
								$this->update();
								$cAdmin->appendBreadcrumb(TEXT_ADMINACCOUNTS_EDIT, '');
								$this->navbar_title = TEXT_ADMINACCOUNTS_EDIT;
								break;
						case 'create':
								$this->create();
								$this->data['url'] = 'index.php?s=cAdminaccounts&amp;action=create';
								$cAdmin->appendBreadcrumb(TEXT_ADMINACCOUNTS_NEW, '');
								$this->navbar_title = TEXT_ADMINACCOUNTS_NEW;
								break;
						case 'new':
								$this->initData();
								$this->data['url'] = 'index.php?s=cAdminaccounts&amp;action=create';
								$cAdmin->appendBreadcrumb(TEXT_ADMINACCOUNTS_NEW, '');
								$this->navbar_title = TEXT_ADMINACCOUNTS_NEW;
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
				$this->data['data']['account_type'] = 0;
				$this->data['data']['username'] = '';
				$this->data['data']['email'] = '';
				$this->data['data']['phone'] = '';
				$this->data['data']['email_language'] = 1;
				$this->data['data']['firstname'] = '';
				$this->data['data']['lastname'] = '';
				$this->data['data']['company'] = '';
				$this->data['data']['street_address'] = '';
				$this->data['data']['street_address_house_number'] = '';
				$this->data['data']['zip'] = '';
				$this->data['data']['city'] = '';
				$this->data['data']['country'] = '';
				$this->data['data']['notice'] = '';
				$this->data['data']['created_on'] = '0000-00-00 00:00:00';
				$this->data['data']['mqllock_count'] = 0;
				$this->data['data']['is_supplier'] = 0;
				$this->data['data']['is_manufacturer'] = 0;
				$this->data['data']['is_referrer_platform'] = 0;
				$this->data['data']['gender'] = 1;
				$this->data['data']['seller_status'] = 1;
				$this->data['data']['company_position'] = '';
				$this->data['data']['email_footer'] = '';
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
				if(false === cAccount::adminrightCheck('cAdminaccounts', 'DELETE_ACCOUNT', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=60');
						die;
				}
				
				//check if user wants to delete or abort the action
				$button_do_not_delete = core()->getPostVar('button_do_not_delete');
				$button_delete = core()->getPostVar('button_delete');
				
				//abort button..
				if($button_do_not_delete !== NULL && $button_do_not_delete === 'not_delete') {
						header('Location: index.php?s=cAdminaccounts&info_message=1');
						die;
				}
				
				//delete button
				if($button_delete !== NULL && $button_delete === 'delete') {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery('UPDATE ' . $db->table('accounts') . ' SET account_type = 0 WHERE id = :id LIMIT 1;');
						$db->bind(':id', (int)$this->data['data']['id']);
						$result = $db->execute();
						
						header('Location: index.php?s=cAdminaccounts&success=24');
						die;
				}
				
				//unknown operation (we didn't get proper input).
				header('Location: index.php?s=cAdmin&error=61');
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
				$renderer->render('site/adminaccounts/confirm_delete_dialog.html');
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
				$renderer->render('site/adminaccounts/editor.html');
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
				$renderer->assign('ADMINRIGHT_DELETE_ACCOUNT', cAccount::adminrightCheck('cAdminaccounts', 'DELETE_ACCOUNT', (int)$_SESSION['user_id']));				
				$renderer->assign('INFO_MESSAGES', $this->info_messages);
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('SUCCESS_MESSAGES', $this->success_messages);
				$renderer->render('site/adminaccounts/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		public function update() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {
						header('Location: index.php?s=cAdminaccounts&error=42');
						die;
				}
				
				//get input values
				$data['id'] = (int)$id;
				$data['account_type'] = (int)core()->getPostVar('account_type');
				$data['username'] = core()->getPostVar('username');
				$data['password'] = core()->getPostVar('password');
				$data['email'] = core()->getPostVar('email');
				$data['phone'] = core()->getPostVar('phone');
				$data['email_language'] = core()->getPostVar('email_language');
				$data['firstname'] = core()->getPostVar('firstname');
				$data['lastname'] = core()->getPostVar('lastname');
				$data['company'] = core()->getPostVar('company');
				$data['street_address'] = core()->getPostVar('street_address');
				$data['street_address_house_number'] = core()->getPostVar('street_address_house_number');
				$data['zip'] = core()->getPostVar('zip');
				$data['city'] = core()->getPostVar('city');
				$data['country'] = core()->getPostVar('country');
				$data['notice'] = core()->getPostVar('notice');
				$data['is_supplier'] = (int)core()->getPostVar('is_supplier');
				$data['is_manufacturer'] = (int)core()->getPostVar('is_manufacturer');
				$data['is_referrer_platform'] = (int)core()->getPostVar('is_referrer_platform');
				$data['gender'] = (int)core()->getPostVar('gender');
				$data['seller_status'] = (int)core()->getPostVar('seller_status');
				$data['company_position'] = core()->getPostVar('company_position');
				$data['email_footer'] = core()->getPostVar('email_footer');
				
				$this->data['data'] = $data;
				
				//Check input values
				//1. check email and email existence..
				if(false === cAdminaccounts::checkAccountExistenceById((int)$data['id'])) {
						header('Location: index.php?s=cAdminaccounts&error=43');
						die;
				}
				
				if(false === cAdminaccounts::validateEmailAddress($data['email'])) {
						$this->errors['email_format'] = true;
						return false;
				}
				
				$email_account_id = cAdminaccounts::getAccountIdByEmail($data['email']);
				
				if($email_account_id !== false && (int)$email_account_id != (int)$data['id']) {
						$this->errors['email_exists'] = true;
						return false;
				}
				
				//Save general data.
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminaccounts&error=44');
						die;
				}
				
				//2nd: check Password and update.. (We only save it, when a new password was submitted.
				if(strlen($data['password']) > 0) {
						$tmp_errors = cAdminaccounts::validatePassword($data['password']);
				
						if(count($tmp_errors) != 0) {
								$this->errors = array_merge($this->errors, $tmp_errors);
								return false;
						}
						
						cAdminaccounts::updatePassword($id, $data['password']);
				}
				
				//Done. Redirect to success page.	
				header('Location: index.php?s=cAdminaccounts&action=edit&id=' . $id . '&success=15');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Update the password.
		///////////////////////////////////////////////////////////////////
		public static function updatePassword($id, $password) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('UPDATE ' . $db->table('accounts') . ' SET password = :password WHERE id = :id LIMIT 1;');
				$db->bind(':password', password_hash($password, PASSWORD_BCRYPT));
				$db->bind(':id', (int)$id);
				$result = $db->execute();
		}
		
		///////////////////////////////////////////////////////////////////
		// Check if the entry exists (by id)
		///////////////////////////////////////////////////////////////////
		public static function checkAccountExistenceById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('accounts') . ' WHERE id = :id LIMIT 1;');
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
		// validate the email address.
		// pregmatch as of http://stackoverflow.com/questions/201323/using-a-regular-expression-to-validate-an-email-address
		//
		// Context:
		//	A valid e-mail address is a string that matches the ABNF production […].
		//	Note: This requirement is a willful violation of RFC 5322, which defines a syntax for e-mail addresses that is simultaneously too strict (before the "@" character), too vague (after the "@" character), and too lax (allowing comments, whitespace characters, and quoted strings in manners unfamiliar to most users) to be of practical use here.
		//	The following JavaScript- and Perl-compatible regular expression is an implementation of the above definition.
		//
		// TODO:
		//	This is a copy of this function in cMail.
		//	All calls of this function should be changed to be done by the other module (cMail).
		//	Then remove the function in this module.
		///////////////////////////////////////////////////////////////////
		public static function validateEmailAddress($email_address) {
				$output_array = array();
				preg_match("/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/", $email_address, $output_array);
				
				if(count($output_array) > 0) {
						return true;
				}
				
				return false;
		}
		
		///////////////////////////////////////////////////////////////////
		// Get account ID by email.
		///////////////////////////////////////////////////////////////////
		public static function getAccountIdByEmail($email_address) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('accounts') . ' WHERE LOWER(email) = LOWER(:email_address) LIMIT 1;');
				$db->bind(':email_address', $email_address);
				$result = $db->execute();
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['id'])) {
						return $tmp['id'];
				}
				
				return false;
		}
		
		///////////////////////////////////////////////////////////////////
		// Validate a password
		///////////////////////////////////////////////////////////////////
		public static function validatePassword($password) {
				$errors = array();
				
				$uppercase = preg_match('@[A-Z]@', $password);
				$lowercase = preg_match('@[a-z]@', $password);
				$number    = preg_match('@[0-9]@', $password);
				
				if(!$uppercase) {
						$errors['password_no_uppercase_char'] = true;
				}
				
				if(!$lowercase) {
						$errors['password_no_lowercase_char'] = true;
				}
				
				if(!$number) {
						$errors['password_no_number'] = true;
				}
				
				if(strlen($password) < 8) {
						$errors['password_length'] = true;
				}
				
				return $errors;
		}
		
		///////////////////////////////////////////////////////////////////
		// Crate a content entry.
		///////////////////////////////////////////////////////////////////
		function create() {
				$data['id'] = 0;
				$data['account_type'] = (int)core()->getPostVar('account_type');
				$data['username'] = core()->getPostVar('username');
				$data['password'] = core()->getPostVar('password');
				$data['email'] = core()->getPostVar('email');
				$data['phone'] = core()->getPostVar('phone');
				$data['email_language'] = core()->getPostVar('email_language');
				$data['firstname'] = core()->getPostVar('firstname');
				$data['lastname'] = core()->getPostVar('lastname');
				$data['company'] = core()->getPostVar('company');
				$data['street_address'] = core()->getPostVar('street_address');
				$data['street_address_house_number'] = core()->getPostVar('street_address_house_number');
				$data['zip'] = core()->getPostVar('zip');
				$data['city'] = core()->getPostVar('city');
				$data['country'] = core()->getPostVar('country');
				$data['notice'] = core()->getPostVar('notice');
				$data['created_on'] = date('Y-m-d H:i:s');
				$data['is_supplier'] = (int)core()->getPostVar('is_supplier');
				$data['is_manufacturer'] = (int)core()->getPostVar('is_manufacturer');
				$data['is_referrer_platform'] = (int)core()->getPostVar('is_referrer_platform');
				$data['gender'] = (int)core()->getPostVar('gender');
				$data['seller_status'] = (int)core()->getPostVar('seller_status');
				$data['company_position'] = (int)core()->getPostVar('company_position');
				$data['email_footer'] = core()->getPostVar('email_footer');
				
				$this->data['data'] = $data;
				
				//Check input values
				//1. check email and email existence..
				if(false === cAdminaccounts::validateEmailAddress($data['email'])) {
						$this->errors['email_format'] = true;
						return false;
				}
				
				$email_account_id = cAdminaccounts::getAccountIdByEmail($data['email']);

				if($email_account_id !== false) {
						$this->errors['email_exists'] = true;
						return false;
				}
				
				//2nd: check Password
				$tmp_errors = cAdminaccounts::validatePassword($data['password']);
				
				if(count($tmp_errors) != 0) {
						$this->errors = array_merge($this->errors, $tmp_errors);
						return false;
				}
				
				$id = $this->save($data);
				
				if(empty($id)) {
						header('Location: index.php?s=cAdminaccounts&error=45');
						die;
				}
	
				header('Location: index.php?s=cAdminaccounts&action=edit&id=' . $id . '&success=16');
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
								'id, account_type, username, email, phone, password, email_language, firstname, ' .
								'lastname, company, street_address, street_address_house_number, ' .
								'zip, city, country, notice, created_on, mqllock_count, ' .
								'is_supplier, is_manufacturer, is_referrer_platform, gender, seller_status, ' .
								'company_position, email_footer ' .
						'FROM ' . $db->table('accounts') . ' ' .
						'ORDER BY lastname, firstname;'
				);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
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
						'INSERT INTO ' . $db->table('accounts') . ' ' .
								'(account_type, username, email, phone, password, email_language, firstname, lastname, ' .
								'company, street_address, street_address_house_number, zip, city, country, notice, created_on, mqllock_count, ' .
								'is_supplier, is_manufacturer, is_referrer_platform, gender, seller_status, ' .
								'company_position, email_footer) ' .
						'VALUES ' .
								'(:account_type, :username, :email, :phone, :password, :email_language, :firstname, :lastname, ' .
								':company, :street_address, :street_address_house_number, :zip, :city, :country, :notice, :created_on, :mqllock_count, ' .
								':is_supplier, :is_manufacturer, :is_referrer_platform, ' .
								':gender, :seller_status, :company_position, :email_footer)'
				);
				$db->bind(':account_type', (int)$data['account_type']);
				$db->bind(':username', $data['username']);
				$db->bind(':email', $data['email']);
				$db->bind(':phone', $data['phone']);
				$db->bind(':password', password_hash($data['password'], PASSWORD_BCRYPT));
				$db->bind(':email_language', $data['email_language']);
				$db->bind(':firstname', $data['firstname']);
				$db->bind(':lastname', $data['lastname']);
				$db->bind(':company', $data['company']);
				$db->bind(':street_address', $data['street_address']);
				$db->bind(':street_address_house_number', $data['street_address_house_number']);
				$db->bind(':zip', $data['zip']);
				$db->bind(':city', $data['city']);
				$db->bind(':country', $data['country']);
				$db->bind(':notice', $data['notice']);
				$db->bind(':created_on', $data['created_on']);
				$db->bind(':mqllock_count', 0);
				$db->bind(':is_supplier', (int)$data['is_supplier']);
				$db->bind(':is_manufacturer', (int)$data['is_manufacturer']);
				$db->bind(':is_referrer_platform', (int)$data['is_referrer_platform']);
				$db->bind(':gender', (int)$data['gender']);
				$db->bind(':seller_status', (int)$data['seller_status']);
				$db->bind(':company_position', $data['company_position']);
				$db->bind(':email_footer', $data['email_footer']);
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
						'UPDATE ' . $db->table('accounts') . ' SET ' .
								'account_type = :account_type, ' .
								'username = :username, ' .
								'email = :email, ' .
								'phone = :phone, ' .
								'email_language = :email_language, ' .
								'firstname = :firstname, ' .
								'lastname = :lastname, ' .
								'company = :company, ' .
								'street_address = :street_address, ' .
								'street_address_house_number = :street_address_house_number, ' .
								'zip = :zip, ' .
								'city = :city, ' .
								'country = :country, ' .
								'notice = :notice, ' .
								'is_supplier = :is_supplier, ' .
								'is_manufacturer = :is_manufacturer, ' .
								'is_referrer_platform = :is_referrer_platform, ' .
								'gender = :gender, ' .
								'seller_status = :seller_status, ' .
								'company_position = :company_position, ' .
								'email_footer = :email_footer ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':account_type', (int)$data['account_type']);
				$db->bind(':username', $data['username']);
				$db->bind(':email', $data['email']);
				$db->bind(':phone', $data['phone']);
				$db->bind(':email_language', $data['email_language']);
				$db->bind(':firstname', $data['firstname']);
				$db->bind(':lastname', $data['lastname']);
				$db->bind(':company', $data['company']);
				$db->bind(':street_address', $data['street_address']);
				$db->bind(':street_address_house_number', $data['street_address_house_number']);
				$db->bind(':zip', $data['zip']);
				$db->bind(':city', $data['city']);
				$db->bind(':country', $data['country']);
				$db->bind(':notice', $data['notice']);
				$db->bind(':is_supplier', (int)$data['is_supplier']);
				$db->bind(':is_manufacturer', (int)$data['is_manufacturer']);
				$db->bind(':is_referrer_platform', (int)$data['is_referrer_platform']);
				$db->bind(':gender', (int)$data['gender']);
				$db->bind(':seller_status', (int)$data['seller_status']);
				$db->bind(':company_position', $data['company_position']);
				$db->bind(':email_footer', $data['email_footer']);
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
								'id, account_type, username, email, phone, password, email_language, firstname, ' .
								'lastname, company, street_address, street_address_house_number, ' .
								'zip, city, country, notice, created_on, mqllock_count, ' .
								'is_supplier, is_manufacturer, is_referrer_platform, gender, seller_status, ' .
								'company_position, email_footer ' .
						'FROM ' . $db->table('accounts') . ' ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				$this->data['data'] = $tmp;
		}
		
		///////////////////////////////////////////////////////////////////////////////
		// Get an account (that is not deactivated..
		///////////////////////////////////////////////////////////////////////////////
		public static function getValidUser($user_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT ' .
								'id, account_type, username, email, phone, password, email_language, firstname, ' .
								'lastname, company, street_address, street_address_house_number, ' .
								'zip, city, country, notice, created_on, mqllock_count, ' .
								'is_supplier, is_manufacturer, is_referrer_platform, gender, seller_status, ' .
								'company_position, email_footer ' .
						'FROM ' . $db->table('accounts') . ' ' .
						'WHERE ' .
								'id = :id AND ' .
								'account_type != 0'
				);
				$db->bind(':id', (int)$user_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				return $tmp;
		}
}
?>