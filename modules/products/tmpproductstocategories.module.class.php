<?php

class cTmpproductstocategories extends cModule {
		///////////////////////////////////////////////////////////////////
		// Temporären Eintrag der Produkt Zuordnung zur Kategorie
		///////////////////////////////////////////////////////////////////
		public static function save($categories_id, $tmp_products_id, $remove = 0) {
				//check if this entry exists
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('tmp_products_to_categories') . ' ' .
						'WHERE ' .
								'tmp_products_id = :tmp_products_id AND ' .
								'categories_id = :categories_id'
				);
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->bind(':categories_id', (int)$categories_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if($tmp === false) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'INSERT INTO ' . $db->table('tmp_products_to_categories') . ' ' .
										'(tmp_products_id, categories_id, remove, date_created) ' .
								'VALUES ' .
										'(:tmp_products_id, :categories_id, :remove, NOW())'
						);
						$db->bind(':tmp_products_id', $tmp_products_id);
						$db->bind(':categories_id', (int)$categories_id);
						$db->bind(':remove', (int)$remove);
						$db->execute();
				} else {
						$db->setQuery(
								'UPDATE ' . $db->table('tmp_products_to_categories') . ' SET ' .
										'remove = :remove, ' .
										'date_created = NOW() ' .
								'WHERE ' .
										'tmp_products_id = :tmp_products_id AND ' .
										'categories_id = :categories_id'
						);
						$db->bind(':remove', (int)$remove);
						$db->bind(':tmp_products_id', $tmp_products_id);
						$db->bind(':categories_id', (int)$categories_id);
						$db->execute();
				}
		}
}

?>