<?php

class cTmpproductimages extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// mark a product image for deleting
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function delete($tmp_products_id, $tmp_products_image, $documents_id) {
				if(empty($tmp_products_image)) {
						$tmp_products_image = '!!mv.delete.(mv_tmp_products_image_delete).Steve Kraemer!!';
				}
				
				if(empty($documents_id)) {
						$documents_id = 0;
				}
				
				//check if this entry exists..
				if($tmp_products_image != '!!mv.delete.(mv_tmp_products_image_delete).Steve Kraemer!!') {
						//remove a temporary image..
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'UPDATE ' . $db->table('tmp_products_images') . ' SET ' .
										'remove_flag = 1 ' .
								'WHERE ' .
										'tmp_products_id = :tmp_products_id AND ' .
										'tmp_images_filename = :tmp_images_filename'
						);
						$db->bind(':tmp_products_id', $tmp_products_id);
						$db->bind(':tmp_images_filename', $tmp_products_image);
						$db->execute();
				} else {
						//remove a products image..
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery('SELECT * FROM ' . $db->table('tmp_products_images') . ' WHERE tmp_products_id = :tmp_products_id AND documents_id = :documents_id');
						$db->bind(':tmp_products_id', $tmp_products_id);
						$db->bind(':documents_id', (int)$documents_id);
						$result = $db->execute();
						
						$data = $result->fetchArrayAssoc();
						
						if(!empty($data)) {
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'UPDATE ' . $db->table('tmp_products_images') . ' SET ' .
												'set remove_flag = 1 ' .
										'WHERE tmp_products_id = :tmp_products_id and documents_id = :documents_id'
								);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':documents_id', (int)$documents_id);
								$db->execute();
						} else {
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'INSERT INTO ' . $db->table('tmp_products_images') . ' ' .
												'(tmp_products_id, documents_id, remove_flag) ' .
										'VALUES ' .
												'(:tmp_products_id, :documents_id, 1)'
								);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':documents_id', $documents_id);
								$db->execute();
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// save a temporary products image in database
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function save(
						$tmp_products_id, $tmp_products_image, $tmp_images_filename, $original_filename, $file_extension,
						$alt_tags, $title_tags, $sort_order, $file_source, $license_type, $qualifier, $documents_id) {
				//if this is an already existing image!
				if(!empty($documents_id)) {
						//edit an existing image
						//check if there is already a temporary entry for this documents_id
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery('SELECT * FROM ' . $db->table('tmp_products_images') . ' WHERE documents_id = :documents_id AND tmp_products_id = :tmp_products_id');
						$db->setChar(':documents_id', (int)$documents_id);
						$db->setChar(':tmp_products_id', $tmp_products_id);
						$result = $db->execute();
						
						$tmp = $result->fetchArrayAssoc();
						
						if(empty($tmp)) {
								//create a new entry..
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'INSERT INTO ' . $db->table('tmp_products_images') . ' ' . 
												'(tmp_products_id, sort_order, file_source, license_type, qualifier, documents_id) ' .
										'VALUES(:tmp_products_id, :sort_order, :file_source, :license_type, :qualifier, :documents_id);'
								);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':sort_order', (int)$sort_order);
								$db->bind(':file_source', $file_source);
								$db->bind(':license_type', $license_type);
								$db->bind(':qualifier', $qualifier);
								$db->bind(':documents_id', (int)$documents_id);
								$db->execute();
						} else {
								//update an entry
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'UPDATE ' . $db->table('tmp_products_images') . ' SET ' .
												'sort_order = :sort_order, ' .
												'file_source = :file_source, ' .
												'license_type = :license_type, ' .
												'qualifier = :qualifier ' .
										'WHERE ' .
												'documents_id = :documents_id AND ' .
												'tmp_products_id = :tmp_products_id'
								);
								$db->bind(':sort_order', (int)$sort_order);
								$db->bind(':file_source', $file_source);
								$db->bind(':license_type', $license_type);
								$db->bind(':qualifier', $qualifier);
								$db->bind(':documents_id', (int)$documents_id);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->execute();
						}
						
						//set the image - if a new image has been uploaded..
						if(!empty($tmp_images_filename)) {
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'UPDATE ' . $db->table('tmp_products_images') . ' SET ' .
												'tmp_images_filename = :tmp_images_filename, ' .
												'original_filename = :original_filename, ' .
												'file_extension = :file_extension ' .
										'WHERE ' .
												'documents_id = :documents_id AND ' .
												'tmp_products_id = :tmp_products_id'
								);
								$db->bind(':tmp_images_filename', $tmp_images_filename);
								$db->bind(':original_filename', $original_filename);
								$db->bind(':file_extension', $file_extension);
								$db->bind(':documents_id', (int)$documents_id);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->execute();
						}
						
						//set the alt and image tags
						//set the text values..
						foreach($alt_tags as $index => $value) {
								//check if the entry exists..
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'SELECT * FROM ' . $db->table('tmp_products_images_descriptions') . ' WHERE ' .
												'tmp_products_id = :tmp_products_id AND ' .
												'documents_id = :documents_id AND ' .
												'language_id = :language_id'
								);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':documents_id', (int)$documents_id);
								$db->bind(':language_id', (int)$index);
								$result = $db->execute();
								$data = $result->fetchArrayAssoc();
								
								if(empty($data)) {
										//insert, because it doesn't exist
										$db = core()->get('db');
										$db->useInstance('systemdb');
										$db->setQuery(
												'INSERT INTO ' . $db->table('tmp_products_images_descriptions') . ' ' .
														'(tmp_products_id, documents_id, language_id, alt_tag, title_tag) ' .
												'VALUES ' .
														'(:tmp_products_id, :documents_id, :language_id, :alt_tag, :title_tag)'
										);
										$db->bind(':tmp_products_id', $tmp_products_id);
										$db->bind(':documents_id', (int)$documents_id);
										$db->bind(':language_id', (int)$index);
										$db->bind(':alt_tag', $alt_tags[$index]);
										$db->bind(':title_tag', $title_tags[$index]);
										$db->execute();
								} else {
										$db = core()->get('db');
										$db->useInstance('systemdb');
										$db->setQuery(
												'UPDATE ' . $db->table('tmp_products_images_descriptions') . ' SET ' .
														'alt_tag = :alt_tag, ' .
														'title_tag = :title_tag ' .
												'WHERE ' .
														'tmp_products_id = :tmp_products_id AND ' .
														'documents_id = :documents_id AND ' .
														'language_id = :language_id'
										);
										$db->bind(':alt_tag', $alt_tags[$index]);
										$db->bind(':title_tag', $title_tags[$index]);
										$db->bind(':tmp_products_id', $tmp_products_id);
										$db->bind(':documents_id', (int)$documents_id);
										$db->bind(':language_id', (int)$index);
										$db->execute();
								}
						}
				} else {
						//create a new image
						//check if there is already a temporary uploaded tmp_products_image
						if(!empty($tmp_products_image)) {
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'UPDATE ' . $db->table('tmp_products_images') . ' SET ' .
												'sort_order = :sort_order, ' .
												'file_source = :file_source, ' .
												'license_type = :license_type, ' .
												'qualifier = :qualifier ' .
										'WHERE ' .
												'tmp_products_id = :tmp_products_id AND ' .
												'tmp_images_filename = :tmp_products_image'
								);
								$db->bind(':sort_order', (int)$sort_order);
								$db->bind(':file_source', $file_source);
								$db->bind(':license_type', $license_type);
								$db->bind(':qualifier', $qualifier);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':tmp_products_image', $tmp_products_image);
								$db->execute();
								
								//set the text values..
								foreach($alt_tags as $index => $value) {
										//check if the entry exists..
										$db = core()->get('db');
										$db->useInstance('systemdb');
										$db->setQuery(
												'SELECT * FROM ' . $db->table('tmp_products_images_descriptions') . ' WHERE ' .
														'tmp_products_id = :tmp_products_id AND ' .
														'tmp_images_filename = :tmp_images_filename AND ' .
														'language_id = :language_id'
										);
										$db->bind(':tmp_products_id', $tmp_products_id);
										$db->bind(':tmp_images_filename', $tmp_products_image);
										$db->bind(':language_id', (int)$index);
										$result = $db->execute();
										$data = $result->fetchArrayAssoc();
										
										if(empty($data)) {
												//insert, because it doesn't exist
												$db = core()->get('db');
												$db->useInstance('systemdb');
												$db->setQuery(
														'INSERT INTO ' . $db->table('tmp_products_images_descriptions') . ' ' .
																'(tmp_products_id, tmp_images_filename, language_id, alt_tag, title_tag) ' .
														'VALUES ' .
																'(:tmp_products_id, :tmp_images_filename, :language_id, :alt_tag, :title_tag)'
												);
												$db->bind(':tmp_products_id', $tmp_products_id);
												$db->bind(':tmp_images_filename', $tmp_products_image);
												$db->bind(':language_id', (int)$index);
												$db->bind(':alt_tag', $alt_tags[$index]);
												$db->bind(':title_tag', $title_tags[$index]);
												$db->execute();
										} else {
												$db = core()->get('db');
												$db->useInstance('systemdb');
												$db->setQuery(
														'UPDATE ' . $db->table('tmp_products_images_descriptions') . ' SET ' .
																'alt_tag = :alt_tag, ' .
																'title_tag = :title_tag ' .
														'WHERE  ' .
																'tmp_products_id = :tmp_products_id AND ' .
																'tmp_images_filename = :tmp_images_filename AND ' .
																'language_id = :language_id'
												);
												$db->bind(':alt_tag', $alt_tags[$index]);
												$db->bind(':title_tag', $title_tags[$index]);
												$db->bind(':tmp_products_id', $tmp_products_id);
												$db->bind(':tmp_images_filename', $tmp_products_image);
												$db->bind(':language_id', $index);
												$db->execute();
										}
								}
								
								//check if a new picture has been uploaded for this entry..
								if(!empty($tmp_images_filename)) {
										$db = core()->get('db');
										$db->useInstance('systemdb');
										$db->setQuery(
												'UPDATE ' . $db->table('tmp_products_images') . ' SET ' .
														'tmp_images_filename = :tmp_images_filename, ' .
														'file_extension = :file_extension, ' .
														'original_filename = :original_filename ' .
												'WHERE ' .
														'tmp_products_id = :tmp_products_id AND ' .
														'tmp_images_filename = :tmp_products_image'
										);
										$db->bind(':tmp_images_filename', $tmp_images_filename);
										$db->bind(':file_extension', $file_extension);
										$db->bind(':original_filename', $original_filename);
										$db->bind(':tmp_products_id', $tmp_products_id);
										$db->bind(':tmp_products_image', $tmp_products_image);
										$db->execute();
								}
						} else {
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'INSERT INTO ' . $db->table('tmp_products_images') . ' ' .
												'(tmp_products_id, tmp_images_filename, original_filename, file_extension, sort_order, file_source, license_type, qualifier)' .
										'VALUES ' .
												'(:tmp_products_id, :tmp_images_filename, :original_filename, :file_extension, :sort_order, :file_source, :license_type, :qualifier)'
								);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':tmp_images_filename', $tmp_images_filename);
								$db->bind(':original_filename', $original_filename);
								$db->bind(':file_extension', $file_extension);
								$db->bind(':sort_order', (int)$sort_order);
								$db->bind(':file_source', $file_source);
								$db->bind(':license_type', $license_type);
								$db->bind(':qualifier', $qualifier);
								$db->execute();
								
								//set the text values..
								foreach($alt_tags as $index => $value) {
										$db = core()->get('db');
										$db->useInstance('systemdb');
										$db->setQuery(
												'INSERT INTO ' . $db->table('tmp_products_images_descriptions') . ' ' .
														'(tmp_products_id, tmp_images_filename, language_id, alt_tag, title_tag) ' .
												'VALUES ' .
														'(:tmp_products_id, :tmp_images_filename, :language_id, :alt_tag, :title_tag)'
										);
										$db->bind(':tmp_products_id', $tmp_products_id);
										$db->bind(':tmp_images_filename', $tmp_images_filename);
										$db->bind(':language_id', (int)$index);
										$db->bind(':alt_tag', $alt_tags[$index]);
										$db->bind(':title_tag', $title_tags[$index]);
										$db->execute();
								}
						}
				}
		}
}

?>