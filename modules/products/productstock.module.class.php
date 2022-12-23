<?php

class cProductstock extends cModule {
		/////////////////////////////////////////////////////////////////////////////////////////////
		// save products stock
		/////////////////////////////////////////////////////////////////////////////////////////////
		public static function save($id, $products_data) {
				$products_stock = $products_data['products_stock'];
				
				if(false !== strpos($products_stock, '.') && false !== strpos($products_stock, ',')) {
						$products_stock = str_replace('.', '', $products_stock);
				}
						
				$products_stock = str_replace(',', '.', $products_stock);
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('products') . ' SET ' .
								'products_stock = :products_stock ' . 
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':products_stock', (float)$products_stock);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
}

?>