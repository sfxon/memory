<?php

class cUrlshortener extends cModule {
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				//Now set our own hooks below the CMS hooks.
				$core = core();
				core()->setHook('cCore|process', 'process');
		}
		
		///////////////////////////////////////////////////////////////////
		// processData
		///////////////////////////////////////////////////////////////////
		public function process() {
				$id = (int)core()->getGetVar('r');
				
				$item = cUrlshortener::loadEntryById($id);
				
				if(false === $item) {
						die('Service is not availlable.');
				}
				
				$this->logEntry($id);
				
				$final_url = $item['final_url'];
				
				if(strpos($final_url, 'https://') !== 0 && strpos($final_url, 'http://') !== 0) {
						$final_url = 'http://' . $final_url;
				}
				
				header('Location: ' . $final_url);
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Log entry.
		///////////////////////////////////////////////////////////////////
		public function logEntry($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('urlshortener_calls') . ' ' .
								'(urlshortener_id, call_time, ip_address_user) ' .
						'VALUES ' .
								'(:urlshortener_id, NOW(), :ip_address_user);'
				);
				$db->bind(':urlshortener_id', (int)$id);
				$db->bind(':ip_address_user', $_SERVER['REMOTE_ADDR']);
				$db->execute();
		}
	
		/////////////////////////////////////////////////////////////////////////////////
		// Create a new entry in the database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function createInDB($data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('urlshortener') . ' ' .
								'(referrer_accounts_id, final_url, created_on, link_data, link_type, status) ' .
						'VALUES ' .
								'(:referrer_accounts_id, :final_url, NOW(), :link_data, :link_type, :status);'
				);
				$db->bind(':referrer_accounts_id', (int)$data['referrer_accounts_id']);
				$db->bind(':final_url', $data['final_url']);
				$db->bind(':link_data', $data['link_data']);
				$db->bind(':link_type', (int)$data['link_type']);
				$db->bind(':status', (int)$data['status']);
				$db->execute();
				
				return $db->insertId();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Update a database entry.
		/////////////////////////////////////////////////////////////////////////////////
		public static function updateInDB($id, $data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('urlshortener') . ' SET ' .
								'referrer_accounts_id = :referrer_accounts_id, ' .
								'final_url = :final_url, ' .
								'link_data = :link_data, ' .
								'link_type = :link_type, ' .
								'status = :status ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':referrer_accounts_id', (int)$data['referrer_accounts_id']);
				$db->bind(':final_url', $data['final_url']);
				$db->bind(':link_data', $data['link_data']);
				$db->bind(':link_type', (int)$data['link_type']);
				$db->bind(':status', (int)$data['status']);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Load entry by id.
		/////////////////////////////////////////////////////////////////////////////////
		public static function loadEntryById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT ' .
								'* ' .
						'FROM ' . $db->table('urlshortener') . ' ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				return $tmp;
		}
}

?>