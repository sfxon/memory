<?php

class cPackagingunits extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Produkttyp anhand der ID laden
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadActive($default_datalanguage = 1) {
				$retval = array();
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('packagingunits') . ' WHERE status = 1;');
				$result = $db->execute();
				
				$data = array();
				
				while($result->next()) {
						$tmp_data = $result->fetchArrayAssoc();
						
						//Load the language fields
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery('SELECT * FROM ' . $db->table('packagingunits_description') . ' WHERE packagingunits_id = :packagingunits_id AND language_id = :language_id');
						$db->bind(':packagingunits_id', (int)$tmp_data['id']);
						$db->bind(':language_id', (int)$default_datalanguage);
						$sub_result = $db->execute();
						
						$tmp = $sub_result->fetchArrayAssoc();
						
						if(empty($tmp)) {
								//if the above configuration wasn't found, try to select at least one entry - independent on language..
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery('SELECT * FROM ' . $db->table('packagingunits_description') . ' WHERE packagingunits_id = :packagingunits_id LIMIT 1');
								$db->bind(':packagingunits_id', (int)$tmp_data['id']);
								$sub_result = $db->execute();
								
								$tmp = $sub_result->fetchArrayAssoc();
						}
						
						if(empty($tmp)) {
								$tmp_data['title'] = '';
								$tmp_data['symbol'] = '';
						} else {
								$tmp_data['title'] = $tmp['title'];
								$tmp_data['symbol'] = $tmp['symbol'];
						}
						
						$data[] = $tmp_data;
				}
		
				return $data;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Produkttyp anhand der ID laden
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('packagingunits') . ' where id = :id');
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
						$db->setQuery(
								'SELECT * FROM ' . $db->table('packagingunits_description') . ' ' .
								'WHERE ' .
										'packagingunits_id = :packagingunits_id AND ' .
										'language_id = :language_id'
						);
						$db->bind(':packagingunits_id', (int)$id);
						$db->bind(':language_id', (int)$lang['id']);
						$result = $db->execute();
						
						$tmp = $result->fetchArrayAssoc();
						
						if(empty($data)) {
								$descriptions[] = array(
														'packagingunits_id' => $id,
														'language_id' => $lang['id'],
														'language_name' => $lang['title'],
														'title' => '',
														'symbol' => ''
															);
						} else {
								$descriptions[] = array(
														'packagingunits_id' => $id,
														'language_id' => $lang['id'],
														'language_name' => $lang['title'],
														'title' => $tmp['title'],
														'symbol' => $tmp['symbol']
															);
						}
				}
				
				$data['descriptions'] = $descriptions;
		
				return $data;
		}
}

?>