<?php

class cProductcategoriesimagesdescriptions extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Save product categories images description for one item and one language
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function save($product_categories_id, $documents_id, $language_id, $alt_tag, $title_tag) {
				//check if this entry exists
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('product_categories_images_descriptions') . 
						'WHERE categories_id = :categories_id AND documents_id = :documents_id AND language_id = :language_id'
				);
				$db->bind(':categories_id', (int)$product_categories_id);
				$db->bind(':documents_id', (int)$documents_id);
				$db->bind(':language_id', (int)$language_id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				if(empty($data)) {
						//insert
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'INSERT INTO ' . $db->table('product_categories_images_descriptions') . ' ' .
										'(categories_id, documents_id, language_id, alt_tag, title_tag) ' .
								'VALUES(:categories_id, :documents_id, :language_id, :alt_tag, :title_tag);'
						);
						$db->bind(':categories_id', (int)$product_categories_id);
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
								'UPDATE ' . $db->table('product_categories_images_descriptions') . ' SET ' .
										'alt_tag = :alt_tag, ' .
										'title_tag = :title_tag ' .
								'WHERE ' .
										'categories_id = :categories_id AND ' .
										'documents_id = :documents_id AND ' .
										'language_id = :language_id;'
						);
						$db->bind(':alt_tag', $alt_tag);
						$db->bind(':title_tag', $title_tag);
						$db->bind(':categories_id', (int)$product_categories_id);
						$db->bind(':documents_id', (int)$documents_id);
						$db->bind(':language_id', (int)$language_id);
						$db->execute();
				}
		}
}

?>