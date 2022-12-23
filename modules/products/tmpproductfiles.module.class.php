<?php

class cTmpproductfiles extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// mark a product file for deleting
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function delete($tmp_products_id, $tmp_products_file, $documents_id) {
				if(empty($tmp_products_file)) {
						$tmp_products_file = '!!mv.delete.(mv_tmp_products_file_delete).Steve Kraemer!!';
				}
				
				if(empty($documents_id)) {
						$documents_id = 0;
				}
				
				//check if this entry exists..
				if($tmp_products_file != '!!mv.delete.(mv_tmp_products_file_delete).Steve Kraemer!!') {
						//remove a temporary file..
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'UPDATE ' . $db->table('tmp_products_files') . ' SET ' .
										'remove_flag = 1 ' .
								'WHERE ' .
										'tmp_products_id = :tmp_products_id AND ' .
										'tmp_files_filename = :tmp_files_filename'
						);
						$db->bind(':tmp_products_id', $tmp_products_id);
						$db->bind(':tmp_files_filename', $tmp_products_file);
						$db->execute();
				} else {
						//remove a products file..
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery('SELECT * FROM ' . $db->table('tmp_products_files') . ' WHERE tmp_products_id = :tmp_products_id AND documents_id = :documents_id');
						$db->bind(':tmp_products_id', $tmp_products_id);
						$db->bind(':documents_id', (int)$documents_id);
						$result = $db->execute();
						
						$data = $result->fetchArrayAssoc();
						
						if(!empty($data)) {
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'UPDATE ' . $db->table('tmp_products_files') . ' SET ' .
												'remove_flag = 1 ' .
										'WHERE ' .
												'tmp_products_id = :tmp_products_id AND ' .
												'documents_id = :documents_id'
								);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':documents_id', (int)$documents_id);
								$db->execute();
						} else {
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'INSERT INTO ' . $db->table('tmp_products_files') . ' ' .
												'(tmp_products_id, documents_id, remove_flag) ' .
										'VALUES' .
												'(:tmp_products_id, :documents_id, 1)'
								);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':documents_id', (int)$documents_id);
								$db->execute();
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// save a temporary products file in database
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function save(
						$tmp_products_id, $tmp_products_file, $tmp_files_filename, $original_filename, $file_extension, 
						$titles, $comments, $external_links, $sort_order, $file_source, $license_type, $qualifier, $documents_id) {
							
				//if this is an already existing file!
				if(!empty($documents_id)) {
						//edit an existing file
						//check if there is already a temporary entry for this documents_id
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery('SELECT * FROM ' . $db->table('tmp_products_files') . ' WHERE documents_id = :documents_id AND tmp_products_id = :tmp_products_id');
						$db->bind(':documents_id', (int)$documents_id);
						$db->bind(':tmp_products_id', $tmp_products_id);
						$result = $db->execute();
						
						$tmp = $result->fetchArrayAssoc();
						
						if(empty($tmp)) {
								//create a new entry..
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'INSERT INTO ' . $db->table('tmp_products_files') . ' ' .
												'(tmp_products_id, sort_order, file_source, license_type, qualifier, documents_id) ' .
										'VALUES ' .
												'(:tmp_products_id, :sort_order, :file_source, :license_type, :qualifier, :documents_id);'
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
										'UPDATE ' . $db->table('tmp_products_files')  . ' SET ' .
												'sort_order = :sort_order, ' .
												'file_source = :file_source, ' .
												'license_type = :license_type, ' .
												'qualifier = :qualifier ' .
										'WHERE ' .
												'documents_id = :documents_id AND ' .
												'tmp_products_id = :tmp_products_id '
								);
								$db->bind(':sort_order', $sort_order);
								$db->bind(':file_source', $file_source);
								$db->bind(':license_type', $license_type);
								$db->bind(':qualifier', $qualifier);
								$db->bind(':documents_id', (int)$documents_id);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->execute();
						}
						
						//set the file - if a new file has been uploaded..
						if(!empty($tmp_files_filename)) {
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'UPDATE ' . $db->table('tmp_products_files') . ' SET ' .
												'tmp_files_filename = :tmp_files_filename, ' .
												'original_filename = :original_filename, ' .
												'file_extension = :file_extension ' .
										'WHERE ' .
												'documents_id = :documents_id AND ' .
												'tmp_products_id = :tmp_products_id'
								);
								$db->bind(':tmp_files_filename', $tmp_files_filename);
								$db->bind(':original_filename', $original_filename);
								$db->bind(':file_extension', $file_extension);
								$db->bind(':documents_id', (int)$documents_id);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->execute();
						}
						
						//set the text values (titles, comments and external_links)
						foreach($titles as $index => $value) {
								//check if the entry exists..
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'SELECT * FROM ' . $db->table('tmp_products_files_descriptions') . ' WHERE ' .
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
												'INSERT INTO ' . $db->table('tmp_products_files_descriptions') . ' (tmp_products_id, documents_id, language_id, title, comment, external_link) ' .
												'VALUES (:tmp_products_id, :documents_id, :language_id, :title, :comment, :external_link)'
										);
										$db->bind(':tmp_products_id', $tmp_products_id);
										$db->bind(':documents_id', (int)$documents_id);
										$db->bind(':language_id', (int)$index);
										$db->bind(':title', $titles[$index]);
										$db->bind(':comment', $comments[$index]);
										$db->bind(':external_link', $external_links[$index]);
										$db->execute();
								} else {
										$db = core()->get('db');
										$db->useInstance('systemdb');
										$db->setQuery(
												'UPDATE ' . $db->table('tmp_products_files_descriptions') . ' SET ' .
														'title = :title, ' .
														'comment = :comment, ' .
														'external_link = :external_link ' .
												'WHERE ' .
														'tmp_products_id = :tmp_products_id AND ' .
														'documents_id = :documents_id AND ' .
														'language_id = :language_id'
										);
										$db->bind(':title', $titles[$index]);
										$db->bind(':comment', $comments[$index]);
										$db->bind(':external_link', $external_links[$index]);
										$db->bind(':tmp_products_id', $tmp_products_id);
										$db->bind(':documents_id', (int)$documents_id);
										$db->bind(':language_id', (int)$index);
										$db->execute();
								}
						}
				} else {
						//create a new file
						//check if there is already a temporary uploaded tmp_products_file
						if(!empty($tmp_products_file)) {
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'UPDATE ' . $db->table('tmp_products_files') . ' SET ' .
												'sort_order = :sort_order, ' .
												'file_source = :file_source, ' .
												'license_type = :license_type, ' .
												'qualifier = :qualifier ' .
										'WHERE ' .
												'tmp_products_id = :tmp_products_id AND ' .
												'tmp_files_filename = :tmp_products_file'
								);
								$db->bind(':sort_order', (int)$sort_order);
								$db->bind(':file_source', $file_source);
								$db->bind(':license_type', $license_type);
								$db->bind(':qualifier', $qualifier);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':tmp_products_file', $tmp_products_file);
								$db->execute();
								
								//set the text values..
								foreach($titles as $index => $value) {
										//check if the entry exists..
										$db = core()->get('db');
										$db->useInstance('systemdb');
										$db->setQuery(
												'SELECT * FROM ' . $db->table('tmp_products_files_descriptions') . ' ' .
												'WHERE ' .
														'tmp_products_id = :tmp_products_id AND ' .
														'tmp_files_filename = :tmp_files_filename AND ' .
														'language_id = :language_id'
										);
										$db->bind(':tmp_products_id', $tmp_products_id);
										$db->bind(':tmp_files_filename', $tmp_products_file);
										$db->bind(':language_id', (int)$index);
										$result = $db->execute();
										$data = $result->fetchArrayAssoc();
										
										if(empty($data)) {
												//insert, because it doesn't exist
												$db = core()->get('db');
												$db->useInstance('systemdb');
												$db->setQuery(
														'INSERT INTO ' . $db->table('tmp_products_files_descriptions') . ' ' .
																'(tmp_products_id, tmp_files_filename, language_id, title, comment, external_link)' .
														'VALUES(:tmp_products_id, :tmp_files_filename, :language_id, :title, :comment, :external_link);'
												);
												$db->bind(':tmp_products_id', $tmp_products_id);
												$db->bind(':tmp_files_filename', $tmp_products_file);
												$db->bind(':language_id',(int)$index);
												$db->bind(':title', $titles[$index]);
												$db->bind(':comment', $comments[$index]);
												$db->bind(':external_link', $external_links[$index]);
												$db->execute();
										} else {
												$db = core()->get('db');
												$db->useInstance('systemdb');
												$db->setQuery(
														'UPDATE ' . $db->table('tmp_products_files_descriptions') . ' SET ' .
																'title = :title, ' .
																'comment = :comment, ' .
																'external_link = :external_link ' .
														'WHERE ' .
																'tmp_products_id = :tmp_products_id AND ' .
																'tmp_files_filename = :tmp_files_filename AND ' .
																'language_id = :language_id'
												);
												$db->bind(':title', $titles[$index]);
												$db->bind(':comment', $comments[$index]);
												$db->bind(':external_link', $external_links[$index]);
												$db->bind(':tmp_products_id', $tmp_products_id);
												$db->bind(':tmp_files_filename', $tmp_products_file);
												$db->bind(':language_id', (int)$index);
												$db->execute();
										}
								}
								
								//check if a new file has been uploaded for this entry..
								if(!empty($tmp_files_filename)) {
										$db = core()->get('db');
										$db->useInstance('systemdb');
										$db->setQuery(
												'UPDATE ' . $db->table('tmp_products_files') . ' SET ' .
														'tmp_files_filename = :tmp_files_filename, ' .
														'file_extension = :file_extension, ' .
														'original_filename = :original_filename ' .
												'WHERE ' .
														'tmp_products_id = :tmp_products_id AND ' .
														'tmp_files_filename = :tmp_products_file'
										);
										$db->bind(':tmp_files_filename', $tmp_files_filename);
										$db->bind(':file_extension', $file_extension);
										$db->bind(':original_filename', $original_filename);
										$db->bind(':tmp_products_id', $tmp_products_id);
										$db->bind(':tmp_products_file', $tmp_products_file);
										$db->execute();
								}
						} else {
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'INSERT INTO ' . $db->table('tmp_products_files') . ' ' .
												'(tmp_products_id, tmp_files_filename, original_filename, file_extension, sort_order, file_source, license_type, qualifier) ' .
										'VALUES ' .
												'(:tmp_products_id, :tmp_files_filename, :original_filename, :file_extension, :sort_order, :file_source, :license_type, :qualifier)'
								);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':tmp_files_filename', $tmp_files_filename);
								$db->bind(':original_filename', $original_filename);
								$db->bind(':file_extension', $file_extension);
								$db->bind(':sort_order', (int)$sort_order);
								$db->bind(':file_source', $file_source);
								$db->bind(':license_type', $license_type);
								$db->bind(':qualifier', $qualifier);
								$db->execute();
								
								//set the text values..
								foreach($titles as $index => $value) {
										$db = core()->get('db');
										$db->useInstance('systemdb');
										$db->setQuery(
												'INSERT INTO ' . $db->table('tmp_products_files_descriptions') . ' ' .
														'(tmp_products_id, tmp_files_filename, language_id, title, comment, external_link) ' .
												'VALUES ' .
														'(:tmp_products_id, :tmp_files_filename, :language_id, :title, :comment, :external_link)'
										);
										$db->bind(':tmp_products_id', $tmp_products_id);
										$db->bind(':tmp_files_filename', $tmp_files_filename);
										$db->bind(':language_id', (int)$index);
										$db->bind(':title', $titles[$index]);
										$db->bind(':comment', $comments[$index]);
										$db->bind(':external_link', $external_links[$index]);
										$db->execute();
								}
						}
				}
		}
}

?>