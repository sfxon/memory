<?php

class cWebsellermachines extends cModule {
		/////////////////////////////////////////////////////////////////////////////////
		// Update navbar data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function loadEntryById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('webseller_machines') . ' ' .
						'WHERE ' .
								'id = :id ' .
						'LIMIT 1;'
				);
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				$retval = $result->fetchArrayAssoc();
				
				return $retval;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Update navbar data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function updateInDB($id, $data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('webseller_machines') . ' SET ' .
								'title = :title, ' .
								'template_folder = :template_folder' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':title', $data['title']);
				$db->bind(':template_folder', $data['template_folder']);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Create an entry.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function createInDB($data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('webseller_machines') . ' ' .
								'(title, template_folder) ' .
						'VALUES ' .
								'(:title, :template_folder) '
				);
				$db->bind(':title', $data['title']);
				$db->bind(':template_folder', $data['template_folder']);
				$db->execute();
				
				return $db->insertId();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load list.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadList() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('webseller_machines') . ' ORDER BY title DESC;'
				);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Check Template Folder.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkTemplateFolder($folder) {
				if(is_dir('data/templates/' . $folder . '/state_pages')) {
						return true;
				}
				
				return false;
		}
}

?>