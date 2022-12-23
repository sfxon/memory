<?php

class cTmpwebsellersessionsproducts extends cModule {
		/////////////////////////////////////////////////////////////////////////////////////////////
		// Save tmp websellersessions product in temporary table.
		/////////////////////////////////////////////////////////////////////////////////////////////
		public static function save($tmp_websellersessions_id, $products_id) {
				$tmp_entry = cTmpwebsellersessionsproducts::loadByWsIdAndTmpWsIdAndProductsId($tmp_websellersessions_id, $products_id);
				
				if(false === $tmp_entry) {
						cTmpwebsellersessionsproducts::create($tmp_websellersessions_id, $products_id);
				} else {
						cTmpwebsellersessionsproducts::update($tmp_websellersessions_id, $products_id);
				}
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		// Loads an entry by the specified parameters.
		/////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadByWsIdAndTmpWsIdAndProductsId($tmp_websellersessions_id, $products_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('tmp_webseller_sessions_products') . ' WHERE ' . 
								'tmp_websellersessions_id = :tmp_websellersessions_id AND ' .
								'products_id = :products_id '
				);
				$db->bind(':tmp_websellersessions_id', $tmp_websellersessions_id);
				$db->bind(':products_id', (int)$products_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
						
				return $tmp;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		// Creates an entry in the database.
		/////////////////////////////////////////////////////////////////////////////////////////////
		public static function create($tmp_websellersessions_id, $products_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('tmp_webseller_sessions_products') . ' ' . 
								'(tmp_websellersessions_id, products_id, delete_flag) ' .
						'VALUES ' .
								'(:tmp_websellersessions_id, :products_id, 0);'
				);
				$db->bind(':tmp_websellersessions_id', $tmp_websellersessions_id);
				$db->bind(':products_id', (int)$products_id);
				$result = $db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		// Creates an entry in the database.
		/////////////////////////////////////////////////////////////////////////////////////////////
		public static function update($tmp_websellersessions_id, $products_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('tmp_webseller_sessions_products') . ' SET ' . 
								'delete_flag = 0 ' .
						'WHERE ' .
								'tmp_websellersessions_id = :tmp_websellersessions_id AND ' .
								'products_id = :products_id '
				);
				$db->bind(':tmp_websellersessions_id', $tmp_websellersessions_id);
				$db->bind(':products_id', (int)$products_id);
				$result = $db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		// Creates an entry in the database.
		/////////////////////////////////////////////////////////////////////////////////////////////
		public static function delete($tmp_websellersessions_id, $products_id) {
				$tmp_entry = cTmpwebsellersessionsproducts::loadByWsIdAndTmpWsIdAndProductsId($tmp_websellersessions_id, $products_id);
				
				if(false === $tmp_entry) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'INSERT INTO ' . $db->table('tmp_webseller_sessions_products') . ' ' . 
										'(tmp_websellersessions_id, products_id, delete_flag) ' .
								'VALUES ' .
										'(:tmp_websellersessions_id, :products_id, 1);'
						);
						$db->bind(':tmp_websellersessions_id', $tmp_websellersessions_id);
						$db->bind(':products_id', (int)$products_id);
						$result = $db->execute();
				} else {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'UPDATE ' . $db->table('tmp_webseller_sessions_products') . ' SET ' . 
										'delete_flag = 1 ' .
								'WHERE ' .
										'tmp_websellersessions_id = :tmp_websellersessions_id AND ' .
										'products_id = :products_id '
						);
						$db->bind(':tmp_websellersessions_id', $tmp_websellersessions_id);
						$db->bind(':products_id', (int)$products_id);
						$result = $db->execute();
				}
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		// Load products by tmp webseller_sessions_id.
		/////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadProductsByTmpSessionsId($tmp_websellersessions_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('tmp_webseller_sessions_products') . ' WHERE ' . 
								'tmp_websellersessions_id = :tmp_websellersessions_id'
				);
				$db->bind(':tmp_websellersessions_id', $tmp_websellersessions_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$retval[] = $result->fetchArrayAssoc();
				}
				
				return $retval;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		// Merge the two arrays. The tmp data overrides the live data..
		/////////////////////////////////////////////////////////////////////////////////////////////
		public static function mergeTmpWithLive($webseller_sessions_id, $ws, $tmp_ws) {
				foreach($ws as $index => $item) {
						foreach($tmp_ws as $tmp_index => $tmp_item) {
								if($item['products_id'] == $tmp_item['products_id']) {
										//check if the product is to be set or to be removed..
										if($tmp_item['delete_flag'] == 0) {
												//Product is to be add..
												$new_item = array(
														'webseller_sessions_id' => $webseller_sessions_id,
														'products_id' => $tmp_item['products_id']
												);
												$ws[$index] = $new_item;
												unset($tmp_ws[$tmp_index]);
												
										} else {
												//Product is to be removed..
												unset($ws[$index]);
												unset($tmp_ws[$tmp_index]);
										}
										
								}
						}
				}
				
				//////////////////
				//Process all remaining tmp items (add them to the list..)
				foreach($tmp_ws as $tmp_item) {
						$new_item = array(
								'webseller_sessions_id' => $webseller_sessions_id,
								'products_id' => $tmp_item['products_id']
						);
						$ws[] = $new_item;
				}
				
				return $ws;
		}
}

?>