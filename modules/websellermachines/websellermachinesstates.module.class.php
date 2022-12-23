<?php

class cWebsellermachinesstates extends cModule {
		/////////////////////////////////////////////////////////////////////////////////
		// Update navbar data in database.
		/////////////////////////////////////////////////////////////////////////////////
		public static function loadEntryById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('webseller_machines_states') . ' ' .
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
						'UPDATE ' . $db->table('webseller_machines_states') . ' SET ' .
								'state_machines_id = :state_machines_id, ' .
								'level = :level, ' .
								'parent_state_id = :parent_state_id, ' .
								'status = :status, ' .
								'title = :title, ' .
								'template_file = :template_file ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':state_machines_id', (int)$data['state_machines_id']);
				$db->bind(':level', (int)$data['level']);
				$db->bind(':parent_state_id', (int)$data['parent_state_id']);
				$db->bind(':status', (int)$data['status']);
				$db->bind(':title', $data['title']);
				$db->bind(':template_file', $data['template_file']);
				$db->bind(':id', (int)$id);
				$db->execute();
				
				return $id;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Create an entry.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function createInDB($data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('webseller_machines_states') . ' ' .
								'(state_machines_id, level, parent_state_id, status, title, template_file) ' .
						'VALUES ' .
								'(:state_machines_id, :level, :parent_state_id, :status, :title, :template_file)'
				);
				$db->bind(':state_machines_id', (int)$data['state_machines_id']);
				$db->bind(':level', (int)$data['level']);
				$db->bind(':parent_state_id', (int)$data['parent_state_id']);
				$db->bind(':status', (int)$data['status']);
				$db->bind(':title', $data['title']);
				$db->bind(':template_file', $data['template_file']);
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
						'SELECT * FROM ' . $db->table('webseller_machines_states') . ' ORDER BY title DESC;'
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
		// Check Template file.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkTemplateFile($path, $file) {
				if(file_exists('data/templates/' . $path . '/state_pages/' . $file)) {
						return true;
				}
				
				return false;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load an entry by machines state.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadStateByJson($json_state, $state_machines_id) {
				$state = json_decode($json_state, true);
				
				if($state['site'] == 'customer_logged_in') {
						if(empty($state['actions'])) {
								//Load default machines state.
								$state = cWebsellermachinesstates::loadFirstState($state_machines_id);
								return $state;
						}
				}
				
				//Try to load the current state.
				if(isset($state['actions']) && isset($state['actions']['machines_state'])) {
						return cWebsellermachinesstates::loadEntryById( $state['actions']['machines_state'] );
				}
				
				return $state;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Loads the first state.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadFirstState($state_machines_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('webseller_machines_states') . ' WHERE state_machines_id = :state_machines_id ORDER BY level ASC LIMIT 1;'
				);
				$db->bind(':state_machines_id', (int)$state_machines_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				return $tmp;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Recursively loads all substates
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadSubstatesByStatesIdRecursive($state_machines_id, $substates_path, &$ws_machines_substates) {
				if(strlen($substates_path) > 0) {
						$substates_path .= '-';
				}
				
				$substates_path .= $state_machines_id;
				
				$tmp = cWebsellermachinesstates::loadEntriesAsArrayByParentId($state_machines_id);
				
				//set the path..
				foreach($tmp as $index => $item) {
						$item['states_path'] = $substates_path;
						$ws_machines_substates[] = $item;
						
						cWebsellermachinesstates::loadSubstatesByStatesIdRecursive($item['id'], $substates_path, $ws_machines_substates);
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load entries as array by parent id.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadEntriesAsArrayByParentId($parent_state_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('webseller_machines_states') . ' WHERE parent_state_id = :parent_state_id ORDER BY level ASC LIMIT 1;'
				);
				$db->bind(':parent_state_id', (int)$parent_state_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Get the topmost state by a state. Returns the state itself, if that is already a topmost state.
		// This function works recursive!
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function getMainState($machines_state) {
				$tmp_entry = cWebsellermachinesstates::loadEntryById($machines_state);
				
				if($tmp_entry['parent_state_id'] == 0) {
						return $tmp_entry['id'];
				}
				
				return cWebsellermachinesstates::getMainState($tmp_entry['parent_state_id']);
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Get the topmost state by a state. Returns the state itself, if that is already a topmost state.
		// This function works recursive!
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function getSubstatesPath($machines_state, $path) {
				$tmp_entry = cWebsellermachinesstates::loadEntryById($machines_state);
				
				if(strlen($path) > 0) {
						$path = '-' . $path;
				}
				
				$path = $machines_state . $path;
				
				if($tmp_entry['parent_state_id'] == 0) {
						return $path;
				}
				
				return cWebsellermachinesstates::getSubstatesPath($tmp_entry['parent_state_id'], $path);
		}
}

?>