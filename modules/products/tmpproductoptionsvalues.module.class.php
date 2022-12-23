<?php

class cTmpproductoptionsvalues extends cModule {
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//Update the tmp table - Set the value so it can be grabbed by a later function in a saving process.
		//For example: cTmpproductoptionsvalues::loadProductsOptionsIdByTmpProductsOptionsValuesId that is called in
		//cProductattributes::saveFromTmp uses this ID.
		//It tries to get the final ID, if a new option_value was created and used as a new attribute.
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		public static function updateProductOptionsValuesIdByTmpProductsOptionsValuesId($tmp_products_options_values_id, $products_options_values_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('tmp_products_options_values') . ' SET ' .
								'products_options_values_id = :products_options_values_id ' .
						'WHERE ' .
								'tmp_products_options_values_id = :tmp_products_options_values_id'
				);
				$db->bind(':products_options_values_id', (int)$products_options_values_id);
				$db->bind(':tmp_products_options_values_id', $tmp_products_options_values_id);
				$result = $db->execute();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load products options values id by tmp products options values id.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadProductsOptionsIdByTmpProductsOptionsValuesId($tmp_products_options_values_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT products_options_values_id FROM ' . $db->table('tmp_products_options_values') . ' ' .
						'WHERE ' .
								'tmp_products_options_values_id = :tmp_products_options_values_id'
				);
				$db->bind(':tmp_products_options_values_id', $tmp_products_options_values_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false !== $tmp) {
						return $tmp['products_options_values_id'];
				}
				
				return false;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// load tmp products options values description
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadDescription($tmp_products_id, $tmp_products_options_values_id) {
				if(empty($tmp_products_options_values_id)) {
						return false;
				}
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('tmp_products_options_values_descriptions') . ' WHERE ' .
								'tmp_products_id = :tmp_products_id and ' .
								'tmp_products_options_values_id = :tmp_products_options_values_id;'
				);
				$db->bind(':tmp_products_id', $tmp_products_id);
				/*$db->bind(':products_options_values_id', $products_options_values_id);*/
				$db->bind(':tmp_products_options_values_id', $tmp_products_options_values_id);
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
		// update a tmp products options value
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function update(
						$tmp_products_options_values_id, $tmp_products_id, $products_options_id, 
						$tmp_products_options_id, $products_options_values_id, $titles, $sort_order) {
				
				//update the base item
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('tmp_products_options_values') . ' SET ' .
									'sort_order = :sort_order ' .
						'WHERE ' .
									'tmp_products_options_values_id = :tmp_products_options_values_id AND ' .
									'tmp_products_id = :tmp_products_id AND ' .
									'tmp_products_options_id = :tmp_products_options_id AND ' .
									'products_options_id = :products_options_id AND ' .
									'products_options_values_id = :products_options_values_id;'
				);
				$db->bind(':sort_order', $sort_order);
				$db->bind(':tmp_products_options_values_id', $tmp_products_options_values_id);
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->bind(':tmp_products_options_id', $tmp_products_options_id);
				$db->bind(':products_options_id', $products_options_id);
				$db->bind(':products_options_values_id', $products_options_values_id);
				$db->execute();
				
