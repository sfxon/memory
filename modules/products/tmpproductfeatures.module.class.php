<?php

class cTmpproductfeatures extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// free tmp products features
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function freeByTmpProductsId($tmp_products_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_features') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Gibt ein Array zurück in dem temporäre Feature und bestehende Feature zusammengefasst sind.
		// -> Stellt also während des Bearbeitens den zukünftigen Stand in bestehende Feature Tabelle dar
		// -> Liefert keine Einträge zurück, bei denen im temporären Eintrag das delete Flag gesetzt ist
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function mergeFeaturesWithTmp($original, $tmp) {
				//replace overriden values
				foreach($original as $index => $org) {
						//check if the id exists
						foreach($tmp as $tmpindex => $tmpdata) {
								if($tmpdata['features_id'] == $org['id']) {	//if the id exists - override!
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
		// Temporäre Feature mit bestehenden Feature mischen
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function load($tmp_products_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('tmp_products_features') . ' WHERE tmp_products_id = :tmp_products_id ORDER BY sort_order');
				$db->bind(':tmp_products_id', $tmp_products_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Create a tmp products feature.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function create(
						$tmp_products_id, $tmp_products_featuresets_id, $tmp_products_featuresets_value_id, $products_featuresets_id, $products_featuresets_values_id,
						$sort_order, $description, $tmp_features_id, $features_id, $delete_flag = 0) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('tmp_products_features') . ' ' .
								'(tmp_products_id, tmp_products_featuresets_id, tmp_products_featuresets_values_id, products_featuresets_id, products_featuresets_values_id, ' .
								'sort_order, description, tmp_features_id, features_id, delete_flag) ' .
						'VALUES ' .
							'(:tmp_products_id, :tmp_products_featuresets_id, :tmp_products_featuresets_value_id, :products_featuresets_id, :products_featuresets_values_id, ' .
							':sort_order, :description, :tmp_features_id, :features_id, :delete_flag)'
				);
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->bind(':tmp_products_featuresets_id', $tmp_products_featuresets_id);
				$db->bind(':tmp_products_featuresets_value_id', $tmp_products_featuresets_value_id);
				$db->bind(':products_featuresets_id', (int)$products_featuresets_id);
				$db->bind(':products_featuresets_values_id', (int)$products_featuresets_values_id);
				$db->bind(':sort_order', (int)$sort_order);
				$db->bind(':description', $description);
				$db->bind(':tmp_features_id', $tmp_features_id);
				$db->bind(':features_id', (int)$features_id);
				$db->bind(':delete_flag', (int)$delete_flag);
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Update a tmp products feature.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function update(
						$tmp_products_id, $tmp_products_featuresets_id, $tmp_products_featuresets_value_id, $products_featuresets_id, $products_featuresets_values_id,
						$sort_order, $description, $tmp_features_id, $features_id, $delete_flag = 0) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('tmp_products_features') . ' SET ' .
								'sort_order = :sort_order, ' .
								'description = :description, ' .
								'features_id = :features_id, ' .
								'delete_flag = :delete_flag ' .
						'WHERE ' .
								'tmp_products_id = :tmp_products_id AND ' .
								'products_featuresets_id = :products_featuresets_id AND ' .
								'tmp_products_featuresets_id = :tmp_products_featuresets_id AND ' .
								'products_featuresets_values_id = :products_featuresets_values_id AND ' .
								'tmp_products_featuresets_values_id = :tmp_products_featuresets_values_id;'
				);
				$db->bind(':sort_order', $sort_order);
				$db->bind(':description', $description);
				$db->bind(':features_id', $features_id);
				$db->bind(':delete_flag', $delete_flag);
				
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->bind(':products_featuresets_id', $products_featuresets_id);
				$db->bind(':tmp_products_featuresets_id', $tmp_products_featuresets_id);
				$db->bind(':products_featuresets_values_id', $products_featuresets_values_id);
				$db->bind(':tmp_products_featuresets_values_id', $tmp_products_featuresets_value_id);
				
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// tmp_products_features
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkTmpFeaturesExistenceButCurrentTmpFeature(
						$tmp_products_id, $products_featuresets_id, $tmp_products_featuresets_id, $products_featuresets_values_id, 
						$tmp_products_featuresets_values_id, $tmp_features_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('tmp_products_features') . ' WHERE ' .
								'tmp_products_id = :tmp_products_id AND ' .
								'products_featuresets_id = :products_featuresets_id AND ' .
								'tmp_products_featuresets_id = :tmp_products_featuresets_id AND ' .
								'products_featuresets_values_id = :products_featuresets_values_id AND ' .
								'tmp_products_featuresets_values_id = :tmp_products_featuresets_values_id AND ' .
								'NOT(tmp_features_id = :tmp_features_id);'
				);
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->bind(':products_featuresets_id', $products_featuresets_id);
				$db->bind(':tmp_products_featuresets_id', $tmp_products_featuresets_id);
				$db->bind(':products_featuresets_values_id', $products_featuresets_values_id);
				$db->bind(':tmp_products_featuresets_values_id', $tmp_products_featuresets_values_id);
				$db->bind(':tmp_features_id', $tmp_features_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				return $tmp;
		}
	
	
	
}

?>