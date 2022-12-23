<?php

class cCustomergroups extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Steuerklasse anhand ID und default language laden..
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadByIdAndDatalanguage($taxclass_id, $default_datalanguage = 1) {
				$retval = array();
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('customergroups') . ' WHERE id = :id');
				$db->bind(':id', (int)$taxclass_id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				if(empty($data)) {
						return false;
				}
						
				//Load the language fields
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('customergroups_description') . ' WHERE customergroups_id = :customergroups_id AND language_id = :language_id');
				$db->bind(':customergroups_id', (int)$taxclass_id);
				$db->bind(':language_id', (int)$default_datalanguage);
				$sub_result = $db->execute();
				
				$tmp = $sub_result->fetchArrayAssoc();
						
				if(empty($tmp)) {
						//if the above configuration wasn't found, try to select at least one entry - independent on language..
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery('SELECT * FROM ' . $db->table('customergroups_description') . ' WHERE customergroups_id = :customergroups_id LIMIT 1');
						$db->bind(':customergroups_id', (int)$taxclass_id);
						$result = $db->execute();
						
						$tmp = $sub_result->fetchArrayAssoc();
				}
						
				if(empty($tmp)) {
						$data['title'] = '';
						$data['description'] = '';
				} else {
						$data['title'] = $tmp['title'];
						$data['description'] = $tmp['description'];
				}
		
				return $data;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Alle aktiven Steuerklassen laden
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadActive($default_datalanguage = 1) {
				$retval = array();
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('customergroups') . ' WHERE status = 1');
				$result = $db->execute();
				
				$data = array();
				
				while($result->next()) {
						$tmp_data = $result->fetchArrayAssoc();
						
						//Load the language fields
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery('SELECT * FROM ' . $db->table('customergroups_description') . ' WHERE customergroups_id = :customergroups_id and language_id = :language_id');
						$db->bind(':customergroups_id', (int)$tmp_data['id']);
						$db->bind(':language_id', (int)$default_datalanguage);
						$sub_result = $db->execute();
						
						$tmp = $sub_result->fetchArrayAssoc();
						
						if(empty($tmp)) {
								//if the above configuration wasn't found, try to select at least one entry - independent on language..
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery('SELECT * FROM ' . $db->table('customergroups_description') . ' WHERE customergroups_id = :customergroups_id LIMIT 1');
								$db->bind(':customergroups_id', (int)$tmp_data['id']);
								$result = $db->execute();
								
								$tmp = $sub_result->fetchArrayAssoc();
						}
						
						if(empty($tmp)) {
								$tmp_data['title'] = '';
								$tmp_data['description'] = '';
						} else {
								$tmp_data['title'] = $tmp['title'];
								$tmp_data['description'] = $tmp['description'];
						}
						
						$data[] = $tmp_data;
				}
		
				return $data;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Steuerklasse anhand der ID laden
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('customergroups') . ' WHERE id = :id');
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				if(empty($data)) {
						return false;
				}
				
				$descriptions = array();
				$data_langs = cDatalanguage::loadActivated();
				
				//Load the language..
				foreach($data_langs as $lang) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery('SELECT * FROM ' . $db->table('customergroups_description') . ' WHERE customergroups_id = :customergroups_id AND language_id = :language_id');
						$db->bind(':customergroups_id', (int)$id);
						$db->bind(':language_id', $lang['id']);
						$result = $db->execute();
						
						$tmp = $result->fetchArrayAssoc();
						
						if(empty($data)) {
								$descriptions[] = array(
														'customergroups_id' => $id,
														'language_id' => $lang['id'],
														'language_name' => $lang['title'],
														'title' => '',
														'description' => ''
															);
						} else {
								$descriptions[] = array(
														'customergroups_id' => $id,
														'language_id' => $lang['id'],
														'language_name' => $lang['title'],
														'title' => $tmp['title'],
														'description' => $tmp['description']
															);
						}
				}
				
				$data['descriptions'] = $descriptions;
		
				return $data;
		}	
}

?>