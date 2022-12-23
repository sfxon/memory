<?php

class cPricecalculation extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////
		// format a price (show minimum 2 decimal digits, but more if there are more!)
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function format($input, $decimal_places = 2) {
				$parts = explode('.', $input);
		
				if(count($parts) == 2) {
						$decimal = $parts[1];
						
						if(strlen($decimal) >= $decimal_places) {
								return $input;
						}
				}
				
				return number_format($input, $decimal_places, '.', '');
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////
		// format a price (show minimum 2 decimal digits, but more if there are more!)
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function nettoToBrutto($input, $taxvalue) {
				$input = (float)$input;
				$taxvalue = (float)$taxvalue;
				
				$tax = cPricecalculation::taxToDec($taxvalue);
				
				return $input * $tax;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// parse an input to a float value
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function taxToDec($value) {
				if(!is_numeric($value)) {
						$value = 0;
				}
				
				if($value != 0) {
						$value = $value / 100;
				}
				
				$value += 1;
				
				return $value;
		}
}

?>