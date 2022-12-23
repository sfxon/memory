<?php

class cProductcategories extends cModule {
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// @INFO		save build fields for an insert or update query
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveBuildFields($categories_data) {
				$retval = array();
				
				//products_id
				//If products id is zero, the product will be created, if it is not - it will be updated.
				if(!isset($categories_data['id'])) {
						$categories_data['id'] = 0;		
				}
				
				$categories_data['id'] = (int)$categories_data['id']; //force integer!
				
				//set the fields
				$retval['id'] = $categories_data['id'];
				$retval['status'] = core()->getPostVar('status');
				$retval['sort_order'] = core()->getPostVar('sort_order');
				$retval['parent_id'] = core()->getGetVar('category_id');
				
				//Return the result..
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// @INFO		Prüfen ob eine Kategorie existiert
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkForExistence($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('product_categories') . ' WHERE id = :id');
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				if(!empty($data)) {
						return true;
				}
				
				return false;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// @INFO		Save Products Categories Data
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function save($product_categories_data) {
				$id = 0;
				
				//Check and prepare all the fields..
				$general_product_categories_data = cProductcategories::saveBuildFields($product_categories_data);
		
				//save general information
				$id = cProductcategories::saveGeneralInformation($general_product_categories_data);
				
				cProductcategoriesdescriptions::saveByPostValues($id, $product_categories_data);
				cProductcategoriesimages::save($id, $product_categories_data);
				
				return $id;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// @INFO		save categories general information
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveGeneralInformation($general_product_categories_data) {
				if(empty($general_product_categories_data['id'])) {
						//insert new entry
						$id = (int)cProductcategories::generalInformationCreate($general_product_categories_data);
				} else {
						//update entry
						cProductcategories::generalInformationUpdate($general_product_categories_data);
						$id = (int)$general_product_categories_data['id'];
				}
				
				return $id;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// @INFO	 create categories general information
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function generalInformationCreate($general_product_categories_data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('product_categories') . ' ' .
								'(status, sort_order, parent_id) ' .
						'VALUES ' .
								'(:status, :sort_order, :parent_id)');
				$db->bind(':status', (int)$general_product_categories_data['status']);
				$db->bind(':sort_order', (int)$general_product_categories_data['sort_order']);
				$db->bind(':parent_id', (int)$general_product_categories_data['parent_id']);
				$db->execute();
				
				return $db->insertId();
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////
		// @INFO		update categories general information
		//////////////////////////////////////////////////////////////////////////////////////////////////
		public static function generalInformationUpdate($general_product_categories_data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('product_categories') . ' SET ' .
								'status = :status, ' .
								'sort_order = :sort_order ' .
						'WHERE id = :id');
				$db->bind(':status', (int)$general_product_categories_data['status']);
				$db->bind(':sort_order', (int)$general_product_categories_data['sort_order']);
				$db->bind(':id', (int)$general_product_categories_data['id']);
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// @INFO		load categories by channel id and parent category id
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadByChannelIdAndParentCategoryId($channel_id, $parent_category_id, $datalanguage) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT id FROM ' . $db->table('product_categories') . ' ' .
						'WHERE ' .
								'channel_id = :channel_id AND ' .
								'parent_id = :parent_id'
				);
				$db->bind(':channel_id', (int)$channel_id);
				$db->bind(':parent_id', (int)$parent_category_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = cProductcategoriesdescriptions::loadByIdAndLanguage($tmp['id'], $datalanguage);
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// @INFO		load categories by parent category id
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadByParentCategoryId($parent_category_id, $datalanguage) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('product_categories') . ' WHERE parent_id = :parent_id');
				$db->bind(':parent_id', (int)$parent_category_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetch_array_assoc();
						$retval[] = cProdctcategoriesdescriptions::loadByIdAndLanguage($tmp['id'], $datalanguage);
				}
				
				return $retval;
		}	
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Kategorie speichern (inkl. Sortierung)
		// - Speichert die Hauptdaten + die Sortierung
		// - für die Beschreibungsdaten bitte die Funktion lib_product_category_description_save
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveWithSortorder($channel_id, $status, $parent_id, $sort_order) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('product_categories') . ' (channel_id, status, parent_id, sort_order) ' .
						'VALUES (:channel_id, :status, :parent_id, :sort_order)'
				);
				$db->bind(':channel_id', $channel_id);
				$db->bind(':status', $status);
				$db->bind(':parent_id', $parent_id);
				$db->bind(':sort_order', (int)$sort_order);
				$db->execute();
				
