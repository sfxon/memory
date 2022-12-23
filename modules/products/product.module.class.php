<?php

class cProduct extends cModule {
		var $data_definition = NULL;
		
		/////////////////////////////////////////////////////////////////////////
		// Constructor, called on instance creation.
		/////////////////////////////////////////////////////////////////////////
		public function construct() {
				$this->data_definition['table_name'] = 'products';
				$this->data_definition['fields'] = cProduct::buildDatabaseObjectDefinitionArray();
				$this->data_definition['indexes'] = cProduct::buildDatabaseObjectIndexDefinitionArray();
				$this->data_definition['foreign_keys'] = cProduct::buildDatabaseObjectIndexForeignKeyDefinition();
		}
		
		/////////////////////////////////////////////////////////////////////////
		// Set the data fields array.
		/////////////////////////////////////////////////////////////////////////
		public static function buildDatabaseObjectDefinitionArray() {
				return array(
						cProduct::defineDataField('id', 											'int', 				11, 	false, 		0),
						cProduct::defineDataField('products_type', 						'int', 				11, 	false,		0),
						cProduct::defineDataField('products_number', 					'varchar',		64,		false,		''),
						cProduct::defineDataField('ean',											'varchar',		32,		false,		''),
						cProduct::defineDataField('vpe',											'float',			7.4,	false,		0.0),
						cProduct::defineDataField('vpe_unit',									'int',				11,		false,		''),
						cProduct::defineDataField('deleted',									'int',				11,		false,		0),
						cProduct::defineDataField('box_height',								'float',			7.4,	false,		0.0),
						cProduct::defineDataField('box_width',								'float',			7.4,	false,		0.0),
						cProduct::defineDataField('box_depth',								'float',			7.4,	false,		0.0),
						cProduct::defineDataField('box_weight',								'float',			7.4,	false,		0.0),
						cProduct::defineDataField('products_height',					'float',			7.4,	false,		0.0),
						cProduct::defineDataField('products_width',						'float',			7.4,	false,		0.0),
						cProduct::defineDataField('products_depth',						'float',			7.4,	false,		0.0),
						cProduct::defineDataField('products_weight',					'float',			7.4,	false,		0.0),
						cProduct::defineDataField('dimensional_weight',				'float',			7.4,	false,		0.0),
						cProduct::defineDataField('products_condition',				'int',				11,		false,		0),
						cProduct::defineDataField('virtual_article',					'int',				1,		false,		0),
						cProduct::defineDataField('bulkyGood',								'int',				1,		false,		0),
						cProduct::defineDataField('delivery_status',					'int',				11,		false,		0),
						cProduct::defineDataField('spedition',								'int',				1,		false,		0),
						cProduct::defineDataField('declaration_erroneous',		'int',				1,		false, 		0),
						cProduct::defineDataField('declaration_incomplete',		'int',				1,		false,		0),
						cProduct::defineDataField('manufacturer',							'int',				1,		false,		0),
						cProduct::defineDataField('manufacturers_number',			'varchar',		64,		false,		''),
						cProduct::defineDataField('products_stock',						'varchar',		64,		false,		0)
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
						cProduct::defineIndexField('id', 'AI', $fields_array)
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