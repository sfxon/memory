<?php

class cTmpproductprices extends cModule {
		///////////////////////////////////////////////////////////////////
		// save temp products price
		///////////////////////////////////////////////////////////////////
		public static function save(
						$price_id, $channel_id, $customergroups_id, $netto, $taxclass_id, $price_quantity, $tmp_products_id, $remove, 
						$old_netto, $old_taxclass_id, $old_price_quantity) {
				//if this is a current set price (price_id != 0)
				if(0 != (int)$price_id) {
						//check if this entry already exists
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery('SELECT * FROM ' . $db->table('tmp_products_prices') . ' WHERE price_id = :price_id AND tmp_products_id = :tmp_products_id');
						$db->bind(':price_id', (int)$price_id);
						$db->bind(':tmp_products_id', $tmp_products_id);
						$result = $db->execute();
						
						$data = $result->fetchArrayAssoc();
						
						if(false === $data) {
								//create this entry
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'INSERT INTO ' . $db->table('tmp_products_prices') . ' ' .
												'(price_id, channel_id, customergroups_id, tmp_products_id, price_netto, taxclass_id, price_quantity, remove) ' .
										'VALUES ' .
												'(:price_id, :channel_id, :customergroups_id, :tmp_products_id, :price_netto, :taxclass_id, :price_quantity, :remove)'
								);
								$db->bind(':price_id', (int)$price_id);
								$db->bind(':channel_id', (int)$channel_id);
								$db->bind(':customergroups_id', (int)$customergroups_id);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':price_netto', $netto);
								$db->bind(':taxclass_id', (int)$taxclass_id);
								$db->bind(':price_quantity', $price_quantity);
								$db->bind(':remove', (int)$remove);
								$db->execute();
								
								return 'insert';
						} else {
								//update this entry
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'UPDATE ' . $db->table('tmp_products_prices') . ' SET ' .
												'price_netto = :price_netto, ' .
												'taxclass_id = :taxclass_id, ' .
												'price_quantity = :price_quantity, ' .
												'remove = :remove ' .
										'WHERE ' .
												'price_id = :price_id ' .
										'AND ' .
												'tmp_products_id = :tmp_products_id'
										);
								$db->bind(':price_netto', $netto);
								$db->bind(':taxclass_id', (int)$taxclass_id);
								$db->bind(':price_quantity', $price_quantity);
								$db->bind(':remove', (int)$remove);
								$db->bind(':price_id', (int)$price_id);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->execute();
								
