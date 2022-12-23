<?php

class cProductprices extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load all prices for one product
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function load($products_id, $default_datalanguage) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('products_prices') . ' WHERE products_id = :products_id');
				$db->bind(':products_id', (int)$products_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						//Netto Preis formatieren
						$tmp['price_netto_formated'] = cPricecalculation::format( $tmp['price_netto'] );
						
						//Steuerklassen Information laden
						$tmp_taxclass = cTaxclasses::loadByIdAndDatalanguage( $tmp['taxclass_id'], $default_datalanguage );
						
						if(!empty($tmp_taxclass['value'])) {
										$tmp['taxclass_name'] = $tmp_taxclass['value'] . '%';
						} else {
								$tmp['taxclass_name'] = '0%';
						}
						
						//Brutto Preis umrechnen und anschließend formatieren
						$tmp['price_brutto'] = cPricecalculation::nettoToBrutto($tmp['price_netto'], $tmp_taxclass['value']);
						$tmp['price_brutto_formated'] = cPricecalculation::format( $tmp['price_brutto'] );
						
						$retval[] = $tmp;
				}
				
				return $retval;
		}
}

?>