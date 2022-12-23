<?php

class cProductattributes extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// lib_products_attribute remove by attributes_id
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function remove($attributes_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('products_attributes') . ' WHERE id = :id');
				$db->bind(':id', $attributes_id);
				$db->execute();
		}

		///////////////////////////////////////////////////////////////////////////////////////////////////
		// lib_products_attribute_load_by_products_id_and_options_id_and_options_values_id
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadByProductsIdAndOptionsValuesId($products_id, $products_options_values_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('products_attributes') . ' WHERE ' .
								'products_id_master = :products_id_master AND ' .
								'products_options_values_id = :products_options_values_id'
				);
				$db->bind(':products_id_master', $products_id);
				$db->bind(':products_options_values_id', $products_options_values_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				return $tmp;
		}

		///////////////////////////////////////////////////////////////////////////////////////////////////
		// checks if a combination of options_id and options_values_id exists in our database,
		// but by titles and a language_id..
		// this is mainly used for data matching with third party connections
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkOptionAndOptionValueCombinationByTitles($language_id, $products_options_title, $products_options_values_title) {
				global $db;
				
				//check the option..
				$option = cProductoptions::loadByTitle($language_id, $products_options_title);
				
				if($option === false) {
						return false;
				}
				
				die('TODO: Implement!! ' . __FILE__ . '; Line: ' . __LINE__);
		}

		///////////////////////////////////////////////////////////////////////////////////////////////////
		// check if an attribute exists by products_id and products_optoins_values_id
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkExistenceByOptionsValuesId($products_id, $products_options_values_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT id FROM ' . $db->table('products_attributes') . ' ' .
						'WHERE products_id_master = :products_id_master ' .
								'AND products_options_values_id = :products_options_values_id;'
				);
				$db->bind(':products_id_master', (int)$products_id);
				$db->bind(':products_options_values_id', $products_options_values_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false === $tmp) {
						return false;
				}
				
				return $tmp['id'];
		}

		///////////////////////////////////////////////////////////////////////////////////////////////////
		// load all attributes for one product, but only the basic information
		// if you need description and a default title too, use mv_products_attributes_load
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadBaseDataOnlyByProductsId($products_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('products_attributes') . ' WHERE products_id_master = :products_id');
				$db->bind(':products_id', $products_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						$tmp['attributes_id'] = $tmp['id'];		//needed for example for the table template in the products editor
						$retval[$tmp['id']] = $tmp;
				}
				
				return $retval;
		}

		///////////////////////////////////////////////////////////////////////////////////////////////////
		// load all products attributes
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadProductsAttributes($products_id, $datalanguages_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('products_attributes') . ' WHERE products_id_master = :products_id');
				$db->bind(':products_id', $products_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						$tmp['attributes_id'] = $tmp['id'];		//needed for example for the table template in the products editor

						//load options id that belongs to this products_options_values_id
						$tmp_products_options_value = cProductoptionsvalues::loadById($tmp['products_options_values_id'], $datalanguages_id);
						
						$products_options_id = $tmp_products_options_value['products_options_id'];		//assign an often needed value
						
						if(false === $products_options_id) {
								$tmp['products_options_id'] = 0;
								$tmp['attributes_model'] = 'AN ERROR OCCURED!!!';
						} else {
								$tmp['products_options_id'] = $products_options_id;
								$tmp['attributes_model'] = cProducts::loadProductsNumberByProductsId($tmp['products_id_slave']);
								
								if(false === $tmp['attributes_model']) {
										$tmp['products_options_id'] = 0;
										$tmp['attributes_model'] = 'AN ERROR OCCURED (2)!!!';
								}
						}
						
						$tmp['tmp_products_options_id'] = '';			//assign an often needed value that shouldn't have a real value here!
						$tmp['tmp_products_options_values_id'] = '';
						
						if(false === cProducts::checkForExistence($tmp['products_id_slave'])) {
								$tmp['slave_product_exists'] = 0;
						} else {
								$tmp['slave_product_exists'] = 1;
						}
						
						$retval[] = $tmp;
				}
				
				return $retval;
		}

		///////////////////////////////////////////////////////////////////////////////////////////
		// Load a single attribute by products attributes id
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function loadById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('products_attributes') . ' ' .
						'WHERE id = :id'
				);
				$db->bind(':id', $id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				return $tmp;
		}

		///////////////////////////////////////////////////////////////////////////////////////////
		// delete products attribute
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function delete($attributes_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('products_attributes') . ' WHERE id = :id');
				$db->bind(':id', $attributes_id);
				$db->execute();
		}

		///////////////////////////////////////////////////////////////////////////////////////////
		// Produkt-Attribute aus temporärer Tabelle speichern
		///////////////////////////////////////////////////////////////////////////////////////////
		public static function saveFromTmp($products_id, $products_data) {
				$tmp_products_id = $products_data['tmp_products_id'];
				$products_attributes_id = 0;
				
				//select all the current entries
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('tmp_products_attributes') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $tmp_products_id);
				$result = $db->execute();
		
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						$bool_create_product = false;
						$bool_create_attribute = false;
						$products_id_slave = 0;
						
						if($tmp['delete_flag'] == 1) {
								//if the delete flag isset and an attributes_id isset -> delete this entry.
								if(!empty($tmp['attributes_id']) && $tmp['delete_flag'] == 1) {
										cProductattributes::delete($tmp['attributes_id']);
								}
						
								continue;		
						}
						
						//check if the attributes_id exists
						if(!empty($tmp['attributes_id'])) {
								//if the attributes_id exists - update existing product option
								//check if the products attribute exists.. if not - create it..
								$products_attribute_current = cProductattributes::loadById($tmp['attributes_id']);		//load a single attribute
														
								if(false === $products_attribute_current) {
										//if the products attribute does not exist create products attribute
										$bool_create_attribute = true;	
								} else {
										$products_attribute_current_products_number = cProducts::loadProductsNumberByProductsId($products_attribute_current['products_id_slave']);
										
										if(false === $products_attribute_current) {
												$bool_create_product = true;
										} else {
												//check if the product is the same as the current one..
												if($products_attribute_current_products_number != $tmp['attributes_model']) {
														//the product is not the same as the current slave product - check if a product with this model id exists..
														$products_id_slave = cProducts::loadProductsIdByProductsNumberButNotEmpty($tmp['attributes_model']);
														
														if(false === $products_id_slave) {
																$bool_create_product = true;
														}
												} else {
														$products_id_slave = $products_attribute_current['products_id_slave'];
												}
										}
								}
						} else {
								$bool_create_attribute = true;
								
								//check if the product exists (by products model..)
								$products_id_slave = cProducts::loadProductsIdByProductsNumberButNotEmpty($tmp['attributes_model']);
										
								if(false === $products_id_slave) {
										$bool_create_product = true;
								}
						}
		
						//if the product is to create
						if($bool_create_product === true) {
								//create the product and get the new slaves products id
								$products_id_slave = cProducts::createByAttributesData($tmp['attributes_model']);
						}
						
						$products_options_values_id = (int)$tmp['products_options_values_id'];
						$products_options_id = (int)$tmp['products_options_id'];
						
						if(0 === $products_options_values_id)  {
								$products_options_values_id = cTmpproductoptionsvalues::loadProductsOptionsIdByTmpProductsOptionsValuesId($tmp['tmp_products_options_values_id']);
						}
						
						//if the attribute is to create
						if($bool_create_attribute === true) {
								//create attribute
								cProductattributes::create($products_id, $products_id_slave, $products_options_values_id, $tmp['sort_order']);
						} else {
								//update attribute
								cProductattributes::update($tmp['attributes_id'], $products_id_slave, $products_options_values_id, $tmp['sort_order']);
						}
				}
		}

		////////////////////////////////////////////////////////////////////////////////////////
		// update product attribute
		////////////////////////////////////////////////////////////////////////////////////////
		public static function update($products_attributes_id, $products_id_slave, $products_options_values_id, $sort_order) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('products_attributes') . ' SET ' .
								'products_id_slave = :products_id_slave, ' .
								'products_options_values_id = :products_options_values_id, ' .
								'sort_order = :sort_order ' .
						'WHERE id = :id'
				);
				$db->bind(':products_id_slave', (int)$products_id_slave);
				$db->bind(':products_options_values_id', (int)$products_options_values_id);
				$db->bind(':sort_order', (int)$sort_order);
				$db->bind(':id', (int)$products_attributes_id);
				$db->execute();
		}

		////////////////////////////////////////////////////////////////////////////////////////
		// create product attribute
		////////////////////////////////////////////////////////////////////////////////////////
		public static function create($products_id_master, $products_id_slave, $products_options_values_id, $sort_order) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('products_attributes') . ' (products_id_master, products_id_slave, products_options_values_id, sort_order) ' .
						'VALUES (:products_id_master, :products_id_slave, :products_options_values_id, :sort_order)'
				);
				$db->bind(':products_id_master', (int)$products_id_master);
				$db->bind(':products_id_slave', (int)$products_id_slave);
				$db->bind(':products_options_values_id', (int)$products_options_values_id);
				$db->bind(':sort_order', (int)$sort_order);
				$db->execute();
				
				$insert_id = $db->insertId();
				
				return $insert_id;
		}


		////////////////////////////////////////////////////////////////////////////////////////
		// load titles for the options and options values of the attributes array
		////////////////////////////////////////////////////////////////////////////////////////
		public static function loadOptionsValuesTitles($products_attributes, $current_datalanguage, $tmp_products_id = false) {
				foreach($products_attributes as $index => $pa) {
						//load the products_options_descriptions
						$lib_products_options_values_data = cProductoptionsvalues::loadDescriptionsByProductsOptionsValuesId($pa['products_options_values_id']);
		
						//try to load the temporary products_options_description
						if($tmp_products_id != false && $pa['tmp_products_options_values_id'] !== '') {
								$tmp_lib_products_options_values_data = cTmpproductoptionsvalues::loadDescription($tmp_products_id, $pa['tmp_products_options_values_id']);
								
								if(false !== $tmp_lib_products_options_values_data) {
										$lib_products_options_values_data = $tmp_lib_products_options_values_data;
								}
						}
		
						//now get the options titles
						if(false !== $lib_products_options_values_data) {
								if(isset($lib_products_options_values_data[$current_datalanguage]) && isset($lib_products_options_values_data[$current_datalanguage]['title'])) {
										$products_attributes[$index]['products_options_values_title'] = $lib_products_options_values_data[$current_datalanguage]['title'];
								}
						}
						
						if(!isset($products_attributes[$index]['products_options_values_title'])) {
								$products_attributes[$index]['products_options_values_title']= '';
						}
				}
				
				return $products_attributes;
		}

		////////////////////////////////////////////////////////////////////////////////////////
		// load titles for the options and options values of the attributes array
		////////////////////////////////////////////////////////////////////////////////////////
		public static function loadOptionsTitles($products_attributes, $current_datalanguage, $tmp_products_id = false) {
				foreach($products_attributes as $index => $pa) {
						//load the products_options_descriptions
						$lib_products_options_data = cProductoptions::loadDescriptionsByProductsOptionsId($pa['products_options_id']);
		
						//try to load the temporary products_options_description
						if($tmp_products_id != false && $pa['tmp_products_options_id'] !== '') {
								$tmp_lib_products_options_data = cTmpproductoptions::loadDescription($tmp_products_id, $pa['products_options_id'], $pa['tmp_products_options_id']);
								
								if(false !== $tmp_lib_products_options_data) {
										$lib_products_options_data = $tmp_lib_products_options_data;
								}
						}
						
						//now get the options titles
						if(false !== $lib_products_options_data) {
								if(isset($lib_products_options_data[$current_datalanguage]) && isset($lib_products_options_data[$current_datalanguage]['title'])) {
										$products_attributes[$index]['products_options_title'] = $lib_products_options_data[$current_datalanguage]['title'];
								}
						}
						
						if(!isset($products_attributes[$index]['products_options_title'])) {
								$products_attributes[$index]['products_options_title']= '';
						}
						
						
				}
				
				return $products_attributes;
		}
}

?>