<?php

class cSetup extends cModule {
		///////////////////////////////////////////////////////////////////
		// Save an entry.
		///////////////////////////////////////////////////////////////////
		public static function saveSettingByModuleAndKey($module, $setup_key, $value) {
				$setting = cSetup::loadSettingByModuleAndKey($module, $setup_key);
				
				if(false === $setting) {
						cSetup::create($module, $setup_key, $value);
				} else {
						cSetup::update($setting['id'], $value);
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Load Setting by module and key.
		///////////////////////////////////////////////////////////////////
		public static function loadSettingByModuleAndKey($module, $setup_key) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('setup') . ' ' .
						'WHERE ' .
								'module = :module AND ' .
								'setup_key = :setup_key ' .
						'LIMIT 1'
				);
				$db->bind(':module', $module);
				$db->bind(':setup_key', $setup_key);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				return $tmp;
		}
		
		///////////////////////////////////////////////////////////////////
		// Load Value by module and key.
		///////////////////////////////////////////////////////////////////
		public static function loadValueByModuleAndKey($module, $setup_key) {
				$result = cSetup::loadSettingByModuleAndKey($module, $setup_key);
				
				if(false === $result) {
						return false;
				}
				
				return $result['setup_value'];
		}
		
		///////////////////////////////////////////////////////////////////
		// Create an database entry.
		///////////////////////////////////////////////////////////////////
		public static function create($module, $setup_key, $setup_value) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('setup') . ' (setup_key, module, setup_value, last_change) ' .
						'VALUES(:setup_key, :module, :setup_value, NOW())'
				);
				$db->bind(':setup_key', $setup_key);
				$db->bind(':module', $module);
				$db->bind(':setup_value', $setup_value);
				$db->execute();
				
				return $db->insertId();
		}
		
		///////////////////////////////////////////////////////////////////
		// Update an database entry.
		///////////////////////////////////////////////////////////////////
		public static function update($id, $setup_value) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('setup') . ' SET ' .
								'setup_value = :setup_value ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':setup_value', $setup_value);
				$db->bind(':id', (int)$id);
				$db->execute();
		}
		
		
}

?>