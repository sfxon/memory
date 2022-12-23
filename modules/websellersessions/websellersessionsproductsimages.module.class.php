<?php

class cWebsellersessionsproductsimages extends cModule {
		////////////////////////////////////////////////////////////////////////////////////////////
		// Load array of images.
		////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadImagesArray($products_id) {
				$product_images = cProductimages::loadByProductsId($products_id);
				$attributes_images = cWebsellersessionsproductsimages::loadAttributesData($products_id);
				$features_images = cWebsellersessionsproductsimages::loadFeaturesData($products_id);

				$retval = array(
						'product_images' => $product_images,
						'attributes_images' => $attributes_images,
						'features_images' => $features_images
				);
				
				return $retval;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////
		// Load images for the attributes - it is simply one image per attribute.
		////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadAttributesData($products_id) {
				$attributes = cProductattributes::loadProductsAttributes($products_id, 1);
				$attributes = cProductattributes::loadOptionsTitles($attributes, 1);
				$attributes = cProductattributes::loadOptionsValuesTitles($attributes, 1);
				return $attributes;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////
		// Load images for the attributes - it is simply one image per attribute.
		////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadFeaturesData($products_id) {
				$features = cProductfeatures::loadProductsFeatures($products_id, 1);
				$features = cProductfeatures::loadFeaturesetsTitles($features, 1);
				$features = cProductfeatures::loadFeaturesetsValuesTitles($features, 1);
				return $features;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////
		// Save an entry in the database.
		////////////////////////////////////////////////////////////////////////////////////////////
		public static function save($websellersessions_id, $products_id, $image_id, $image_type, $original_filename, $file_extension) {
				$tmp_entry = cWebsellersessionsproductsimages::loadSessionsProductsImage($websellersessions_id, $products_id, $image_id, $image_type);
				
				if(false === $tmp_entry) {
						cWebsellersessionsproductsimages::create($websellersessions_id, $products_id, $image_id, $image_type, $original_filename, $file_extension);
				} else {
						cWebsellersessionsproductsimages::update($websellersessions_id, $products_id, $image_id, $image_type, $original_filename, $file_extension);
				}
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////
		// Load a specific entry.
		////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadSessionsProductsImage($websellersessions_id, $products_id, $image_id, $image_type) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('webseller_sessions_products_images') . ' ' .
						'WHERE ' .
								'websellersessions_id = :websellersessions_id AND ' .
								'products_id = :products_id AND ' .
								'image_id = :image_id AND ' .
								'image_type = :image_type'
				);
				$db->bind(':websellersessions_id', $websellersessions_id);
				$db->bind(':products_id', (int)$products_id);
				$db->bind(':image_id', (int)$image_id);
				$db->bind(':image_type', $image_type);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				return $tmp;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////
		// Create an entry.
		////////////////////////////////////////////////////////////////////////////////////////////
		public static function create($websellersessions_id, $products_id, $image_id, $image_type, $original_filename, $file_extension) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('webseller_sessions_products_images') . ' ' .
								'(websellersessions_id, products_id, image_id, image_type, original_filename, file_extension) ' .
						'VALUES ' .
								'(:websellersessions_id, :products_id, :image_id, :image_type, :original_filename, :file_extension) '
				);
				$db->bind(':websellersessions_id', (int)$websellersessions_id);
				$db->bind(':products_id', (int)$products_id);
				$db->bind(':image_id', (int)$image_id);
				$db->bind(':image_type', $image_type);
				$db->bind(':original_filename', $original_filename);
				$db->bind(':file_extension', $file_extension);
				$db->execute();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////
		// Update an entry.
		////////////////////////////////////////////////////////////////////////////////////////////
		public static function update($websellersessions_id, $products_id, $image_id, $image_type, $original_filename, $file_extension) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('webseller_sessions_products_images') . ' SET ' .
								'original_filename = :original_filename, ' .
								'file_extension = :file_extension ' .
						'WHERE ' .
								'websellersessions_id = :websellersessions_id AND ' .
								'products_id = :products_id AND ' .
								'image_id = :image_id AND ' .
								'image_type = :image_type'
				);
				$db->bind(':original_filename', $original_filename);
				$db->bind(':file_extension', $file_extension);
				$db->bind(':websellersessions_id', (int)$websellersessions_id);
				$db->bind(':products_id', (int)$products_id);
				$db->bind(':image_id', (int)$image_id);
				$db->bind(':image_type', $image_type);
				$db->execute();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////
		// Check the existence of a file.
		////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkImagesExistence($websellersessions_id, $user_id, $products_id, $products_images_array) {
				foreach($products_images_array as $image_index => $image_array) {
						/*$file_exists = false;
						$db_img_data = cWebsellersessionsproductsimages::*/
						foreach($image_array as $img_index => $img) {
								switch($image_index) {
										case 'product_images':
												$image_type = 'product';
												break;
										case 'attributes_images':
												//var_dump($img);
												$image_type = 'attribute';
												break;
										case 'features_images':
												$image_type = 'feature';
												break;
								}
								
								$file_exists = false;
								$webseller_session_image = cWebsellersessionsproductsimages::loadSessionsProductsImage($websellersessions_id, $products_id, $img['id'], $image_type);
								
								if(false === $webseller_session_image) {
										$products_images_array[$image_index][$img_index]['image_exists'] = false;
										$products_images_array[$image_index][$img_index]['image_src'] = '';
										continue;
								}
								
								$filename = 'img-' . $image_type . '-' . $products_id . '-' . $img['id'] . $webseller_session_image['file_extension'];
								$path = 'data/webseller/sessions/' . $user_id . '/' . $websellersessions_id . '/';
								
								if(file_exists($path . $filename)) {
										$products_images_array[$image_index][$img_index]['image_exists'] = true;
										$products_images_array[$image_index][$img_index]['image_src'] = $path . $filename;
								} else {
										$products_images_array[$image_index][$img_index]['image_exists'] = false;
										$products_images_array[$image_index][$img_index]['image_src'] = '';
								}
						}
				}
				
				return $products_images_array;
		}
}