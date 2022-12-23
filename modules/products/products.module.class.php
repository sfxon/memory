<?php

define('SYSTEM_MAX_PRODUCT_TITLES', 5);
define('SYSTEM_MAX_PRODUCT_DESCRIPTIONS', 4);

class cProducts extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// @INFO		load some basic data for a product
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadSimpleData($products_id, $datalanguage_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('products') . ' WHERE id = :id');
				$db->bind(':id', (int)$products_id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				if(false === $data) {
						return false;
				}
				
				$data['titles'] = cProducts::loadTitlesByProductsId($products_id);
				
				//set additional data..
				if(isset($data['titles'][$datalanguage_id][0][1])) {
						$data['display_title'] = $data['titles'][$datalanguage_id][0][1];
				} else {
						$data['display_title'] = $data['titles'][$datalanguage_id][0][1] = '- - -';
				}
				
				return $data;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// @INFO		load products by specific category
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadProductsByCategory($categories_id, $datalanguage_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('products_to_categories') . ' WHERE categories_id = :categories_id');
				$db->bind(':categories_id', (int)$categories_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$tmp_product = cProducts::loadSimpleData($tmp['products_id'], $datalanguage_id);
						
						$retval[] = $tmp_product;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// @INFO		load products weight
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadWeightByProductsId($products_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT products_weight FROM ' . $db->table('products') . ' WHERE id = :id');
				$db->bind(':id', $products_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false === $tmp) {
						return false;
				}
				
				return $tmp['products_weight'];
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// @INFO		load products ean
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadEanByProductsId($products_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT ean FROM ' . $db->table('products') . ' WHERE id = :id');
				$db->bind(':id', $products_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false === $tmp) {
						return false;
				}
				
				return $tmp['ean'];
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Anzahl Artikel in der Datenbank zählen (alle in der Datenbank, no mater what kind)
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function getTotalCount() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT COUNT(*) AS total_count FROM ' . $db->table('products'));
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				$tmp = $tmp['total_count'];
				
				return $tmp;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Anzahl Artikel in der Datenbank zählen, alle in der Datenbank, but not the ones, that are
		// used as attributes for other products.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function getTotalCountWithoutAttributeProducts() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT count(p.id) AS total_count FROM aloha_products p ' .
								'LEFT JOIN aloha_products_attributes pa ' .
										'ON p.ID = pa.products_id_slave ' .
						'WHERE pa.products_id_slave IS NULL'
				);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				$tmp = $tmp['total_count'];
				
				return $tmp;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// load a list of products
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadList($index, $max_results, $sort_fields) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('products') . ' LIMIT ' . (int) $index . ', ' . (int)$max_results);
				$result = $db->execute();
				
				$retval = false;
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$tmp['titles'] = cProducts::loadTitlesByProductsId($tmp['id']);
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load a list of products, but not the ones, that are used as attributes for other products.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadListWithoutAttributeProducts($index, $max_results, $sort_fields) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT p.* FROM aloha_products p ' .
								'LEFT JOIN aloha_products_attributes pa ' .
										'ON p.ID = pa.products_id_slave ' .
						'WHERE pa.products_id_slave IS NULL ' .
						'LIMIT ' . (int)$index . ', ' . (int)$max_results
				);
				$result = $db->execute();
				
				$retval = false;
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$tmp['titles'] = cProducts::loadTitlesByProductsId($tmp['id']);
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// create a product by attributes only available data (model)
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function createByAttributesData($products_number) {
				//save main data
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('INSERT INTO ' . $db->table('products') . ' (products_number) VALUES(:products_number)');
				$db->bind(':products_number', $products_number);
				$db->execute();
				
				$products_id = $db->insertId();
				
				//create a simple description - products_number is the title of the product..
				cProducts::saveTitles($products_id, $products_number);
				
				return $products_id;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// creates some basic descriptions by just one string - we only set the string as title
		// all other fields to ""
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function cProductscreateTitlesBasic($products_id, $title) {
				$datalanguages = cDatalanguages::loadActivated();
				$channels = cChannel::loadActiveChannels();
				
				foreach($datalanguages as $lang) {
						foreach($channels as $channel) {
								for($i = 1; $i < SYSTEM_MAX_PRODUCT_TITLES; $i++) {
										cProduct::saveTitle($products_id, $lang['id'], $i, $channel['id'], $title);
								}
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// create a temp products id - make sure it is not already data set to it->
		// we remove existing data for the temp_products_id for this case..
		// TODO: complete delete functions for tmp tables..
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function generateTmpProductsId($prefix) {
				global $db;
				
				$retval = $prefix . uniqid('', true);
				
				//clear the tables..
				//temp prices		
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_prices') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $retval);
				$db->execute();
				
				//temp buying prices
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_buying_prices') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $retval);
				$db->execute();
				
				//temp categories
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_to_categories') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $retval);
				$db->execute();
				
				//temp pictures
				
				//temp attributes
				
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Save Products Data
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function save($products_data) {
				$id = 0;
				
				//Check and prepare all the fields..
				$general_products_data = cProducts::saveBuildFields($products_data);
		
				//save general information
				$id = cProducts::saveGeneralInformation($general_products_data);
				
				if(!empty($id)) {
						//save
						cProducts::saveTitles($id, $products_data);
						cProducts::saveDescriptions($id, $products_data);
						cProducts::saveCategories($id, $products_data);
						cProducts::saveBuyingPrices($id, $products_data);
						cProducts::savePrices($id, $products_data);
						cProducts::saveImages($id, $products_data);
						cProducts::saveFiles($id, $products_data);
						cProducts::saveChannelsData($id, $products_data);
						//Attribute
						cProductoptions::saveFromTemp($id, $products_data);
						cProductoptionsvalues::saveFromTmp($id, $products_data);
						cProductattributes::saveFromTmp($id, $products_data);
						cProducts::freeTmpProductOptionsByTmpProductsId($products_data);
						cProducts::freeTmpProductOptionsValuesByTmpProductsId($products_data);
						cTmpproductattributes::freeByTmpProductsId($products_data['tmp_products_id']);
						//Features
						cProductfeaturesets::saveFromTemp($id, $products_data);
						cProductfeaturesetsvalues::saveFromTmp($id, $products_data);
						cProductfeatures::saveFromTmp($id, $products_data);
						cProducts::freeTmpProductFeaturesetsByTmpProductsId($products_data);
						cProducts::freeTmpProductFeaturesetsValuesByTmpProductsId($products_data);
						cTmpproductfeatures::freeByTmpProductsId($products_data['tmp_products_id']);
						
						//save products stock
						cProductstock::save($id, $products_data);
				}
				
				return $id;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// free tmp products options
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function freeTmpProductOptionsByTmpProductsId($products_data) {
				$tmp_products_id = $products_data['tmp_products_id'];
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_options') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->execute();
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_options_descriptions') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// free tmp products options
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function freeTmpProductOptionsValuesByTmpProductsId($products_data) {
				$tmp_products_id = $products_data['tmp_products_id'];
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_options_values') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->execute();
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_options_values_descriptions') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// free tmp products options
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function freeTmpProductFeaturesetsByTmpProductsId($products_data) {
				$tmp_products_id = $products_data['tmp_products_id'];
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_featuresets') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->execute();
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_featuresets_descriptions') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// free tmp products options
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function freeTmpProductFeaturesetsValuesByTmpProductsId($products_data) {
				$tmp_products_id = $products_data['tmp_products_id'];
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_featuresets_values') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->execute();
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_featuresets_values_descriptions') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Channelspezifische Produktdaten sichern
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveChannelsData($products_id, $products_data) {
				if(isset($products_data['channeldata'])) {
						foreach($products_data['channeldata'] as $channel_id => $channeldata) {
								if(is_array($channeldata)) {
										foreach($channeldata as $channel_preferences_model_id => $value) {
												//check if this entry exists
												$db = core()->get('db');
												$db->useInstance('systemdb');
												$db->setQuery(
														'SELECT * FROM ' . $db->table('products_channel_options') . ' ' .
														'WHERE ' .
																'products_id = :products_id AND ' . 
																'channel_id = :channel_id AND ' .
																'channel_preferences_model_id = :channel_preferences_model_id'
												);
												$db->bind(':products_id', (int)$products_id);
												$db->bind(':channel_id', (int)$channel_id);
												$db->bind(':channel_preferences_model_id', (int)$channel_preferences_model_id);
												$result = $db->execute();
												
												$tmp = $result->fetchArrayAssoc();
												
												if($tmp === false) {
														//insert
														$db = core()->get('db');
														$db->useInstance('systemdb');
														$db->setQuery(
																'INSERT INTO ' . $db->table('products_channel_options') . ' ' .
																		'(products_id, channel_id, channel_preferences_model_id, value) ' .
																'VALUES ' .
																		'(:products_id, :channel_id, :channel_preferences_model_id, :value)'
														);
														$db->bind(':products_id', (int)$products_id);
														$db->bind(':channel_id', (int)$channel_id);
														$db->bind(':channel_preferences_model_id', (int)$channel_preferences_model_id);
														$db->bind(':value', $value);
														$db->execute();
												} else {
														//update
														$db = core()->get('db');
														$db->useInstance('systemdb');
														$db->setQuery(
																'UPDATE ' . $db->table('products_channel_options') . ' SET ' .
																		'value = :value ' .
																'WHERE ' .
																		'products_id = :products_id AND ' .
																		'channel_id = :channel_id AND ' .
																		'channel_preferences_model_id = :channel_preferences_model_id'
														);
														$db->bind(':value', $value);
														$db->bind(':products_id', (int)$products_id);
														$db->bind(':channel_id', (int)$channel_id);
														$db->bind(':channel_preferences_model_id', (int)$channel_preferences_model_id);
														$db->execute();
												}
										}
								}
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Dateien sichern
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveFiles($id, $products_data) {
				$tmp_products_id = $products_data['tmp_products_id'];
				
				$file_source_folder = 'data/tmp/tmpuploads/';
				$file_dest_folder = 'data/files/product_files/';
				
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('tmp_products_files') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $tmp_products_id);
				$result = $db->execute();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						//if this item is removed or to be removed..
						if($tmp['remove_flag'] == 1 ) {		//if this item is removed (temporary and existing documents)
								if(!empty($tmp['documents_id'])) {		//if this item is to be removed (existing documents only!)
										//mv_documents_remove($tmp['documents_id']);
										cProductfiles::remove($id, $tmp['documents_id']);		//only remove the image - but leave the document intact..
								}
								continue;		//skip the following inserts..
						}
		
						//check if the document already exists
						if(!empty($tmp['documents_id'])) {
								$document_id = $tmp['documents_id'];
						} else {
								//create the document in database
								$document_id = cDocument::create();
						}
		
						//move the files
						if(!empty($tmp['tmp_files_filename'])) {
								rename($file_source_folder . $tmp['tmp_files_filename'], $file_dest_folder . $document_id . $tmp['file_extension']);
								$file_extension = str_replace('.', '', $tmp['file_extension']);
								cDocument::saveFileData($document_id, $file_extension);
						}
		
						//save the extended data in document
						cDocument::saveData($document_id, 1, $tmp['file_source'], $tmp['license_type'], $tmp['qualifier']);
					
						//save file data in database
						cProductfiles::saveData($id, $document_id, $tmp['sort_order']);
						
						//save the additional data in database
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT * FROM ' . $db->table('tmp_products_files_descriptions') . ' ' .
								'WHERE ' .
										'tmp_products_id = :tmp_products_id AND ' .
										'(tmp_files_filename = :tmp_files_filename OR documents_id = :documents_id)'
						);
						$db->bind(':tmp_products_id', $tmp_products_id);
						$db->bind(':tmp_files_filename', $tmp['tmp_files_filename']);
						$db->bind(':documents_id', (int)$document_id);
						$subresult = $db->execute();
		
						while($subresult->next()) {
								$subtmp = $subresult->fetchArrayAssoc();
								cProductfiles::saveDescriptions($id, $document_id, $subtmp['language_id'], $subtmp['title'], $subtmp['comment'], $subtmp['external_link']);
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Produktbilder sichern
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveImages($id, $products_data) {
				$tmp_products_id = $products_data['tmp_products_id'];
				
				$file_source_folder = 'data/tmp/tmpuploads/';
				$file_dest_folder = 'data/images/product_images/';
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('tmp_products_images') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $tmp_products_id);
				$result = $db->execute();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						//if this item is removed or to be removed..
						if($tmp['remove_flag'] == 1 ) {		//if this item is removed (temporary and existing documents)
								if(!empty($tmp['documents_id'])) {		//if this item is to be removed (existing documents only!)
										//mv_documents_remove($tmp['documents_id']);
										cProductimages::remove($id, $tmp['documents_id']);		//only remove the image - but leave the document intact..
								}
								continue;		//skip the following inserts..
						}
		
						//check if the document already exists
						if(!empty($tmp['documents_id'])) {
								$document_id = $tmp['documents_id'];
						} else {
								//create the document in database
								$document_id = cDocument::create();
						}
		
						//move the files
						if(!empty($tmp['tmp_images_filename'])) {
								rename($file_source_folder . $tmp['tmp_images_filename'], $file_dest_folder . $document_id . $tmp['file_extension']);
								
								$file_extension = str_replace('.', '', $tmp['file_extension']);
								cDocument::saveFileData($document_id, $file_extension);
						}
		
						//save the extended data in document
						cDocument::saveData($document_id, 1, $tmp['file_source'], $tmp['license_type'], $tmp['qualifier']);
		
						//save image data in database
						cProductimages::saveData($id, $document_id, $tmp['sort_order']);
		
						//save the additional data in database
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'SELECT * FROM ' . $db->table('tmp_products_images_descriptions') . ' ' .
								'WHERE ' .
										'tmp_products_id = :tmp_products_id AND ' .
										'(tmp_images_filename = :tmp_images_filename OR documents_id = :documents_id)'
						);
						$db->bind(':tmp_products_id', $tmp_products_id);
						$db->bind(':tmp_images_filename', $tmp['tmp_images_filename']);
						$db->bind(':documents_id', $document_id);
						$subresult = $db->execute();
		
						while($subresult->next()) {
								$subtmp = $subresult->fetchArrayAssoc();
								cProductimages::saveDescriptions($id, $document_id, $subtmp['language_id'], $subtmp['alt_tag'], $subtmp['title_tag']);
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Allgemeine Produktinformationen speichern
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveGeneralInformation($products_data) {
				$id = false;
		
				if($products_data['id'] == 0) {
						$id = cProducts::createGeneralInformation($products_data);
				} else {
						$id = cProducts::updateGeneralInformation($products_data);
				}
				
				return $id;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Produkt Einkaufspreise speichern
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveBuyingPrices($id, $products_data) {
				$tmp_products_id = $products_data['tmp_products_id'];
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('tmp_products_buying_prices') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $tmp_products_id);
				$result = $db->execute();
				
				while($result->next()) {
						$data = $result->fetchArrayAssoc();
						
						if($data['remove'] != 0 && !empty($data['buying_price_id'])) {
								//remove this entry..
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery('DELETE FROM ' . $db->table('products_buying_prices') . ' WHERE id = :id');
								$db->bind(':id', $data['buying_price_id']);
								$db->execute();
						} else if($data['remove'] == 0) {
								if(!empty($data['buying_price_id'])) {
										//update an existing item
										$db = core()->get('db');
										$db->useInstance('systemdb');
										$db->setQuery(
												'UPDATE ' . $db->table('products_buying_prices') . ' SET ' .
														'price_netto = :price_netto, ' .
														'taxclass_id = :taxclass_id, ' .
														'suppliers_id = :suppliers_id ' .
												'WHERE ' .
														'id = :id'
										);
										$db->bind(':price_netto', (float)$data['price_netto']);
										$db->bind(':taxclass_id', (int)$data['taxclass_id']);
										$db->bind(':suppliers_id', (int)$data['suppliers_id']);
										$db->bind(':id', (int)$data['buying_price_id']);
										$db->execute();
								} else {
										//insert a new item..
										//check that this combination doesn't already exist..
										$db = core()->get('db');
										$db->useInstance('systemdb');
										$db->setQuery(
												'SELECT * FROM ' . $db->table('products_buying_prices') . ' ' .
												'WHERE ' .
														'price_netto = :price_netto AND ' .
														'taxclass_id = :taxclass_id AND ' .
														'suppliers_id = :suppliers_id AND ' .
														'products_id = :products_id'
										);
										$db->bind(':price_netto', (float)$data['price_netto']);
										$db->bind(':taxclass_id', (int)$data['taxclass_id']);
										$db->bind(':suppliers_id', (int)$data['suppliers_id']);
										$db->bind(':products_id', (int)$id);
										$subresult = $db->execute();
										
										$subdata = $subresult->fetchArrayAssoc();
										
										if(false === $subdata) {		//only create this entry, if an entry with exactly this data doesn't already exist..
												$db = core()->get('db');
												$db->useInstance('systemdb');
												$db->setQuery(
														'INSERT INTO ' . $db->table('products_buying_prices') . ' ' .
																'(products_id, price_netto, taxclass_id, suppliers_id) ' .
														'VALUES ' .
																'(:products_id, :price_netto, :taxclass_id, :suppliers_id)'
												);
												$db->bind(':products_id', (int)$id);
												$db->bind(':price_netto', (float)$data['price_netto']);
												$db->bind(':taxclass_id', (int)$data['taxclass_id']);
												$db->bind(':suppliers_id', (int)$data['suppliers_id']);
												$db->execute();
										}
								}
						}
				}
				
				//clean up..
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_buying_prices') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Produkt Preise speichern
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function savePrices($id, $products_data) {
				$tmp_products_id = $products_data['tmp_products_id'];
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('tmp_products_prices') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $tmp_products_id);
				$result = $db->execute();
				
				while($result->next()) {
						$data = $result->fetchArrayAssoc();
						
						if($data['remove'] != 0 && !empty($data['price_id'])) {
								//remove this entry..
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery('DELETE FROM ' . $db->table('products_prices') . ' WHERE id = :id');
								$db->bind(':id', (int)$data['price_id']);
								$db->execute();
						} else if($data['remove'] == 0) {
								if(!empty($data['price_id'])) {
										//update an existing item
										$db = core()->get('db');
										$db->useInstance('systemdb');
										$db->setQuery(
												'UPDATE ' . $db->table('products_prices') . ' SET ' .
														'price_netto = :price_netto, ' .
														'taxclass_id = :taxclass_id, ' .
														'price_quantity = :price_quantity ' .
												'WHERE ' .
														'id = :id'
																);
										$db->bind(':price_netto', (float)$data['price_netto']);
										$db->bind(':taxclass_id', (int)$data['taxclass_id']);
										$db->bind(':price_quantity', (float)$data['price_quantity']);
										$db->bind(':id', (int)$data['price_id']);
										$db->execute();
								} else {
										//insert a new item..
										//check that this combination doesn't already exist..
										$db = core()->get('db');
										$db->useInstance('systemdb');
										$db->setQuery(
												'SELECT * FROM ' . $db->table('products_prices') . ' ' .
												'WHERE ' .
														'channel_id = :channel_id AND ' .
														'customergroups_id = :customergroups_id AND ' .
														'price_netto = :price_netto AND ' .
														'taxclass_id = :taxclass_id AND ' .
														'price_quantity = :price_quantity and ' .
														'products_id = :products_id'
										);
										$db->bind(':channel_id', (int)$data['channel_id']);
										$db->bind(':customergroups_id', (int)$data['customergroups_id']);
										$db->bind(':price_netto', (float)$data['price_netto']);
										$db->bind(':taxclass_id', (int)$data['taxclass_id']);
										$db->bind(':price_quantity', (float)$data['price_quantity']);
										$db->bind(':products_id', (int)$id);
										$subresult = $db->execute();
										
										$subdata = $subresult->fetchArrayAssoc();
										
										if(false === $subdata) {		//only create this entry, if an entry with exactly this data doesn't already exist..
												$db = core()->get('db');
												$db->useInstance('systemdb');
												$db->setQuery(
														'INSERT INTO ' . $db->table('products_prices') . ' ' .
																'(products_id, price_netto, taxclass_id, price_quantity, channel_id, customergroups_id) ' .
														'VALUES ' .
																'(:products_id, :price_netto, :taxclass_id, :price_quantity, :channel_id, :customergroups_id)'
												);
												$db->bind(':products_id', (int)$id);
												$db->bind(':price_netto', (float)$data['price_netto']);
												$db->bind(':taxclass_id', (int)$data['taxclass_id']);
												$db->bind(':price_quantity', (float)$data['price_quantity']);
												$db->bind(':channel_id', (int)$data['channel_id']);
												$db->bind(':customergroups_id', (int)$data['customergroups_id']);
												$db->execute();
										}
								}
						}
				}
				
				//clean up..
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_prices') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Produktkategorien speichern
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveCategories($id, $products_data) {
				$tmp_products_id = $products_data['tmp_products_id'];
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('tmp_products_to_categories') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $tmp_products_id);
				$result = $db->execute();
				
				while($result->next()) {
						$data = $result->fetchArrayAssoc();
						
						if($data['remove'] != 0) {
								//remove the products category
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery('DELETE FROM ' . $db->table('products_to_categories') . ' WHERE products_id = :products_id AND categories_id = :categories_id');
								$db->bind(':products_id', (int)$id);
								$db->bind(':categories_id', (int)$data['categories_id']);
								$db->execute();
								
								continue;
						} else {
								//check this combination doesn't already exists
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery('SELECT * FROM ' . $db->table('products_to_categories') . ' WHERE products_id = :products_id AND categories_id = :categories_id');
								$db->bind(':products_id', (int)$id);
								$db->bind(':categories_id', (int)$data['categories_id']);
								$subresult = $db->execute();
								
								$subdata = $subresult->fetchArrayAssoc();
								
								if(empty($subdata)) {						
										//add this entry						
										$db = core()->get('db');
										$db->useInstance('systemdb');
										$db->setQuery('INSERT INTO ' . $db->table('products_to_categories') . ' (products_id, categories_id) VALUES(:products_id, :categories_id);');
										$db->bind(':products_id', (int)$id);
										$db->bind(':categories_id', (int)$data['categories_id']);
										$db->execute();
								}
						}
				}
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_to_categories') . ' WHERE tmp_products_id = :tmp_products_id');
				$db->bind(':tmp_products_id', $tmp_products_id);
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Save products_to_categories entry.
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveProductsToCategoriesEntry($products_id, $categories_id) {
				//check this combination doesn't already exists
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('products_to_categories') . ' WHERE products_id = :products_id AND categories_id = :categories_id');
				$db->bind(':products_id', (int)$products_id);
				$db->bind(':categories_id', (int)$categories_id);
				$subresult = $db->execute();
				
				$subdata = $subresult->fetchArrayAssoc();
				
				if(empty($subdata)) {						
						//add this entry						
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery('INSERT INTO ' . $db->table('products_to_categories') . ' (products_id, categories_id) VALUES(:products_id, :categories_id);');
						$db->bind(':products_id', (int)$products_id);
						$db->bind(':categories_id', (int)$categories_id);
						$db->execute();
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Alle Artikeltexte speichern
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveDescriptions($id, $products_data) {
				$datalanguages = cDatalanguages::loadActivated();
				$channels = cChannel::loadActiveChannels();
				
				foreach($datalanguages as $lang) {
						foreach($channels as $channel) {
								if(isset($products_data['description']) && is_array($products_data['description'])) {
										if(isset($products_data['description'][$lang['id']]) && is_array($products_data['description'][$lang['id']])) {
												if(isset($products_data['description'][$lang['id']][$channel['id']]) && is_array($products_data['description'][$lang['id']][$channel['id']])) {
														foreach($products_data['description'][$lang['id']][$channel['id']] as $title_id => $description) {
																cProducts::saveDescription($id, $lang['id'], $title_id, $channel['id'], $description);
														}
												}
										}
								}
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Alle Produkttitel speichern
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveTitles($id, $products_data) {
				$datalanguages = cDatalanguages::loadActivated();
				$channels = cChannel::loadActiveChannels();
				
				foreach($datalanguages as $lang) {
						foreach($channels as $channel) {
								if(isset($products_data['title']) && is_array($products_data['title'])) {
										if(isset($products_data['title'][$lang['id']]) && is_array($products_data['title'][$lang['id']])) {
												if(isset($products_data['title'][$lang['id']][$channel['id']]) && is_array($products_data['title'][$lang['id']][$channel['id']])) {
														foreach($products_data['title'][$lang['id']][$channel['id']] as $title_id => $title) {
																cProducts::saveTitle($id, $lang['id'], $title_id, $channel['id'], $title);
														}
												}
										}
								}
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Datenbankeintrag aktualisieren
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function updateGeneralInformation($data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('products') . ' SET ' .
								'products_type = :products_type, ' .
								'products_number = :products_number, ' .
								'ean = :ean, ' .
								'vpe = :vpe, ' .
								'vpe_unit = :vpe_unit, ' .
								'box_height = :box_height, ' .
								'box_width = :box_width, ' .
								'box_depth = :box_depth, ' .
								'box_weight = :box_weight, ' .
								'products_height = :products_height, ' .
								'products_width = :products_width, ' .
								'products_depth = :products_depth, ' .
								'products_weight = :products_weight, ' .
								'dimensional_weight = :dimensional_weight, ' .
								'products_condition = :products_condition, ' .
								'virtual_article = :virtual_article, ' .
								'bulky_good = :bulky_good, ' .
								'delivery_status = :delivery_status, ' .
								'spedition = :spedition, ' .
								'declaration_erroneous = :declaration_erroneous, ' .
								'declaration_incomplete = :declaration_incomplete, ' .
								'manufacturer = :manufacturer, ' .
								'manufacturers_number = :manufacturers_number ' .
						'WHERE id = :id'
				);
				$db->bind(':products_type', (int)$data['products_type']);
				$db->bind(':products_number', $data['products_number']);
				$db->bind(':ean', $data['ean']);
				$db->bind(':vpe', (float)$data['vpe']);
				$db->bind(':vpe_unit', (int)$data['vpe_unit']);
				$db->bind(':box_height', (float)$data['box_height']);
				$db->bind(':box_width', (float)$data['box_width']);
				$db->bind(':box_depth', (float)$data['box_depth']);
				$db->bind(':box_weight', (float)$data['box_weight']);
				$db->bind(':products_height', (float)$data['products_height']);
				$db->bind(':products_width', (float)$data['products_width']);
				$db->bind(':products_depth', (float)$data['products_depth']);
				$db->bind(':products_weight', (float)$data['products_weight']);
				$db->bind(':dimensional_weight', (float)$data['dimensional_weight']);
				$db->bind(':products_condition', (int)$data['products_condition']);
				$db->bind(':virtual_article', (int)$data['virtual_article']);
				$db->bind(':bulky_good', (int)$data['bulky_good']);
				$db->bind(':delivery_status', (int)$data['delivery_status']);
				$db->bind(':spedition', (int)$data['spedition']);
				$db->bind(':declaration_erroneous', (int)$data['declaration_erroneous']);
				$db->bind(':declaration_incomplete', (int)$data['declaration_incomplete']);
				$db->bind(':manufacturer', (int)$data['manufacturer']);
				$db->bind(':manufacturers_number', $data['manufacturers_number']);
				$db->bind(':id', (int)$data['id']);
				$db->execute();
				
				return $data['id'];
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Datenbankeintrag erstellen
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function createGeneralInformation($data) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('products') . '( ' .
								'products_type, products_number, ean, vpe, vpe_unit, deleted, box_height, ' .
								'box_width, box_depth, box_weight, products_height, products_width, ' .
								'products_depth,	products_weight, dimensional_weight, products_condition,  ' .
								'virtual_article, bulky_good, delivery_status, spedition, declaration_erroneous, declaration_incomplete, ' .
								'manufacturer, manufacturers_number' .
						') VALUES ( ' .
								':products_type, :products_number, :ean, :vpe, :vpe_unit, :deleted, :box_height, ' .
								':box_width, :box_depth, :box_weight, :products_height, :products_width, ' . 
								':products_depth,	:products_weight, :dimensional_weight, :products_condition, ' . 
								':virtual_article, :bulky_good, :delivery_status, :spedition, :declaration_erroneous, :declaration_incomplete, ' .
								':manufacturer, :manufacturers_number' .
						')'
				);
				$db->bind(':products_type', (int)$data['products_type']);
				$db->bind(':products_number', $data['products_number']);
				$db->bind(':ean', $data['ean']);
				$db->bind(':vpe', (float)$data['vpe']);
				$db->bind(':vpe_unit', (int)$data['vpe_unit']);
				$db->bind(':deleted', 0);
				$db->bind(':box_height', (float)$data['box_height']);
				$db->bind(':box_width', (float)$data['box_width']);
				$db->bind(':box_depth', (float)$data['box_depth']);
				$db->bind(':box_weight', (float)$data['box_weight']);
				$db->bind(':products_height', (float)$data['products_height']);
				$db->bind(':products_width', (float)$data['products_width']);
				$db->bind(':products_depth', (float)$data['products_depth']);
				$db->bind(':products_weight', (float)$data['products_weight']);
				$db->bind(':dimensional_weight', (float)$data['dimensional_weight']);
				$db->bind(':products_condition', (int)$data['products_condition']);
				$db->bind(':virtual_article', (int)$data['virtual_article']);
				$db->bind(':bulky_good', (int)$data['bulky_good']);
				$db->bind(':delivery_status', (int)$data['delivery_status']);
				$db->bind(':spedition', (int)$data['spedition']);
				$db->bind(':declaration_erroneous', (int)$data['declaration_erroneous']);
				$db->bind(':declaration_incomplete', (int)$data['declaration_incomplete']);
				$db->bind(':manufacturer', (int)$data['manufacturer']);
				$db->bind(':manufacturers_number', $data['manufacturers_number']);
				$db->execute();
				
				return $db->insertId();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Prüfen ob ein Artikel existiert
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkForExistence($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('products') . ' WHERE id = :id');
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				if(!empty($data)) {
						return true;
				}
				
				return false;
		}
				
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Alle Eingaben parsen - fehlende Felder ggf. hinzufügen und mit Default Werten versehen
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveBuildFields($products_data) {
				$retval = array();
				
				//products_id
				//If products id is zero, the product will be created, if it is not - it will be updated.
				if(!isset($products_data['id'])) {
						$products_data['id'] = 0;		
				}
				
				$products_data['id'] = (int)$products_data['id']; //force integer!
				
				if(false == cProducts::checkForExistence($products_data['id'])) {
						$products_data['id'] = 0;
				}
				
				$retval['id'] = (int)$products_data['id'];
				
				//products_type
				if(!isset($products_data['products_type'])) {
						$products_data['products_type'] = 0;
				}
				
				$products_data['products_type'] = (int)$products_data['products_type'];		//force integer!
				
				if(false == cProducttypes::loadById($products_data['products_type'])) {
						$products_data['products_type'] = 0;
				}
				
				$retval['products_type'] = $products_data['products_type'];
				
				//products_number
				if(!isset($products_data['products_number'])) {
						$products_data['products_number'] = '';
				}
				
				$retval['products_number'] = $products_data['products_number'];
				
				//ean
				if(!isset($products_data['ean'])) {
						$products_data['ean'] = '';
				}
				
				$retval['ean'] = $products_data['ean'];
				
				//vpe
				$retval['vpe'] = cProducts::parseFloatField($products_data, 'vpe');		//force float!
				
				//vpe_unit
				if(!isset($products_data['vpe_unit'])) {
						$products_data['vpe_unit'] = 0;
				}
				
				$products_data['vpe_unit'] = (int)$products_data['vpe_unit'];		//force integer!
				
				if(false == cPackagingunits::loadById($products_data['vpe_unit'])) {
						$products_data['vpe_unit'] = 0;
				}
				
				$retval['vpe_unit'] = $products_data['vpe_unit'];
				
				//box_height
				$retval['box_height'] = cProducts::parseFloatField($products_data, 'box_height');
				
				//box_width
				$retval['box_width'] = cProducts::parseFloatField($products_data, 'box_width');
				
				//box_depth
				$retval['box_depth'] = cProducts::parseFloatField($products_data, 'box_depth');
				
				//box_weight
				$retval['box_weight'] = cProducts::parseFloatField($products_data, 'box_weight');
				
				//products_height
				$retval['products_height'] = cProducts::parseFloatField($products_data, 'products_height');
				
				//products_width
				$retval['products_width'] = cProducts::parseFloatField($products_data, 'products_width');
				
				//products_depth
				$retval['products_depth'] = cProducts::parseFloatField($products_data, 'products_depth');
				
				//products_weight
				$retval['products_weight'] = cProducts::parseFloatField($products_data, 'products_weight');
				
				//dimensional_weight
				$retval['dimensional_weight'] = cProducts::parseFloatField($products_data, 'dimensional_weight');
				
				//products_condition
				if(!isset($products_data['products_condition'])) {
						$products_data['products_condition'] = 0;
				}
				
				$products_data['products_condition'] = (int)$products_data['products_condition'];		//force integer!
				
				if(false == cProductconditions::loadById($products_data['products_condition'])) {
						$products_data['products_condition'] = 0;
				}
				
				$retval['products_condition'] = $products_data['products_condition'];
				
				//virtual_article
				if(!isset($products_data['virtual_article'])) {
						$products_data['virtual_article'] = 0;
				}
				
				$products_data['virtual_article'] = (int)$products_data['virtual_article'];
				
				if($products_data['virtual_article'] != 1) {
						$products_data['virtual_article'] = 0;
				}
				
				$retval['virtual_article'] = $products_data['virtual_article'];
				
				//bulky_good
				if(!isset($products_data['bulky_good'])) {
						$products_data['bulky_good'] = 0;
				}
				
				$products_data['bulky_good'] = (int)$products_data['bulky_good'];
				
				if($products_data['bulky_good'] != 1) {
						$products_data['bulky_good'] = 0;
				}
				
				$retval['bulky_good'] = $products_data['bulky_good'];
				
				//delivery_status
				if(!isset($products_data['delivery_status'])) {
						$products_data['delivery_status'] = 0;
				}
				
				$products_data['delivery_status'] = (int)$products_data['delivery_status'];		//force integer!
				
				if(false == cPackagingunits::loadById($products_data['delivery_status'])) {
						$products_data['delivery_status'] = 0;
				}
				
				$retval['delivery_status'] = $products_data['delivery_status'];
				
				//spedition
				if(!isset($products_data['spedition'])) {
						$products_data['spedition'] = 0;
				}
				
				$products_data['spedition'] = (int)$products_data['spedition'];
				
				if($products_data['spedition'] != 1) {
						$products_data['spedition'] = 0;
				}
				
				$retval['spedition'] = $products_data['spedition'];
				
				//hersteller
				$retval['manufacturer'] = (int)$products_data['manufacturer'];
				$retval['manufacturers_number'] = $products_data['manufacturers_number'];
				
				//erroneous and imcomplete declarations
				$retval['declaration_erroneous'] = (int)$products_data['declaration_erroneous'];
				$retval['declaration_incomplete'] = (int)$products_data['declaration_incomplete'];
				
				//Return the result..
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Parse a data field in the array of expected type float
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function parseFloatField($products_data, $field_name) {
				if(!isset($products_data[$field_name])) {
						$products_data[$field_name] = 0;
				}
				
				$products_data[$field_name] = str_replace(',', '.', $products_data[$field_name]);
				
				return (float) $products_data[$field_name];		//force float!
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Save a product description
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveDescription($products_id, $languages_id, $description_id, $channel_id, $text_data) {
				//check if this text entry exists..
				$products_description = cProducts::loadDescription($products_id, $languages_id, $description_id, $channel_id);
				
				if(false === $products_description) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'INSERT INTO ' . $db->table('products_descriptions') . ' ' .
										'(products_id, languages_id, description_id, channel_id, text_data) ' .
								'VALUES ' .
										'(:products_id, :languages_id, :description_id, :channel_id, :text_data);'
						);
						$db->bind(':products_id', (int)$products_id);
						$db->bind(':languages_id', (int)$languages_id);
						$db->bind(':description_id', (int)$description_id);
						$db->bind(':channel_id', (int)$channel_id);
						$db->bind(':text_data', $text_data);
						$db->execute();
				} else {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'UPDATE ' . $db->table('products_descriptions') . ' SET ' .
										'text_data = :text_data ' .
								'WHERE ' .
										'products_id = :products_id AND ' .
										'languages_id = :languages_id AND ' .
										'description_id = :description_id AND ' .
										'channel_id = :channel_id'
						);
						$db->bind(':text_data', $text_data);
						$db->bind(':products_id', (int)$products_id);
						$db->bind(':languages_id', (int)$languages_id);
						$db->bind(':description_id', (int)$description_id);
						$db->bind(':channel_id', (int)$channel_id);
						$db->execute();
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load a product description
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadDescription($products_id, $languages_id, $description_id, $channel_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('products_descriptions') . ' ' .
						'WHERE ' . 
								'products_id = :products_id AND ' .
								'languages_id = :languages_id AND ' .
								'description_id = :description_id AND ' .
								'channel_id = :channel_id'
				);
				$db->bind(':products_id', (int)$products_id);
				$db->bind(':languages_id', (int)$languages_id);
				$db->bind(':description_id', (int)$description_id);
				$db->bind(':channel_id', (int)$channel_id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				return $data;										
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load all descriptions for one product
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadDescriptionsByProductsId($products_id) {
				$retval = array();
				
				$datalanguages = cDatalanguages::loadActivated();
				$channels = cChannel::loadActiveChannels();
				
				foreach($datalanguages as $lang) {
						foreach($channels as $channel) {
								for($i = 1; $i < SYSTEM_MAX_PRODUCT_DESCRIPTIONS; $i++) {
										$tmp = cProducts::loadDescription($products_id, $lang['id'], $i, $channel['id']);
										
										if(!empty($tmp)) {
												$retval[$lang['id']][$channel['id']][$i] = $tmp['text_data'];
										} else {
												$retval[$lang['id']][$channel['id']][$i] = '';
										}
								}
						}
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Save a product title
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveTitle($products_id, $languages_id, $title_id, $channel_id, $text_data) {
				//check if this text entry exists..
				$products_title = cProducts::loadTitle($products_id, $languages_id, $title_id, $channel_id);
				
				if(false === $products_title) {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'INSERT INTO ' . $db->table('products_titles') . ' ' .
										'(products_id, languages_id, title_id, channel_id, text_data) ' .
								'VALUES ' .
										'(:products_id, :languages_id, :title_id, :channel_id, :text_data);'
						);
						$db->bind(':products_id', (int)$products_id);
						$db->bind(':languages_id', (int)$languages_id);
						$db->bind(':title_id', (int)$title_id);
						$db->bind(':channel_id', (int)$channel_id);
						$db->bind(':text_data', $text_data);
						$db->execute();
				} else {
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'UPDATE ' . $db->table('products_titles') . ' SET ' .
										'text_data = :text_data ' .
								'WHERE  ' .
										'products_id = :products_id AND ' .
										'languages_id = :languages_id AND ' .
										'title_id = :title_id AND ' .
										'channel_id = :channel_id'
						);
						$db->bind(':text_data', $text_data);
						$db->bind(':products_id', (int)$products_id);
						$db->bind(':languages_id', (int)$languages_id);
						$db->bind(':title_id', (int)$title_id);
						$db->bind(':channel_id', (int)$channel_id);
						$db->execute();
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load a product title
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadTitle($products_id, $languages_id, $title_id, $channel_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'select * from ' . $db->table('products_titles') . ' ' .
						'WHERE ' .
								'products_id = :products_id AND ' .
								'languages_id = :languages_id AND ' .
								'title_id = :title_id AND ' .
								'channel_id = :channel_id'
				);
				$db->bind(':products_id', (int)$products_id);
				$db->bind(':languages_id', (int)$languages_id);
				$db->bind(':title_id', (int)$title_id);
				$db->bind(':channel_id', (int)$channel_id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				return $data;										
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load all titles for one product
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadTitlesByProductsId($products_id) {
				$retval = array();
				
				$datalanguages = cDatalanguages::loadActivated();
				$channels = cChannel::loadActiveChannels();
				
				foreach($datalanguages as $lang) {
						foreach($channels as $channel) {
								for($i = 1; $i < SYSTEM_MAX_PRODUCT_TITLES; $i++) {
										$tmp = cProducts::loadTitle($products_id, $lang['id'], $i, $channel['id']);
										
										if(!empty($tmp)) {
												$retval[$lang['id']][$channel['id']][$i] = $tmp['text_data'];
										} else {
												$retval[$lang['id']][$channel['id']][$i] = '';
										}
								}
						}
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load all titles for one product
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadProductcategoriesByProductsIdAndChannel($products_id, $channel_id, $default_datalanguage) {
				$retval = false;
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('products_to_categories') . ' p2c ' .
								'JOIN ' . $db->table('product_categories') . ' pc ON pc.id = p2c.categories_id ' .
						'WHERE ' .
								'p2c.products_id = :products_id AND ' .
								'pc.channel_id = :channel_id;'
				);
				$db->bind(':products_id', (int)$products_id);
				$db->bind(':channel_id', (int)$channel_id);
				$result = $db->execute();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$tmp['categories_string'] = cProductcategories::buildStringPlain($tmp['categories_id'], $default_datalanguage);
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// load products number by products id
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadProductsNumberByProductsId($products_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT products_number FROM ' . $db->table('products') . ' WHERE id = :id');
				$db->bind(':id', (int)$products_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false === $tmp) {
						return false;
				}
				
				return $tmp['products_number'];
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// load products by products number
		//
		// products number should be unique - so there can only be one result!
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadProductsIdByProductsNumber($products_number) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id FROM ' . $db->table('products') . ' WHERE products_number = :products_number');
				$db->bind(':products_number', $products_number);
				$result = $db->execute();
		
				$tmp = $result->fetchArrayAssoc();
				
				if(false === $tmp) {
						return false;
				}
		
				return $tmp['id'];
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// load products by products number
		//
		// products number should be unique - so there can only be one result!
		// if products number is empty - return..
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadProductsIdByProductsNumberButNotEmpty($products_number) {
				if($products_number == '') {
						return false;
				}
				
				return cProducts::loadProductsIdByProductsNumber($products_number);
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// save products data
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveBasedataByDataArray($products_id, $data) {
				if(empty($products_id)) {
						$products_id = cProducts::createBasedataByDataArray($products_id, $data);
				} else {
						$products_id = cProducts::updateBasedataByDataArray($products_id, $data);
				}
				
				return $products_id;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// create products data
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function createBasedataByDataArray($products_id, $data) {
				$parameter_string = '';
				$values_string = '';
				
				foreach($data as $index => $d) {
						if(!empty($parameter_string)) {
								$parameter_string .= ', ';
								$values_string .= ', ';
						}
						
						$parameter_string .= $index;
						$values_string .= ':' . $index;
				}
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('INSERT INTO ' . $db->table('products') . '(' . $parameter_string . ') VALUES (' . $values_string . ')');
				
				reset($data);
				
				foreach($data as $index => $d) {
						$db->bind(':' . $index, $d);
				}
				
				$result = $db->execute();
				return $db->insertId();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// update products data
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function updateBasedataByDataArray($products_id, $data) {
				$parameter_string = '';
				
				foreach($data as $index => $d) {
						if(!empty($parameter_string)) {
								$parameter_string .= ', ';
						}
						
						$parameter_string .= $index . ' = :' . $index;
				}
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('UPDATE ' . $db->table('products') . ' SET ' . $parameter_string . ' WHERE id = :products_id');
				
				reset($data);
				
				foreach($data as $index => $d) {
						$db->bind(':' . $index, $d);
				}
				
				$db->bind(':products_id', $products_id);		
				$result = $db->execute();
				
				return $products_id;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// save products channels data (special channel data for a product
		// input is provided by an array and the products_id
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveChannelSpecificDataByDataArray($products_id, $data, $channel_id) {
				foreach($data as $index => $value) {
						//check if a products channel option exists
						if(false === cProductchannels::loadValueByProductChannelPreference($products_id, $channel_id, $index)) {
								//insert
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'INSERT INTO ' . $db->table('products_channel_options') . ' (products_id, channel_id, channel_preferences_model_id, value) ' .
										'VALUES (:products_id, :channel_id, :channel_preferences_model_id, :value)'
								);
								$db->bind(':products_id', (int)$products_id);
								$db->bind(':channel_id', (int)$channel_id);
								$db->bind(':channel_preferences_model_id', (int)$index);
								$db->bind(':value', $value);
								$db->execute();
						} else {
								//update
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery(
										'UPDATE ' . $db->table('products_channel_options') . ' SET ' .
												'value = :value ' .
										'WHERE ' .
												'products_id = :products_id AND ' .
												'channel_id = :channel_id AND ' .
												'channel_preferences_model_id = :channel_preferences_model_id;'
								);
								$db->bind(':value', $value);
								$db->bind(':products_id', (int)$products_id);
								$db->bind(':channel_id', (int)$channel_id);
								$db->bind(':channel_preferences_model_id', (int)$index);
								$db->execute();
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Complex search input that can search in different product fields.
		// Uses the product object as input.
		///////////////////////////////////////////////////////////////////////////////////////////////////////////
		public static function search($searchterm) {
				
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Load complex product data.
		///////////////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadComplexProductData($id, $default_datalanguage) {
				//Initialice important data..
				$retval = array();			
				$retval['products_id'] = $id;
				$retval['default_datalanguage'] = $default_datalanguage;
				$retval['producttypes'] = cProducttypes::loadActive($default_datalanguage);
				$retval['packagingunits'] = cPackagingunits::loadActive($default_datalanguage);
				$retval['productconditions'] = cProductconditions::loadActive($default_datalanguage);
				$retval['deliverystatus'] = cDeliverystatus::loadActive($default_datalanguage);
				$retval['datalanguages'] = cDatalanguages::loadActivated();
				$retval['channels'] = cChannel::loadActiveChannels();
				$retval['suppliers'] = cAccount::loadSuppliers();				
				$retval['taxclasses'] = cTaxclasses::loadActive($default_datalanguage);
				$retval['buying_prices'] = array();
				$retval['prices'] = array();
				$retval['customergroups'] = cCustomergroups::loadActive();
				$retval['products_images'] = array();
				$retval['products_files'] = array();
				$retval['products_channels_data'] = cProductchannels::loadPreferencesModel($retval['channels'], $retval['datalanguages']);
				$retval['manufacturers'] = cAccount::loadManufacturers();
				$retval['products_options'] = cProductoptions::loadOptions($default_datalanguage);
				$retval['products_featuresets'] = cProductfeaturesets::loadFeaturesets($default_datalanguage);
				
				//Load core products data..
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('products') . ' where id = :id');
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
	
				if(empty($data)) {
						return false;
				}
				
				//load the title texts
				$data['titles'] = cProducts::loadTitlesByProductsId($id);
				
				//load the descriptions texts
				$data['descriptions'] = cProducts::loadDescriptionsByProductsId($id);
				
				//load products_categories
				$products_categories = array();
				
				//load channels
				
				
				foreach($retval['channels'] as $channel) {
						$tmp = cProducts::loadProductcategoriesByProductsIdAndChannel($id, $channel['id'], $default_datalanguage);					
						$products_categories[$channel['id']] = $tmp;
				}
				
				$data['products_categories'] = $products_categories;
				
				//load products buying prices
				$buying_prices = cProductbuyingprices::loadAsArray($id, $default_datalanguage);
				
				//load products prices
				$prices = cProductprices::load($id, $default_datalanguage);
				
				//load products images
				$retval['products_images'] = cProductimages::loadByProductsId($id);
				
				//load products files
				$products_files = cProductfiles::loadByProductsId($id);
				
				//load channels specific data
				cProductchannels::loadProductsChannelsValueByChannelReferenceArray($id, $retval['products_channels_data']);		//this uses a reference as parameter - so no return value is needed..
	
				//assign the data to the main object..
				$data['data'] = $data;
				
				//load attributes
				$data['products_attributes'] = cProductattributes::loadProductsAttributes((int)$id, $default_datalanguage);
				$data['products_attributes'] = cProductattributes::loadOptionsTitles($data['products_attributes'], $default_datalanguage, '');
				$data['products_attributes'] = cProductattributes::loadOptionsValuesTitles($data['products_attributes'], $default_datalanguage, '');
				
				//load features
				$data['products_features'] = cProductfeatures::loadProductsFeatures((int)$id, $default_datalanguage);
				$data['products_features'] = cProductfeatures::loadFeaturesetsTitles($data['products_features'], $default_datalanguage, '');
				$data['products_features'] = cProductfeatures::loadFeaturesetsValuesTitles($data['products_features'], $default_datalanguage, '');

				$retval['products_data'] = $data;
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Load complex product data.
		///////////////////////////////////////////////////////////////////////////////////////////////////////////
		public static function getCategoriesFirstProductsId($categories_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT p.id FROM ' . $db->table('products_to_categories') . ' p2c ' .
								'JOIN ' . $db->table('products') . ' p ON p.id = p2c.products_id ' .
								'JOIN ' . $db->table('products_channel_options') . ' pco ON pco.products_id = p2c.products_id AND pco.channel_preferences_model_id = 1 ' .
						'WHERE p2c.categories_id = :categories_id ' .
						'ORDER BY pco.value ' .
						'LIMIT 1'
				);
				$db->bind(':categories_id', (int)$categories_id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
				
				if(!isset($data['id'])) {
						return false;
				}
				
				return $data['id'];
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Load complex product data.
		///////////////////////////////////////////////////////////////////////////////////////////////////////////
		public static function updateProductsManufacturersId($products_id, $manufacturer) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('products') . ' SET ' .
								'manufacturer = :manufacturer ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':manufacturer', (int)$manufacturer);
				$db->bind(':id', (int)$products_id);
				$result = $db->execute();
		}
}

?>