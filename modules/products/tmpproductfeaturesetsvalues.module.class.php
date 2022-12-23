<?php

class cTmpproductfeaturesetsvalues extends cModule {
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//Update the tmp table - Set the value so it can be grabbed by a later function in a saving process.
		//For example: cTmpproductfeaturesetsvalues::loadProductsFeaturesetsIdByTmpProductsFeaturesetsValuesId that is called in
		//cProductfeatures::saveFromTmp uses this ID.
		//It tries to get the final ID, if a new feature_value was created and used as a new feature.
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		public static function updateProductFeaturesetsValuesIdByTmpProductsFeaturesetsValuesId($tmp_products_featuresets_values_id, $products_featuresets_values_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('tmp_products_featuresets_values') . ' SET ' .
								'products_featuresets_values_id = :products_featuresets_values_id ' .
						'WHERE ' .
								'tmp_products_featuresets_values_id = :tmp_products_featuresets_values_id'
				);
				$db->bind(':products_featuresets_values_id', (int)$products_featuresets_values_id);
				$db->bind(':tmp_products_featuresets_values_id', $tmp_products_featuresets_values_id);
				$result = $db->execute();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load products featuresets values id by tmp products featuresets values id.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadProductsFeaturesetsIdByTmpProductsFeaturesetsValuesId($tmp_products_featuresets_values_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT products_featuresets_values_id FROM ' . $db->table('tmp_products_featuresets_values') . ' ' .
						'WHERE ' .
								'tmp_products_featuresets_values_id = :tmp_products_featuresets_values_id'
				);
				$db->bind(':tmp_products_featuresets_values_id', $tmp_products_featuresets_values_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false !== $tmp) {
						return $tmp['products_featuresets_values_id'];
				}
				
				return false;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// load tmp products featuresets values description
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadDescription($tmp_products_id, $tmp_products_featuresets_values_id) {
				if(empty($tmp_products_featuresets_values_id)) {
						return false;
				}
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('tmp_products_featuresets_values_descriptions') . ' WHERE ' .
								'tmp_products_id = :tmp_products_id and ' .
								'tmp_products_featuresets_values_id = :tmp_products_featuresets_values_id;'
				);
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->bind(':tmp_products_featuresets_values_id', $tmp_products_featuresets_values_id);
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
		// update a tmp products featuresets value
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function update(
						$tmp_products_featuresets_values_id, $tmp_products_id, $products_featuresets_id, 
						$tmp_products_featuresets_id, $products_featuresets_values_id, $titles, $sort_order) {
				
				//update the base item
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('tmp_products_featuresets_values') . ' SET ' .
									'sort_order = :sort_order ' .
						'WHERE ' .
									'tmp_products_featuresets_values_id = :tmp_products_featuresets_values_id AND ' .
									'tmp_products_id = :tmp_products_id AND ' .
									'tmp_products_featuresets_id = :tmp_products_featuresets_id AND ' .
									'products_featuresets_id = :products_featuresets_id AND ' .
									'products_featuresets_values_id = :products_featuresets_values_id;'
				);
				$db->bind(':sort_order', (int)$sort_order);
				$db->bind(':tmp_products_featuresets_values_id', $tmp_products_featuresets_values_id);
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->bind(':tmp_products_featuresets_id', $tmp_products_featuresets_id);
				$db->bind(':products_featuresets_id', (int)$products_featuresets_id);
				$db->bind(':products_featuresets_values_id', (int)$products_featuresets_values_id);
				$db->execute();
				
