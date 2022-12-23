<?php

class cAccount extends cModule {		
		/////////////////////////////////////////////////////////////////////////
		// Define where to set this module in the boot (hook) chain.
		/////////////////////////////////////////////////////////////////////////
		public static function setBootHooks() {
				core()->setBootHook('cSession');
		}
		
		////////////////////////////////////////////////////////////////////////
		// This is executed when the system boots and the chain reached this module.
		////////////////////////////////////////////////////////////////////////
		public static function boot() {
				$logged_in = false;
				
				if(isset($_SESSION['user_id'])) {
						if(false === cAccount::checkUser((int)$_SESSION['user_id'])) {
								unset($_SESSION['user_id']);		//Logout finally!
						}
				}
				
				//now - if we are still logged in..
				if(isset($_SESSION['user_id'])) {
						core()->set('user_id', (int)$_SESSION['user_id']);
				} else {
						core()->set('user_id', (int)0);
				}
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Returns an array of all modules.
		//
		// Return Value Array Shema:
		//		array(
		//				array(
		//						'module' => 'path/and/module/name',
		//						'version' => '1.6'		//Minimum version of dependent module that is needed to run this module.
		//				), 
		//				array(..)
		//		);
		//
		//		The systems core logic checks all dependencies in the auto loader.
		//			
		//////////////////////////////////////////////////////////////////////////
		public static function getDependenciesAsArray() {
				return array(
						array(
								'module' => '/core/session/cSession'
						)
				);
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Returns the version of a module.
		// Returns 0 (zero) if you define no version for your module.
		//////////////////////////////////////////////////////////////////////////
		public static function getVersion() {
				return 0.1;
		}
		
		/////////////////////////////////////////////////////////////////////////
		// check a user..
		/////////////////////////////////////////////////////////////////////////
		public static function checkUser($user_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('accounts') . ' WHERE id = :id LIMIT 1');
				$db->bind(':id', (int)$user_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['id'])) {
						return true;
				}
				
				return false;
		}
		
		///////////////////////////////////////////////////////////////////////
		// Gets a navbar item - either it is active or inactive..
		///////////////////////////////////////////////////////////////////////
		public static function getNavbarData() {
				$site_id = core()->get('site_id');
				
				if(isset($_SESSION['user_id'])) {
						return array(
								'url' => '//' . cSite::loadSiteUrl($site_id) . 'myaccount/index.html',
								'title' => TEXT_NAVBAR_ACCOUNT
						);
				}
				
				return array(
						'url' => '//' . cSite::loadSiteUrl($site_id) . 'login/index.html',
						'title' => TEXT_NAVBAR_LOGIN
				);
		}
		
		/////////////////////////////////////////////////////////////////////
		// Check if the user got an specific administration right.
		/////////////////////////////////////////////////////////////////////
		public static function adminrightCheck($module, $rightskey, $user_id) {
				$systemrights_id = cAccount::systemrightLoadByModuleAndRightskey($module, $rightskey);
				
				if(empty($systemrights_id['id'])) {
						return false;
				}
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('select * from ' . $db->table('adminrights') . ' where systemrights_id = :systemrights_id and accounts_id = :accounts_id');
				$db->bind(':systemrights_id', (int)$systemrights_id['id']);
				$db->bind(':accounts_id', (int)$user_id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				if(empty($data['status'])) {
						return false;
				}
				
				return true;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load data for one specific system right
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function systemrightLoadByModuleAndRightskey($module, $rightskey) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('select * from ' . $db->table('systemrights') . ' where module = :module and rightskey = :rightskey');
				$db->bind(':module', $module);
				$db->bind(':rightskey', $rightskey);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				return $data;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// Load data for one user.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadUserData($user_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('accounts') . ' WHERE id = :id LIMIT 1;');
				$db->bind(':id', (int)$user_id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				return $data;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// Save customers email address.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveCustomersEmailAddress($user_id, $email_address) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('UPDATE ' . $db->table('accounts') . ' SET email = :email_address WHERE id = :id LIMIT 1;');
				$db->bind(':email_address', $email_address);
				$db->bind(':id', (int)$user_id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				return $data;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// Save customers new password.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveCustomersPassword($user_id, $password) {
				$password_hash = password_hash($password, PASSWORD_BCRYPT);
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('UPDATE ' . $db->table('accounts') . ' SET password = :password WHERE id = :id LIMIT 1;');
				$db->bind(':password', $password_hash);
				$db->bind(':id', (int)$user_id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				return $data;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// Save customers email language.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveCustomersEmailLanguage($user_id, $email_language) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('UPDATE ' . $db->table('accounts') . ' SET email_language = :email_language WHERE id = :id LIMIT 1;');
				$db->bind(':email_language', $email_language);
				$db->bind(':id', (int)$user_id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				return $data;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// load contacts by type..
		///////////////////////////////////////////////////////////////////////////////////////////////////	
		public static function loadSuppliers($index = 0, $max_results = 0, $sort_fields = '') {
				$limit = '';
		
				if(0 !== (int)$max_results) {
						$limit = ' LIMIT ' . (int)$index . ', ' . (int)$max_results;
				}
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('accounts') . ' WHERE is_supplier = 1 ORDER BY company, firstname, lastname' . $limit);
				$result = $db->execute();
				
				$retval = false;
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load accounts by type (manufacturer)
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadManufacturers($index = 0, $max_results = 0, $sort_fields = '') {
				$limit = '';
				
				if(0 !== (int)$max_results) {
						$limit = ' LIMIT ' . (int)$index . ', ' . (int)$max_results;
				}
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('accounts') . ' WHERE is_manufacturer = 1 ORDER BY company, firstname, lastname' . $limit);
				$result = $db->execute();
				
				$retval = false;
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load accounts by type (manufacturer)
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadReferrerPlatforms($index = 0, $max_results = 0, $sort_fields = '') {
				$limit = '';
				
				if(0 !== (int)$max_results) {
						$limit = ' LIMIT ' . (int)$index . ', ' . (int)$max_results;
				}
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('accounts') . ' ' . 
						'WHERE ' .
								'is_referrer_platform = 1 ' .
						'ORDER BY company, firstname, lastname' . $limit
				);
				$result = $db->execute();
				
				$retval = false;
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load seller status by account id.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function getSellerStatusByAccountId($account_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT seller_status FROM ' . $db->table('accounts') . ' WHERE id = :id LIMIT 1');
				$db->bind(':id', (int)$account_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false === $tmp) {
						return false;
				}
				
				return $tmp['seller_status'];
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load seller status by account id.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function getSalutationByGender($gender) {
				switch($gender) {
						case 1:
								return 'Herr';
						case 2:
								return 'Frau';
				}
				
				return '';
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// Load manufacturer by company name.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadManufacturersIdByCompanyName($company) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('accounts') . ' WHERE company = :company AND is_manufacturer = 1 LIMIT 1');
				$db->bind(':company', $company);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['id'])) {
						return $tmp['id'];
				}
				
				return false;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// Create Manufacturers account by manufacturers name.
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function createManufacturersAccountByManufacturersName($company) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('accounts') . ' ' .
								'(account_type, username, email_language, firstname, lastname, company, notice, created_on, is_manufacturer, gender) ' .
						'VALUES ' .
								'(1, :username, 1, :firstname, :lastname, :company, :notice, NOW(), 1, 1);'
				);
				$db->bind(':username', $company);
				$db->bind(':firstname', $company);
				$db->bind(':lastname', $company);
				$db->bind(':company', $company);
				$db->bind(':notice', 'Automatisch erstellter Account.');
				$db->execute();
				
				return $db->insertId();
		}
}

?>