				foreach($titles as $language_id => $title) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'UPDATE ' . $db->table('tmp_products_options_values_descriptions') . ' ' .
										'SET title = :title ' .
								'WHERE ' .
										'tmp_products_options_values_id = :tmp_products_options_values_id AND ' .
										'tmp_products_id = :tmp_products_id AND ' .
										'tmp_products_options_id = :tmp_products_options_id AND ' .
										'products_options_id = :products_options_id AND ' .
										'products_options_values_id = :products_options_values_id AND ' .
										'languages_id = :languages_id;'
						);
						$db->bind(':title', $title);
						$db->bind(':tmp_products_options_values_id', $tmp_products_options_values_id);
						$db->bind(':tmp_products_id', $tmp_products_id);
						$db->bind(':tmp_products_options_id', $tmp_products_options_id);
						$db->bind(':products_options_id', $products_options_id);
						$db->bind(':products_options_values_id', $products_options_values_id);
						$db->bind(':languages_id', $language_id);
						$db->execute();
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// add a tmp products options value
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function add(
						$tmp_products_options_values_id, $tmp_products_id, $products_options_id, 
						$tmp_products_options_id, $products_options_values_id, $titles, $sort_order) {
				//insert the base item
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' .  $db->table('tmp_products_options_values') . ' ' .
								'(tmp_products_options_values_id, tmp_products_id, tmp_products_options_id, products_options_id, products_options_values_id, sort_order) VALUES ' .
								'(:tmp_products_options_values_id, :tmp_products_id, :tmp_products_options_id, :products_options_id, :products_options_values_id, :sort_order)'
				);
				$db->bind(':tmp_products_options_values_id', $tmp_products_options_values_id);
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->bind(':tmp_products_options_id', $tmp_products_options_id);
				$db->bind(':products_options_id', $products_options_id);
				$db->bind(':products_options_values_id', $products_options_values_id);
				$db->bind(':sort_order', (int)$sort_order);
				$db->execute();
				
				foreach($titles as $language_id => $title) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'INSERT INTO ' .  $db->table('tmp_products_options_values_descriptions') . ' ' .
										'(tmp_products_options_values_id, tmp_products_id, tmp_products_options_id, products_options_id, products_options_values_id, languages_id, title) ' .
								'VALUES ' .
										'(:tmp_products_options_values_id, :tmp_products_id, :tmp_products_options_id, :products_options_id, :products_options_values_id, :languages_id, :title)'
						);
						$db->bind(':tmp_products_options_values_id', $tmp_products_options_values_id);
						$db->bind(':tmp_products_id', $tmp_products_id);
						$db->bind(':tmp_products_options_id', $tmp_products_options_id);
						$db->bind(':products_options_id', $products_options_id);
						$db->bind(':products_options_values_id', $products_options_values_id);
						$db->bind(':languages_id', $language_id);
						$db->bind(':title', $title);
						$db->execute();
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// merges two arrays into one - overriding the live data with the new tmp
		//
		// this is for the superb edit mode in the products editor, 
		// where data is not finally saved, before the user hits the save button
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function mergeProductsOptionsValuesWithTmp($original, $tmp) {
				//replace overriden values
				foreach($original as $index => $org) {
						//check if the id exists
						foreach($tmp as $tmpindex => $tmpdata) {
								if($tmpdata['products_options_values_id'] == $org['id']) {	//if the id exists - override!
										$original[$index] = $tmpdata;
										unset($tmp[$tmpindex]);
										break;
								}
						}
				}
				
				//add new values
				foreach($tmp as $tmpindex => $tmpdata) {
						$set = false;
						
						//find the position where we can enter the data
						foreach($original as $index => $org) {
								if($org['sort_order'] < $tmpindex['sort_order']) {
										//insert..
										array_splice($original, $index, 0, $tmpdata);
										$set = true;
										break;
								}
						}
						
						if($set === false) {
								$original[] = $tmpdata;
						}
				}
				
				//print_r($original);
				
				return $original;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// load products options values
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function load($tmp_products_id, $products_options_id, $tmp_products_options_id, $datalanguages_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT pov.* FROM ' . $db->table('tmp_products_options_values') . ' pov ' .
								'JOIN ' . $db->table('tmp_products_options_values_descriptions') . ' povd ON ' .
										'pov.tmp_products_id = povd.tmp_products_id AND ' .
										'pov.products_options_id = povd.products_options_id AND ' .
										'pov.tmp_products_options_id = povd.tmp_products_options_id AND ' .
										'pov.tmp_products_options_values_id = povd.tmp_products_options_values_id AND ' .
										'povd.languages_id = :datalanguages_id ' .
						'WHERE  ' .
								'pov.tmp_products_id = :tmp_products_id AND ' .
								'pov.products_options_id = :products_options_id AND ' .
								'pov.tmp_products_options_id = :tmp_products_options_id ' .
						'ORDER BY pov.sort_order, povd.title'
						);
						
				$db->bind(':datalanguages_id', $datalanguages_id);
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->bind(':products_options_id', $products_options_id);
				$db->bind(':tmp_products_options_id', $tmp_products_options_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$tmp['descriptions'] = cTmpproductoptionsvalues::loadDescriptions($tmp['tmp_products_id'], $tmp['products_options_id'], $tmp['tmp_products_options_id'], $tmp['tmp_products_options_values_id']);
						
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// load the tmp products options values descriptions for a specific products options value
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadDescriptions($tmp_products_id, $products_options_id, $tmp_products_options_id, $tmp_products_options_values_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('tmp_products_options_values_descriptions') . ' WHERE ' .
								'tmp_products_id = :tmp_products_id AND ' .
								'products_options_id = :products_options_id AND ' .
								'tmp_products_options_id = :tmp_products_options_id AND ' .
								'tmp_products_options_values_id = :tmp_products_options_values_id'
				);
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->bind(':products_options_id', $products_options_id);
				$db->bind(':tmp_products_options_id', $tmp_products_options_id);
				$db->bind(':tmp_products_options_values_id', $tmp_products_options_values_id);
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