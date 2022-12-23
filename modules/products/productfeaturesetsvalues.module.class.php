<?php

class cProductfeaturesetsvalues extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////
		// load a single products_featuresets_values_descriptions by featuresets_values_id and language_id
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function loadDescriptionByFeaturesetsValuesIdAndLanguageId($products_featuresets_values_id, $languages_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('products_featuresets_values_descriptions') . ' WHERE ' .
								'products_featuresets_values_id = :products_featuresets_values_id AND ' .
								'languages_id = :languages_id'
				);
				$db->bind(':products_featuresets_values_id', $products_featuresets_values_id);
				$db->bind(':languages_id', $languages_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				return $tmp;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////
		// Save a products featuresets values description.
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function saveDescription($products_featuresets_values_id, $languages_id, $title) {
				//check if combination of language id and products_featuresets_id exists
				if(false === cProductfeaturesetsvalues::loadDescriptionByFeaturesetsValuesIdAndLanguageId($products_featuresets_values_id, $languages_id)) {
						//insert
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'INSERT INTO ' . $db->table('products_featuresets_values_descriptions') . ' (products_featuresets_values_id, languages_id, title) VALUES ' .
								'(:products_featuresets_values_id, :languages_id, :title)'
						);
						$db->bind(':products_featuresets_values_id', (int)$products_featuresets_values_id);
						$db->bind(':languages_id', (int)$languages_id);
						$db->bind(':title', $title);
						$db->execute();
				} else {
						//update
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'UPDATE ' . $db->table('products_featuresets_values_descriptions') . ' SET ' .
										'title = :title ' .
								'WHERE ' . 
										'products_featuresets_values_id = :products_featuresets_values_id AND ' .
										'languages_id = :languages_id'
						);
						$db->bind(':title', $title);
						$db->bind(':products_featuresets_values_id', (int)$products_featuresets_values_id);
						$db->bind(':languages_id', (int)$languages_id);
						$db->execute();
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////
		// create a products featuresets value (creates only the base data - not the description)
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function create($products_featuresets_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('INSERT INTO ' . $db->table('products_featuresets_values') . '(products_featuresets_id, sort_order) VALUES (:products_featuresets_id, 0);');
				$db->bind(':products_featuresets_id', (int)$products_featuresets_id);
				$db->execute();
				
				return $db->insertId();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////
		// load one products featuresets data by language_id and title
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function loadByTitleAndFeaturesetsId($language_id, $products_featuresets_id, $products_featuresets_values_title) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT povd.* FROM ' . $db->table('products_featuresets_values_descriptions') . ' povd ' .
						'JOIN ' . $db->table('products_featuresets_values') . ' pov ' .
								'ON povd.products_featuresets_values_id = pov.id ' .
						'WHERE ' .
								'povd.languages_id = :languages_id AND ' .
								'povd.title = :title AND ' .
								'pov.products_featuresets_id = :products_featuresets_id;'
				);
				$db->bind(':languages_id', $language_id);
				$db->bind(':title', $products_featuresets_values_title);
				$db->bind(':products_featuresets_id', $products_featuresets_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				return $tmp;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////
		// load a products featuresets value description data by the products featuresets id
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function loadDescriptionsByProductsFeaturesetsValuesId($products_featuresets_values_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('products_featuresets_values_descriptions') . ' WHERE products_featuresets_values_id = :products_featuresets_values_id');
				$db->bind(':products_featuresets_values_id', $products_featuresets_values_id);
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
		// Save products featuresets values from temporary table to live-data table
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveFromTmp($id, $products_data) {
				$tmp_products_id = $products_data['tmp_products_id'];
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('tmp_products_featuresets_values') . ' ' .
						'WHERE ' .
									'tmp_products_id = :tmp_products_id;'
				);
				$db->bind(':tmp_products_id', $tmp_products_id);
				$result = $db->execute();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						$products_featuresets_values_id = 0;
						
						if(0 == $tmp['products_featuresets_id']) {
								//get the products_featuresets_id out of the tmp_products_featuresets table
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery('SELECT products_featuresets_id FROM ' . $db->table('tmp_products_featuresets') . ' WHERE tmp_products_id = :tmp_products_id AND tmp_products_featuresets_id = :tmp_products_featuresets_id');
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':tmp_products_featuresets_id', $tmp['tmp_products_featuresets_id']);
								$subresult = $db->execute();
								
								$subtmp = $subresult->fetchArrayAssoc();
								
								if(empty($subtmp['products_featuresets_id'])) {
										continue;
								}
								
								$products_featuresets_id = $subtmp['products_featuresets_id'];
						} else {
								$products_featuresets_id = $tmp['products_featuresets_id'];
						}
						
						//if the products_featuresets_values_id != false - this is an existing entry - we have to update..
						if(!empty($tmp['products_featuresets_values_id'])) {
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery('UPDATE ' . $db->table('products_featuresets_values') . ' SET sort_order = :sort_order WHERE id = :products_featuresets_values_id');
								$db->bind(':sort_order', (int)$tmp['sort_order']);
								$db->bind(':products_featuresets_values_id', (int)$tmp['products_featuresets_values_id']);
								$db->execute();
								
								cProductfeaturesetsvalues::saveDescriptionsFromTmp($tmp['products_featuresets_values_id'], $tmp_products_id, $tmp['tmp_products_featuresets_values_id']);
								$products_featuresets_values_id = $tmp['products_featuresets_values_id'];
						//products featuresets_values_id == 0 - this is a new entry - we can insert this!
						} else {
								//now we got all required data. Run the inserts!
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery('INSERT INTO ' . $db->table('products_featuresets_values') . ' (products_featuresets_id, sort_order) VALUES(:products_featuresets_id, :sort_order)');
								$db->bind(':products_featuresets_id', $products_featuresets_id);
								$db->bind(':sort_order', $tmp['sort_order']);
								$db->execute();
								
								$products_featuresets_values_id = $db->insertId();
								
								cProductfeaturesetsvalues::saveDescriptionsFromTmp($products_featuresets_values_id, $tmp_products_id, $tmp['tmp_products_featuresets_values_id']);
						}
						
						//Update the tmp table - Set the value so it can be grabbed by a later function in a saving process.
						//For example: cTmpproductfeaturesetsvalues::loadProductsFeaturesetsIdByTmpProductsFeaturesetsValuesId that is called in
						//cProductfeatures::saveFromTmp uses this ID.
						//It tries to get the final ID, if a new featureset_value was created and used as a new feature.
						cTmpproductfeaturesetsvalues::updateProductFeaturesetsValuesIdByTmpProductsFeaturesetsValuesId($tmp['tmp_products_featuresets_values_id'], $products_featuresets_values_id);
				}
		}
				
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// save the product featuresets values descriptions from the temporary table to the live data table
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveDescriptionsFromTmp($products_featuresets_values_id, $tmp_products_id, $tmp_products_featuresets_values_id) {
				//we got the entry in the main table - let us insert the descriptions
				//select the temporary data..
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('tmp_products_featuresets_values_descriptions') . ' WHERE ' .
								'tmp_products_featuresets_values_id = :tmp_products_featuresets_values_id AND ' .
								'tmp_products_id = :tmp_products_id;'
				);
				$db->bind(':tmp_products_featuresets_values_id', $tmp_products_featuresets_values_id);
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
								'SELECT * FROM ' . $db->table('products_featuresets_values_descriptions') . ' WHERE ' . 
											'products_featuresets_values_id = :products_featuresets_values_id AND ' .
											'languages_id = :languages_id;'
						);
						$db->bind(':products_featuresets_values_id', (int)$products_featuresets_values_id);
						$db->bind(':languages_id', (int)$tmp['languages_id']);
						$subresult = $db->execute();
						
						$subtmp = $subresult->fetchArrayAssoc();
						
						if(!empty($subtmp)) {
								//existing item - update
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'UPDATE ' . $db->table('products_featuresets_values_descriptions') . ' SET ' .
												'title = :title ' .
										'WHERE ' . 
												'products_featuresets_values_id = :products_featuresets_values_id AND ' .
												'languages_id = :languages_id;'
								);
								$db->bind(':title', $tmp['title']);
								$db->bind(':products_featuresets_values_id', (int)$products_featuresets_values_id);
								$db->bind(':languages_id', (int)$tmp['languages_id']);
								$db->execute();
						} else {
								//new item - insert
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'INSERT INTO ' . $db->table('products_featuresets_values_descriptions') . ' (products_featuresets_values_id, languages_id, title) VALUES ' .
										'(:products_featuresets_values_id, :languages_id, :title);'
								);
								$db->bind(':products_featuresets_values_id', (int)$products_featuresets_values_id);
								$db->bind(':languages_id', (int)$tmp['languages_id']);
								$db->bind(':title', $tmp['title']);
								$db->execute();
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// check the existence of a products featuresets value - except the one that is in edit mode right now!
		//
		// -> the one editet right now should not be checked, because some of it's featuresets can be intendet to be the same!
		// -> we only want to get sure, we don't make a doublicate of another item!
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkExistenceButCurrentOne($tmp_products_featuresets_values_id, $tmp_products_id, $products_featuresets_id, $tmp_products_featuresets_id, $products_featuresets_values_id, $titles) {
				//check the temporary table
				$result = cProductfeaturesetsvalues::checkExistenceButCurrentOneTmpTable($tmp_products_featuresets_values_id, $tmp_products_id, $products_featuresets_id, $tmp_products_featuresets_id, $products_featuresets_values_id, $titles);
		
				if(count($result) > 0) {
						//build the error string!
						return cProductfeaturesetsvalues::checkErrorStringBuilder($result);
				}
				
				//check the live table
				$result = cProductfeaturesetsvalues::checkExistenceButCurrentOneLiveTable($tmp_products_featuresets_values_id, $tmp_products_id, $products_featuresets_id, $tmp_products_featuresets_id, $products_featuresets_values_id, $titles);
				
				if(count($result) > 0) {
						//build the error string!
						return cProductfeaturesetsvalues::checkErrorStringBuilder($result);
				}
		
				return false;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Check the existence of a products featuresets value.
		//
		// This function checks for temporary and live tables.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkExistence($tmp_products_id, $products_featuresets_id, $tmp_products_featuresets_id, $titles) {
				//check the temporary table
				$result = cProductfeaturesetsvalues::checkExistenceTmpTable($tmp_products_id, $products_featuresets_id, $tmp_products_featuresets_id, $titles);
		
				if(count($result) > 0) {
						//build the error string!
						return cProductfeaturesetsvalues::checkErrorStringBuilder($result);
				}
				
				//check the live table
				$result = cProductfeaturesetsvalues::checkExistenceLiveTable($products_featuresets_id, $titles);
				
				if(count($result) > 0) {
						//build the error string!
						return cProductfeaturesetsvalues::checkErrorStringBuilder($result);
				}
				
				return false;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Check if a product featuresets value exists in the temporary table- except the one that is in edit mode right now!
		//
		// -> the one editet right now should not be checked, because some of it's featuresets can be intendet to be the same!
		// -> we only want to get sure, we don't make a doublicate of another item!
		//
		//
		// returns an array of found items - or otherwise an empty array.
		//
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkExistenceButCurrentOneTmpTable(
						$tmp_products_featuresets_values_id, $tmp_products_id, $products_featuresets_id, $tmp_products_featuresets_id, $products_featuresets_values_id, $titles) {
				$retval = array();
				
				foreach($titles as $language_id => $title) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT * FROM ' . $db->table('tmp_products_featuresets_values_descriptions') . ' ' .
								'WHERE ' .
										'tmp_products_id = :tmp_products_id AND ' .
										'products_featuresets_id = :products_featuresets_id AND ' .
										'tmp_products_featuresets_id = :tmp_products_featuresets_id AND ' .
										'title = :title AND ' .
										'languages_id = :datalanguages_id AND ' .
										
										'NOT(' . //here comes the exception - do not treath the current item..
												'tmp_products_featuresets_values_id = :tmp_products_featuresets_values_id AND ' .
												'tmp_products_id = :tmp_products_id2 AND ' .
												'products_featuresets_id = :products_featuresets_id2 AND ' .
												'tmp_products_featuresets_id = :tmp_products_featuresets_id2 AND ' .
												'products_featuresets_values_id = :products_featuresets_values_id AND ' .
												'languages_id = :datalanguages_id2 ' .
										');'
						);
						$db->bind(':tmp_products_id', $tmp_products_id);
						$db->bind(':products_featuresets_id', $products_featuresets_id);
						$db->bind(':tmp_products_featuresets_id', $tmp_products_featuresets_id);
						$db->bind(':title', $title);
						$db->bind(':datalanguages_id', $language_id);
						//the exceptions variable binding..
						$db->bind(':tmp_products_featuresets_values_id', $tmp_products_featuresets_values_id);
						$db->bind(':tmp_products_id2', $tmp_products_id);
						$db->bind(':products_featuresets_id2', $products_featuresets_id);
						$db->bind(':tmp_products_featuresets_id2', $tmp_products_featuresets_id);
						$db->bind(':products_featuresets_values_id', $products_featuresets_values_id);
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
		// Check if a product featuresets value exists in the temporary table.
		//
		// Returns an array of found items - or otherwise an empty array.
		//
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkExistenceTmpTable($tmp_products_id, $products_featuresets_id, $tmp_products_featuresets_id, $titles) {
				$retval = array();
				
				foreach($titles as $language_id => $title) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT * FROM ' . $db->table('tmp_products_featuresets_values_descriptions') . ' ' .
								'WHERE ' .
										'tmp_products_id = :tmp_products_id AND ' .
										'products_featuresets_id = :products_featuresets_id AND ' .
										'tmp_products_featuresets_id = :tmp_products_featuresets_id AND ' .
										'title = :title AND ' .
										'languages_id = :datalanguages_id'
						);
						$db->bind(':tmp_products_id', $tmp_products_id);
						$db->bind(':products_featuresets_id', $products_featuresets_id);
						$db->bind(':tmp_products_featuresets_id', $tmp_products_featuresets_id);
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
		// Erstellt die Fehlermeldungen, falls Einträge für products_featuresets_values bereits existieren
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkErrorStringBuilder($language_ids) {
				$retval = '';
				
				foreach($language_ids as $lang) {
						//Sprachdaten abrufen
						$tmp_language_data = cDatalanguages::loadById($lang);
						
						$retval .= TEXT_ERROR_PRODUCTS_FEATURESETS_VALUE_ALREADY_EXISTS . ' ' . $tmp_language_data['title'] . ".\n";
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Check if a product featuresets value exists in the live data table- except the one that is in edit mode right now!
		//
		// -> the one editet right now should not be checked, because some of it's featuresets can be intendet to be the same!
		// -> we only want to get sure, we don't make a doublicate of another item!
		//
		//
		// returns an array of found items - or otherwise an empty array.
		//
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkExistenceButCurrentOneLiveTable($tmp_products_featuresets_values_id, $tmp_products_id, $products_featuresets_id, $tmp_products_featuresets_id, $products_featuresets_values_id, $titles) {
				$retval = array();
				
				foreach($titles as $language_id => $title) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'select pov.* from ' . $db->table('products_featuresets_values') . ' pov ' .
										'JOIN ' . $db->table('products_featuresets_values_descriptions') . ' povd ON pov.id = povd.products_featuresets_values_id ' .
								'WHERE ' .
										'pov.products_featuresets_id = :products_featuresets_id AND ' .
										'povd.title = :title AND ' .
										'povd.languages_id = :datalanguages_id AND ' .
										'NOT(pov.id = :products_featuresets_values_id);'
						);
						$db->bind(':products_featuresets_id', $products_featuresets_id);
						$db->bind(':title', $title);
						$db->bind(':datalanguages_id', $language_id);
						$db->bind(':products_featuresets_values_id', $products_featuresets_values_id);
						$result = $db->execute();
						
						$tmp = $result->fetchArrayAssoc();
						
						if($tmp !== false) {
								$retval[] = $language_id;
						}
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// check if a product featuresets value exists in the live data table
		//
		// returns an array of found items - or otherwise an empty array.
		//
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkExistenceLiveTable($products_featuresets_id, $titles) {
				global $db;
				
				$retval = array();
				
				foreach($titles as $language_id => $title) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT pov.* FROM ' . $db->table('products_featuresets_values') . ' pov ' .
										'JOIN ' . $db->table('products_featuresets_values_descriptions') . ' povd ON pov.id = povd.products_featuresets_values_id ' .
								'WHERE ' .
												'pov.products_featuresets_id = :products_featuresets_id AND ' .
												'povd.title = :title AND ' .
												'povd.languages_id = :datalanguages_id'
						);
						$db->bind(':products_featuresets_id', $products_featuresets_id);
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
		// Load one products_featuresets_values item by products_featuresets_values_id
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadById($products_featuresets_values_id, $datalanguages_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' .
								$db->table('products_featuresets_values') . ' pov ' .
						'JOIN ' . $db->table('products_featuresets_values_descriptions') . ' povd ' .
								'ON pov.id = povd.products_featuresets_values_id AND povd.languages_id = :datalanguages_id ' .
						'WHERE pov.id = :id ' .
						'ORDER BY pov.sort_order, povd.title'
				);
				$db->bind(':datalanguages_id', (int)$datalanguages_id);
				$db->bind(':id', (int)$products_featuresets_values_id);
				
				$result = $db->execute();
				
				//We should only receive one - because we are asking for the auto-increment main id of the table!!
				$retval = $result->fetchArrayAssoc();
				$retval['descriptions'] = cProductfeaturesetsvalues::loadDescriptions($retval['id']);
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// load products featuresets values (load all for a specific products featuresets id)
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadProductFeaturesetsValues($products_featuresets_id, $datalanguages_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('products_featuresets_values') . ' pov ' .
						'JOIN ' . $db->table('products_featuresets_values_descriptions') . ' povd ' .
										'ON pov.id = povd.products_featuresets_values_id AND povd.languages_id = :datalanguages_id ' .
						'WHERE ' .
								'pov.products_featuresets_id = :products_featuresets_id ' .
						'ORDER BY pov.sort_order, povd.title'
				);
				$db->bind(':datalanguages_id', (int)$datalanguages_id);
				$db->bind(':products_featuresets_id', (int)$products_featuresets_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$tmp['descriptions'] = cProductfeaturesetsvalues::loadDescriptions($tmp['id']);
						
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// load products featuresets values descriptions
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadDescriptions($products_featuresets_values_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('products_featuresets_values_descriptions') . ' WHERE products_featuresets_values_id = :products_featuresets_values_id');
				$db->bind(':products_featuresets_values_id', $products_featuresets_values_id);
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