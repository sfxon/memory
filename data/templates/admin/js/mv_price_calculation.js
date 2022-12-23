//javascript

///////////////////////////////////////////////////////////////////////////////////////////////////
// calculate the gros from net
///////////////////////////////////////////////////////////////////////////////////////////////////
function mv_netto_to_brutto(netto, tax) {
		netto = mv_parse_float(netto);
		tax = mv_parse_float(tax);
		
		tax = mv_parse_tax_to_dec(tax);
		
		return netto * tax;
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// calculate the net from gros
///////////////////////////////////////////////////////////////////////////////////////////////////
function mv_brutto_to_netto(brutto, tax) {
		brutto = mv_parse_float(brutto);
		tax = mv_parse_float(tax);
		
		tax = mv_parse_tax_to_dec(tax);
		
		if(tax != 0 && brutto != 0) {
				var netto = brutto / tax;
		} else {
				var netto = 0;
		}
		
		
		return netto;
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// parse an input to a float value
///////////////////////////////////////////////////////////////////////////////////////////////////
function mv_parse_float(value) {
		if(value === 0) {
				value = '0';
		} else {
				value = value.toString(value);
		}
		value = value.replace(',', '.');
		value = parseFloat(value);
		
		if(isNaN(value)) {
				value = 0;
		}

		return value;
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// parse an input to a float value
///////////////////////////////////////////////////////////////////////////////////////////////////
function mv_parse_tax_to_dec(value) {
		if(isNaN(value)) {
				value = 0;
		}
		
		if(value != 0) {
				value = value / 100;
		}
		
		value += 1;
		
		return value;
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// format a number to display 2 signs after the decimal point. Even if it hasn't numbers after decimal point
///////////////////////////////////////////////////////////////////////////////////////////////////
function mv_number_format_decimal(value, decimals = 2) {
		value = mv_parse_float(value);
		value = value.toString();
		
		parts = value.split('.');
		
		value = parseFloat(value);
		
		if(parts.length < 2) {
				return value.toFixed(2);
		}
		
		if(parts[1].length < 2) {
				return value.toFixed(2);
		}
		
		return value;
}

