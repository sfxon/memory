<?php

class cWebsellersessions extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Create session.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function updateLogoInDB($session_id, $original_logo_filename, $logo_file_extension) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('webseller_sessions') . ' SET ' .
								'original_logo_filename = :original_logo_filename, ' .
								'logo_file_extension = :logo_file_extension ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':original_logo_filename', $original_logo_filename);
				$db->bind(':logo_file_extension', $logo_file_extension);
				$db->bind(':id', (int)$session_id);
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Create session.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function createInDB($session_key, $session_type, $customers_id, $webseller_machines_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('webseller_sessions') . ' ' .
								'(user_id, webseller_session_key, status, start_date, session_type, webseller_machines_id) ' .
						'VALUES ' .
								'(:user_id, :webseller_session_key, :status, NOW(), :session_type, :webseller_machines_id)'
				);
				$db->bind(':user_id', (int)$customers_id);
				$db->bind(':webseller_session_key', $session_key);
				$db->bind(':status', (int)1);
				$db->bind(':session_type', (int)$session_type);
				$db->bind(':webseller_machines_id', (int)$webseller_machines_id);
				$db->execute();
				
				return $db->insertId();
		}
	
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Create a temp  id - make sure it is not already data set to it.
		// We remove existing data for the temp_websellersessions_id.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadSessionById($session_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('webseller_sessions') . ' WHERE id = :id LIMIT 1;');
				$db->bind(':id', (int)$session_id);
				$result = $db->execute();
				$tmp = $result->fetchArrayAssoc();
				
				return $tmp;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Create a temp  id - make sure it is not already data set to it.
		// We remove existing data for the temp_websellersessions_id.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function generateTmpId($prefix) {
				global $db;
				
				$retval = $prefix . uniqid('', true);
				
				//clear the tables..
				//temp prices		
				/*$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_prices') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $retval);
				$db->execute();*/
				return $retval;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////////
		// Prepare system for saving files.
		/////////////////////////////////////////////////////////////////////////////////////////////////
		public static function prepareForSavingFiles($user_id, $websellersessions_id) {
				$path = 'data/webseller/sessions/';

				//check if the user path exists
				if(!is_dir($path . $user_id)) {
						mkdir($path . $user_id, 0777);
				}
				
				//check if the session path exists
				if(!is_dir($path . $user_id . '/' . $websellersessions_id)) {
						mkdir($path . $user_id . '/' . $websellersessions_id, 0777);
				}
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////////
		// Prepare system for saving files.
		/////////////////////////////////////////////////////////////////////////////////////////////////
		public static function doesLogoExist($data) {
				$path = 'data/webseller/sessions/';
				$path .= $data['user_id'];
				$path .= '/';
				$path .= $data['id'];
				
				$filename = cWebsellersessions::getLogoFilenameFromLogoInfoArray($data);
				
				if(file_exists($path . '/' . $filename)) {
						return true;
				}
				
				return false;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////////
		// Prepare system for saving files.
		/////////////////////////////////////////////////////////////////////////////////////////////////
		public static function getLogoFilenameFromLogoInfoArray($data) {
				$filename = 'logo' . $data['logo_file_extension'];
				return $filename;
		}
	
	
	
}

?>