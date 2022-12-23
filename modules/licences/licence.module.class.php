<?php

class cLicence extends cModule {
		var $id;
		var $accounts_id;
		var $account_number;
		var $amount_max;
		var $date_end_of_licence;
		var $date_begin_of_licence;
		var $mql_account_number;
		var $is_mql_number_editable;
		var $demo_account;
		
		/////////////////////////////////////////////////////////////////////////////
		// Load from database.
		/////////////////////////////////////////////////////////////////////////////
		public function loadFromDatabase($licence_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT ' . 
								'id, accounts_id, account_number, amount_max, date_end_of_licence, date_begin_of_licence, ' .
								'mql_account_number, is_mql_number_editable, demo_account ' .
						'FROM ' . $db->table('accounts_licences') . ' ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':id', (int)$licence_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				return $tmp;
		}
}

?>