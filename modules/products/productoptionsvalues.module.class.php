<?php

class cProductoptionsvalues extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////
		// load a single products_options_values_descriptions by options_values_id and language_id
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function loadDescriptionByOptionsValuesIdAndLanguageId($products_options_values_id, $languages_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('products_options_values_descriptions') . ' WHERE ' .
								'products_options_values_id = :products_options_values_id AND ' .
								'languages_id = :languages_id'
				);
				$db->bind(':products_options_values_id', $products_options_values_id);
				$db->bind(':languages_id', $languages_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				return $tmp;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////
		// save a products options values description
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function saveDescription($products_options_values_id, $languages_id, $title) {
				//check if combination of language id and products_options_id exists
				if(false === cProductoptionsvalues::loadDescriptionByOptionsValuesIdAndLanguageId($products_options_values_id, $languages_id)) {
						//insert
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'INSERT INTO ' . $db->table('products_options_values_descriptions') . ' (products_options_values_id, languages_id, title) VALUES ' .
								'(:products_options_values_id, :languages_id, :title)'
						);
						$db->bind(':products_options_values_id', (int)$products_options_values_id);
						$db->bind(':languages_id', (int)$languages_id);
						$db->bind(':title', $title);
						$db->execute();
				} else {
						//update
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'UPDATE ' . $db->table('products_options_values_descriptions') . ' SET ' .
										'title = :title ' .
								'WHERE ' . 
										'products_options_values_id = :products_options_values_id AND ' .
										'languages_id = :languages_id'
						);
						$db->bind(':title', $title);
						$db->bind(':products_options_values_id', (int)$products_options_values_id);
						$db->bind(':languages_id', (int)$languages_id);
						$db->execute();
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////
		// create a products options value (creates only the base data - not the description)
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function create($products_options_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('INSERT INTO ' . $db->table('products_options_values') . '(products_options_id, sort_order) VALUES (:products_options_id, 0);');
				$db->bind(':products_options_id', (int)$products_options_id);
				$db->execute();
				
				return $db->insertId();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////
		// load one products options data by language_id and title
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function loadByTitleAndOptionsId($language_id, $products_options_id, $products_options_values_title) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT povd.* FROM ' . $db->table('products_options_values_descriptions') . ' povd ' .
						'JOIN ' . $db->table('products_options_values') . ' pov ' .
								'ON povd.products_options_values_id = pov.id ' .
						'WHERE ' .
								'povd.languages_id = :languages_id AND ' .
								'povd.title = :title AND ' .
								'pov.products_options_id = :products_options_id;'
				);
				$db->bind(':languages_id', $language_id);
				$db->bind(':title', $products_options_values_title);
				$db->bind(':products_options_id', $products_options_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				return $tmp;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////
		// load a products options value description data by the products options id
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function loadDescriptionsByProductsOptionsValuesId($products_options_values_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('products_options_values_descriptions') . ' WHERE products_options_values_id = :products_options_values_id');
				$db->bind(':products_options_values_id', $products_options_values_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[$tmp['languages_id']] = $tmp;
				}
				
				if(count($retval) == 0) {
						return false;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// save products options values from temporary table to live-data table
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveFromTmp($id, $products_data) {
				$tmp_products_id = $products_data['tmp_products_id'];
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('tmp_products_options_values') . ' ' .
						'WHERE ' .
									'tmp_products_id = :tmp_products_id;'
				);
				$db->bind(':tmp_products_id', $tmp_products_id);
				$result = $db->execute();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						$products_options_values_id = 0;
						
						if(0 == $tmp['products_options_id']) {
								//get the products_options_id out of the tmp_products_options table
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery('SELECT products_options_id FROM ' . $db->table('tmp_products_options') . ' WHERE tmp_products_id = :tmp_products_id AND tmp_products_options_id = :tmp_products_options_id');
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':tmp_products_options_id', $tmp['tmp_products_options_id']);
								$subresult = $db->execute();
								
								$subtmp = $subresult->fetchArrayAssoc();
								
								if(empty($subtmp['products_options_id'])) {
										continue;
								}
								
								$products_options_id = $subtmp['products_options_id'];
						} else {
								$products_options_id = $tmp['products_options_id'];
						}
						
						//if the products_options_values_id != false - this is an existing entry - we have to update..
						if(!empty($tmp['products_options_values_id'])) {
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery('UPDATE ' . $db->table('products_options_values') . ' SET sort_order = :sort_order WHERE id = :products_options_values_id');
								$db->bind(':sort_order', (int)$tmp['sort_order']);
								$db->bind(':products_options_values_id', (int)$tmp['products_options_values_id']);
								$db->execute();
								
								cProductoptionsvalues::saveDescriptionsFromTmp($tmp['products_options_values_id'], $tmp_products_id, $tmp['tmp_products_options_values_id']);
								$products_options_values_id = $tmp['products_options_values_id'];
						//products options_values_id == 0 - this is a new entry - we can insert this!
						} else {
								//now we got all required data. Run the inserts!
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery('INSERT INTO ' . $db->table('products_options_values') . ' (products_options_id, sort_order) VALUES(:products_options_id, :sort_order)');
								$db->bind(':products_options_id', $products_options_id);
								$db->bind(':sort_order', $tmp['sort_order']);
								$db->execute();
								
								$products_options_values_id = $db->insertId();
								
								cProductoptionsvalues::saveDescriptionsFromTmp($products_options_values_id, $tmp_products_id, $tmp['tmp_products_options_values_id']);
						}
						
						//Update the tmp table - Set the value so it can be grabbed by a later function in a saving process.
						//For example: cTmpproductoptionsvalues::loadProductsOptionsIdByTmpProductsOptionsValuesId that is called in
						//cProductattributes::saveFromTmp uses this ID.
						//It tries to get the final ID, if a new option_value was created and used as a new attribute.
						cTmpproductoptionsvalues::updateProductOptionsValuesIdByTmpProductsOptionsValuesId($tmp['tmp_products_options_values_id'], $products_options_values_id);
				}
		}
				
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// save the product options values descriptions from the temporary table to the live data table
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveDescriptionsFromTmp($products_options_values_id, $tmp_products_id, $tmp_products_options_values_id) {
				//we got the entry in the main table - let us insert the descriptions
				//select the temporary data..
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('tmp_products_options_values_descriptions') . ' WHERE ' .
								'tmp_products_options_values_id = :tmp_products_options_values_id AND ' .
								'tmp_products_id = :tmp_products_id;'
				);
				$db->bind(':tmp_products_options_values_id', $tmp_products_options_values_id);
				$db->bind(':tmp_products_id', $tmp_products_id);
				$result = $db->execute();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						//we check if we got an enty - so we can see if this is an insert or an update
						//you have to keep in mind, that even for existing items - there could be added a new language - and in this way,
						//we get the new language strings inserted clean. :)
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT * FROM ' . $db->table('products_options_values_descriptions') . ' WHERE ' . 
											'products_options_values_id = :products_options_values_id AND ' .
											'languages_id = :languages_id;'
						);
						$db->bind(':products_options_values_id', (int)$products_options_values_id);
						$db->bind(':languages_id', (int)$tmp['languages_id']);
						$subresult = $db->execute();
						
						$subtmp = $subresult->fetchArrayAssoc();
						
						if(!empty($subtmp)) {
								//existing item - update
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'UPDATE ' . $db->table('products_options_values_descriptions') . ' SET ' .
												'title = :title ' .
										'WHERE ' . 
												'products_options_values_id = :products_options_values_id AND ' .
												'languages_id = :languages_id;'
								);
								$db->bind(':title', $tmp['title']);
								$db->bind(':products_options_values_id', (int)$products_options_values_id);
								$db->bind(':languages_id', (int)$tmp['languages_id']);
								$db->execute();
						} else {
								//new item - insert
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'INSERT INTO ' . $db->table('products_options_values_descriptions') . ' (products_options_values_id, languages_id, title) VALUES ' .
										'(:products_options_values_id, :languages_id, :title);'
								);
								$db->bind(':products_options_values_id', (int)$products_options_values_id);
								$db->bind(':languages_id', (int)$tmp['languages_id']);
								$db->bind(':title', $tmp['title']);
								$db->execute();
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// check the existence of a products options value - except the one that is in edit mode right now!
		//
		// -> the one editet right now should not be checked, because some of it's options can be intendet to be the same!
		// -> we only want to get sure, we don't make a doublicate of another item!
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkExistenceButCurrentOne($tmp_products_options_values_id, $tmp_products_id, $products_options_id, $tmp_products_options_id, $products_options_values_id, $titles) {
				//check the temporary table
				$result = cProductoptionsvalues::checkExistenceButCurrentOneTmpTable($tmp_products_options_values_id, $tmp_products_id, $products_options_id, $tmp_products_options_id, $products_options_values_id, $titles);
		
				if(count($result) > 0) {
						//build the error string!
						return cProductoptionsvalues::checkErrorStringBuilder($result);
				}
				
				//check the live table
				$result = cProductoptionsvalues::checkExistenceButCurrentOneLiveTable($tmp_products_options_values_id, $tmp_products_id, $products_options_id, $tmp_products_options_id, $products_options_values_id, $titles);
				
				if(count($result) > 0) {
						//build the error string!
						return cProductoptionsvalues::checkErrorStringBuilder($result);
				}
		
				return false;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// check the existence of a products options value
		//
		// this function checks for temporary and live tables
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkExistence($tmp_products_id, $products_options_id, $tmp_products_options_id, $titles) {
				//check the temporary table
				$result = cProductoptionsvalues::checkExistenceTmpTable($tmp_products_id, $products_options_id, $tmp_products_options_id, $titles);
		
				if(count($result) > 0) {
						//build the error string!
						return cProductoptionsvalues::checkErrorStringBuilder($result);
				}
				
				//check the live table
				$result = cProductoptionsvalues::checkExistenceLiveTable($products_options_id, $titles);
				
				if(count($result) > 0) {
						//build the error string!
						return cProductoptionsvalues::checkErrorStringBuilder($result);
				}
				
				return false;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// check if a product options value exists in the temporary table- except the one that is in edit mode right now!
		//
		// -> the one editet right now should not be checked, because some of it's options can be intendet to be the same!
		// -> we only want to get sure, we don't make a doublicate of another item!
		//
		//
		// returns an array of found items - or otherwise an empty array.
		//
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkExistenceButCurrentOneTmpTable($tmp_products_options_values_id, $tmp_products_id, $products_options_id, $tmp_products_options_id, $products_options_values_id, $titles) {
				$retval = array();
				
				foreach($titles as $language_id => $title) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT * FROM ' . $db->table('tmp_products_options_values_descriptions') . ' ' .
								'WHERE ' .
										'tmp_products_id = :tmp_products_id AND ' .
										'products_options_id = :products_options_id AND ' .
										'tmp_products_options_id = :tmp_products_options_id AND ' .
										'title = :title AND ' .
										'languages_id = :datalanguages_id AND' .
										
										'NOT(' . //here comes the exception - do not treath the current item..
												'tmp_products_options_values_id = :tmp_products_options_values_id AND ' .
												'tmp_products_id = :tmp_products_id2 AND ' .
												'products_options_id = :products_options_id2 AND ' .
												'tmp_products_options_id = :tmp_products_options_id2 AND ' .
												'products_options_values_id = :products_options_values_id AND ' .
												'languages_id = :datalanguages_id2 ' .
										');'
						);
						$db->bind(':tmp_products_id', $tmp_products_id);
						$db->bind(':products_options_id', $products_options_id);
						$db->bind(':tmp_products_options_id', $tmp_products_options_id);
						$db->bind(':title', $title);
						$db->bind(':datalanguages_id', $language_id);
						//the exceptions variable binding..
						$db->bind(':tmp_products_options_values_id', $tmp_products_options_values_id);
						$db->bind(':tmp_products_id2', $tmp_products_id);
						$db->bind(':products_options_id2', $products_options_id);
						$db->bind(':tmp_products_options_id2', $tmp_products_options_id);
						$db->bind(':products_options_values_id', $products_options_values_id);
						$db->bind(':datalanguages_id2', $language_id);
						//done -> execute
						$result = $db->execute();
						
						$tmp = $result->fetchArrayAssoc();
				
						if($tmp !== false) {
								$retval[] = $language_id;
						}
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// check if a product options value exists in the temporary table
		//
		// returns an array of found items - or otherwise an empty array.
		//
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkExistenceTmpTable($tmp_products_id, $products_options_id, $tmp_products_options_id, $titles) {
				$retval = array();
				
				foreach($titles as $language_id => $title) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT * FROM ' . $db->table('tmp_products_options_values_descriptions') . ' ' .
								'WHERE ' .
										'tmp_products_id = :tmp_products_id AND ' .
										'products_options_id = :products_options_id AND ' .
										'tmp_products_options_id = :tmp_products_options_id AND ' .
										'title = :title AND ' .
										'languages_id = :datalanguages_id'
						);
						$db->bind(':tmp_products_id', $tmp_products_id);
						$db->bind(':products_options_id', $products_options_id);
						$db->bind(':tmp_products_options_id', $tmp_products_options_id);
						$db->bind(':title', $title);
						$db->bind(':datalanguages_id', $language_id);
						$result = $db->execute();
						
						$tmp = $result->fetchArrayAssoc();
				
						if($tmp !== false) {
								$retval[] = $language_id;
						}
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Erstellt die Fehlermeldungen, falls Einträge für products_options_values bereits existieren
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkErrorStringBuilder($language_ids) {
				$retval = '';
				
				foreach($language_ids as $lang) {
						//Sprachdaten abrufen
						$tmp_language_data = cDatalanguages::loadById($lang);
						
						$retval .= TEXT_ERROR_PRODUCTS_OPTIONS_VALUE_ALREADY_EXISTS . ' ' . $tmp_language_data['title'] . ".\n";
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// check if a product options value exists in the live data table- except the one that is in edit mode right now!
		//
		// -> the one editet right now should not be checked, because some of it's options can be intendet to be the same!
		// -> we only want to get sure, we don't make a doublicate of another item!
		//
		//
		// returns an array of found items - or otherwise an empty array.
		//
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkExistenceButCurrentOneLiveTable($tmp_products_options_values_id, $tmp_products_id, $products_options_id, $tmp_products_options_id, $products_options_values_id, $titles) {
				$retval = array();
				
				foreach($titles as $language_id => $title) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'select pov.* from ' . $db->table('products_options_values') . ' pov ' .
										'JOIN ' . $db->table('products_options_values_descriptions') . ' povd ON pov.id = povd.products_options_values_id ' .
								'WHERE ' .
										'pov.products_options_id = :products_options_id AND ' .
										'povd.title = :title AND ' .
										'povd.languages_id = :datalanguages_id AND ' .
										'NOT(pov.id = :products_options_values_id);'
						);
						$db->bind(':products_options_id', $products_options_id);
						$db->bind(':title', $title);
						$db->bind(':datalanguages_id', $language_id);
						$db->bind(':products_options_values_id', $products_options_values_id);
						$result = $db->execute();
						
						$tmp = $result->fetchArrayAssoc();
						
						if($tmp !== false) {
								$retval[] = $language_id;
						}
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// check if a product options value exists in the live data table
		//
		// returns an array of found items - or otherwise an empty array.
		//
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkExistenceLiveTable($products_options_id, $titles) {
				global $db;
				
				$retval = array();
				
				foreach($titles as $language_id => $title) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT pov.* FROM ' . $db->table('products_options_values') . ' pov ' .
										'JOIN ' . $db->table('products_options_values_descriptions') . ' povd ON pov.id = povd.products_options_values_id ' .
								'WHERE ' .
												'pov.products_options_id = :products_options_id AND ' .
												'povd.title = :title AND ' .
												'povd.languages_id = :datalanguages_id'
						);
						$db->bind(':products_options_id', $products_options_id);
						$db->bind(':title', $title);
						$db->bind(':datalanguages_id', $language_id);
						$result = $db->execute();
						
						$tmp = $result->fetchArrayAssoc();
						
						if($tmp !== false) {
								$retval[] = $language_id;
						}
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// load one products_options_values item by products_options_values_id
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadById($products_options_values_id, $datalanguages_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' .
								$db->table('products_options_values') . ' pov ' .
						'JOIN ' . $db->table('products_options_values_descriptions') . ' povd ON pov.id = povd.products_options_values_id AND povd.languages_id = :datalanguages_id ' .
						'WHERE pov.id = :id ' .
						'ORDER BY pov.sort_order, povd.title'
				);
				$db->bind(':datalanguages_id', (int)$datalanguages_id);
				$db->bind(':id', (int)$products_options_values_id);
				
				$result = $db->execute();
				
				//We should only receive one - because we are asking for the auto-increment main id of the table!!
				$retval = $result->fetchArrayAssoc();
				$retval['descriptions'] = cProductoptionsvalues::loadDescriptions($retval['id']);
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// load products options values (load all for a specific products options id)
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadProductOptionsValues($products_options_id, $datalanguages_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('products_options_values') . ' pov ' .
						'JOIN ' . $db->table('products_options_values_descriptions') . ' povd ' .
										'ON pov.id = povd.products_options_values_id AND povd.languages_id = :datalanguages_id ' .
						'WHERE ' .
								'pov.products_options_id = :products_options_id ' .
						'ORDER BY pov.sort_order, povd.title'
				);
				$db->bind(':datalanguages_id', (int)$datalanguages_id);
				$db->bind(':products_options_id', (int)$products_options_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$tmp['descriptions'] = cProductoptionsvalues::loadDescriptions($tmp['id']);
						
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// load products options values descriptions
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadDescriptions($products_options_values_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('products_options_values_descriptions') . ' WHERE products_options_values_id = :products_options_values_id');
				$db->bind(':products_options_values_id', $products_options_values_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[$tmp['languages_id']] = $tmp;
				}
				
				return $retval;
		}
}

?>