<?php

class cProductfeaturesets extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////
		// load a single products_featuresets_descriptions by featuresets_id and language_id
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function loadDescriptionByFeaturesetsIdAndLanguageId($products_featuresets_id, $language_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('products_featuresets_descriptions') . ' WHERE ' .
								'products_featuresets_id = :products_featuresets_id AND ' .
								'language_id = :language_id'
				);
				$db->bind(':products_featuresets_id', (int)$products_featuresets_id);
				$db->bind(':language_id', (int)$language_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				return $tmp;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////
		// save products featureset description
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function saveDescription($products_featuresets_id, $language_id, $title) {
				//check if combination of language id and products_featuresets_id exists
				if(false === cProductfeaturesets::loadDescriptionByFeaturesetsIdAndLanguageId($products_featuresets_id, $language_id)) {
						//insert
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'INSERT INTO ' . $db->table('products_featuresets_descriptions') . ' (products_featuresets_id, language_id, title) VALUES ' .
								'(:products_featuresets_id, :language_id, :title)'
						);
						$db->bind(':products_featuresets_id', (int)$products_featuresets_id);
						$db->bind(':language_id', (int)$language_id);
						$db->bind(':title', $title);
						$db->execute();
				} else {
						//update
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'UPDATE ' . $db->table('products_featuresets_descriptions') . ' SET ' .
										'title = :title ' .
								'WHERE ' . 
										'products_featuresets_id = :products_featuresets_id AND ' .
										'language_id = :language_id'
						);
						$db->bind(':title', $title);
						$db->bind(':products_featuresets_id', (int)$products_featuresets_id);
						$db->bind(':language_id', (int)$language_id);
						$db->execute();
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////
		// create a products featureset (creates only the base data - not the description)
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function createFeatureset() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('INSERT INTO ' . $db->table('products_featuresets') . '(sort_order) VALUES (0);');
				$db->execute();
				
				return $db->insertId();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////
		// load one products featuresets data by language_id and title
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function loadByTitle($language_id, $products_featuresets_title) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('products_featuresets_descriptions') . ' WHERE ' .
								'language_id = :language_id AND ' .
								'title = :title'
				);
				$db->bind(':language_id', $language_id);
				$db->bind(':title', $products_featuresets_title);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				return $tmp;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////
		// load a products featuresets description data by the products featuresets id
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function loadDescriptionsByProductsFeaturesetsId($products_featuresets_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('products_featuresets_descriptions') . ' WHERE products_featuresets_id = :products_featuresets_id');
				$db->bind(':products_featuresets_id', $products_featuresets_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[$tmp['language_id']] = $tmp;
				}
				
				if(count($retval) == 0) {
						return false;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////
		// Produkt-Featuresets aus temporärer Tabelle speichern
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function saveFromTemp($products_id, $products_data) {
				$tmp_products_id = $products_data['tmp_products_id'];
				$products_featuresets_id = 0;
				
				//select all the current entries
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('tmp_products_featuresets') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $tmp_products_id);
				$result = $db->execute();
		
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
		
						if(!empty($tmp['products_featuresets_id'])) {
								//update existing product featureset
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery('UPDATE ' . $db->table('products_featuresets') . ' SET sort_order = :sort_order WHERE id = :products_featuresets_id');
								$db->bind(':sort_order', (int)$tmp['sort_order']);
								$db->bind(':products_featuresets_id', (int)$tmp['products_featuresets_id']);
								$db->execute();
								
								$products_featuresets_id = $tmp['products_featuresets_id'];
						} else {
								//insert new product featureset
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery('INSERT INTO ' . $db->table('products_featuresets') . ' (sort_order) VALUES(:sort_order)');
								$db->bind(':sort_order', (int)$tmp['sort_order']);
								$db->execute();
								
								$products_featuresets_id = $db->insertId();
								
								//update the temporary table with the new id, so the tmp products featuresets values for this tmp products featureset can be added to the new products featureset
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'UPDATE ' . $db->table('tmp_products_featuresets') . ' SET products_featuresets_id = :products_featuresets_id WHERE ' .
												'tmp_products_id = :tmp_products_id AND ' .
												'tmp_products_featuresets_id	= :tmp_products_featuresets_id AND ' .
												'products_featuresets_id = 0;'
								);
								$db->bind(':products_featuresets_id', (int)$products_featuresets_id);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':tmp_products_featuresets_id', $tmp['tmp_products_featuresets_id']);
								$db->execute();
						}
						
						//do the product featuresets descriptions
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT * FROM ' . $db->table('tmp_products_featuresets_descriptions') . ' ' .
								'WHERE ' .
										'tmp_products_id = :tmp_products_id AND ' .
										'tmp_products_featuresets_id = :tmp_products_featuresets_id'
						);
						$db->bind(':tmp_products_id', $tmp_products_id);
						$db->bind(':tmp_products_featuresets_id', $tmp['tmp_products_featuresets_id']);
						$subresult = $db->execute();
						
						while($subresult->next()) {
								$subtmp = $subresult->fetchArrayAssoc();
								
								if(!empty($tmp['tmp_products_id'])) {
										//check if this language entry exists
										$db = core()->get('db');
										$db->useInstance('systemdb');
										$db->setQuery(
												'SELECT * FROM ' . $db->table('products_featuresets_descriptions') . ' ' .
												'WHERE ' .
														'products_featuresets_id = :products_featuresets_id AND ' .
														'language_id = :language_id'
										);
										$db->bind(':products_featuresets_id', (int)$products_featuresets_id);
										$db->bind(':language_id', (int)$subtmp['language_id']);
										$desc_result = $db->execute();
										
										$desc_tmp = $desc_result->fetchArrayAssoc();
										
										if(!empty($desc_tmp)) {
												//update
												$db = core()->get('db');
												$db->useInstance('systemdb');
												$db->setQuery(
														'UPDATE ' . $db->table('products_featuresets_descriptions') . ' SET ' .
																'title = :title ' .
														'WHERE ' .
																'products_featuresets_id = :products_featuresets_id AND ' .
																'language_id = :language_id'
												);
												$db->bind(':title', $subtmp['title']);
												$db->bind(':products_featuresets_id', (int)$products_featuresets_id);
												$db->bind(':language_id', (int)$subtmp['language_id']);
												$db->execute();
										} else {
												//insert
												$db = core()->get('db');
												$db->useInstance('systemdb');
												$db->setQuery(
														'INSERT INTO ' . $db->table('products_featuresets_descriptions') . ' ' .
																'(products_featuresets_id, language_id, title) ' .
														'VALUES ' .
																'(:products_featuresets_id, :language_id, :title)'
												);
												$db->bind(':products_featuresets_id', (int)$products_featuresets_id);
												$db->bind(':language_id', (int)$subtmp['language_id']);
												$db->bind(':title', $subtmp['title']);
												$db->execute();
										}
								} else {
										//insert product featureset description
										$db = core()->get('db');
										$db->useInstance('systemdb');
										$db->setQuery(
												'INSERT INTO ' . $db->table('products_featuresets_descriptions') . ' ' .
														'(products_featuresets_id, language_id, title) ' .
												'VALUES ' .
														'(:products_featuresets_id, :language_id, :title)'
										);
										$db->bind(':products_featuresets_id', (int)$products_featuresets_id);
										$db->bind(':language_id',(int)$subtmp['language_id']);
										$db->bind(':title', $subtmp['title']);
										$db->execute();
								}
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load products featuresets.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadFeaturesets($default_datalanguage) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT po.* FROM ' . $db->table('products_featuresets') . ' AS po ' .
								'LEFT JOIN ' . $db->table('products_featuresets_descriptions') . ' AS pod ' .
										'ON po.id = pod.products_featuresets_id AND pod.language_id = :default_datalanguage ' .
						'ORDER BY pod.title'
				);
				$db->bind(':default_datalanguage', (int)$default_datalanguage);
				$result = $db->execute();
				
				//get the results and add the datalanguage..
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT * FROM ' . $db->table('products_featuresets_descriptions') . ' WHERE ' .
										'products_featuresets_id = :products_featuresets_id'
						);
						$db->bind(':products_featuresets_id', (int)$tmp['id']);
						$subresult = $db->execute();
						
						$descriptions = array();
						
						while($subresult->next()) {
								$subtmp = $subresult->fetchArrayAssoc();
								$descriptions[$subtmp['language_id']] = $subtmp;
						}
						
						$tmp['descriptions'] = $descriptions;
						$retval[] = $tmp;
				}
				
				return $retval;
		}
}

?>