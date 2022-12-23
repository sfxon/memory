<?php

class cProductcategoriesdescriptions extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// @INFO		save product categories descriptions
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveByPostValues($id, $product_categories_data) {
				$datalanguages = cDatalanguages::loadActivated();
				
				foreach($datalanguages as $lang) {
						$title = core()->getPostVar('title_' . $lang['id']);
						$heading_title = core()->getPostVar('heading_title_' . $lang['id']);
						$description = core()->getPostVar('description_' . $lang['id']);
						$meta_title = core()->getPostVar('meta_title_' . $lang['id']);
						$meta_description = core()->getPostVar('meta_description_' . $lang['id']);
						$meta_keywords = core()->getPostVar('meta_keywords_' . $lang['id']);
						$rewrite_url = core()->getPostVar('rewrite_url_' . $lang['id']);
						
						cProductcategoriesdescriptions::save(
								$id, $lang['id'], 
								$title, $heading_title, $description, $meta_title, $meta_description, $meta_keywords, $rewrite_url
						);
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// @INFO		Alle Beschreibungen für eine Produktkategorie laden
		// @PARAM		$category_id		Die Kategorie-ID
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadByCategoryId($category_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('product_categories_description') . ' WHERE product_categories_id = :categories_id');
				$db->bind(':categories_id', $category_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Kategoriebeschreibung speichern
		// -speichert die Beschreibung für eine Kategorie in einer Sprache!
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function save($category_id, $languages_id, $title, $heading_title, $description, $meta_title, $meta_description, $meta_keywords, $rewrite_url) {
				//check if entry exists.		
				$tmp = cProductcategoriesdescriptions::loadByIdAndLanguage($category_id, $languages_id);
				
				if(false !== $tmp) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'UPDATE ' . $db->table('product_categories_description') . ' SET ' .
										'title = :title, ' .
										'heading_title = :heading_title, ' .
										'description = :description, ' .
										'meta_title = :meta_title, ' .
										'meta_description = :meta_description, ' .
										'meta_keywords = :meta_keywords, ' .
										'rewrite_url = :rewrite_url ' . 
								'WHERE ' .
										'product_categories_id = :categories_id AND ' .
										'languages_id = :languages_id'
						);
						$db->bind(':title', $title);
						$db->bind(':heading_title', $heading_title);
						$db->bind(':description', $description);
						$db->bind(':meta_title', $meta_title);
						$db->bind(':meta_description', $meta_description);
						$db->bind(':meta_keywords', $meta_keywords);
						$db->bind(':rewrite_url', $rewrite_url);
						$db->bind(':categories_id', $category_id);
						$db->bind(':languages_id', $languages_id);
						$db->execute();
				} else {
						//insert
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'INSERT INTO ' . $db->table('product_categories_description') .
										'(product_categories_id, languages_id, title, heading_title, description, meta_title, meta_description, meta_keywords, ' .
										'rewrite_url) ' .
								'VALUES ' .
										'(:categories_id, :languages_id, :title, :heading_title, :description, :meta_title, :meta_description, :meta_keywords, ' .
										':rewrite_url) '
						);
						$db->bind(':categories_id', $category_id);
						$db->bind(':languages_id', $languages_id);
						$db->bind(':title', $title);
						$db->bind(':heading_title', $heading_title);
						$db->bind(':description', $description);
						$db->bind(':meta_title', $meta_title);
						$db->bind(':meta_description', $meta_description);
						$db->bind(':meta_keywords', $meta_keywords);
						$db->bind(':rewrite_url', $rewrite_url);
						$db->execute();
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load by id and language
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadByIdAndLanguage($category_id, $languages_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('product_categories_description') . 
						' WHERE product_categories_id = :products_categories_id AND languages_id = :languages_id'
				);
				$db->bind(':products_categories_id', $category_id);
				$db->bind(':languages_id', $languages_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				return $tmp;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load by Name and language id
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadCategoriesIdByTitleAndLanguageId($title, $languages_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT product_categories_id FROM ' . $db->table('product_categories_description') . 
						' WHERE title = :title AND languages_id = :languages_id'
				);
				$db->bind(':title', $title);
				$db->bind(':languages_id', (int)$languages_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['product_categories_id'])) {
						return $tmp['product_categories_id'];
				}
				
				return false;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////////
		// Create productcategoriesdescription in database.
		/////////////////////////////////////////////////////////////////////////////////////////////////
		public static function createProductCategoriesDescription(
				$category_id, $languages_id, $title, $heading_title,
				$description, $meta_title, $meta_description, $meta_keywords, $rewrite_url)
		{
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('product_categories_description') .
								'(product_categories_id, languages_id, title, heading_title, description, meta_title, meta_description, meta_keywords, ' .
								'rewrite_url) ' .
						'VALUES ' .
								'(:categories_id, :languages_id, :title, :heading_title, :description, :meta_title, :meta_description, :meta_keywords, ' .
								':rewrite_url) '
				);
				$db->bind(':categories_id', $category_id);
				$db->bind(':languages_id', $languages_id);
				$db->bind(':title', $title);
				$db->bind(':heading_title', $heading_title);
				$db->bind(':description', $description);
				$db->bind(':meta_title', $meta_title);
				$db->bind(':meta_description', $meta_description);
				$db->bind(':meta_keywords', $meta_keywords);
				$db->bind(':rewrite_url', $rewrite_url);
				$db->execute();
		}
}

?>