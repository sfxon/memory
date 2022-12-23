<?php

class cTmpproductbuyingprices extends cModule {
		///////////////////////////////////////////////////////////////////
		// save temp products buying price
		///////////////////////////////////////////////////////////////////
		public static function save(
						$buying_price_id, $netto, $taxclass_id, $suppliers_id, $tmp_products_id, $remove, 
						$old_netto, $old_taxclass_id, $old_suppliers_id) {
								
				//if this is a current set buying price (buying_price_id != 0)
				if(0 != (int)$buying_price_id) {
						//check if this entry already exists
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT * FROM ' . $db->table('tmp_products_buying_prices') . ' ' .
								'WHERE ' .
										'buying_price_id = :buying_price_id AND ' .
										'tmp_products_id = :tmp_products_id'
						);
						$db->bind(':buying_price_id', (int)$buying_price_id);
						$db->bind(':tmp_products_id', $tmp_products_id);
						$result = $db->execute();
						
						$data = $result->fetchArrayAssoc();
						
						if(false === $data) {
								//create this entry
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'INSERT INTO ' . $db->table('tmp_products_buying_prices') . ' ' .
												'(buying_price_id, tmp_products_id, price_netto, taxclass_id, suppliers_id, remove) ' .
										'VALUES ' .
												'(:buying_price_id, :tmp_products_id, :price_netto, :taxclass_id, :suppliers_id, :remove)'
								);
								$db->bind(':buying_price_id', (int)$buying_price_id);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':price_netto', $netto);
								$db->bind(':taxclass_id', (int)$taxclass_id);
								$db->bind(':suppliers_id', (int)$suppliers_id);
								$db->bind(':remove', (int)$remove);
								$db->execute();
								
								return 'insert';
						} else {
								//update this entry
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'UPDATE ' . $db->table('tmp_products_buying_prices') . ' SET ' .
												'price_netto = :price_netto, ' .
												'taxclass_id = :taxclass_id, ' .
												'suppliers_id = :suppliers_id, ' .
												'remove = :remove ' .
										'WHERE ' .
												'buying_price_id = :buying_price_id AND ' .
												'tmp_products_id = :tmp_products_id'
								);
								$db->bind(':price_netto', $netto);
								$db->bind(':taxclass_id', (int)$taxclass_id);
								$db->bind(':suppliers_id', (int)$suppliers_id);
								$db->bind(':remove', (int)$remove);
								$db->bind(':buying_price_id', (int)$buying_price_id);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->execute();
								
								if($data['remove'] == 0) {
										return 'insert';
								} else {
										return 'update';
								}
						}
				//if this is a new buying price entry
				} else {
						//check if this is an update
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT * FROM ' . $db->table('tmp_products_buying_prices') . ' ' .
								'WHERE ' .
										'buying_price_id = 0 AND ' .
										'tmp_products_id = :tmp_products_id AND ' .
										'price_netto = :price_netto AND ' .
										'taxclass_id = :taxclass_id AND ' .
										'suppliers_id = :suppliers_id'
						);
						$db->bind(':tmp_products_id', $tmp_products_id);
						$db->bind(':price_netto', $old_netto);
						$db->bind(':taxclass_id', (int)$old_taxclass_id);
						$db->bind(':suppliers_id', (int)$old_suppliers_id);
						$result = $db->execute();
						
						$data = $result->fetchArrayAssoc();
						
						if(false !== $data) {
								//update this existing entry that should be updated..
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'UPDATE ' . $db->table('tmp_products_buying_prices') . ' SET ' .
												'price_netto = :price_netto, ' .
												'taxclass_id = :taxclass_id, ' .
												'suppliers_id = :suppliers_id, ' .
												'remove = :remove ' .
										'WHERE ' .
												'buying_price_id = 0 AND ' .
												'tmp_products_id = :tmp_products_id AND ' .
												'price_netto = :old_price_netto AND ' .
												'taxclass_id = :old_taxclass_id AND ' .
												'suppliers_id = :old_suppliers_id'
								);
								$db->bind(':price_netto', $netto);
								$db->bind(':taxclass_id', (int)$taxclass_id);
								$db->bind(':suppliers_id', (int)$suppliers_id);
								$db->bind(':remove', (int)$remove);															
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':old_price_netto', $old_netto);
								$db->bind(':old_taxclass_id', (int)$old_taxclass_id);
								$db->bind(':old_suppliers_id', (int)$old_suppliers_id);
								$result = $db->execute();
								
								if($data['remove'] == 0) {
										return 'insert';
								} else {
										return 'update';
								}
						} else {
														//check if an entry with this values exists.. (IMPORTANT - this is different to the check before - because here it is the new values that are testet!
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'SELECT * FROM ' . $db->table('tmp_products_buying_prices') . ' ' .
										'WHERE ' .
												'buying_price_id = 0 AND ' .
												'tmp_products_id = :tmp_products_id AND ' .
												'price_netto = :price_netto AND ' .
												'taxclass_id = :taxclass_id AND ' .
												'suppliers_id = :suppliers_id'
								);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':price_netto', $netto);
								$db->bind(':taxclass_id', (int)$taxclass_id);
								$db->bind(':suppliers_id', (int)$suppliers_id);
								$result = $db->execute();
								
								$data = $result->fetchArrayAssoc();
								
								if(false === $data) {
										//insert the item
										$db = core()->get('db');
										$db->useInstance('systemdb');
										$db->setQuery(
												'INSERT INTO ' . $db->table('tmp_products_buying_prices') . ' ' .
														'(buying_price_id, tmp_products_id, price_netto, taxclass_id, suppliers_id, remove) ' .
												'VALUES ' .
														'(:buying_price_id, :tmp_products_id, :price_netto, :taxclass_id, :suppliers_id, :remove)'
										);
										$db->bind(':buying_price_id', (int)$buying_price_id);
										$db->bind(':tmp_products_id', $tmp_products_id);
										$db->bind(':price_netto', $netto);
										$db->bind(':taxclass_id', (int)$taxclass_id);
										$db->bind(':suppliers_id', (int)$suppliers_id);
										$db->bind(':remove', (int)$remove);
										$db->execute();
										
										return 'insert';
								} else {
										//Update the item (it was not intendet to update this - but it only is an update - there can be only one item with this value set..
										$db = core()->get('db');
										$db->useInstance('systemdb');
										$db->setQuery(
												'UPDATE ' . $db->table('tmp_products_buying_prices') . ' SET ' .
														'price_netto = :price_netto, ' .
														'taxclass_id = :taxclass_id, ' .
														'suppliers_id = :suppliers_id, ' .
														'remove = :remove ' .
												'WHERE ' .
														'buying_price_id = 0 AND ' .
														'tmp_products_id = :tmp_products_id AND ' .
														'price_netto = :old_price_netto AND ' .
														'taxclass_id = :old_taxclass_id AND ' .
														'suppliers_id = :old_suppliers_id'
										);
										$db->bind(':price_netto', $netto);
										$db->bind(':taxclass_id', (int)$taxclass_id);
										$db->bind(':suppliers_id', (int)$suppliers_id);
										$db->bind(':remove', (int)$remove);															
										$db->bind(':tmp_products_id', $tmp_products_id);
										$db->bind(':old_price_netto', $old_netto);
										$db->bind(':old_taxclass_id', (int)$old_taxclass_id);
										$db->bind(':old_suppliers_id', (int)$old_suppliers_id);
										$result = $db->execute();
										
										return 'update';
								}
						}
				}
				
				return false;
		}
}
?>