				foreach($titles as $language_id => $title) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'UPDATE ' . $db->table('tmp_products_featuresets_values_descriptions') . ' ' .
										'SET title = :title ' .
								'WHERE ' .
										'tmp_products_featuresets_values_id = :tmp_products_featuresets_values_id AND ' .
										'tmp_products_id = :tmp_products_id AND ' .
										'tmp_products_featuresets_id = :tmp_products_featuresets_id AND ' .
										'products_featuresets_id = :products_featuresets_id AND ' .
										'products_featuresets_values_id = :products_featuresets_values_id AND ' .
										'languages_id = :languages_id;'
						);
						$db->bind(':title', $title);
						$db->bind(':tmp_products_featuresets_values_id', $tmp_products_featuresets_values_id);
						$db->bind(':tmp_products_id', $tmp_products_id);
						$db->bind(':tmp_products_featuresets_id', $tmp_products_featuresets_id);
						$db->bind(':products_featuresets_id', (int)$products_featuresets_id);
						$db->bind(':products_featuresets_values_id', (int)$products_featuresets_values_id);
						$db->bind(':languages_id', (int)$language_id);
						$db->execute();
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Add a tmp products featuresets value.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function add(
						$tmp_products_featuresets_values_id, $tmp_products_id, $products_featuresets_id, 
						$tmp_products_featuresets_id, $products_featuresets_values_id, $titles, $sort_order) {
				//insert the base item
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' .  $db->table('tmp_products_featuresets_values') . ' ' .
								'(tmp_products_featuresets_values_id, tmp_products_id, tmp_products_featuresets_id, products_featuresets_id, products_featuresets_values_id, sort_order) ' .
						' VALUES ' .
								'(:tmp_products_featuresets_values_id, :tmp_products_id, :tmp_products_featuresets_id, :products_featuresets_id, :products_featuresets_values_id, :sort_order)'
				);
				$db->bind(':tmp_products_featuresets_values_id', $tmp_products_featuresets_values_id);
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->bind(':tmp_products_featuresets_id', $tmp_products_featuresets_id);
				$db->bind(':products_featuresets_id', (int)$products_featuresets_id);
				$db->bind(':products_featuresets_values_id', (int)$products_featuresets_values_id);
				$db->bind(':sort_order', (int)$sort_order);
				$db->execute();
				
				foreach($titles as $language_id => $title) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'INSERT INTO ' .  $db->table('tmp_products_featuresets_values_descriptions') . ' ' .
										'(tmp_products_featuresets_values_id, tmp_products_id, tmp_products_featuresets_id, products_featuresets_id, ' .
										'products_featuresets_values_id, languages_id, title) ' .
								'VALUES ' .
										'(:tmp_products_featuresets_values_id, :tmp_products_id, :tmp_products_featuresets_id, :products_featuresets_id, ' .
										':products_featuresets_values_id, :languages_id, :title)'
						);
						$db->bind(':tmp_products_featuresets_values_id', $tmp_products_featuresets_values_id);
						$db->bind(':tmp_products_id', $tmp_products_id);
						$db->bind(':tmp_products_featuresets_id', $tmp_products_featuresets_id);
						$db->bind(':products_featuresets_id', (int)$products_featuresets_id);
						$db->bind(':products_featuresets_values_id', (int)$products_featuresets_values_id);
						$db->bind(':languages_id', (int)$language_id);
						$db->bind(':title', $title);
						$db->execute();
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Merges two arrays into one, overriding the live data with the new tmp.
		//
		// This is for the superb edit mode in the products editor, 
		// where data is not finally saved, before the user hits the save button.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function mergeProductsFeaturesetsValuesWithTmp($original, $tmp) {
				//replace overriden values
				foreach($original as $index => $org) {
						//check if the id exists
						foreach($tmp as $tmpindex => $tmpdata) {
								if($tmpdata['products_featuresets_values_id'] == $org['id']) {	//if the id exists - override!
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
		// Load products featuresets values.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function load($tmp_products_id, $products_featuresets_id, $tmp_products_featuresets_id, $datalanguages_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT pov.* FROM ' . $db->table('tmp_products_featuresets_values') . ' pov ' .
								'JOIN ' . $db->table('tmp_products_featuresets_values_descriptions') . ' povd ON ' .
										'pov.tmp_products_id = povd.tmp_products_id AND ' .
										'pov.products_featuresets_id = povd.products_featuresets_id AND ' .
										'pov.tmp_products_featuresets_id = povd.tmp_products_featuresets_id AND ' .
										'pov.tmp_products_featuresets_values_id = povd.tmp_products_featuresets_values_id AND ' .
										'povd.languages_id = :datalanguages_id ' .
						'WHERE  ' .
								'pov.tmp_products_id = :tmp_products_id AND ' .
								'pov.products_featuresets_id = :products_featuresets_id AND ' .
								'pov.tmp_products_featuresets_id = :tmp_products_featuresets_id ' .
						'ORDER BY pov.sort_order, povd.title'
						);
						
				$db->bind(':datalanguages_id', (int)$datalanguages_id);
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->bind(':products_featuresets_id', (int)$products_featuresets_id);
				$db->bind(':tmp_products_featuresets_id', $tmp_products_featuresets_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$tmp['descriptions'] = cTmpproductfeaturesetsvalues::loadDescriptions($tmp['tmp_products_id'], $tmp['products_featuresets_id'], $tmp['tmp_products_featuresets_id'], $tmp['tmp_products_featuresets_values_id']);
						
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load the tmp products featuresets values descriptions for a specific products featuresets value.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadDescriptions($tmp_products_id, $products_featuresets_id, $tmp_products_featuresets_id, $tmp_products_featuresets_values_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('tmp_products_featuresets_values_descriptions') . ' WHERE ' .
								'tmp_products_id = :tmp_products_id AND ' .
								'products_featuresets_id = :products_featuresets_id AND ' .
								'tmp_products_featuresets_id = :tmp_products_featuresets_id AND ' .
								'tmp_products_featuresets_values_id = :tmp_products_featuresets_values_id'
				);
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->bind(':products_featuresets_id', (int)$products_featuresets_id);
				$db->bind(':tmp_products_featuresets_id', $tmp_products_featuresets_id);
				$db->bind(':tmp_products_featuresets_values_id', $tmp_products_featuresets_values_id);
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