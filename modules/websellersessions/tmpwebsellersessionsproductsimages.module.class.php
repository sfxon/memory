<?php

class cTmpwebsellersessionsproductsimages extends cModule {
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Save a logo that has been previously uploaded to the temp folder.
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		public static function save($tmp_websellersessions_id, $tmp_images_filename, $original_filename, $file_extension, $products_id, $image_id, $image_type) {
				//Check if an entry for this websellersessions_id exists
				$tmpwebsellersession_data = cTmpwebsellersessionsproductsimages::loadTmpImage($tmp_websellersessions_id, $products_id, $image_id, $image_type);
				
				if(false === $tmpwebsellersession_data) {
						cTmpwebsellersessionsproductsimages::create($tmp_websellersessions_id, $tmp_images_filename, $original_filename, $file_extension, $products_id, $image_id, $image_type);				
				} else {
						cTmpwebsellersessionsproductsimages::update($tmp_websellersessions_id, $tmp_images_filename, $original_filename, $file_extension, $products_id, $image_id, $image_type);				
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Insert.
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		public static function create($tmp_websellersessions_id, $tmp_images_filename, $original_filename, $file_extension, $products_id, $image_id, $image_type) {
				//No entry in database - insert new one.
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('tmp_webseller_sessions_products_images') . ' ' .
								'(tmp_websellersessions_id, products_id, image_id, image_type, tmp_images_filename, original_filename, file_extension) ' .
						'VALUES ' .
								'(:tmp_websellersessions_id, :products_id, :image_id, :image_type, :tmp_images_filename, :original_filename, :file_extension) '
				);
				$db->bind(':tmp_websellersessions_id', $tmp_websellersessions_id);
				$db->bind(':products_id', (int)$products_id);
				$db->bind(':image_id', (int)$image_id);
				$db->bind(':image_type', $image_type);
				$db->bind(':tmp_images_filename', $tmp_images_filename);
				$db->bind(':original_filename', $original_filename);
				$db->bind(':file_extension', $file_extension);
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Update.
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		public static function update($tmp_websellersessions_id, $tmp_images_filename, $original_filename, $file_extension, $products_id, $image_id, $image_type) {
				//Entry in database exists: update!
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('tmp_webseller_sessions_products_images') . ' SET ' .
								'tmp_images_filename = :tmp_images_filename, ' .
								'original_filename = :original_filename, ' .
								'file_extension = :file_extension ' .
						'WHERE ' .
								'tmp_websellersessions_id = :tmp_websellersessions_id AND ' .
								'products_id = :products_id AND ' .
								'image_id = :image_id AND ' .
								'image_type = :image_type'
				);
				$db->bind(':tmp_images_filename', $tmp_images_filename);
				$db->bind(':original_filename', $original_filename);
				$db->bind(':file_extension', $file_extension);
				$db->bind(':tmp_websellersessions_id', $tmp_websellersessions_id);
				$db->bind(':products_id', (int)$products_id);
				$db->bind(':image_id', (int)$image_id);
				$db->bind(':image_type', $image_type);
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Load temp logo images data from temp table.
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadTmpImage($tmp_websellersessions_id, $products_id, $image_id, $image_type) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('tmp_webseller_sessions_products_images') . ' ' .
						'WHERE ' .
								'tmp_websellersessions_id = :tmp_websellersessions_id AND ' .
								'products_id = :products_id AND ' .
								'image_id = :image_id AND ' .
								'image_type = :image_type ' .
						'LIMIT 1'
				);
				$db->bind(':tmp_websellersessions_id', $tmp_websellersessions_id);
				$db->bind(':products_id', (int)$products_id);
				$db->bind(':image_id', (int)$image_id);
				$db->bind(':image_type', $image_type);
				$result = $db->execute();
				
				return $result->fetchArrayAssoc();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Load an array of temp product images from the temp table.
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadTmpImages($tmp_websellersessions_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('tmp_webseller_sessions_products_images') . ' ' .
						'WHERE ' .
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
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Copy temorary image to live..
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		public static function copyImageFromTempToLive($user_id, $websellersessions_id, $tmp_websellersessions_id, $products_id, $image_id, $image_type) {
				//$data, $tmp_logo_image
				$tmp_data = cTmpwebsellersessionsproductsimages::loadTmpImage($tmp_websellersessions_id, $products_id, $image_id, $image_type);
				
				//copy the file
				$src = 'data/tmp/tmpuploads/' . $tmp_data['tmp_images_filename'];
				$dst = 'data/webseller/sessions/' . $user_id . '/' . $websellersessions_id . '/img-' . $image_type . '-' . $products_id . '-' . $image_id . $tmp_data['file_extension'];
				
				copy($src, $dst);
				
				//Update the websellersession_table with all needed data.
				cWebsellersessionsproductsimages::save($websellersessions_id, $products_id, $image_id, $image_type, $tmp_data['original_filename'], $tmp_data['file_extension']);
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////
		// Load a specific entry.
		////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadTmpSessionsProductsImage($tmp_websellersessions_id, $products_id, $image_id, $image_type) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('tmp_webseller_sessions_products_images') . ' ' .
						'WHERE ' .
								'tmp_websellersessions_id = :tmp_websellersessions_id AND ' .
								'products_id = :products_id AND ' .
								'image_id = :image_id AND ' .
								'image_type = :image_type'
				);
				$db->bind(':tmp_websellersessions_id', $tmp_websellersessions_id);
				$db->bind(':products_id', (int)$products_id);
				$db->bind(':image_id', (int)$image_id);
				$db->bind(':image_type', $image_type);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				return $tmp;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////
		// Check the existence of a file.
		////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkTmpImagesExistence($tmp_websellersessions_id, $user_id, $products_id, $products_images_array) {
				foreach($products_images_array as $image_index => $image_array) {
						/*$file_exists = false;
						$db_img_data = cWebsellersessionsproductsimages::*/
						foreach($image_array as $img_index => $img) {
								switch($image_index) {
										case 'product_images':
												$image_type = 'product';
												break;
										case 'attributes_images':
												$image_type = 'attribute';
												break;
										case 'features_images':
												$image_type = 'feature';
												break;
								}
								
								$file_exists = false;
								$webseller_session_image = cTmpwebsellersessionsproductsimages::loadTmpSessionsProductsImage($tmp_websellersessions_id, $products_id, $img['id'], $image_type);
								
								if(false === $webseller_session_image) {
										if(!isset($products_images_array[$image_index][$img_index]['image_exists'])) {
												$products_images_array[$image_index][$img_index]['image_exists'] = false;
												$products_images_array[$image_index][$img_index]['image_src'] = '';
										}
										continue;
								}
								
								$filename = $webseller_session_image['tmp_images_filename'];
								$path = 'data/tmp/tmpuploads/';
								
								if(file_exists($path . $filename)) {
										$products_images_array[$image_index][$img_index]['image_exists'] = true;
										$products_images_array[$image_index][$img_index]['image_src'] = $path . $filename;
								} else if(!isset($products_images_array[$image_index][$img_index]['image_exists'])) {
										$products_images_array[$image_index][$img_index]['image_exists'] = false;
										$products_images_array[$image_index][$img_index]['image_src'] = '';
								}
						}
				}
				
				return $products_images_array;
		}
}

?>