<?php

class cWebsellersessionsproducts extends cModule {
		/////////////////////////////////////////////////////////////////////////////////////
		// Load all entries that have a specific webseller_session_id and products_id
		/////////////////////////////////////////////////////////////////////////////////////
		public static function loadProductsBySessionsId($webseller_sessions_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('webseller_sessions_products') . ' WHERE webseller_sessions_id = :webseller_sessions_id');
				$db->bind(':webseller_sessions_id', (int)$webseller_sessions_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$retval[] = $result->fetchArrayAssoc();
				}
				
				return $retval;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////
		// Load all entries that have a specific webseller_session_id and categories_id
		/////////////////////////////////////////////////////////////////////////////////////
		public static function loadProductsBySessionsIdAndCategoriesId($webseller_sessions_id, $categories_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT wsp.* FROM ' . $db->table('webseller_sessions_products') . ' wsp ' .
								'JOIN ' . $db->table('products_to_categories') . ' p2c ' .
										'ON p2c.products_id = wsp.products_id ' .
						' WHERE ' .
								'webseller_sessions_id = :webseller_sessions_id AND ' .
								'p2c.categories_id = :categories_id'
				);
				$db->bind(':webseller_sessions_id', (int)$webseller_sessions_id);
				$db->bind(':categories_id', (int)$categories_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$retval[] = $result->fetchArrayAssoc();
				}
				
				return $retval;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////
		// General save function.
		/////////////////////////////////////////////////////////////////////////////////////
		public static function save($tmp_websellersessions_id, $webseller_sessions_id, $products_id) {
				$check_product = cWebsellersessionsproducts::loadProductByWsIdAndProductsId($webseller_sessions_id, $products_id);
				
				if(empty($check_product)) {
						cWebsellersessionsproducts::create($webseller_sessions_id, $products_id);
				} else {
						cWebsellersessionsproducts::update($webseller_sessions_id, $products_id);
				}
		}
		
		/////////////////////////////////////////////////////////////////////////////////////
		// Load an entry.
		/////////////////////////////////////////////////////////////////////////////////////
		public static function loadProductByWsIdAndProductsId($webseller_sessions_id, $products_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('webseller_sessions_products') . ' WHERE ' .
								'webseller_sessions_id = :webseller_sessions_id AND ' .
								'products_id = :products_id'
				);
				$db->bind(':webseller_sessions_id', (int)$webseller_sessions_id);
				$db->bind(':products_id', (int)$products_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$retval[] = $result->fetchArrayAssoc();
				}
				
				return $retval;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////
		// Create an entry in the database.
		/////////////////////////////////////////////////////////////////////////////////////
		public static function create($webseller_sessions_id, $products_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('webseller_sessions_products') . ' ' .
								'(webseller_sessions_id, products_id) ' .
						'VALUES ' .
								'(:webseller_sessions_id, :products_id)'
				);
				$db->bind(':webseller_sessions_id', (int)$webseller_sessions_id);
				$db->bind(':products_id', (int)$products_id);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////////
		// Create an entry in the database.
		/////////////////////////////////////////////////////////////////////////////////////
		public static function update($webseller_sessions_id, $products_id) {
				//There is no data yet..
				
				/*$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('webseller_sessions_products') . ' ' .
								'(webseller_sessions_id, products_id) ' .
						'VALUES ' .
								'(:webseller_sessions_id, :products_id)'
				);
				$db->bind(':webseller_sessions_id', (int)$webseller_sessions_id);
				$db->bind(':products_id', (int)$products_id);
				$db->execute();*/
				
		}
		
		/////////////////////////////////////////////////////////////////////////////////////
		// Delete an entry in the database.
		/////////////////////////////////////////////////////////////////////////////////////
		public static function delete($webseller_sessions_id, $products_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'DELETE FROM ' . $db->table('webseller_sessions_products') . ' WHERE ' .
								'webseller_sessions_id = :webseller_sessions_id AND ' .
								'products_id = :products_id'
				);
				$db->bind(':webseller_sessions_id', (int)$webseller_sessions_id);
				$db->bind(':products_id', (int)$products_id);
				$db->execute();
		}
		
		
}

?>