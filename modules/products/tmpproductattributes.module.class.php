<?php

class cTmpproductattributes extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// free tmp products attributes
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function freeByTmpProductsId($tmp_products_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_attributes') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Gibt ein Array zurück in dem temporäre Attribute und bestehende Attribute zusammengefasst sind.
		// -> Stellt also während des Bearbeitens den zukünftigen Stand in bestehende Attribute Tabelle dar
		// -> Liefert keine Einträge zurück, bei denen im temporären Eintrag das delete Flag gesetzt ist
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function mergeAttributesWithTmp($original, $tmp) {
				//replace overriden values
				foreach($original as $index => $org) {
						//check if the id exists
						foreach($tmp as $tmpindex => $tmpdata) {
								if($tmpdata['attributes_id'] == $org['id']) {	//if the id exists - override!
										if($tmpdata['delete_flag'] == 1) {
												unset($original[$index]);
										} else {
												$original[$index] = $tmpdata;
										}
										unset($tmp[$tmpindex]);
										break;
								}
						}
				}
				
				//add new values
				foreach($tmp as $tmpindex => $tmpdata) {
						$set = false;
						
						if($tmpdata['delete_flag'] == 1) {
								continue;
						}
						
						//find the position where we can enter the data
						foreach($original as $index => $org) {
								if($org['sort_order'] > $tmpdata['sort_order']) {
										//insert
										$insert_array = array($tmpdata);
										
										array_splice($original, $index, 0, array($tmpdata));
										$set = true;
										break;
								}
						}
						
						if($set === false) {
								$original[] = $tmpdata;
						}
				}
				
				//print_r($original);
				
				return $original;
				
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// temporäre attribute mit bestehenden Attributen mischen
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function load($tmp_products_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('tmp_products_attributes') . ' WHERE tmp_products_id = :tmp_products_id ORDER BY sort_order');
				$db->bind(':tmp_products_id', $tmp_products_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						//check if there is a slave product for this model id..
						$tmp['products_id_slave'] = cProducts::loadProductsIdByProductsNumber($tmp['attributes_model']);
						
						if($tmp['products_id_slave'] === false) {
								$tmp['slave_product_exists'] = 0;
						} else {
								$tmp['slave_product_exists'] = 1;
						}
						
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// create a tmp products attribute
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function create(
						$tmp_products_id, $tmp_products_options_id, $tmp_products_options_value_id, $products_options_id, $products_options_values_id,
						$attributes_model, $sort_order, $tmp_attributes_id, $attributes_id, $delete_flag = 0) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('tmp_products_attributes') . ' ' .
								'(tmp_products_id, tmp_products_options_id, tmp_products_options_values_id, products_options_id, products_options_values_id, ' .
								'attributes_model, sort_order, tmp_attributes_id, attributes_id, delete_flag) ' .
						'VALUES ' .
							'(:tmp_products_id, :tmp_products_options_id, :tmp_products_options_value_id, :products_options_id, :products_options_values_id, ' .
							' :attributes_model, :sort_order, :tmp_attributes_id, :attributes_id, :delete_flag)'
				);
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->bind(':tmp_products_options_id', $tmp_products_options_id);
				$db->bind(':tmp_products_options_value_id', $tmp_products_options_value_id);
				$db->bind(':products_options_id', $products_options_id);
				$db->bind(':products_options_values_id', $products_options_values_id);
				$db->bind(':attributes_model', $attributes_model);
				$db->bind(':sort_order', $sort_order);
				$db->bind(':tmp_attributes_id', $tmp_attributes_id);
				$db->bind(':attributes_id', $attributes_id);
				$db->bind(':delete_flag', $delete_flag);
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// update a tmp products attribute
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function update(
						$tmp_products_id, $tmp_products_options_id, $tmp_products_options_value_id, $products_options_id, $products_options_values_id,
						$attributes_model, $sort_order, $tmp_attributes_id, $attributes_id, $delete_flag = 0) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('tmp_products_attributes') . ' SET ' .
								'attributes_model = :attributes_model, ' .
								'sort_order = :sort_order, ' .
								'attributes_id = :attributes_id, ' .
								'delete_flag = :delete_flag ' .
						'WHERE ' .
								'tmp_products_id = :tmp_products_id AND ' .
								'products_options_id = :products_options_id AND ' .
								'tmp_products_options_id = :tmp_products_options_id AND ' .
								'products_options_values_id = :products_options_values_id AND ' .
								'tmp_products_options_values_id = :tmp_products_options_values_id;'
				);
				$db->bind(':attributes_model', $attributes_model);
				$db->bind(':sort_order', $sort_order);
				$db->bind(':attributes_id', $attributes_id);
				$db->bind(':delete_flag', $delete_flag);
				
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->bind(':products_options_id', $products_options_id);
				$db->bind(':tmp_products_options_id', $tmp_products_options_id);
				$db->bind(':products_options_values_id', $products_options_values_id);
				$db->bind(':tmp_products_options_values_id', $tmp_products_options_value_id);
				
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// tmp_products_attributes
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkTmpAttributesExistenceButCurrentTmpAttribute(
						$tmp_products_id, $attributes_model, $products_options_id, $tmp_products_options_id, $products_options_values_id, 
						$tmp_products_options_values_id, $tmp_attributes_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('tmp_products_attributes') . ' WHERE ' .
								'tmp_products_id = :tmp_products_id AND ' .
								'products_options_id = :products_options_id AND ' .
								'tmp_products_options_id = :tmp_products_options_id AND ' .
								'products_options_values_id = :products_options_values_id AND ' .
								'tmp_products_options_values_id = :tmp_products_options_values_id AND ' .
								'NOT(tmp_attributes_id = :tmp_attributes_id);'
				);
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->bind(':products_options_id', $products_options_id);
				$db->bind(':tmp_products_options_id', $tmp_products_options_id);
				$db->bind(':products_options_values_id', $products_options_values_id);
				$db->bind(':tmp_products_options_values_id', $tmp_products_options_values_id);
				$db->bind(':tmp_attributes_id', $tmp_attributes_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				return $tmp;
		}
	
	
	
}

?>