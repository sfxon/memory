<?php

class cProductimages extends cModule {
		/////////////////////////////////////////////////////////////////////////////////////////////
		// Delete product image
		/////////////////////////////////////////////////////////////////////////////////////////////
		public static function remove($products_id, $documents_id) {
				//delete image..
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('products_images') . ' WHERE products_id = :products_id AND documents_id = :documents_id');
				$db->bind(':products_id', (int)$products_id);
				$db->bind(':documents_id', (int)$documents_id);
				$db->execute();
				
				//delete alt and title tags..
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('products_images_descriptions') . ' WHERE products_id = :products_id AND documents_id = :documents_id');
				$db->bind(':products_id', (int)$products_id);
				$db->bind(':documents_id', (int)$documents_id);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		// Save product images data
		/////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveData($products_id, $documents_id, $sort_order) {
				//check if this entry exists
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('products_images') . ' WHERE products_id = :products_id AND documents_id = :documents_id');
				$db->bind(':products_id', (int)$products_id);
				$db->bind(':documents_id', (int)$documents_id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				if(empty($data)) {
						//insert
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'INSERT INTO ' . $db->table('products_images') . ' ' .
										'(products_id, documents_id, sort_order) ' .
								'VALUES ' .
										'(:products_id, :documents_id, :sort_order);'
						);
						$db->bind(':products_id', (int)$products_id);
						$db->bind(':documents_id', (int)$documents_id);
						$db->bind(':sort_order', (int)$sort_order);
						$db->execute();
				} else {
						//update
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'UPDATE ' . $db->table('products_images') . ' SET ' .
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
		public static function saveDescriptions($products_id, $documents_id, $language_id, $alt_tag, $title_tag) {
				//check if this entry exists
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('products_images_descriptions') . ' ' .
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
								'INSERT INTO ' . $db->table('products_images_descriptions') . ' ' .
										'(products_id, documents_id, language_id, alt_tag, title_tag) ' .
								'VALUES ' .
										'(:products_id, :documents_id, :language_id, :alt_tag, :title_tag);'
						);
						$db->bind(':products_id', (int)$products_id);
						$db->bind(':documents_id', (int)$documents_id);
						$db->bind(':language_id', (int)$language_id);
						$db->bind(':alt_tag', $alt_tag);
						$db->bind(':title_tag', $title_tag);
						$db->execute();
				} else {
						//update
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'UPDATE ' . $db->table('products_images_descriptions') . ' WHERE ' .
										'alt_tag = :alt_tag, ' .
										'title_tag = :title_tag ' .
								'WHERE ' .
										'products_id = :products_id AND ' .
										'documents_id = :documents_id AND ' .
										'language_id = :language_id'
						);
						$db->bind(':alt_tag', $alt_tag);
						$db->bind(':title_tag', $title_tag);
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
				
				//select all images for this products id		
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT d.*, pi.products_id, pi.sort_order FROM ' . $db->table('documents') . ' d ' .
								'JOIN ' . $db->table('products_images') . ' pi ON d.id = pi.documents_id ' .
						'WHERE pi.products_id = :products_id ' .
						'ORDER BY pi.sort_order ASC'
				);
				$db->bind(':products_id', (int)$products_id);
				$result = $db->execute();
				
				$data = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						//prepare languages..
						$products_images_descriptions = array();
						
						foreach($datalanguages as $tmpdl) {
								$products_images_descriptions[$tmpdl['id']] = array(
										'language_id' => $tmpdl['id'],
										'alt_tag' => '',
										'title_tag' => ''
								);
						}
						
						//select languages
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery('SELECT * FROM ' . $db->table('products_images_descriptions') . ' WHERE products_id = :products_id AND documents_id = :documents_id');
						$db->bind(':products_id', (int)$products_id);
						$db->bind(':documents_id', (int)$tmp['id']);
						$subresult = $db->execute();
		
						while($subresult->next()) {
								$subtmp = $subresult->fetchArrayAssoc();
								
								$products_images_descriptions[$subtmp['language_id']]['language_id'] = (int)$subtmp['language_id'];
								$products_images_descriptions[$subtmp['language_id']]['alt_tag'] = $subtmp['alt_tag'];
								$products_images_descriptions[$subtmp['language_id']]['title_tag'] = $subtmp['title_tag'];
								
						}
						
						$tmp['products_images_descriptions'] = $products_images_descriptions;
						
						$data[] = $tmp;
				}
				
				return $data;
		}
}

?>