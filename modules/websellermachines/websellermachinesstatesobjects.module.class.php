<?php

class cWebsellermachinesstatesobjects extends cModule {
		/////////////////////////////////////////////////////////////////////////////////
		// Update navbar data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function loadEntryById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('webseller_machines_states_objects') . ' ' .
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
						'UPDATE ' . $db->table('webseller_machines_states_objects') . ' SET ' .
								'webseller_machines_states_id = :webseller_machines_states_id, ' .
								'object_identifier = :object_identifier ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':webseller_machines_states_id', (int)$data['webseller_machines_states_id']);
				$db->bind(':object_identifier', $data['object_identifier']);
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
						'INSERT INTO ' . $db->table('webseller_machines_states_objects') . ' ' .
								'(webseller_machines_states_id, object_identifier) ' .
						'VALUES ' .
								'(:webseller_machines_states_id, :object_identifier)'
				);
				$db->bind(':webseller_machines_states_id', (int)$data['webseller_machines_states_id']);
				$db->bind(':object_identifier', $data['object_identifier']);
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
						'SELECT * FROM ' . $db->table('webseller_machines_states_objects') . ' ORDER BY id DESC;'
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
		// Load list by webseller_machines_states_id
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadListByWebsellerMachinesStatesId($webseller_machines_states_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('webseller_machines_states_objects') . ' WHERE ' .
								'webseller_machines_states_id = :webseller_machines_states_id ' . 
						'ORDER BY id DESC;'
				);
				$db->bind(':webseller_machines_states_id', $webseller_machines_states_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load and instanciate objects by Load list by webseller_machines_states_id
		// @param	current_data = An array of data, that can be used in the parsing.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function instanciateObjectsByWebsellerMachinesStatesId($webseller_machines_states_id, $current_data) {
				$classes = cWebsellermachinesstatesobjects::loadListByWebsellerMachinesStatesId($webseller_machines_states_id);
				
				$data = array();
				
				foreach($classes as $class) {
						$class_name = substr($class['object_identifier'], 0, strpos($class['object_identifier'], '::'));
						$class_name_length = strlen($class_name . '::');
						$function_name_length = strpos($class['object_identifier'], '(') - $class_name_length; 
						$function_name = substr( $class['object_identifier'], $class_name_length, $function_name_length);		//Extract the function name
						$param_string_start = strpos($class['object_identifier'], '(') + 1;
						$param_string_end = strpos($class['object_identifier'], ')');
						$param_string = substr($class['object_identifier'], $param_string_start, $param_string_end - $param_string_start);
						$params = explode(',', $param_string);
						
						//check the three key values: class_name, function_name, params
						if(strlen($class_name) == 0) {
								return array();
						}
						
						if(strlen($function_name) == 0) {
								return array();
						}
						
						//Build construction and function execution string (the beginning of the function execution).
						$object_loader = '$data[\'' . $class_name . '\'] = new ' . $class_name . '();' . "\n";
						$object_loader .= '$data[\'' . $class_name . '\'] = $data[\'' . $class_name . '\']->' . $function_name . '(';		//Set the function
						
						//Set the parameters, if there are any! 
						$final_param_string = '';

						if(strlen($param_string) > 0) {
								foreach($params as $param) {
										$param_name = str_replace('$', '', $param);
										$value = '';
										
										if(isset($current_data[$param_name])) {
												$value = $current_data[$param_name];
										} else {
												return array();
										}
										
										//escape the single quote, to avoid hack attempts!
										$value = str_replace("'", "", $value);
									
										//add a commate, if there is already a parameter
										if(strlen($final_param_string) > 0) {
												$final_param_string .= ', ';
										}
										
										$final_param_string .= "'" . $value . "'";
								}
						}
						
						$object_loader .= $final_param_string;
						$object_loader .= ");";
						
						eval($object_loader);
				}
				
				return $data;
		}
}

?>