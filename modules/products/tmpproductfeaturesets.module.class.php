<?php

class cTmpproductfeaturesets extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// load tmp products featuresets description
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadDescription($tmp_products_id, $products_featuresets_id, $tmp_products_featuresets_id) {
				if(empty($tmp_products_featuresets_id)) {
						return false;
				}
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('tmp_products_featuresets_descriptions') . ' WHERE ' .
								'tmp_products_id = :tmp_products_id AND ' .
								'tmp_products_featuresets_id = :tmp_products_featuresets_id;'
				);
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->bind(':tmp_products_featuresets_id', $tmp_products_featuresets_id);
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
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Save tmp products featureset
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function save($tmp_products_id, $tmp_products_featuresets_id, $products_featuresets_id, $sort_order) {
				if($products_featuresets_id != 0) {
						//if this is an existing entry
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT * FROM ' . $db->table('tmp_products_featuresets') . ' WHERE ' .
										'products_featuresets_id = :products_featuresets_id AND ' .
										'tmp_products_id = :tmp_products_id AND ' .
										'tmp_products_featuresets_id = :tmp_products_featuresets_id'
						);
						$db->bind(':products_featuresets_id', (int)$products_featuresets_id);
						$db->bind(':tmp_products_id', $tmp_products_id);
						$db->bind(':tmp_products_featuresets_id', $tmp_products_featuresets_id);
						$result = $db->execute();
						
						$tmp = $result->fetchArrayAssoc();
						
						if(false === $tmp) {
								//insert
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'INSERT INTO ' . $db->table('tmp_products_featuresets') . ' ' .
												'(tmp_products_id, tmp_products_featuresets_id, products_featuresets_id, sort_order) ' .
										'VALUES ' .
												'(:tmp_products_id, :tmp_products_featuresets_id, :products_featuresets_id, :sort_order)'
								);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':tmp_products_featuresets_id', $tmp_products_featuresets_id);
								$db->bind(':products_featuresets_id', (int)$products_featuresets_id);
								$db->bind(':sort_order', (int)$sort_order);
								$db->execute();
						} else {
								//update
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'UPDATE ' . $db->table('tmp_products_featuresets') . ' SET ' .
												'sort_order = :sort_order ' .
										'WHERE ' .
												'tmp_products_id = :tmp_products_id AND ' .
												'tmp_products_featuresets_id = :tmp_products_featuresets_id AND ' .
												'products_featuresets_id = :products_featuresets_id'
								);
								$db->bind(':sort_order', (int)$sort_order);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':tmp_products_featuresets_id', $tmp_products_featuresets_id);
								$db->bind(':products_featuresets_id', (int)$products_featuresets_id);
								$db->execute();
						}
				} else {
						//this is a new entry
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT * FROM ' . $db->table('tmp_products_featuresets') . ' ' .
								'WHERE ' .
										'tmp_products_id = :tmp_products_id AND ' .
										'tmp_products_featuresets_id = :tmp_products_featuresets_id;'
						);
						$db->bind(':tmp_products_id', $tmp_products_id);
						$db->bind(':tmp_products_featuresets_id', $tmp_products_featuresets_id);
						$result = $db->execute();
						
						$tmp = $result->fetchArrayAssoc();
						
						if($tmp === false) {
								//insert
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'INSERT INTO ' . $db->table('tmp_products_featuresets') . ' ' .
												'(tmp_products_id, tmp_products_featuresets_id, products_featuresets_id, sort_order) ' .
										'VALUES ' .
												'(:tmp_products_id, :tmp_products_featuresets_id, 0, :sort_order)'
								);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':tmp_products_featuresets_id', $tmp_products_featuresets_id);
								$db->bind(':sort_order',(int) $sort_order);
								$db->execute();
						} else {
								//update
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'UPDATE ' . $db->table('tmp_products_featuresets') . ' SET ' .
												'sort_order = :sort_order ' .
										'WHERE ' .
												'tmp_products_id = :tmp_products_id AND ' .
												'tmp_products_featuresets_id = :tmp_products_featuresets_id'
								);
								$db->bind(':sort_order', (int)$sort_order);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':tmp_products_featuresets_id', $tmp_products_featuresets_id);
								$db->execute();
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Save the tmp products featuresets description
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveDescription($tmp_products_id, $tmp_products_featuresets_id, $titles) {
				foreach($titles as $language_id => $title) {
						//check if there is an entry
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT * FROM ' . $db->table('tmp_products_featuresets_descriptions') . ' ' .
								'WHERE ' .
										'tmp_products_id = :tmp_products_id AND ' . 
										'tmp_products_featuresets_id = :tmp_products_featuresets_id AND ' .
										'language_id = :language_id'
						);
						$db->bind(':tmp_products_id', $tmp_products_id);
						$db->bind(':tmp_products_featuresets_id', $tmp_products_featuresets_id);
						$db->bind(':language_id', (int)$language_id);
						$result = $db->execute();
						
						$tmp = $result->fetchArrayAssoc();
						
						if($tmp === false) {
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'INSERT INTO ' . $db->table('tmp_products_featuresets_descriptions') . ' ' .
												'(tmp_products_id, tmp_products_featuresets_id, language_id, title) ' .
										'VALUES ' .
												'(:tmp_products_id, :tmp_products_featuresets_id, :language_id, :title)'
								);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':tmp_products_featuresets_id', $tmp_products_featuresets_id);
								$db->bind(':language_id', (int)$language_id);
								$db->bind(':title', $title);
								$db->execute();
						} else {
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'UPDATE ' . $db->table('tmp_products_featuresets_descriptions') . ' SET ' .
												'title = :title ' .
										'WHERE ' .
												'tmp_products_id = :tmp_products_id AND ' .
												'tmp_products_featuresets_id = :tmp_products_featuresets_id AND ' .
												'language_id = :language_id'
								);
								$db->bind(':title', $title);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':tmp_products_featuresets_id', $tmp_products_featuresets_id);
								$db->bind(':language_id', (int)$language_id);
								$db->execute();
						}
				}
		}
}
?>