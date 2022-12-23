<?php

class cProductcategoriesimages extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// @INFO		save product categories images
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function save($id, $product_categories_data) {
				$tmp_product_categories_id = $product_categories_data['tmp_product_categories_id'];
				
				$file_source_folder = 'data/tmp/tmpuploads/';
				$file_dest_folder = 'data/images/categories_images/';
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('tmp_product_categories_images') . ' ' . 
						'WHERE tmp_product_categories_id = :tmp_product_categories_id'
				);
				$db->bind(':tmp_product_categories_id', $tmp_product_categories_id);
				$result = $db->execute();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						//if this item is removed or to be removed..
						if($tmp['remove_flag'] == 1 ) {		//if this item is removed (temporary and existing documents)
								if(!empty($tmp['documents_id'])) {		//if this item is to be removed (existing documents only!)
										//mv_documents_remove($tmp['documents_id']);
										cProductcategoriesimages::remove($id, $tmp['documents_id']);		//only remove the image - but leave the document intact..
								}
								continue;		//skip the following inserts..
						}
		
						//check if the document already exists
						if(!empty($tmp['documents_id'])) {
								$document_id = $tmp['documents_id'];
						} else {
								//create the document in database
								$document_id = cDocument::create();
						}
		
						//move the files
						if(!empty($tmp['tmp_images_filename'])) {
								rename($file_source_folder . $tmp['tmp_images_filename'], $file_dest_folder . $document_id . $tmp['file_extension']);
								cDocument::saveFileData($document_id, str_replace('.', '', $tmp['file_extension']));
						}
		
						//save the extended data in document
						cDocument::saveData($document_id, 1, $tmp['file_source'], $tmp['license_type'], $tmp['qualifier']);
		
						//save image data in database
						cProductcategoriesimages::saveInDatabase($id, $document_id, $tmp['sort_order']);
		
						//save the additional data in database
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT * ' .
										'FROM ' . $db->table('tmp_product_categories_images_descriptions') . ' ' .
								'WHERE ' .
										'tmp_product_categories_id = :tmp_product_categories_id AND ' .
										'(tmp_images_filename = :tmp_images_filename OR documents_id = :documents_id)'
						);
						$db->bind(':tmp_product_categories_id', $tmp_product_categories_id);
						$db->bind(':tmp_images_filename', $tmp['tmp_images_filename']);
						$db->bind(':documents_id', $document_id);
						$subresult = $db->execute();
		
						while($subresult->next()) {
								$subtmp = $subresult->fetchArrayAssoc();
								cProductcategoriesimagesdescriptions::save($id, $document_id, $subtmp['language_id'], $subtmp['alt_tag'], $subtmp['title_tag']);
						}
				}
		}
				
		/////////////////////////////////////////////////////////////////////////////////////////////
		// Delete temporary product categories image.
		/////////////////////////////////////////////////////////////////////////////////////////////
		public static function tmpImagesRemove($product_categories_id, $documents_id) {
				//delete image..
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM from ' . $db->table('product_categories_images') . ' WHERE categories_id = :categories_id AND documents_id = :documents_id');
				$db->bind(':categories_id', (int)$product_categories_id);
				$db->bind(':documents_id', (int)$documents_id);
				$db->execute();
				
				//delete alt and title tags..
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'DELETE FROM ' . $db->table('product_categories_images_descriptions') . ' ' .
						'WHERE categories_id = :categories_id AND documents_id = :documents_id;'
				);
				$db->bind(':categories_id', (int)$product_categories_id);
				$db->bind(':documents_id', (int)$documents_id);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		// Save product categories images data in database.
		/////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveInDatabase($product_categories_id, $documents_id, $sort_order) {
				//check if this entry exists
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('product_categories_images') . ' ' .
						'WHERE categories_id = :categories_id AND documents_id = :documents_id'
				);
				$db->bind(':categories_id', (int)$product_categories_id);
				$db->bind(':documents_id', (int)$documents_id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				if(empty($data)) {
						//insert
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'INSERT INTO ' . $db->table('product_categories_images') . ' ' .
										'(categories_id, documents_id, sort_order)' .
								'VALUES(:categories_id, :documents_id, :sort_order);'
						);
						$db->bind(':categories_id', (int)$product_categories_id);
						$db->bind(':documents_id', (int)$documents_id);
						$db->bind(':sort_order', (int)$sort_order);
						$db->execute();
				} else {
						//update
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'UPDATE ' . $db->table('product_categories_images') . ' SET ' .
										'sort_order = :sort_order ' .
								'WHERE ' .
										'categories_id = :categories_id AND ' .
										'documents_id = :documents_id'
						);
						$db->bind(':sort_order', (int)$sort_order);
						$db->bind(':categories_id', (int)$product_categories_id);
						$db->bind(':documents_id', (int)$documents_id);
						$db->execute();
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////
		// @INFO		load images for a product-category
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function loadByCategoryId($categories_id) {
				$datalanguages = cDatalanguages::loadActivated();
				
				//select all images for this products id		
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT d.*, ci.categories_id, ci.sort_order from ' . $db->table('documents') . ' d
								JOIN ' . $db->table('product_categories_images') . ' ci ON d.id = ci.documents_id
						WHERE ci.categories_id = :categories_id
						ORDER BY ci.sort_order ASC'
				);
				$db->bind(':categories_id', (int)$categories_id);
				$result = $db->execute();
				
				$data = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						//prepare languages..
						$categories_images_descriptions = array();
						
						foreach($datalanguages as $tmpdl) {
								$categories_images_descriptions[$tmpdl['id']] = array(
																																'language_id' => $tmpdl['id'],
																																'alt_tag' => '',
																																'title_tag' => ''
																																		);
						}
						
						//select languages
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT * FROM ' . $db->table('product_categories_images_descriptions') . ' ' .
								'WHERE categories_id = :categories_id AND documents_id = :documents_id');
						$db->bind(':categories_id', $categories_id);
						$db->bind(':documents_id', $tmp['id']);
						$subresult = $db->execute();
		
						while($subresult->next()) {
								$subtmp = $subresult->fetchArrayAssoc();
								
								$categories_images_descriptions[$subtmp['language_id']]['language_id'] = $subtmp['language_id'];
								$categories_images_descriptions[$subtmp['language_id']]['alt_tag'] = $subtmp['alt_tag'];
								$categories_images_descriptions[$subtmp['language_id']]['title_tag'] = $subtmp['title_tag'];
								
						}
						
						$tmp['categories_images_descriptions'] = $categories_images_descriptions;
						
						$data[] = $tmp;
				}
				
				return $data;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////
		// @INFO		saves the data for a categories image
		///////////////////////////////////////////////////////////////////////////////////////////
		/*function mv_categories_images_save_data($categories_id, $documents_id) {
				global $db;
				
				//check if this entry exists
				$db->setQuery('select * from ' . TABLE_PRODUCT_CATEGORIES_IMAGES . ' where categories_id = :categories_id and documents_id = :documents_id');
				$db->setChar(':categories_id', $categories_id);
				$db->setChar(':documents_id', $documents_id);
				$result = $db->execute();
				
				$data = $result->fetch_array_assoc();
				
				if(empty($data)) {
						//insert
						$db->setQuery('insert into ' . TABLE_PRODUCT_CATEGORIES_IMAGES . ' (categories_id, documents_id, sort_order) values(:categories_id, :documents_id, :sort_order);');
						$db->setChar(':categories_id', $categories_id);
						$db->setChar(':documents_id', $documents_id);
						$db->setChar(':sort_order', '0');
						$db->execute();
				} else {
						//update
						$db->setQuery('update ' . TABLE_PRODUCT_CATEGORIES_IMAGES . ' set
															sort_order = :sort_order
													where
															categories_id = :categories_id and
															documents_id = :documents_id
													');
						$db->setChar(':sort_order', $sort_order);
						$db->setChar(':categories_id', $categories_id);
						$db->setChar(':documents_id', $documents_id);
						$db->execute();
				}
		}*/
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		// Delete product categories image
		/////////////////////////////////////////////////////////////////////////////////////////////
		public static function remove($product_categories_id, $documents_id) {
				//delete image..
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('product_categories_images') . ' WHERE categories_id = :categories_id AND documents_id = :documents_id;');
				$db->bind(':categories_id', (int)$product_categories_id);
				$db->bind(':documents_id', (int)$documents_id);
				$db->execute();
				
				//delete alt and title tags..
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'DELETE FROM ' . $db->table('product_categories_images_descriptions') . ' ' . 
						'WHERE categories_id = :categories_id AND documents_id = :documents_id'
				);
				$db->bind(':categories_id', $product_categories_id);
				$db->bind(':documents_id', $documents_id);
				$db->execute();
		}
}

?>