								if($data['remove'] == 0) {
										return 'insert';
								} else {
										return 'update';
								}
						}
				//if this is a new price entry
				} else {
						//check if this is an update
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT * FROM ' . $db->table('tmp_products_prices') . ' ' .
								'WHERE ' .
										'price_id = 0 AND ' .
										'tmp_products_id = :tmp_products_id AND ' .
										'price_netto = :price_netto AND ' .
										'taxclass_id = :taxclass_id AND ' .
										'price_quantity = :price_quantity AND ' .
										'channel_id = :channel_id AND ' .
										'customergroups_id = :customergroups_id'
						);
						$db->bind(':tmp_products_id', $tmp_products_id);
						$db->bind(':price_netto', $old_netto);
						$db->bind(':taxclass_id', (int)$old_taxclass_id);
						$db->bind(':price_quantity', $old_price_quantity);
						$db->bind(':channel_id', (int)$channel_id);
						$db->bind(':customergroups_id', (int)$customergroups_id);
						$result = $db->execute();
						
						$data = $result->fetchArrayAssoc();
						
						if(false !== $data) {
								//update this existing entry that should be updated..
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'UPDATE ' . $db->table('tmp_products_prices') . ' SET ' .
												'price_netto = :price_netto, ' .
												'taxclass_id = :taxclass_id, ' .
												'price_quantity = :price_quantity, ' .
												'remove = :remove ' .
										'WHERE ' .
												'price_id = 0 AND ' .
												'tmp_products_id = :tmp_products_id AND ' .
												'channel_id = :channel_id AND ' .
												'customergroups_id = :customergroups_id AND ' .
												'price_netto = :old_price_netto AND ' .
												'taxclass_id = :old_taxclass_id AND ' .
												'price_quantity = :price_quantity'
								);
								$db->bind(':price_netto', $netto);
								$db->bind(':taxclass_id', $taxclass_id);
								$db->bind(':price_quantity', $price_quantity);
								$db->bind(':remove', $remove);															
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':channel_id', $channel_id);
								$db->bind(':customergroups_id', $customergroups_id);
								$db->bind(':old_price_netto', $old_netto);
								$db->bind(':old_taxclass_id', $old_taxclass_id);
								$db->bind(':old_price_quantity', $old_price_quantity);
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
										'SELECT * FROM ' . $db->table('tmp_products_prices') . 
										'WHERE ' .
												'price_id = 0 AND ' .
												'tmp_products_id = :tmp_products_id AND ' .
												'price_netto = :price_netto AND ' .
												'taxclass_id = :taxclass_id AND ' .
												'price_quantity = :price_quantity AND ' .
												'channel_id = :channel_id AND ' .
												'customergroups_id = :customergroups_id'
								);
								$db->bind(':tmp_products_id', $tmp_products_id);
								$db->bind(':price_netto', $netto);
								$db->bind(':taxclass_id', (int)$taxclass_id);
								$db->bind(':price_quantity', $price_quantity);
								$db->bind(':channel_id', (int)$channel_id);
								$db->bind(':customergroups_id', (int)$customergroups_id);
								$result = $db->execute();
								
								$data = $result->fetchArrayAssoc();
								
								if(false === $data) {
										//insert the item
										$db = core()->get('db');
										$db->useInstance('systemdb');
										$db->setQuery(
												'INSERT INTO ' . $db->table('tmp_products_prices') . ' ' .
														'(price_id, tmp_products_id, price_netto, taxclass_id, price_quantity, remove, channel_id, customergroups_id) ' .
												'VALUES ' .
														'(:price_id, :tmp_products_id, :price_netto, :taxclass_id, :price_quantity, :remove, :channel_id, :customergroups_id)'
										);
										$db->bind(':price_id', (int)$price_id);
										$db->bind(':tmp_products_id', $tmp_products_id);
										$db->bind(':price_netto', $netto);
										$db->bind(':taxclass_id', (int)$taxclass_id);
										$db->bind(':price_quantity', $price_quantity);
										$db->bind(':remove', (int)$remove);
										$db->bind(':channel_id', (int)$channel_id);
										$db->bind(':customergroups_id', (int)$customergroups_id);
										$db->execute();
										
										return 'insert';
								} else {
										//Update the item (it was not intendet to update this - but it only is an update - there can be only one item with this value set..
										$db = core()->get('db');
										$db->useInstance('systemdb');
										$db->setQuery(
												'UPDATE ' . $db->table('tmp_products_prices') . ' SET ' .
														'price_netto = :price_netto, ' .
														'taxclass_id = :taxclass_id, ' .
														'price_quantity = :price_quantity, ' .
														'remove = :remove ' .
												'WHERE ' .
														'price_id = 0 AND ' .
														'channel_id = :channel_id AND ' .
														'customergroups_id = :customergroups_id AND ' .
														'tmp_products_id = :tmp_products_id AND ' .
														'price_netto = :old_price_netto AND ' .
														'taxclass_id = :old_taxclass_id AND ' .
														'price_quantity = :old_price_quantity'
										);
										$db->bind(':price_netto', $netto);
										$db->bind(':taxclass_id', (int)$taxclass_id);
										$db->bind(':price_quantity', $price_quantity);
										$db->bind(':remove', (int)$remove);
										$db->bind(':channel_id', (int)$channel_id);
										$db->bind(':customergroups_id', (int)$customergroups_id);
										$db->bind(':tmp_products_id', $tmp_products_id);
										$db->bind(':old_price_netto', $old_netto);
										$db->bind(':old_taxclass_id', (int)$old_taxclass_id);
										$db->bind(':old_price_quantity', $old_price_quantity);
										$result = $db->execute();
										
										return 'update';
								}
						}
				}
				
				return false;
		}
}
?>