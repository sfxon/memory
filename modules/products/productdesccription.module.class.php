<?php

class cProductdescription extends cModule {
		var $data_definition = NULL;
		
		/////////////////////////////////////////////////////////////////////////
		// Constructor, called on instance creation.
		/////////////////////////////////////////////////////////////////////////
		public function construct() {
				$this->data_definition['table_name'] = 'products_descriptions';
				$this->data_definition['fields'] = cProductdescription::buildDatabaseObjectDefinitionArray();
				$this->data_definition['indexes'] = cProductdescription::buildDatabaseObjectIndexDefinitionArray();
				$this->data_definition['foreign_keys'] = cProductdescription::buildDatabaseObjectIndexForeignKeyDefinition();
		}
		
		/////////////////////////////////////////////////////////////////////////
		// Set the data fields array.
		/////////////////////////////////////////////////////////////////////////
		public static function buildDatabaseObjectDefinitionArray() {
				return array(
						cProductdescription::defineDataField('id', 											'int', 				11, 	false, 		0),
						cProductdescription::defineDataField('languages_id', 						'int', 				11, 	false,		0),
						cProductdescription::defineDataField('description_id', 					'int',				11,		false,		''),
						cProductdescription::defineDataField('channel_id',								'int',				11,		false,		''),
						cProductdescription::defineDataField('text_data',								'text',				 0,	false,		0.0)
				);
		}
		
		////////////////////////////////////////////////////////////////////////
		// Returns an array with the description of one datafield.
		////////////////////////////////////////////////////////////////////////
		public static function defineDataField($name, $datatype, $datatype_length, $can_be_null, $default_value, $comment = '') {
				return array(
						'name' => $name,
						'datatype' => $datatype,
						'default_value' => $default_value
				);
		}
		
		////////////////////////////////////////////////////////////////////////
		// Returns an array with the description of all indexes for this object.
		////////////////////////////////////////////////////////////////////////
		public static function buildDatabaseObjectIndexDefinitionArray() {
				return array(
				);
		}
		
		////////////////////////////////////////////////////////////////////////
		// Returns an array with the description of one index.
		// Name:
		//		Name of the index.
		// Index Types:
		//		AI = Auto Increment
		// Fields Array:
		//		Simple array of names, example: ('id', 'name', 'model')
		////////////////////////////////////////////////////////////////////////
		public static function defineIndexField($name, $index_type, $fields_array) {
				return array(
						'name' => $name,
						'index_type' => $index_type,
						'fields_array' => $fields_array
				);
		}
		
		public static function buildDatabaseObjectIndexForeignKeyDefinition() {
				return array();		//This table is not used as foreign key table..
		}
}

?>