				return $db->insertId();
		}	
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Kategorie speichern
		// - Speichert nur die Hauptdaten
		// - für die Beschreibungsdaten bitte die Funktion lib_product_category_description_save
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveInDatabase($channel_id, $status, $parent_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('product_categories') . ' (channel_id, status, parent_id) ' .
						'VALUES (:channel_id, :status, :parent_id)'
				);
				$db->bind(':channel_id', $channel_id);
				$db->bind(':status', $status);
				$db->bind(':parent_id', $parent_id);
				$db->execute();
				
				return $db->insertId();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Kategorien für einen Channel laden
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadByChannelId($channel_id, $default_language_id, $parent_id = 0) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT pc.*, pcd.title, pcd.description ' .
						'FROM ' . $db->table('product_categories') . ' pc ' .
								'JOIN ' . $db->table('product_categories_description') . ' pcd ' .
										'ON ' .
												'pc.id = pcd.product_categories_id AND ' .
												'pcd.languages_id = :language_id ' .
						'WHERE ' .
								'channel_id = :channel_id AND ' .
								'parent_id = :parent_id'
				);
				$db->bind(':language_id', (int)$default_language_id);
				$db->bind(':channel_id', (int)$channel_id);
				$db->bind(':parent_id', (int)$parent_id);
				$result = $db->execute();
				
				$retval = false;
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						$tmp['childs'] = cProductcategories::loadByChannelId($channel_id, $default_language_id, $tmp['id']);
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Daten für eine Kategorie laden
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadByIdAndDefaultLanguage($id, $default_datalanguage) {
				$data = cProductcategories::loadById($id);
				
				if($data === false) {
						return $data;
				}
				
				foreach($data['descriptions'] as $desc) {
						if($desc['languages_id'] == $default_datalanguage) {
								$data['title'] = $desc['title'];
								$data['description'] = $desc['description'];
								break;
						} else {
								$data['title'] = 'not found';
								$data['description'] = 'not found';
						}
				}
		
				return $data;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Daten für eine Kategorie laden
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('product_categories') . ' WHERE id = :id');
				$db->bind(':id', $id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				if(empty($data)) {
						return false;
				}
				
				$descriptions = array();
				$data_langs = cDatalanguages::loadActivated();
				
				//Load the languages..
				foreach($data_langs as $lang) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT * from ' . $db->table('product_categories_description') . ' ' .
								'WHERE ' .
										'product_categories_id = :product_categories_id AND ' .
										'languages_id = :languages_id'
						);
						$db->bind(':product_categories_id', (int)$id);
						$db->bind(':languages_id', $lang['id']);
						$result = $db->execute();
						
						$tmp = $result->fetchArrayAssoc();
		
						if(empty($tmp)) {
								$descriptions[] = array(
														'product_categories_id' => $id,
														'languages_id' => $lang['id'],
														'language_name' => $lang['title'],
														'title' => '',
														'description' => ''
															);
						} else {
								$descriptions[] = array(
														'product_categories_id' => $id,
														'languages_id' => $lang['id'],
														'language_name' => $lang['title'],
														'title' => $tmp['title'],
														'description' => $tmp['description']
															);
						}
				}
				
				$data['descriptions'] = $descriptions;
		
				return $data;
		}
		
		//TODO: continue...
			
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Daten für eine Kategorie laden
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadTreeReverse($product_categories_id, $default_datalanguage, &$data = false) {
				if($product_categories_id == 0) {
						return false;
				}
		
				$product_category = cProductcategories::loadByIdAndDefaultLanguage($product_categories_id, $default_datalanguage);
				cProductcategories::loadTreeReverse($product_category['parent_id'], $default_datalanguage, $data);
				$data[] = $product_category;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Build a plain text string for the categories tree part
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function buildStringPlain($product_categories_id, $default_datalanguage, $cat_divider = '&nbsp;&gt;&nbsp;') {
				$retval = '';
				$arr = array();
				
				cProductcategories::loadTreeReverse($product_categories_id, $default_datalanguage, $arr);
				
				//$arr = array_reverse($arr);
				
				foreach($arr as $ar) {
						if(!empty($retval)) {
								$retval .= $cat_divider;
						}
						
						$retval .= $ar['title'];
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Build a plain text string for the categories tree part
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function createSimple($category_name, $channel_id, $parent_id, $status, $languages_id, $sort_order) {
				$category_id = cProductcategories::saveWithSortorder($channel_id, $status, $parent_id, $sort_order);
				cProductcategoriesdescriptions::createProductCategoriesDescription($category_id, $languages_id, $category_name, $category_name, '', '', '', '', '');
				
				return $category_id;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Get max sort order of a specific categories subcategories.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function getMaxSortOrder($channel_id, $parent_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT MAX(sort_order) as max_sort_order from ' . $db->table('product_categories') . ' ' .
						'WHERE ' .
								'channel_id = :channel_id AND ' .
								'parent_id = :parent_id'
				);
				$db->bind(':channel_id', (int)$channel_id);
				$db->bind(':parent_id', (int)$parent_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				return (int)$tmp['max_sort_order'];
		}
}
?>