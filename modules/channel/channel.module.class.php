<?php

class cChannel extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Aktive Verkaufskanäle laden
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadActiveChannels($default_datalanguage = 1) {
				$retval = array();
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('channels') . ' WHERE status = 1');
				$result = $db->execute();
				
				$data = array();
				
				$data[] = cChannel::getDefaultChannel();
				
				while($result->next()) {
						$tmp_data = $result->fetchArrayAssoc();
						
						//Load the language fields
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery('SELECT * FROM ' . $db->table('channels_description') . ' WHERE channels_id = :channels_id AND language_id = :language_id');
						$db->bind(':channels_id', (int)$tmp_data['id']);
						$db->bind(':language_id', $default_datalanguage);
						$sub_result = $db->execute();
						
						$tmp = $sub_result->fetchArrayAssoc();
						
						if(empty($tmp)) {
								//if the above configuration wasn't found, try to select at least one entry - independent on language..
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery('SELECT * FROM ' . $db->table('channels_description') . ' WHERE channels_id = :channels_id LIMIT 1');
								$db->bind(':channels_id', (int)$tmp_data['id']);
								$sub_result = $db->execute();
								
								$tmp = $sub_result->fetchArrayAssoc();
						}
						
						if(empty($tmp)) {
								$tmp_data['title'] = '';
								$tmp_data['description'] = '';
						} else {
								$tmp_data['title'] = $tmp['title'];
								$tmp_data['description'] = $tmp['description'];
						}
						
						//load the channel type description
						$tmp_data['channel_type_data'] = cChanneltype::getById($tmp_data['channel_type']);
						
						
						
						$data[] = $tmp_data;
				}
		
				return $data;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// setup a default channel
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function getDefaultChannel() {
				$retval = array(
											'id' => 0,
											'status' => 1,
											'channel_type' => 0,
											'title' => TEXT_CHANNELS_DEFAULT_TITLE,
											'description' => TEXT_CHANNELS_DEFAULT_CHANNEL_DESCRIPTION,
											'channel_type_data' => array(
																								'id' => 0,
																								'title' => TEXT_CHANNELS_DEFAULT_TITLE
																									)
												);
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Channel anhand der ID und der default Sprache laden..
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadByIdAndDefaultLanguage($id, $default_datalanguage) {
				$channel = cChannel::loadById($id);
				
				if($channel === false) {
						return $channel;
				}
				
				foreach($channel['descriptions'] as $desc) {
						if($desc['language_id'] == $default_datalanguage) {
								$channel['title'] = $desc['title'];
								$channel['description'] = $desc['description'];
						}
				}
		
				return $channel;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Channel anhand der ID laden
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('channels') . ' WHERE id = :id');
				$db->bind(':id', $id);
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
						$db->setQuery('SELECT * FROM ' . $db->table('channels_description') . ' WHERE channels_id = :channels_id AND language_id = :language_id');
						$db->bind(':channels_id', (int)$id);
						$db->bind(':language_id', $lang['id']);
						$result = $db->execute();
						
						$tmp = $result->fetchArrayAssoc();
						
						if(empty($data)) {
								$descriptions[] = array(
														'channels_id' => $id,
														'language_id' => $lang['id'],
														'language_name' => $lang['title'],
														'title' => '',
														'description' => ''
															);
						} else {
								$descriptions[] = array(
														'channels_id' => $id,
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