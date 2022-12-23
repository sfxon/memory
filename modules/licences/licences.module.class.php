<?php

class cLicences extends cModule {
		/////////////////////////////////////////////////////////////////////////////
		// Check if user has licences.
		/////////////////////////////////////////////////////////////////////////////
		public static function CheckUserHasLicences($accounts_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT ' . 
								'count(id) as licence_count ' .
						'FROM ' . $db->table('accounts_licences') . ' ' .
						'WHERE ' .
								'accounts_id = :accounts_id ' .
						'GROUP BY accounts_id'
				);
				$db->bind(':accounts_id', (int)$accounts_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(!isset($tmp['licence_count'])) {
						return false;
				}
				
				if($tmp['licence_count'] == 0) {
						return false;
				}
				
				return true;
		}
}

?>