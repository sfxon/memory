<?php

class cWebsellersessionslive extends cModule {
		/////////////////////////////////////////////////////////////////////////////////
		// End this live session.
		/////////////////////////////////////////////////////////////////////////////////
		public static function endLiveSession($webseller_sessions_live_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('webseller_sessions_live') . ' ' .
								'SET ' .
								'session_ended_on = NOW() ' .
						'WHERE id = :id;'
				);
				$db->bind(':id', (int)$webseller_sessions_live_id);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Write a log entry.
		/////////////////////////////////////////////////////////////////////////////////
		public static function writeLog($logmessage, $session_data, $additional_data = array(), $extended_log = true) {
				$folder = 'data/logs/websellersessionslive/';
				$filename = $session_data['id'] . '.txt';
				$extended_filename = $session_data['id'] . '.ext.txt';
				
				$fp = fopen($folder . $filename, 'a');
				$output = "\n\n" . '-START LOGENTRY - ' . date('Y-m-d H:i:s') . " -------------------------------------' . \n";
				$output .= 'Logmessage: ' . print_r($logmessage, true) . "\n";
				$output .= "Session-Data:\n" . print_r($session_data, true);
				$output .= "\n\nAdditional_data:\n" . print_r($additional_data, true);
				fwrite($fp, $output);
				fclose($fp);
				
				if($extended_log === true) {
						$fp = fopen($folder . $extended_filename, 'a');
						fwrite($fp, $output);
						fwrite($fp, "\n\nBacktrace:\n");
						fwrite($fp, print_r(debug_backtrace(), true));
						fclose($fp);
				}
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads an database entry by id.
		/////////////////////////////////////////////////////////////////////////////////
		public static function loadLiveData($live_session_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('webseller_sessions_live') . ' ' .
								'WHERE ' .
								'id = :id ' .
						'LIMIT 1;'
				);
				$db->bind(':id', (int)$live_session_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				return $tmp;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Create an entry.
		/////////////////////////////////////////////////////////////////////////////////
		public static function create($session_id, $seller_id, $state_json, $session_started_on) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('webseller_sessions_live') . ' ' .
								'(webseller_sessions_id, seller_id, state, session_started_on, last_action_by) ' .
						'VALUES ' .
								'(:webseller_sessions_id, :seller_id, :state, :session_started_on, \'seller\') '
				);
				$db->bind(':webseller_sessions_id', (int)$session_id);
				$db->bind(':seller_id', (int)$seller_id);
				$db->bind(':state', $state_json);
				$db->bind(':session_started_on', $session_started_on);
				$db->execute();
				
				return $db->insertId();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Update an entry.
		/////////////////////////////////////////////////////////////////////////////////
		public static function updateByCustomer($session_id, $last_customer_action) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('webseller_sessions_live') . ' set ' .
								'last_customer_action = :last_customer_action,
								customer_sync_count = customer_sync_count + 1,
								customer_last_sync_time = NOW(),' .
								'last_action_by = \'customer\' ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':last_customer_action', $last_customer_action);
				$db->bind(':id', (int)$session_id);
				$db->execute();
				
				cWebsellersessionslive::writeLog('Updating a customer call of the site in the database. (websellersessionslive::updateByCustomer).', $session_id, $last_customer_action);
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Update by seller
		/////////////////////////////////////////////////////////////////////////////////
		public static function updateBySeller($live_session_id, $state) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('webseller_sessions_live') . ' set ' .
								'state = :state, ' .
								'seller_sync_count = seller_sync_count + 1, ' .
								'seller_last_sync_time = NOW(),' .
								'last_action_by = \'seller\' ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':state', $state);
				$db->bind(':id', (int)$live_session_id);
				$db->execute();
				
				cWebsellersessionslive::writeLog('Updating a seller call of the site in the database. (websellersessionslive::updateBySeller).', $live_session_id, $state);
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Returns the state as json.
		/////////////////////////////////////////////////////////////////////////////////
		public static function makeStateJson($site, $action_array = array()) {
				$retval = array(
						'site' => $site,
						'actions' => $action_array
				);
				
				return json_encode($retval);
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Load all live sessions for one session id (that state that they are live by their status.)
		/////////////////////////////////////////////////////////////////////////////////
		public static function loadWebsellerSessionsRunningLiveInstancesByWsId($session_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('webseller_sessions_live') . ' ' .
						'WHERE ' .
								'webseller_sessions_id = :id AND ' .
								'session_started_on IS NOT NULL AND ' .
								'session_ended_on IS NULL'
				);
				$db->bind(':id', (int)$session_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Load all live sessions for one session id (that state that they are live by their status.)
		/////////////////////////////////////////////////////////////////////////////////
		public static function countLiveSessionsByWebsellersessionsId($session_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT COUNT(*) as anzahl FROM ' . $db->table('webseller_sessions_live') . ' ' .
						'WHERE ' .
								'webseller_sessions_id = :id AND ' .
								'session_started_on IS NOT NULL AND ' .
								'session_ended_on IS NULL'
				);
				$db->bind(':id', (int)$session_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['anzahl'])) {
						return $tmp['anzahl'];
				}
				
				return false;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Load all live sessions for one session id (that state that they are live by their status.)
		/////////////////////////////////////////////////////////////////////////////////
		public static function countEndedSessionsByWebsellersessionsId($session_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT COUNT(*) AS anzahl FROM ' . $db->table('webseller_sessions_live') . ' ' .
						'WHERE ' .
								'webseller_sessions_id = :id AND ' .
								'session_started_on IS NOT NULL AND ' .
								'session_ended_on IS NOT NULL'
				);
				$db->bind(':id', (int)$session_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['anzahl'])) {
						return $tmp['anzahl'];
				}
				
				return false;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Load all products for a live session,
		// with all the session specific data for the user.
		/////////////////////////////////////////////////////////////////////////////////
		public static function loadAllProducts($live_session_id) {
				$products = array();
				
				$live_session_data = cWebsellersessionslive::loadLiveData($live_session_id);
				
				$state = $live_session_data['state'];
				$state = json_decode($state, true);
				
				$categories_id = (int)$state['actions']['categories_id'];
				$webseller_sessions_data = cWebsellersessions::loadSessionById($live_session_data['webseller_sessions_id']);
				//$webseller_sessions_products = cWebsellersessionsproducts::loadProductsBySessionsId($webseller_sessions_data['id']);
				$webseller_sessions_products = cWebsellersessionsproducts::loadProductsBySessionsIdAndCategoriesId($webseller_sessions_data['id'], $categories_id);

				foreach($webseller_sessions_products as $index => $wsp) {
						$products_data = cProducts::loadComplexProductData($wsp['products_id'], 1);
						
						if(false == $products_data) {
								return false;
						}
						
						$products_images_array = cWebsellersessionsproductsimages::loadImagesArray($wsp['products_id']);
						$products_images_array = cWebsellersessionsproductsimages::checkImagesExistence($webseller_sessions_data['id'], $webseller_sessions_data['user_id'], $wsp['products_id'], $products_images_array);

						$products_data['webseller_products_images'] = $products_images_array;
						$webseller_sessions_products[$index]['products_data'] = $products_data;
				}
				
				$retval['products'] = $webseller_sessions_products;
				$retval['categories_data'] = cProductcategories::loadById($categories_id);
				$retval['categories_descriptions'] = cProductcategoriesdescriptions::loadByCategoryId($categories_id);
				
				return $retval;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Load all products for a live session,
		// with all the session specific data for the user.
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadLiveSessionProductScreenData($live_session_id) {
				$live_session_data = cWebsellersessionslive::loadLiveData($live_session_id);
				$webseller_sessions_data = cWebsellersessions::loadSessionById($live_session_data['webseller_sessions_id']);
				
				$state = $live_session_data['state'];
				$state = json_decode($state, true);
				
				if(!isset($state['actions']) || !isset($state['actions']['products_id'])) {
						return false;
				}
				
				$products_id = (int)$state['actions']['products_id'];
				$products_data = cProducts::loadComplexProductData($products_id, 1);
				
				if(false == $products_data) {
						return false;
				}
				
				$products_images_array = cWebsellersessionsproductsimages::loadImagesArray($products_id);
				$products_images_array = cWebsellersessionsproductsimages::checkImagesExistence($webseller_sessions_data['id'], $webseller_sessions_data['user_id'], $products_id, $products_images_array);

				$products_data['webseller_products_images'] = $products_images_array;
				
				//Load the attributes products..
				$attribute_products = array();
				
				foreach($products_data['products_data']['products_attributes'] as $attribute) {
						$attribute_product = cProducts::loadComplexProductData($attribute['products_id_slave'], 1);
						$attribute_products[] = $attribute_product;
				}
				
				//Flag the options that really have attributes
				foreach($products_data['products_options'] as $index => $option) {
						foreach($products_data['products_data']['products_attributes'] as $attribute) {
								if($attribute['products_options_id'] == $option['id']) {
										$products_data['products_options'][$index]['product_has_option'] = true;
										break;
								}
						}
				}
				
				//Load the categories data..
				$categories_id = cWebsellersessionslive::loadCategoriesIdByProductsId($products_id);
				$categories_data = cProductcategories::loadById($categories_id);
				
				$retval['products_data'] = $products_data;
				$retval['attribute_products'] = $attribute_products;
				$retval['categories_data'] = $categories_data;
				
				return $retval;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Load all products for a live session,
		// with all the session specific data for the user.
		/////////////////////////////////////////////////////////////////////////////////
		public static function loadCategoriesIdByProductsId($products_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT p2c.categories_id FROM ' . $db->table('products_to_categories') . ' p2c ' . 
						'WHERE ' .
								'p2c.products_id = :products_id'
								
				);
				$db->bind(':products_id', (int)$products_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['categories_id'])) {
						return $tmp['categories_id'];
				}
				
				return false;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Set submitted feature_id as substate GET variable..
		/////////////////////////////////////////////////////////////////////////////////
		public static function setSubmittedFeatureIdAsSubstateGetValue() {
				if(isset($_GET['features_id'])) {
						$_GET['substates_value'] = $_GET['features_id'];
				} else {
						$_GET['substates_value'] = 0;
				}
		}
}

?>