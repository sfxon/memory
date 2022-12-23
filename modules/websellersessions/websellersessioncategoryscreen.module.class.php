<?php

class cWebsellersessioncategoryscreen extends cModule {
		public static function loadAllCategories($webseller_sessions_id) {
				$webseller_sessions_data = cWebsellersessions::loadSessionById($webseller_sessions_id);
				
				//WARNING! This does not order the categories. If one product is in multiple categories,
				//it is selected in random! So for the webseller, this means - only use one category!
				//We could ease this, by choosing a specific parent category,
				//but that would require more complex code, and we want it easy at the moment.
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT p2c.categories_id FROM ' . $db->table('webseller_sessions_products') . ' wsp ' .
								'JOIN ' . $db->table('products_to_categories') . ' p2c ON ' .
										'wsp.products_id = p2c.products_id ' .
						'WHERE wsp.webseller_sessions_id = :webseller_sessions_id ' .
						'GROUP BY p2c.categories_id;'
				);
				$db->bind(':webseller_sessions_id', (int)$webseller_sessions_id);
				$result = $db->execute();
				
				$categories = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						//Select the first product of this category and load it's data.
						$products_id = cProducts::getCategoriesFirstProductsId($tmp['categories_id']);
						
						if(empty($products_id)) {
								continue;
						}
						
						$products_data = cProducts::loadComplexProductData($products_id, 1);
						$products_images_array = cWebsellersessionsproductsimages::loadImagesArray($products_id);
						$products_images_array = cWebsellersessionsproductsimages::checkImagesExistence($webseller_sessions_data['id'], $webseller_sessions_data['user_id'], $products_id, $products_images_array);

						$products_data['webseller_products_images'] = $products_images_array;
						$tmp['product'] = $products_data;
						
						$categories[] = $tmp;
				}
				
				return $categories;
		}
}

?>