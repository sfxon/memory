<?php

class cDocument extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// load documents data by id
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadDocumentById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('documents') . ' WHERE id = :id');
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Create a document in database and return the id
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function create() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('INSERT INTO ' . $db->table('documents') . ' (document_type) VALUES(0);');
				$db->execute();
				
				return $db->insertId();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// save documents file data
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveFileData($document_id, $file_extension) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('documents') . ' SET ' .
								'file_extension = :file_extension ' .
						'WHERE ' .
								'id = :id;'
				);
				$db->bind(':file_extension', $file_extension);
				$db->bind(':id', $document_id);
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Save document data
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveData($document_id, $document_type, $file_source, $license_type, $qualifier) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('documents') . ' SET ' .
								'document_type = :document_type, ' .
								'file_source = :file_source, ' .
								'license_type = :license_type, ' .
								'qualifier = :qualifier ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':document_type', $document_type);
				$db->bind(':file_source', $file_source);
				$db->bind(':license_type', $license_type);
				$db->bind(':qualifier', $qualifier);
				$db->bind(':id', $document_id);
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// get a file extension by a given documents id
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function getFileExtensionById($document_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT file_extension FROM ' . $db->table('documents') . ' WHERE id = :id');
				$db->bind(':id', $document_id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				if($data == false) {
						return $data;
				}
				
				return $data['file_extension'];
		}
}
?>