<?php

class cChannelpreferences extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load an option for a dropdown of a datamodell entry..
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadModelOptionById($channel_preferences_model_option_id, $datalanguages) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('channel_preferences_model_options') . ' WHERE id = :id');
				$db->bind(':id', (int)$channel_preferences_model_option_id);
				$result = $db->execute();
				
				$retval = $result->fetchArrayAssoc();
				
				if(empty($result)) {
						return false;
				}
				
				$descriptions = array();
				
				foreach($datalanguages as $lang) {
						$langtmp = cChannelpreferences::loadModelOptionsDescriptionByIdAndLanguage($retval['id'], $lang['id']);
						
						if(empty($langtmp)) {
										$langtmp = array(
												'channel_preferences_model_options_id' => $retval['id'],
												'language_id' => $lang['id'],
												'title' => ''
										);
						}
							
						$langtmp['language_name'] = $lang['title'];
						$descriptions[$lang['id']] = $langtmp;
				}
				
				$retval['descriptions'] = $descriptions;
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load all options for a model id..
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadModelOptionsByModelsId($channel_preferences_model_id, $datalanguages) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('channel_preferences_model_options') . ' ' .
						'WHERE ' .
								'channel_preferences_model_id = :channel_preferences_model_id ' .
						'ORDER BY sort_order'
				);
				$db->bind(':channel_preferences_model_id', (int)$channel_preferences_model_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$tmp['descriptions'] = array();
						
						foreach($datalanguages as $lang) {
								$langtmp = cChannelpreferences::loadModelOptionsDescriptionByIdAndLanguage($tmp['id'], $lang['id']);
								
								if(empty($langtmp)) {
										$langtmp = array(
												'channel_preferences_model_options_id' => $tmp['id'],
												'language_id' => $lang['id'],
												'title' => ''
										);
								}
								
								$langtmp['languages_name'] = $lang['title'];
								$tmp['descriptions'][$lang['id']] = $langtmp;
						}
						
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load a channels options description
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadModelOptionsDescriptionByIdAndLanguage($channel_preferences_model_options_id, $language_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('channel_preferences_model_options_description') . ' ' .
						'WHERE ' .
								'channel_preferences_model_options_id = :channel_preferences_model_options_id AND ' .
								'language_id = :language_id;'
				);
				$db->bind(':channel_preferences_model_options_id', (int)$channel_preferences_model_options_id);
				$db->bind('language_id', (int)$language_id);
				$result = $db->execute();
				
		
				$retval = $result->fetchArrayAssoc();
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Save a channel preferences model - option -
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveModelOptions($channel_preferences_model_options_id, $channel_preferences_model_id, $sort_order, $titles) {
				//check if this entry exists..
				if(!empty($channel_preferences_model_options_id)) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery('SELECT * FROM ' . $db->table('channel_preferences_model_options') . ' WHERE id = :id');
						$db->bind(':id', $channel_preferences_model_options_id);
						$result = $db->execute();
						
						$tmp = $result->fetchArrayAssoc();
						
						if(empty($tmp)) {
								$channel_preferences_model_options_id = 0;
						}
				}
				
				//create or update?
				if(empty($channel_preferences_model_options_id)) {
						//insert
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'INSERT INTO ' . $db->table('channel_preferences_model_options') . ' ' .
										'(channel_preferences_model_id, sort_order) ' .
								'VALUES ' .
								'(:channel_preferences_model_id, :sort_order)'
						);
						$db->bind(':channel_preferences_model_id', (int)$channel_preferences_model_id);
						$db->bind(':sort_order', (int)$sort_order);
						$db->execute();
						
						$channel_preferences_model_options_id = $db->insertId();
				} else {
						//update
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'UPDATE ' . $db->table('channel_preferences_model_options') . ' SET ' .
										'sort_order = :sort_order ' .
								'WHERE id = :id'
						);
						$db->bind(':sort_order', (int)$sort_order);
						$db->bind(':id', (int)$channel_preferences_model_options_id);
						$db->execute();
				}
				
				//update descriptions..
				foreach($titles as $index => $value) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT * FROM ' . $db->table('channel_preferences_model_options_description') . ' ' .
								'WHERE ' .
										'channel_preferences_model_options_id = :channel_preferences_model_options_id AND ' .
										'language_id = :language_id'
						);
						$db->bind(':channel_preferences_model_options_id', (int)$channel_preferences_model_options_id);
						$db->bind(':language_id', (int)$index);
						$result = $db->execute();
						
						$tmp = $result->fetchArrayAssoc();
						
						if(empty($tmp)) {
								//create
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'INSERT INTO ' . $db->table('channel_preferences_model_options_description') . ' ' .
												'(channel_preferences_model_options_id, language_id, title) ' .
										'VALUES ' .
												'(:channel_preferences_model_options_id, :language_id, :title)'
								);
								$db->bind(':channel_preferences_model_options_id', (int)$channel_preferences_model_options_id);
								$db->bind(':language_id', (int)$index);
								$db->bind(':title', $value);
								$db->execute();
						} else {
								//update
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'UPDATE ' . $db->table('channel_preferences_model_options_description') . ' SET ' .
												'title = :title ' .
										'WHERE ' .
												'channel_preferences_model_options_id = :channel_preferences_model_options_id AND ' .
												'language_id = :language_id'
								);
								$db->bind(':title', $value);
								$db->bind(':channel_preferences_model_options_id', (int)$channel_preferences_model_options_id);
								$db->bind(':language_id', (int)$index);
								$db->execute();
						}
				}
																	
				return $channel_preferences_model_options_id;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load one data model entry by its id
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadModelById($channel_preferences_model_id, $datalanguages) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('channel_preferences_model') . ' WHERE id = :id');
				$db->bind(':id', (int)$channel_preferences_model_id);
				$result = $db->execute();
				
				$retval = $result->fetchArrayAssoc();
				
				if($retval === false) {
						return false;
				}
				
				$descriptions = array();
				
				//load descriptions for this entry..
				foreach($datalanguages as $lang) {
						//try to load that entry..
						$tmp = cChannelpreferences::loadModelDescriptionByIdAndLanguage($channel_preferences_model_id, $lang['id']);
		
						if(empty($tmp)) {
								$title = '';
						} else {
								$title = $tmp['title'];
						}
						
						$descriptions[$lang['id']] = array(
								'channel_preferences_model_id' => $channel_preferences_model_id,
								'language_id' => $lang['id'],
								'language_name' => $lang['title'],
								'title' => $title
						);
				}
		
				$retval['descriptions'] = $descriptions;
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load complete data model for one channel
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadModelByChanneltypesId($channeltypes_id, $datalanguages) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('channel_preferences_model') . ' WHERE channel_type = :channel_type ORDER BY sort_order');
				$db->bind(':channel_type', (int)$channeltypes_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmpdata = $result->fetchArrayAssoc();
						
						if(false === $retval) {
								return false;
						}
						
						//Load the language fields..
						$descriptions = array();
						
						foreach($datalanguages as $lang) {
								//try to load that entry..
								$tmp = cChannelpreferences::loadModelDescriptionByIdAndLanguage($tmpdata['id'], $lang['id']);
		
								if(empty($tmp)) {
										$title = '';
								} else {
										$title = $tmp['title'];
								}
								
								$descriptions[$lang['id']] = array(
										'channel_preferences_model_id' => $tmpdata['id'],
										'language_id' => $lang['id'],
										'language_name' => $lang['title'],
										'title' => $title
								);
						}
				
						$tmpdata['descriptions'] = $descriptions;
						
						//if this is a dropdown field - load the possible fields..
						$dropdown_values = array();
						
						if($tmpdata['input_type'] == 2) {
								$dropdown_values = cChannelpreferences::loadModelOptionsByModelsId($tmpdata['id'], $datalanguages);
						}
						
						$tmpdata['options'] = $dropdown_values;
						
						$retval[] = $tmpdata;
				}
				
				return $retval;
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Save a channeltypes data model entry..
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveModel($id, $channel_type, $sort_order, $input_type, $module_id, $default_value, $titles) {
				if($id == 0) {
						//create
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'INSERT INTO ' . $db->table('channel_preferences_model') . ' ' .
										'(channel_type, sort_order, input_type, module_id, default_value) ' .
								'VALUES ' .
										'(:channel_type, :sort_order, :input_type, :module_id, :default_value)'
						);
						$db->bind(':channel_type', $channel_type);
						$db->bind(':sort_order', (int)$sort_order);
						$db->bind(':input_type', $input_type);
						$db->bind(':module_id', (int)$module_id);
						$db->bind(':default_value', $default_value);
						$db->execute();
					
						$id = $db->insertId();
				} else {
						//update
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'UPDATE ' . $db->table('channel_preferences_model') . ' SET ' .
										'channel_type = :channel_type, ' .
										'sort_order = :sort_order, ' .
										'input_type = :input_type, ' .
										'module_id = :module_id, ' .
										'default_value = :default_value ' .
								'WHERE id = :id'
						);
						$db->bind(':channel_type', $channel_type);
						$db->bind(':sort_order', (int)$sort_order);
						$db->bind(':input_type', $input_type);
						$db->bind(':module_id', (int)$module_id);
						$db->bind(':default_value', $default_value);
						$db->bind(':id', (int)$id);
						$db->execute();
				}
				
				//set the title texts.
				foreach($titles as $language_id => $value) {
						//check if entry exists
						$tmp = cChannelpreferences::loadModelDescriptionByIdAndLanguage($id, $language_id);
						
						if(false === $tmp) {
								//insert
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'INSERT INTO ' . $db->table('channel_preferences_model_description') . ' ' .
												'(channel_preferences_model_id, language_id, title) ' .
										'VALUES ' .
												'(:channel_preferences_model_id, :language_id, :title);'
								);
								$db->bind(':channel_preferences_model_id', (int)$id);
								$db->bind(':language_id', (int)$language_id);
								$db->bind(':title', $value);
								$db->execute();
						} else {
								//update
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'UPDATE ' . $db->table('channel_preferences_model_description') . ' SET ' .
												'title = :title ' .
										'WHERE ' .
												'channel_preferences_model_id = :channel_preferences_model_id AND ' .
												'language_id = :language_id'
								);
								$db->bind(':title', $value);
								$db->bind(':channel_preferences_model_id', (int)$id);
								$db->bind(':language_id', (int)$language_id);
								$db->execute();
						}
				}
				
				return $id;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// load one preferences model description by model id and language id
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadModelDescriptionByIdAndLanguage($id, $language_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('channel_preferences_model_description') . ' ' .
						'WHERE ' .
								'language_id = :language_id AND ' .
								'channel_preferences_model_id = :channel_preferences_model_id'
				);
				$db->bind(':language_id', (int)$language_id);
				$db->bind(':channel_preferences_model_id', (int)$id);
				$result = $db->execute();
						
				$tmp = $result->fetchArrayAssoc();
				
				return $tmp;
		}
}
?>