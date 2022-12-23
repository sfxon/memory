<?php

class cProductfiles extends cModule {
		////////////////////////////////////////////////////////////////////////////////////
		// upload a file
		////////////////////////////////////////////////////////////////////////////////////
		// basic backend implementation for a file upload
		// -> this takes a file, and saves it to a "destination"
		// -> returns an associative array, 
		//		if an error occured, the array contains the index 'error'
		//		if no error occured, the array contains information about the file
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function uploadFile($file_input_name, $destination_path, $destination_filename) {
				$retval = array();
				
				if(isset($_FILES[$file_input_name]['tmp_name'])) {
						if(is_uploaded_file($_FILES[$file_input_name]['tmp_name'])) {
								//get the file extension
								$retval['file_extension'] = pathinfo($_FILES[$file_input_name]['tmp_name'], PATHINFO_EXTENSION);
								
								//move the file
								$result = @move_uploaded_file($_FILES[$file_input_name]['tmp_name'], $destination_path . $destination_filename . $retval['file_extension']);
								
								if(empty($result)) {
										$retval['error'] = '87';
										return $retval;
								}
								
								//set the new filename..
								$retval['original_filename'] = $_FILES[$file_input_name]['name'];
								$retval['filename'] = $destination_filename . $retval['file_extension'];
						} else {
								$retval['error'] = '88';
								return $retval;
						}
				} else {
						$retval['error'] = '89';		//uploaded file not found
				}
				
				return $retval;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		// Delete product file
		/////////////////////////////////////////////////////////////////////////////////////////////
		public static function remove($products_id, $documents_id) {
				//delete file..
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('products_files') . ' WHERE products_id = :products_id AND documents_id = :documents_id');
				$db->bind(':products_id', (int)$products_id);
				$db->bind(':documents_id', (int)$documents_id);
				$db->execute();
				
				//delete alt and title tags..
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('products_files_descriptions') . ' WHERE products_id = :products_id AND documents_id = :documents_id');
				$db->bind(':products_id', (int)$products_id);
				$db->bind(':documents_id', (int)$documents_id);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		// Save product files data
		/////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveData($products_id, $documents_id, $sort_order) {
				//check if this entry exists
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('products_files') . ' WHERE products_id = :products_id AND documents_id = :documents_id');
				$db->bind(':products_id', $products_id);
				$db->bind(':documents_id', $documents_id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				if(empty($data)) {
						//insert
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery('INSERT INTO ' . $db->table('products_files') . ' (products_id, documents_id, sort_order) VALUES(:products_id, :documents_id, :sort_order);');
						$db->bind(':products_id', $products_id);
						$db->bind(':documents_id', $documents_id);
						$db->bind(':sort_order', (int)$sort_order);
						$db->execute();
				} else {
						//update
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'UPDATE ' . $db->table('products_files') . ' SET ' .
										'sort_order = :sort_order ' .
								'WHERE ' .
										'products_id = :products_id AND ' .
										'documents_id = :documents_id'
						);
						$db->bind(':sort_order', (int)$sort_order);
						$db->bind(':products_id', (int)$products_id);
						$db->bind(':documents_id', (int)$documents_id);
						$db->execute();
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Save products images description for one item and one language
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveDescriptions($products_id, $documents_id, $language_id, $title, $comment, $external_link) {
				//check if this entry exists
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('products_files_descriptions') . ' ' .
						'WHERE ' .
								'products_id = :products_id AND ' .
								'documents_id = :documents_id AND ' .
								'language_id = :language_id'
				);
				$db->bind(':products_id', (int)$products_id);
				$db->bind(':documents_id', (int)$documents_id);
				$db->bind(':language_id', (int)$language_id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				if(empty($data)) {
						//insert
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'INSERT INTO ' . $db->table('products_files_descriptions') . ' ' .
										'(products_id, documents_id, language_id, title, comment, external_link) ' .
								'VALUES ' .
										'(:products_id, :documents_id, :language_id, :title, :comment, :external_link);'
						);
						$db->bind(':products_id', (int)$products_id);
						$db->bind(':documents_id', (int)$documents_id);
						$db->bind(':language_id', (int)$language_id);
						$db->bind(':title', $title);
						$db->bind(':comment', $comment);
						$db->bind(':external_link', $external_link);
						$db->execute();
				} else {
						//update
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'UPDATE ' . $db->table('products_files_descriptions') . ' SET ' .
										'title = :title, ' .
										'comment = :comment, ' .
										'external_link = :external_link ' .
								'WHERE ' .
										'products_id = :products_id AND ' .
										'documents_id = :documents_id AND ' .
										'language_id = :language_id'
						);
						$db->bind(':title', $title);
						$db->bind(':comment', $comment);
						$db->bind(':external_link', $external_link);
						$db->bind(':products_id', (int)$products_id);
						$db->bind(':documents_id', (int)$documents_id);
						$db->bind(':language_id', (int)$language_id);
						$db->execute();
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Produktbilder anhand der Dokumenten ID laden
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadByProductsId($products_id) {
				$datalanguages = cDatalanguages::loadActivated();
				
				//select all files for this products id		
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT d.*, pi.products_id, pi.sort_order FROM ' . $db->table('documents') . ' d ' .
								'JOIN ' . $db->table('products_files') . ' pi ON d.id = pi.documents_id ' .
						'WHERE pi.products_id = :products_id ' .
						'ORDER BY pi.sort_order ASC'
				);
				$db->bind(':products_id', (int)$products_id);
				$result = $db->execute();
				
				$data = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						//prepare languages..
						$products_files_descriptions = array();
						
						foreach($datalanguages as $tmpdl) {
								$products_files_descriptions[$tmpdl['id']] = array(
										'language_id' => $tmpdl['id'],
										'title' => '',
										'comment' => '',
										'external_link' => ''
								);
						}
						
						//select languages
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT * FROM ' . $db->table('products_files_descriptions') . ' ' .
								'WHERE ' .
										'products_id = :products_id AND ' .
										'documents_id = :documents_id'
						);
						$db->bind(':products_id', (int)$products_id);
						$db->bind(':documents_id', (int)$tmp['id']);
						$subresult = $db->execute();
		
						while($subresult->next()) {
								$subtmp = $subresult->fetchArrayAssoc();
								
								$products_files_descriptions[$subtmp['language_id']]['language_id'] = $subtmp['language_id'];
								$products_files_descriptions[$subtmp['language_id']]['title'] = $subtmp['title'];
								$products_files_descriptions[$subtmp['language_id']]['comment'] = $subtmp['comment'];
								$products_files_descriptions[$subtmp['language_id']]['external_link'] = $subtmp['external_link'];
						}
						
						$tmp['products_files_descriptions'] = $products_files_descriptions;
						
						$data[] = $tmp;
				}
				
				return $data;
		}
}

?>