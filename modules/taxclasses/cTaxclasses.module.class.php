<?php

class cTaxclasses extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Steuerklasse anhand ID und default language laden..
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadByIdAndDatalanguage($taxclass_id, $default_datalanguage = 1) {
				$retval = array();
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * from ' . $db->table('taxclasses') . ' WHERE id = :id');
				$db->bind(':id', (int)$taxclass_id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				if(empty($data)) {
						return false;
				}
						
				//Load the language fields
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('taxclasses_descriptions') . ' WHERE taxclasses_id = :taxclasses_id AND languages_id = :languages_id');
				$db->bind(':taxclasses_id', (int)$taxclass_id);
				$db->bind(':languages_id', (int)$default_datalanguage);
				$sub_result = $db->execute();
				
				$tmp = $sub_result->fetchArrayAssoc();
						
				if(empty($tmp)) {
						//if the above configuration wasn't found, try to select at least one entry - independent on language..
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery('SELECT * FROM ' . $db->table('taxclasses_descriptions') . ' WHERE taxclasses_id = :taxclasses_id limit 1');
						$db->bind(':taxclasses_id', (int)$taxclass_id);
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
				$db->setQuery('SELECT * FROM ' . $db->table('taxclasses') . ' WHERE status = 1');
				$result = $db->execute();
				
				$data = array();
				
				while($result->next()) {
						$tmp_data = $result->fetchArrayAssoc();
						
						//Load the language fields
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery('SELECT * FROM ' . $db->table('taxclasses_descriptions') . ' WHERE taxclasses_id = :taxclasses_id AND languages_id = :languages_id');
						$db->bind(':taxclasses_id', (int)$tmp_data['id']);
						$db->bind(':languages_id', (int)$default_datalanguage);
						$sub_result = $db->execute();
						
						$tmp = $sub_result->fetchArrayAssoc();
						
						if(empty($tmp)) {
								//if the above configuration wasn't found, try to select at least one entry - independent on language..
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery('SELECT * FROM ' . $db->table('taxclasses_descriptions') . ' WHERE taxclasses_id = :taxclasses_id LIMIT 1');
								$db->bind(':taxclasses_id', (int)$tmp_data['id']);
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
		function mv_taxclass_load_by_id($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('taxclasses') . ' WHERE id = :id');
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				if(empty($data)) {
						return false;
				}
				
				$descriptions = array();
				$data_langs = cDatalanguages::loadActivated();
				
				//Load the languages..
				foreach($data_langs as $lang) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery('SELECT * FROM ' . $db->table('taxclasses_descriptions') . ' WHERE taxclasses_id = :taxclasses_id AND languages_id = :languages_id');
						$db->bind(':taxclasses_id', (int)$id);
						$db->bind(':languages_id', (int)$lang['id']);
						$result = $db->execute();
						
						$tmp = $result->fetchArrayAssoc();
						
						if(empty($data)) {
								$descriptions[] = array(
														'taxclasses_id' => $id,
														'languages_id' => $lang['id'],
														'language_name' => $lang['title'],
														'title' => '',
														'description' => ''
															);
						} else {
								$descriptions[] = array(
														'taxclasses_id' => $id,
														'languages_id' => $lang['id'],
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