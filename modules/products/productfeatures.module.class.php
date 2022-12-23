<?php

class cProductfeatures extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// lib_products_feature remove by features_id
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function remove($features_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('products_features') . ' WHERE id = :id');
				$db->bind(':id', (int)$features_id);
				$db->execute();
		}

		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load by Products-ID and Featuresets Values Id.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadByProductsIdAndFeaturesetsValuesId($products_id, $products_featuresets_values_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('products_features') . ' WHERE ' .
								'products_id = :products_id AND ' .
								'products_featuresets_values_id = :products_featuresets_values_id'
				);
				$db->bind(':products_id', (int)$products_id);
				$db->bind(':products_featuresets_values_id', (int)$products_featuresets_values_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				return $tmp;
		}

		///////////////////////////////////////////////////////////////////////////////////////////////////
		// checks if a combination of featuresets_id and featuresets_values_id exists in our database,
		// but by titles and a language_id..
		// this is mainly used for data matching with third party connections
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkFeaturesetAndFeaturesetValueCombinationByTitles($language_id, $products_featuresets_title, $products_featuresets_values_title) {
				//check the featureset..
				$feature = cProductfeaturesets::loadByTitle($language_id, $products_featuresets_title);
				
				if($feature === false) {
						return false;
				}
				
				die('TODO: Implement!! ' . __FILE__ . '; Line: ' . __LINE__);
		}

		///////////////////////////////////////////////////////////////////////////////////////////////////
		// check if an feature exists by products_id and products_optoins_values_id
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkExistenceByFeaturesetsValuesId($products_id, $products_featuresets_values_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT id FROM ' . $db->table('products_features') . ' ' .
						'WHERE products_id = :products_id ' .
								'AND products_featuresets_values_id = :products_featuresets_values_id;'
				);
				$db->bind(':products_id', (int)$products_id);
				$db->bind(':products_featuresets_values_id', (int)$products_featuresets_values_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false === $tmp) {
						return false;
				}
				
				return $tmp['id'];
		}

		///////////////////////////////////////////////////////////////////////////////////////////////////
		// load all features for one product, but only the basic information
		// if you need description and a default title too, use mv_products_features_load
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadBaseDataOnlyByProductsId($products_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('products_features') . ' WHERE products_id = :products_id');
				$db->bind(':products_id', $products_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						$tmp['features_id'] = $tmp['id'];		//needed for example for the table template in the products editor
						$retval[$tmp['id']] = $tmp;
				}
				
				return $retval;
		}

		///////////////////////////////////////////////////////////////////////////////////////////////////
		// load all products features
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadProductsFeatures($products_id, $datalanguages_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('products_features') . ' WHERE products_id = :products_id');
				$db->bind(':products_id', $products_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						$tmp['features_id'] = $tmp['id'];		//Needed for example for the table template in the products editor.

						//Load featuresetss id that belongs to this products_featuresets_values_id.
						$tmp_products_featuresets_value = cProductfeaturesetsvalues::loadById($tmp['products_featuresets_values_id'], $datalanguages_id);
						$products_featuresets_id = $tmp_products_featuresets_value['products_featuresets_id'];		//assign an often needed value
						
						if(false === $products_featuresets_id) {
								$tmp['products_featuresets_id'] = 0;
						} else {
								$tmp['products_featuresets_id'] = $products_featuresets_id;
						}
						
						$tmp['tmp_products_featuresets_id'] = '';			//assign an often needed value that shouldn't have a real value here!
						$tmp['tmp_products_featuresets_values_id'] = '';
						
						$retval[] = $tmp;
				}
				
				return $retval;
		}

		///////////////////////////////////////////////////////////////////////////////////////////
		// Load a single feature by products features id
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function loadById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('products_features') . ' ' .
						'WHERE id = :id'
				);
				$db->bind(':id', $id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				return $tmp;
		}

		///////////////////////////////////////////////////////////////////////////////////////////
		// Delete products feature.
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function delete($features_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('products_features') . ' WHERE id = :id');
				$db->bind(':id', $features_id);
				$db->execute();
		}

		///////////////////////////////////////////////////////////////////////////////////////////
		// Produkt-feature aus temporärer Tabelle speichern
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function saveFromTmp($products_id, $products_data) {
				$tmp_products_id = $products_data['tmp_products_id'];
				$products_features_id = 0;
				
				//select all the current entries
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('tmp_products_features') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $tmp_products_id);
				$result = $db->execute();
		
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						$bool_create_product = false;
						$bool_create_feature = false;
						
						if($tmp['delete_flag'] == 1) {
								//if the delete flag isset and an features_id isset -> delete this entry.
								if(!empty($tmp['features_id']) && $tmp['delete_flag'] == 1) {
										cProductfeatures::delete($tmp['features_id']);
								}
						
								continue;		
						}
						
						//check if the features_id exists
						if(!empty($tmp['features_id'])) {
								//if the features_id exists - update existing product featureset
								//check if the products feature exists.. if not - create it..
								$products_feature_current = cProductfeatures::loadById($tmp['features_id']);		//load a single feature
														
								if(false === $products_feature_current) {
										//if the products feature does not exist create products feature
										$bool_create_feature = true;	
								} else {
								}
						} else {
								$bool_create_feature = true;
						}
						
						$products_featuresets_values_id = (int)$tmp['products_featuresets_values_id'];
						$products_featuresets_id = (int)$tmp['products_featuresets_id'];
						
						if(0 === $products_featuresets_values_id)  {
								$products_featuresets_values_id = cTmpproductfeaturesetsvalues::loadProductsFeaturesetsIdByTmpProductsFeaturesetsValuesId($tmp['tmp_products_featuresets_values_id']);
						}
						
						//if the feature is to create
						if($bool_create_feature === true) {
								//create feature
								cProductfeatures::create($products_id, $products_featuresets_values_id, $tmp['sort_order'], $tmp['description']);
						} else {
								//update feature
								cProductfeatures::update($tmp['features_id'], $products_featuresets_values_id, $tmp['sort_order'], $tmp['description']);
						}
				}
		}

		////////////////////////////////////////////////////////////////////////////////////////
		// Update product feature.
		////////////////////////////////////////////////////////////////////////////////////////
		public static function update($products_features_id, $products_featuresets_values_id, $sort_order, $description) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('products_features') . ' SET ' .
								'products_featuresets_values_id = :products_featuresets_values_id, ' .
								'sort_order = :sort_order, ' .
								'description = :description ' .
						'WHERE id = :id'
				);
				$db->bind(':products_featuresets_values_id', (int)$products_featuresets_values_id);
				$db->bind(':sort_order', (int)$sort_order);
				$db->bind(':description', $description);
				$db->bind(':id', (int)$products_features_id);
				$db->execute();
		}

		////////////////////////////////////////////////////////////////////////////////////////
		// create product feature
		////////////////////////////////////////////////////////////////////////////////////////
		public static function create($products_id, $products_featuresets_values_id, $sort_order, $description) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('products_features') . ' (products_id, products_featuresets_values_id, sort_order, description) ' .
						'VALUES (:products_id, :products_featuresets_values_id, :sort_order, :description)'
				);
				$db->bind(':products_id', (int)$products_id);
				$db->bind(':products_featuresets_values_id', (int)$products_featuresets_values_id);
				$db->bind(':sort_order', (int)$sort_order);
				$db->bind(':description', $description);
				$db->execute();
				
				$insert_id = $db->insertId();
				
				return $insert_id;
		}


		////////////////////////////////////////////////////////////////////////////////////////
		// load titles for the featuresets and featuresets values of the features array
		////////////////////////////////////////////////////////////////////////////////////////
		public static function loadFeaturesetsValuesTitles($products_features, $current_datalanguage, $tmp_products_id = false) {
				foreach($products_features as $index => $pa) {
						//load the products_featuresets_descriptions
						$lib_products_featuresets_values_data = cProductfeaturesetsvalues::loadDescriptionsByProductsFeaturesetsValuesId($pa['products_featuresets_values_id']);
		
						//try to load the temporary products_featuresets_description
						if($tmp_products_id != false && $pa['tmp_products_featuresets_values_id'] !== '') {
								$tmp_lib_products_featuresets_values_data = cTmpproductfeaturesetsvalues::loadDescription($tmp_products_id, $pa['tmp_products_featuresets_values_id']);
								
								if(false !== $tmp_lib_products_featuresets_values_data) {
										$lib_products_featuresets_values_data = $tmp_lib_products_featuresets_values_data;
								}
						}
		
						//now get the featuresets titles
						if(false !== $lib_products_featuresets_values_data) {
								if(isset($lib_products_featuresets_values_data[$current_datalanguage]) && isset($lib_products_featuresets_values_data[$current_datalanguage]['title'])) {
										$products_features[$index]['products_featuresets_values_title'] = $lib_products_featuresets_values_data[$current_datalanguage]['title'];
								}
						}
						
						if(!isset($products_features[$index]['products_featuresets_values_title'])) {
								$products_features[$index]['products_featuresets_values_title']= '';
						}
				}
				
				return $products_features;
		}

		////////////////////////////////////////////////////////////////////////////////////////
		// Load titles for the featuresets and featuresets values of the features array.
		////////////////////////////////////////////////////////////////////////////////////////
		public static function loadFeaturesetsTitles($products_features, $current_datalanguage, $tmp_products_id = false) {
				foreach($products_features as $index => $pa) {
						//load the products_featuresets_descriptions
						$lib_products_featuresets_data = cProductfeaturesets::loadDescriptionsByProductsFeaturesetsId($pa['products_featuresets_id']);
		
						//try to load the temporary products_featuresets_description
						if($tmp_products_id != false && $pa['tmp_products_featuresets_id'] !== '') {
								$tmp_lib_products_featuresets_data = cTmpproductfeaturesets::loadDescription($tmp_products_id, $pa['products_featuresets_id'], $pa['tmp_products_featuresets_id']);
								
								if(false !== $tmp_lib_products_featuresets_data) {
										$lib_products_featuresets_data = $tmp_lib_products_featuresets_data;
								}
						}
						
						//now get the featuresets titles
						if(false !== $lib_products_featuresets_data) {
								if(isset($lib_products_featuresets_data[$current_datalanguage]) && isset($lib_products_featuresets_data[$current_datalanguage]['title'])) {
										$products_features[$index]['products_featuresets_title'] = $lib_products_featuresets_data[$current_datalanguage]['title'];
								}
						}
						
						if(!isset($products_features[$index]['products_featuresets_title'])) {
								$products_features[$index]['products_featuresets_title']= '';
						}
						
						
				}
				
				return $products_features;
		}
}

?>