<?php

class cAdminproductimport extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINPRODUCTIMPORT;
		var $navbar_id = 0;
		var $errors = array();
		var $info_messages = array();
		var $success_messages = array();
		var $data = array();
		
		var $indexes = array();
		var $expected_indexes_order = array();
		var $columns = 0;
		
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				//If the user is not logged in..
				if(!isset($_SESSION['user_id'])) {
						header('Location: index.php/login/');
						die;
				}
				
				//check the rights..
				if(false === cAccount::adminrightCheck('cAdminproductimport', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=123');
						die;
				}
				
				//We use the Admin module for output.
				cAdmin::setSmallBodyExecutionalHooks();	
				
				//Now set our own hooks below the CMS hooks.
				$core = core();
				core()->setHook('cCore|process', 'process');
				core()->setHook('cRenderer|content', 'content');
		}
	
	
		///////////////////////////////////////////////////////////////////
		// processData
		///////////////////////////////////////////////////////////////////
		function process() {
				$this->action = core()->getGetVar('action');
				$cAdmin = core()->getInstance('cAdmin');
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINPRODUCTIMPORT, 'index.php?s=cAdminproductimport');
				$this->data['url'] = 'index.php?s=cAdminproductimport&amp;action=import';
				$this->initData();
				
				switch($this->action) {
						default:
								$this->import();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Daten initialisieren.
		///////////////////////////////////////////////////////////////////
		private function initData() {
				//Die Werte der Indizes sind entweder Zahlen, welche die Anzahl der aufeinanderfolgenden
				//Spalten mit dieser Überschrift symbolisieren.
				//Ist der Wert hingegen ein Array, dann bedeutet es, das eine tieferliegende
				//Kombination vorkommen kann - also aufeinander folgende sich wiederholende Werte..
				
				$this->expected_indexes_order = array(
						'ID' => $this->getIndexItem('1', 'int'),
						'Artikelnummer' => $this->getIndexItem('1', 'varchar'),
						'Hersteller' => $this->getIndexItem('1', 'varchar'),
						'Bezeichnung' => $this->getIndexItem('1', 'varchar'),
						'Kurzbezeichnung' => $this->getIndexItem('1', 'varchar'),
						'List' => $this->getIndexItem(
								array(
										'List%' => $this->getIndexItem('1', 'varchar')
								),
								'array'
						),
						'Kategorie 1' => $this->getIndexItem('1', 'varchar'),
						'Kategorie 2' => $this->getIndexItem('1', 'varchar'),
						'Kategorie 3' => $this->getIndexItem('1', 'varchar'),
						'Sortierung' => $this->getIndexItem('1', 'int'),
						'Attribute' => $this->getIndexItem(
								array(
										'Atribut%' => $this->getIndexItem('1', 'varchar'),
										'Ausprägung%' => $this->getIndexItem('1', 'varchar'),
										'Sortierung%' => $this->getIndexItem('1', 'int')
								),
								'array'
						),
						'Details' => $this->getIndexItem(
								array(
										'Details%' => $this->getIndexItem('1', 'varchar'),
										'Sortierung%' => $this->getIndexItem('1', 'int'),
										'Text%' => $this->getIndexItem('1', 'varchar')
								),
								'array'
						),
						'Druckinfo' => $this->getIndexItem(
								array(
										'Druck%' => $this->getIndexItem('1', 'varchar'),
										'Sortierung%' => $this->getIndexItem('1', 'int'),
										'Text%' => $this->getIndexItem('1', 'varchar')
								),
								'array'
						)
				);
		}
		
		///////////////////////////////////////////////////////////////////
		// Return index item array.
		///////////////////////////////////////////////////////////////////
		private function getIndexItem($number_of_items, $datatype) {
				return array(
						'number_of_items' => $number_of_items,
						'datatype' => $datatype
				);
		}
		
		///////////////////////////////////////////////////////////////////
		// Start the import (if data is provided).
		///////////////////////////////////////////////////////////////////
		private function import() {
				if($this->action == 'import') {
						$this->checkFile();
						$this->saveData();
						
						header('Location: index.php?s=cAdminproductimport&success=' . urlencode('Die Daten wurden erfolgreich importiert'));
						die;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// This saves all the data.
		///////////////////////////////////////////////////////////////////
		private function saveData() {
				$count = 0;
				$fp = fopen($_FILES['file']['tmp_name'], 'r');
				
				while($row = fgetcsv($fp, 999999, ',', '"', '"')) {
						if($count == 0) {		//Skip first line (it is the headings..
								$count++;
								continue;
						}
						
						$id = $this->saveProductGeneralData($row);
						$count++;
				}
				
				fclose($fp);
		}
		
		///////////////////////////////////////////////////////////////////
		// Save product general data.
		///////////////////////////////////////////////////////////////////
		private function saveProductGeneralData($row) {
				$columns = $this->getTableFields('products');
				unset($columns['id']);		//Mysql generates a new id..
				$columns['products_number'] = $this->getRowsValue($row, 'Artikelnummer');
				$columns['product_type'] = 1;		//Standard Artikel
				$columns['products_condition'] = 1;		//Artikel-Zustand: neu
				
				$products_id = 0;
				
				$products_id = cProducts::createGeneralInformation($columns);
				cProducts::saveTitle($products_id, 1, 1, 0, $this->getRowsValue($row, 'Bezeichnung'));
				cProducts::saveTitle($products_id, 1, 2, 0, $this->getRowsValue($row, 'Bezeichnung'));
				cProducts::saveDescription($products_id, 1, 1, 0, $this->getRowsValue($row, 'Kurzbezeichnung'));
				$this->saveShortDescriptionByList($products_id, $row);
				$this->saveManufacturer($products_id, $this->getRowsValue($row, 'Hersteller'));
				$this->saveCategory($products_id, $this->getRowsValue($row, 'Kategorie 2'));		//We use only category 2 at the moment!
				$this->saveProductsSortOrder($products_id, $this->getRowsValue($row, 'Sortierung'));
				$this->saveProductsAttributes($products_id, $columns['products_number'], $row);
				$this->saveProductsFeatures($products_id, $row);
				$this->saveProductsFeaturesDruck($products_id, $row);
				$this->saveProductsPlaceholderMainImage($products_id);
				$this->saveProductDefaultImages($products_id, $row);		//This are attribute images a.t.m.
		}
		
		///////////////////////////////////////////////////////////////////
		// Save products placeholder image.
		///////////////////////////////////////////////////////////////////
		private function saveProductsPlaceholderMainImage($products_id) {
				$src = 'data/images/placeholder.jpg';
				$dst_path = 'data/images/product_images/';
				
				//Create document
				$documents_id = cDocument::create();												//Dokument anlegen
				cDocument::saveFileData($documents_id, 'jpg');							//Dokumentdaten anlegen (Datei-Extension)
				
				//Create product image
				$images_id = cProductimages::saveData($products_id, $documents_id, 0);		//Artikel-Bild Daten anlegen
				
				//copy file
				$dst = $dst_path . $documents_id . '.jpg';
				copy($src, $dst);
		}
		
		
		///////////////////////////////////////////////////////////////////
		// Save products default images.
		///////////////////////////////////////////////////////////////////
		private function saveProductDefaultImages($products_id, $row) {
				$import_images_folder = 'import_images/standardbilder/';
				$import_images_folder .= $this->makeClearFilesystemName(utf8_decode($this->getRowsValue($row, 'Kategorie 2')));
				$import_images_folder .= '/';
				
				//Attribute (Farben)
				$item_name = $this->makeClearFilesystemName(utf8_decode($this->getRowsValue($row, 'Bezeichnung')));
				
				$subfolder = 'farben/';
				$subfolder .= $item_name;
				$subfolder .= '/';
				
				//Skip some attributes, that get real images!
				switch($item_name) {
						case 'bueroclipjohn':
						case 'bueroclipjohnny':
						case 'register':
								return;
				}
				
				
				
				foreach($this->indexes['Attribute'] as $attribute) {
						$options_value = $row[$attribute[utf8_decode('Ausprägung')]['col_index']];
						
						if($options_value == '') {
								continue;
						}
						
						
						$filename = $this->makeClearFilesystemName($options_value) . '.jpg';
						$options_value = utf8_encode($options_value);
						$path = $import_images_folder . $subfolder . $filename;
						
						if(!file_exists($path)) {
								echo 'Bild wurde nicht gefunden: ';
								echo $path;
								die;
						}
						
						
						$documents_id = cDocument::create();												//Dokument anlegen
						cDocument::saveFileData($documents_id, 'jpg');							//Dokumentdaten anlegen
						
						//get options id
						$option = utf8_encode($row[$attribute['Atribut']['col_index']]);
						$options_data = cProductoptions::loadByTitle(1, $option);
						$options_id = $options_data['products_options_id'];
						
						//get attributes products id.
						$options_values_data = cProductoptionsvalues::loadByTitleAndOptionsId(1, $options_id, $options_value);
						$options_values_id = $options_values_data['products_options_values_id'];
						
						//Load feature by products id and options values id
						$slave_products_data = cProductattributes::loadByProductsIdAndOptionsValuesId($products_id, $options_values_id);
						$slave_products_id = $slave_products_data['products_id_slave'];
						
						//Save the attributes image
						$images_id = cProductimages::saveData($slave_products_id, $documents_id, 0);		//Artikel-Bild Daten anlegen
						
						//Move the file
						$dst = 'data/images/product_images/' . $documents_id . '.jpg';
						copy($path, $dst);
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Fehlerfreien Namen für Dateisystem erzeugen.
		///////////////////////////////////////////////////////////////////
		private function makeClearFilesystemName($name) {
				$name = utf8_encode($name);
				$name = strtolower($name);
				
				//replace umlauts and sz
				$name = str_replace('ä', 'ae', $name);
				$name = str_replace('Ä', 'ae', $name);
				$name = str_replace('ö', 'oe', $name);
				$name = str_replace('Ö', 'oe', $name);
				$name = str_replace('ü', 'ue', $name);
				$name = str_replace('Ü', 'ue', $name);
				
				$name = str_replace('ß', 'ss', $name);
				
				//Einige andere nervige Zeichen..
				$name = str_replace(' ', '', $name);
				$name = str_replace('-', '', $name);
				$name = str_replace('_', '', $name);
				$name = str_replace('/', '', $name);
				
				return $name;
		}
		
		///////////////////////////////////////////////////////////////////
		// Save products attributes.
		///////////////////////////////////////////////////////////////////
		private function saveProductsFeatures($products_id, $row) {
				$feature_number = 0;
			
				foreach($this->indexes['Details'] as $detail) {
						$feature_number++;	
						$featureset = 'Details';				
						$featuresets_value = utf8_encode($row[$detail['Details']['col_index']]);
						$sort_order = utf8_encode($row[$detail[utf8_decode('Sortierung')]['col_index']]);
						$description = utf8_encode($row[$detail['Text']['col_index']]);
						
						if('' == $featuresets_value || '' == $sort_order || '' == $description) {
								continue;
						}
						
						//Get featuresets id
						$featuresets_data = cProductfeaturesets::loadByTitle(1, $featureset);
						$featuresets_id = 0;
						
						if(false === $featuresets_data)  {
								$featuresets_id = cProductfeaturesets::createFeatureset();
								cProductfeaturesets::saveDescription($featuresets_id, 1, $featureset);
						} else {
								$featuresets_id = $featuresets_data['products_featuresets_id'];
						}
						
						//Get featuresets_values_id
						$featuresets_values_data = cProductfeaturesetsvalues::loadByTitleAndFeaturesetsId(1, $featuresets_id, $featuresets_value);
						$featuresets_values_id = 0;
						
						if(false === $featuresets_values_data) {
								$featuresets_values_id = cProductfeaturesetsvalues::create($featuresets_id);
								cProductfeaturesetsvalues::saveDescription($featuresets_values_id, 1, $featuresets_value);
						} else {
								$featuresets_values_id = $featuresets_values_data['products_featuresets_values_id'];
						}
						
						//Save featureset
						cProductfeatures::create($products_id, $featuresets_values_id, $sort_order, $description);
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Save products attributes.
		///////////////////////////////////////////////////////////////////
		private function saveProductsFeaturesDruck($products_id, $row) {
				$feature_number = 0;
			
				foreach($this->indexes['Druckinfo'] as $detail) {
						$feature_number++;	
						$featureset = 'Druck';				
						$featuresets_value = utf8_encode($row[$detail['Druck']['col_index']]);
						$sort_order = utf8_encode($row[$detail[utf8_decode('Sortierung')]['col_index']]);
						$description = utf8_encode($row[$detail['Text']['col_index']]);
						
						if('' == $featuresets_value || '' == $sort_order || '' == $description) {
								continue;
						}
						
						//Get featuresets id
						$featuresets_data = cProductfeaturesets::loadByTitle(1, $featureset);
						$featuresets_id = 0;
						
						if(false === $featuresets_data)  {
								$featuresets_id = cProductfeaturesets::createFeatureset();
								cProductfeaturesets::saveDescription($featuresets_id, 1, $featureset);
						} else {
								$featuresets_id = $featuresets_data['products_featuresets_id'];
						}
						
						//Get featuresets_values_id
						$featuresets_values_data = cProductfeaturesetsvalues::loadByTitleAndFeaturesetsId(1, $featuresets_id, $featuresets_value);
						$featuresets_values_id = 0;
						
						if(false === $featuresets_values_data) {
								$featuresets_values_id = cProductfeaturesetsvalues::create($featuresets_id);
								cProductfeaturesetsvalues::saveDescription($featuresets_values_id, 1, htmlentities($featuresets_value));
						} else {
								$featuresets_values_id = $featuresets_values_data['products_featuresets_values_id'];
						}
						
						//Save featureset
						cProductfeatures::create($products_id, $featuresets_values_id, $sort_order, $description);
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Save products attributes.
		///////////////////////////////////////////////////////////////////
		private function saveProductsAttributes($products_id, $products_model, $row) {
				$attribute_number = 0;
			
				foreach($this->indexes['Attribute'] as $attribute) {
						$attribute_number++;					
						$option = utf8_encode($row[$attribute['Atribut']['col_index']]);
						$options_value = utf8_encode($row[$attribute[utf8_decode('Ausprägung')]['col_index']]);
						$attribute_sort_order = utf8_encode($row[$attribute['Sortierung']['col_index']]);
						
						if('' == $option || '' == $options_value || '' == $attribute_sort_order) {
								continue;
						}
						
						//Get options id
						$options_data = cProductoptions::loadByTitle(1, $option);
						$options_id = 0;
						
						if(false === $options_data)  {
								$options_id = cProductoptions::createOption();
								cProductoptions::saveDescription($options_id, 1, $option);
						} else {
								$options_id = $options_data['products_options_id'];
						}
						
						//Get options_values_id
						$options_values_data = cProductoptionsvalues::loadByTitleAndOptionsId(1, $options_id, $options_value);
						$options_values_id = 0;
						
						if(false === $options_values_data) {
								$options_values_id = cProductoptionsvalues::create($options_id);
								cProductoptionsvalues::saveDescription($options_values_id, 1, $options_value);
						} else {
								$options_values_id = $options_values_data['products_options_values_id'];
						}
						
						//Save attribute
						$attributes_model = $products_model . '_' . $attribute_number;
						$products_id_slave = cProducts::createByAttributesData($attributes_model);
						cProducts::saveTitle($products_id_slave, 1, 2, 0, $this->makeClearFilesystemName(utf8_decode($options_value)) . '.jpg');
						cProductattributes::create($products_id, $products_id_slave, $options_values_id, $attribute_sort_order);
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Save category.
		///////////////////////////////////////////////////////////////////
		private function saveProductsSortOrder($products_id, $sort_order) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('products_channel_options') . ' ' .
								'(products_id, channel_id, channel_preferences_model_id, value) ' .
						'VALUES ' .
								'(:products_id, :channel_id, :channel_preferences_model_id, :value)'
				);
				$db->bind(':products_id', (int)$products_id);
				$db->bind(':channel_id', (int)0);
				$db->bind(':channel_preferences_model_id', (int)1);
				$db->bind(':value', (int)$sort_order);
				$db->execute();
		}
		
		///////////////////////////////////////////////////////////////////
		// Save category.
		///////////////////////////////////////////////////////////////////
		private function saveCategory($products_id, $category_name) {
				//Get categories id by categories_name
				$category_id = cProductcategoriesdescriptions::loadCategoriesIdByTitleAndLanguageId($category_name, 1);
				
				if(false === $category_id) {
						$sort_order = cProductcategories::getMaxSortOrder(0, 0);
						$sort_order += 10;
						
						$category_id = cProductcategories::createSimple($category_name, 0, 0, 1, 1, $sort_order);
				}
				
				cProducts::saveProductsToCategoriesEntry($products_id, $category_id);
		}
		
		///////////////////////////////////////////////////////////////////
		// Save manufacturer.
		///////////////////////////////////////////////////////////////////
		private function saveManufacturer($products_id, $manufacturers_name) {
				$accounts_id = cAccount::loadManufacturersIdByCompanyName($manufacturers_name);
				
				if(false == $accounts_id) {
						$accounts_id = cAccount::createManufacturersAccountByManufacturersName($manufacturers_name);
				}
				
				cProducts::updateProductsManufacturersId($products_id, (int)$accounts_id);
		}
		
		///////////////////////////////////////////////////////////////////
		// Save short description by list.
		///////////////////////////////////////////////////////////////////
		private function saveShortDescriptionByList($products_id, $row) {
				$count = 0;
				$text = '<ul class="mv-checked-list">';
				
				foreach($this->indexes['List'] as $index => $value) {
						if($row[ $value['List']['col_index'] ] != '') {
								$text .= '<li>' . htmlentities(utf8_encode($row[ $value['List']['col_index'] ])) . '</li>';
								$count++;
						}
				}
				
				$text .= '</ul>';
				
				if($count != 0) {
						cProducts::saveDescription($products_id, 1, 2, 0, $text);
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Get the value for one row!
		///////////////////////////////////////////////////////////////////
		private function getRowsValue($row, $column_name) {
				if(!isset($this->indexes[$column_name])) {
						die('Kein Index mit dem Titel ' . $column_name . ' gefunden in ' . __FILE__ . ', Zeile ' . __LINE__);
				}
				
				return utf8_encode($row[ $this->indexes[$column_name]['col_index'] ]);
		}
					
		
		///////////////////////////////////////////////////////////////////
		// Get table fields.
		///////////////////////////////////////////////////////////////////
		private function getTableFields($table_name) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SHOW COLUMNS FROM ' . $db->table($table_name) . ';');
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						
						if(0 === strpos($tmp['Type'], 'int')) {
								$tmp['Default'] = 0;
						} else {
								$tmp['Default'] = '';
						}						
						
						$retval[$tmp['Field']] = $tmp['Default'];
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////
		// Check the CSV File for validity.
		// Every subfunction redirects by it's own,
		// if it encounters an error.
		///////////////////////////////////////////////////////////////////
		private function checkFile() {
				//Check File was uploaded.	
				$this->checkFileUploadState();			
				
				//Check the heading, also sets the indexes and counts the number
				//of columns.
				$this->checkFileHeading();
				
				//Check data rows.
				$this->checkRows();
		}
		
		///////////////////////////////////////////////////////////////////
		// Check all data rows.
		///////////////////////////////////////////////////////////////////
		private function checkRows() {
				$count = 0;				
				$fp = fopen($_FILES['file']['tmp_name'], 'r');
				
				while($row = fgetcsv($fp, 999999, ',', '"', '"')) {
						if($count == 0) {		//Skip first row - it contains the heading..
								$count++;
								continue;
						}
						
						$this->checkRow($row, $count);
						$count++;
				}
				
				fclose($fp);
		}
		
		///////////////////////////////////////////////////////////////////
		// Check the data of one row.
		///////////////////////////////////////////////////////////////////
		private function checkRow($row, $count) {
				//We only count, thats all..
				if(count($row) != 0 && count($row) != $this->columns) {
						header('Location: index.php?s=cAdminproductimport&error=' . urlencode('Die Spaltenanzahl in Zeile ' . $count . ' entspricht nicht der erwartenen Anzahl an Spalten. Bitte die Eingabedatei prüfen! Import wurde abgebrochen.'));
						die;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// First row in the file contains all headings.
		// Here we check them for validity.
		// This also sets the expected indexes and counts the number of
		// columns.
		///////////////////////////////////////////////////////////////////
		private function checkFileHeading() {
				$fp = fopen($_FILES['file']['tmp_name'], 'r');
				$row = fgetcsv($fp, 999999, ',', '"', '"');
				fclose($fp);
				$col_index = 0;
				
				$this->indexes = $this->processIndexArray($this->expected_indexes_order, $row, $col_index, 0);
				$this->columns = $col_index;
		}
		
		///////////////////////////////////////////////////////////////////
		// Process iundex array.
		///////////////////////////////////////////////////////////////////
		private function processIndexArray($expected_indexes, $row, &$col_index, $level) {
				$level++;
				$loop_count = 0;
				$indexes = array();
				
				foreach($expected_indexes as $column_name => $info) {
						/*
						echo '<hr /><pre>';
						var_dump($column_name);
						var_dump($info);
						echo '</pre>';
						*/
					
						if($info['datatype'] == 'array') {
								$tmp_sub_array = array();
								
								do {
										$tmp = $this->processIndexArray($info['number_of_items'], $row, $col_index, $level);
										
										if($tmp !== false) {
												$tmp_sub_array[] = $tmp;
										}
								} while($tmp != false);
								
								if(count($tmp_sub_array) == 0) {
										header('Location: index.php?s=cAdminproductimport&error=' . urlencode('Fehler beim Auslesen eines Subarrays. Der Index in Spalte ' . ($col_index + 1) . ' entspricht nicht dem erwarteten Wert "' . $column_name . '"- Text in Datei ist stattdessen "' . $row[$col_index] . '"'));
										die;
								}
								
								$indexes[$column_name] = $tmp_sub_array;
								continue;
						} else {
								$column_name = utf8_decode(str_replace('%', '', $column_name));
								
								//var_dump($column_name);
								
								if(!isset($row[$col_index])) {
										if($level > 1) {
												return false;
										}
										
										header('Location: index.php?s=cAdminproductimport&success=' . urlencode('Es sind weniger Spalten als erwartete Indexe in der Datei vorhanden. Aktueller Spaltenzähler ist ' . $col_index));
										die;
								}
								
								if(0 !== strpos($row[$col_index], $column_name)) {
										if($level > 1) {
												if($loop_count == 0) {
														return false;
												} else {
														//var_dump($row[$col_index]);
														//var_dump(utf8_decode($column_name));
														
														header('Location: index.php?s=cAdminproductimport&error=' . urlencode('Der Index in Spalte ' . ($col_index + 1) . ' entspricht nicht dem erwarteten Wert "' . $column_name . '"- Text in Datei ist stattdessen "' . $row[$col_index] . '"'));
														die;
												}
										} else {
												header('Location: index.php?s=cAdminproductimport&error=' . urlencode('Der Index in Spalte ' . ($col_index + 1) . ' entspricht nicht dem erwarteten Wert "' . $column_name . '"- Text in Datei ist stattdessen "' . $row[$col_index] . '"'));
												die;
										}
								}
								
								$info['col_index'] = $col_index;
								$indexes[$column_name] = $info;
						}
						
						$loop_count++;
						$col_index++;
				}
				
				return $indexes;
		}
		
		///////////////////////////////////////////////////////////////////
		// Check if the file was uploaded correctly.
		///////////////////////////////////////////////////////////////////
		private function checkFileUploadState() {
				if(count($_FILES) == 0) {
						header('Location: index.php?s=cAdminproductimport&error=' . urlencode('Die Datei konnte nicht importiert werden. Es wurde keine Datei übergeben, oder es ist ein Fehler beim Hochladen der Datei aufgetreten.'));
						die;
				}
				
				if(!isset($_FILES['file'])) {
						header('Location: index.php?s=cAdminproductimport&error=' . urlencode('Die Datei konnte nicht importiert werden. $_FILES[\'file\'] ist nicht gesetzt.'));
						die;
				}
				
				if(!isset($_FILES['file']['error']) || !isset($_FILES['file']['tmp_name'])) {
						header('Location: index.php?s=cAdminproductimport&error=' . urlencode('Die Datei konnte nicht importiert werden. [\'error\'] oder [\'tmp_name\'] sind nicht gesetzt.'));
						die;
				}
				
				if($_FILES['file']['error'] != 0) {
						header('Location: index.php?s=cAdminproductimport&error=' . urlencode('Die Datei konnte nicht importiert werden. Es wurde keine Datei übergeben, oder es ist ein Fehler beim Hochladen der Datei aufgetreten. Fehlercode aus FILE-Array: ' . $_FILES['file']['error']));
						die;
				}
				
				if(!file_exists($_FILES['file']['tmp_name'])) {
						header('Location: index.php?s=cAdminproductimport&error=' . urlencode('Die Datei konnte nicht importiert werden. Die hochgeladene Datei existiert nicht (geprüft mit file_exists).'));
						die;
				}
				
				//try to open the file
				$fp = fopen($_FILES['file']['tmp_name'], 'r');
				if(false === $fp) {
						header('Location: index.php?s=cAdminproductimport&error=' . urlencode('Die hochgeladene Datei konnte nicht zum Lesen geöffnet werden. Sind die Dateirechte falsch eingestellt?'));
						die;
				}
				fclose($fp);
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page content.
		///////////////////////////////////////////////////////////////////
		public function content() {
				switch($this->action) {
						default:
								$this->drawDialog();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the list view.
		///////////////////////////////////////////////////////////////////
		function drawDialog() {
				//Collect messages..
				$info_message = core()->getGetVar('info_message');
				$error = core()->getGetVar('error');
				$success = core()->getGetVar('success');
				
				if(NULL !== $info_message) {
						$this->info_messages[] = $info_message;
				}
				
				if(NULL !== $error) {
						$this->errors[] = $error;
				}
				
				if(NULL !== $success) {
						$this->success_messages[] = $success;
				}
				
				//Render page..
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('ADMINRIGHT_DELETE_ACCOUNT', cAccount::adminrightCheck('cAdminaccounts', 'DELETE_ACCOUNT', (int)$_SESSION['user_id']));				
				$renderer->assign('INFO_MESSAGES', $this->info_messages);
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('SUCCESS_MESSAGES', $this->success_messages);
				$renderer->render('site/adminproductimport/dialog.html');
		}
}
?>