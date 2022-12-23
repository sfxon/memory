<?php

define('PAGE_MAX', 50);

class cAdminproducts extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINPRODUCTS;
		var $navbar_id = 0;
		var $errors = array();
		var $info_messages = array();
		var $success_messages = array();
		
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
				if(false === cAccount::adminrightCheck('cAdminproducts', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=79');
						die;
				}
				
				//We use the Admin module for output.
				cAdmin::setSmallBodyExecutionalHooks();	
				
				//Now set our own hooks below the CMS hooks.
				$core = core();
				core()->setHook('cCore|process', 'process');
				core()->setHook('cRenderer|content', 'content');
				core()->setHook('cRenderer|footer', 'footer');
		}
		
				///////////////////////////////////////////////////////////////////
		// processData
		///////////////////////////////////////////////////////////////////
		function process() {
				$this->data['products'] = array();				
				$this->paths = array();
				
				$this->action = core()->getGetVar('action');
				$cAdmin = core()->getInstance('cAdmin');
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINPRODUCTS, 'index.php?s=cAdminproducts');
				
				$this->datalanguages = cDatalanguages::loadActivated();
				$this->current_datalanguage = 1;		//TODO: Change this later..
				$this->default_datalanguage = 1;		//TODO: Change this later..
				
				switch($this->action) {
						//Eigenschaften
						case 'ajax_products_feature_set_delete_flag':
								$this->ajaxProductsFeatureSetDeleteFlag();
								break;
						case 'ajax_products_feature_save':
								$this->ajaxProductsFeatureSave();
								break;
						case 'ajax_products_featuresets_values_add':
								$this->ajaxProductsFeaturesetsValuesAdd();
								break;
						case 'ajax_products_featuresets_values_load':
								$this->ajaxProductsFeaturesetsValuesLoad();
								break;
						case 'ajax_product_featureset_save':
								$this->ajaxProductFeaturesetSave();
								break;
						//Attribute
						case 'ajax_products_attribute_set_delete_flag':
								$this->ajaxProductsAttributeSetDeleteFlag();
								break;
						case 'ajax_products_attribute_save':
								$this->ajaxProductsAttributeSave();
								break;
						case 'ajax_products_options_values_add':
								$this->ajaxProductsOptionsValuesAdd();
								break;
						case 'ajax_products_options_values_load':
								$this->ajaxProductsOptionsValuesLoad();
								break;
						case 'ajax_product_option_save':
								$this->ajaxProductOptionSave();
								break;
						//Bilder
						case 'ajax_delete_image':
								$this->ajaxDeleteImage();
								break;
						case 'ajax_upload_image':
								$this->ajaxUploadImage();
								break;
						case 'ajax_delete_file':
								$this->ajaxDeleteFile();
								break;
						case 'ajax_upload_file':
								$this->ajaxUploadFile();
								break;
						case 'ajax_buying_price_save_tmp_delete':
								$this->ajaxBuyingPriceSaveTmpDelete();
								break;
						case 'ajax_buying_price_save_tmp':
								$this->ajaxBuyingPriceSaveTmp();
								break;
						case 'ajax_price_save_tmp_delete':
								$this->ajaxPriceSaveTmpDelete();
								break;
						case 'ajax_price_save_tmp':
								$this->ajaxPriceSaveTmp();
								break;
						case 'ajax_remove_products_category_temp':
								$this->ajaxRemoveProductsCategoryTemp();
								break;					
						case 'ajax_add_products_category_temp':
								$this->ajaxAddProductsCategoryTemp();
								break;
								
						case 'edit':
								$this->initData();
								$this->initProductsEditorData();
								$this->editorLoadData();
								$this->getContent();
								$cAdmin->appendBreadcrumb(TEXT_ADMINPRODUCTS_EDIT, '');
								$this->navbar_title = TEXT_ADMINPRODUCTS_EDIT;
								break;
								
						case 'update':
								$this->initData();
								$this->update();
								$cAdmin->appendBreadcrumb(TEXT_ADMINPRODUCTS_EDIT, '');
								$this->navbar_title = TEXT_ADMINPRODUCTS_EDIT;
								break;
								
						case 'create':
								$this->create();
								$cAdmin->appendBreadcrumb(TEXT_ADMINPRODUCTS_NEW, '');
								$this->navbar_title = TEXT_ADMINPRODUCTS_NEW;
								break;
								
						case 'new':
								$this->initData();
								$this->initProductsEditorData();
								$cAdmin->appendBreadcrumb(TEXT_ADMINPRODUCTS_NEW, '');
								$this->navbar_title = TEXT_ADMINPRODUCTS_NEW;
								break;
								
						case 'search':
								$this->search();
								break;
								
						default:
								/*$this->getChannels();
								$this->getActiveChannel();
								$this->getActiveCategory();
								$this->getCategories();*/
								$this->getList();
								break;
				}
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Features
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////
		// ajaxProductsFeatureSetDeleteFlag
		///////////////////////////////////////////////////////////////////
		private function ajaxProductsFeatureSetDeleteFlag() {
				$retval = array();
				$this->datalanguages = cDatalanguages::loadActivated();
				
				$products_id = (int)core()->getGetVar('products_id');
				$tmp_products_id = core()->getGetVar('tmp_products_id');
				
				$tmp_products_featuresets_id = core()->getPostVar('tmp_products_featuresets_id');
				$tmp_products_featuresets_value_id = core()->getPostVar('tmp_products_featuresets_value_id');
				$products_featuresets_id = (int)core()->getPostVar('products_featuresets_id');
				$products_featuresets_values_id = (int)core()->getPostVar('products_featuresets_values_id');
				
				$tmp_features_id = core()->getPostVar('tmp_features_id');
				$featuers_id = (int)core()->getPostVar('features_id');
				
				//if the tmp_features_id is not set - create one..
				if(empty($tmp_features_id)) {
						$tmp_features_id = uniqid('', true);		//we need this for editing..
				}

				$features_sort_order = 0;
				$description = '';
				
				//if the features_id is empty - check if an feature for this product with this featuresets_value already exist - and then use that one..
				//(this is for the features editor convenience/lazyness
				if(empty($features_id) && !empty($products_featuresets_values_id) && !empty($products_id)) {
						$check_features_id = cProductfeatures::checkExistenceByFeaturesetsValuesId($products_id, $products_featuresets_values_id);
	
						if(false !== $check_features_id) {
								$features_id = $check_features_id;
						}
				}
				
				//check if a temp entry exists..
				//if one exists -> update!
				if(cTmpproductfeatures::checkTmpFeaturesExistenceButCurrentTmpFeature($tmp_products_id, $products_featuresets_id, $tmp_products_featuresets_id, $products_featuresets_values_id, $tmp_products_featuresets_value_id, $tmp_features_id)) {
						cTmpproductfeatures::update(
								$tmp_products_id, $tmp_products_featuresets_id, $tmp_products_featuresets_value_id, $products_featuresets_id, $products_featuresets_values_id,
								$features_sort_order, $description, $tmp_features_id, $features_id, 1
						);
				//if none exists -> insert
				} else {
						cTmpproductfeatures::create(
								$tmp_products_id, $tmp_products_featuresets_id, $tmp_products_featuresets_value_id, $products_featuresets_id, $products_featuresets_values_id,
								$features_sort_order, $description, $tmp_features_id, $features_id, 1);
				}
				
				//load the products features
				$products_features = cProductfeatures::loadProductsFeatures($products_id, $this->default_datalanguage);
				
				//load the products tmp features
				$tmp_products_features = cTmpproductfeatures::load($tmp_products_id);
				
				//merge products features and tmp products features
				$products_features = cTmpproductfeatures::mergeFeaturesWithTmp($products_features, $tmp_products_features);
				$products_features = cProductfeatures::loadFeaturesetsTitles($products_features, $this->default_datalanguage, $tmp_products_id);
				$products_features = cProductfeatures::loadFeatuersetsValuesTitles($products_features, $this->default_datalanguage, $tmp_products_id);
				
				//build the table output
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('CURRENT_DATALANGUAGE_ID', $this->current_datalanguage);
				$renderer->assign('DATALANGUAGES', $this->datalanguages);
				$renderer->assign('PRODUCTS_FEATURES', $products_features);
				$html_table = $renderer->fetch('site/adminproducts/products_features_table.html');
	
				$retval = array(
										'status' => 'success',
										'html_table' => $html_table
				);
				
				$retval = json_encode($retval);
				echo $retval;
				die;
		}
		
		//////////////////////////////////////////////////////////////////
		// ajaxProductsFeatureSave
		///////////////////////////////////////////////////////////////////
		private function ajaxProductsFeatureSave() {
				$retval = array();
				$this->datalanguages = cDatalanguages::loadActivated();
				
				$products_id = (int)core()->getGetVar('products_id');
				$tmp_products_id = core()->getGetVar('tmp_products_id');

				$tmp_products_featuresets_id = core()->getPostVar('tmp_products_featuresets_id');
				$tmp_products_featuresets_value_id = core()->getPostVar('tmp_products_featuresets_value_id');
				$products_featuresets_id = (int)core()->getPostVar('products_featuresets_id');
				$products_featuresets_values_id = (int)core()->getPostVar('products_featuresets_values_id');

				$features_sort_order = (int)core()->getPostVar('sort_order');
				$features_description = core()->getPostVar('description');
				
				$tmp_features_id = core()->getPostVar('tmp_features_id');
				$features_id = (int)core()->getPostVar('features_id');
				
				//if the tmp_features_id is not set - create one..
				if(empty($tmp_features_id)) {
						$tmp_features_id = uniqid('', true);		//we need this for editing..
				}
				
				//if the features_id is empty - check if an feature for this product with this featuresets_value already exist - and then use that one..
				//(this is for the features editor convenience/lazyness
				if(empty($features_id) && !empty($products_featuresets_values_id) && !empty($products_id)) {
						$check_features_id = cProductfeatures::checkExistenceByFeaturesetsValuesId($products_id, $products_featuresets_values_id);
	
						if(false !== $check_features_id) {
								$features_id = $check_features_id;
						}
				}
				
				//check if a temp entry exists..
				//if one exists -> update!
				if(cTmpproductfeatures::checkTmpFeaturesExistenceButCurrentTmpFeature($tmp_products_id, $products_featuresets_id, $tmp_products_featuresets_id, $products_featuresets_values_id, $tmp_products_featuresets_value_id, $tmp_features_id)) {
						cTmpproductfeatures::update(
								$tmp_products_id, $tmp_products_featuresets_id, $tmp_products_featuresets_value_id, $products_featuresets_id, $products_featuresets_values_id,
								$features_sort_order, $features_description, $tmp_features_id, $features_id
						);
				//if none exists -> insert
				} else {
						cTmpproductfeatures::create(
								$tmp_products_id, $tmp_products_featuresets_id, $tmp_products_featuresets_value_id, $products_featuresets_id, $products_featuresets_values_id,
								$features_sort_order, $features_description, $tmp_features_id, $features_id);
				}
				
				//load the products features
				$products_features = cProductfeatures::loadProductsFeatures($products_id, $this->default_datalanguage);
				
	
				//load the products tmp features
				$tmp_products_features = cTmpproductfeatures::load($tmp_products_id);
				
				//merge products features and tmp products features
				$products_features = cTmpproductfeatures::mergeFeaturesWithTmp($products_features, $tmp_products_features);
				$products_features = cProductfeatures::loadFeaturesetsTitles($products_features, $this->default_datalanguage, $tmp_products_id);
				$products_features = cProductfeatures::loadFeaturesetsValuesTitles($products_features, $this->default_datalanguage, $tmp_products_id);
				
				//build the table output
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('CURRENT_DATALANGUAGE_ID', $this->current_datalanguage);
				$renderer->assign('DATALANGUAGES', $this->datalanguages);
				$renderer->assign('PRODUCTS_FEATURES', $products_features);
				$html_table = $renderer->fetch('site/adminproducts/products_features_table.html');
	
				$retval = array(
										'status' => 'success',
										'html_table' => $html_table
				);
				
				$retval = json_encode($retval);
				echo $retval;
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// try to add a new products_featuresets_value!
		///////////////////////////////////////////////////////////////////
		private function ajaxProductsFeaturesetsValuesAdd() {
				$retval = array();
				$this->datalanguages = cDatalanguages::loadActivated();
				
				$tmp_products_id = core()->getPostVar('tmp_products_id');
				$products_featuresets_id = (int)core()->getPostVar('products_featuresets_id');
				$tmp_products_featuresets_id = core()->getPostVar('tmp_products_featuresets_id');
				$products_featuresets_values_id = (int)core()->getPostVar('products_featuresets_values_id');
				$tmp_products_featuresets_values_id = core()->getPostVar('tmp_products_featuresets_values_id');
				$sort_order = (int)core()->getPostVar('sort_order');
				$titles = array();
				$errorstring = '';
		
				foreach($this->datalanguages as $tmplang) {
						$titles[$tmplang['id']] = core()->getPostVar('products_featuresets_values_title_' . $tmplang['id']);
				}
				
				//Die erste Bedingung dieser dreifachen if-else abfrage tritt ein,
				//wenn weder ein temporärer noch ein live-daten eintrag für die product featureset values existiert.
				//Es wird ein Eintrag in die temporäre Tabelle hinzugefügt.
				//Dieser wird gespeichert, wenn man im Haupteditor auf Speichern klickt.
				if(empty($tmp_products_featuresets_values_id) && empty($products_featuresets_values_id)) {
						$tmp_products_featuresets_values_id = uniqid('', true);
						
						$errorstring = cProductfeaturesetsvalues::checkExistence($tmp_products_id, $products_featuresets_id, $tmp_products_featuresets_id, $titles);
		
						if(!empty($errorstring)) {
								$retval = array(
												'status' => 'error',
												'message' => $errorstring
								);
						} else {
								cTmpproductfeaturesetsvalues::add($tmp_products_featuresets_values_id, $tmp_products_id, $products_featuresets_id, $tmp_products_featuresets_id, $products_featuresets_values_id, $titles, $sort_order);
						}
				
				//In diesem Fall ist ein temporärer Eintrag hinterlegt.
				//Das bedeutet es wurde ein Eintrag aus der temporären Tabelle zum Bearbeiten ausgewählt.
				//Dieser kann auch einen Live Eintrag beinhalten, der bereits zum Bearbeiten ausgewählt wurde.
				} else if(!empty($tmp_products_featuresets_values_id)) {
						$errorstring = cProductfeaturesetsvalues::checkExistenceButCurrentOne($tmp_products_featuresets_values_id, $tmp_products_id, $products_featuresets_id, $tmp_products_featuresets_id, $products_featuresets_values_id, $titles);
						
						if(!empty($errorstring)) {
								$retval = array(
												'status' => 'error',
												'message' => $errorstring
								);
						} else {
								cTmpproductfeaturesetsvalues::update($tmp_products_featuresets_values_id, $tmp_products_id, $products_featuresets_id, $tmp_products_featuresets_id, $products_featuresets_values_id, $titles, $sort_order);
						}
						
				//In diesem Fall ist die temporäre products_featuresets_values_id leer- das bedeutet, ein Eintrag aus der Live-Datenbank wird bearbeitet,
				//der noch nicht in Bearbeitung ist! Es wird für diesen also ein temporärer Eintrag hinzugefügt,
				//der im Falle einer Speicherung anschließend den live eintrag mit den neuen Einstellungen versieht.
				} else {
						$tmp_products_featuresets_values_id = uniqid('', true);		//we need this for editing..
						
						$errorstring = cProductfeaturesetsvalues::checkExistenceButCurrentOne($tmp_products_featuresets_values_id, $tmp_products_id, $products_featuresets_id, $tmp_products_featuresets_id, $products_featureset_values_id, $titles);
						
						if(!empty($errorstring)) {
								$retval = array(
												'status' => 'error',
												'message' => $errorstring
								);
						} else {
								cTmpproductfeaturesetsvalues::add($tmp_products_featuresets_values_id, $tmp_products_id, $products_featuresets_id, $tmp_products_featuresets_id, $products_featuresets_values_id, $titles, $sort_order);
						}
				}
				
				//Wenn in der Verarbeitung kein Fehler aufgetreten ist, Tabellenwerte neu abrufen, da neue Einträge existieren
				if(empty($errorstring)) {
						//load the products_featuresets_values
						$products_featuresets_values = cProductfeaturesetsvalues::loadProductFeaturesetsValues($products_featuresets_id, $this->default_datalanguage);
				
						//load the tmp_products_featuresets_values
						$tmp_products_featuresets_values = cTmpproductfeaturesetsvalues::load($tmp_products_id, $products_featuresets_id, $tmp_products_featuresets_id, $this->default_datalanguage);
						
						//merge the two arrays (sorted)
						$products_featuresets_values = cTmpproductFeaturesetsvalues::mergeProductsFeaturesetsValuesWithTmp($products_featuresets_values, $tmp_products_featuresets_values);
						
						//build the table output
						$renderer = core()->getInstance('cRenderer');
						$renderer->setTemplate($this->template);
						$renderer->assign('DATA', $this->data);
						$renderer->assign('CURRENT_DATALANGUAGE_ID', $this->current_datalanguage);
						$renderer->assign('DATALANGUAGES', $this->datalanguages);
						$renderer->assign('PRODUCTS_FEATURESETS_VALUES', $products_featuresets_values);
						$html_table = $renderer->fetch('site/adminproducts/products_featuresets_values_table.html');
						
						$retval = array(
												'status' => 'success',
												'tmp_products_featuresets_values_id' => $tmp_products_featuresets_values_id,
												'html_table' => $html_table
						);
				}
		
				$retval = json_encode($retval);
				echo $retval;
				die;
		}
			
		///////////////////////////////////////////////////////////////////
		// Load products featuresets values by an ajax call:
		// returns an html table as output.
		///////////////////////////////////////////////////////////////////
		private function ajaxProductsFeaturesetsValuesLoad() {
				$this->datalanguages = cDatalanguages::loadActivated();
				
				$tmp_products_id = core()->getGetVar('tmp_products_id');
				$products_featuresets_id = (int)core()->getPostVar('products_featuresets_id');
				$tmp_products_featuresets_id = core()->getPostVar('tmp_products_featuresets_id');
				
				//Load the products_featuresets_values.
				$products_featuresets_values = cProductfeaturesetsvalues::loadProductFeaturesetsValues($products_featuresets_id, $this->default_datalanguage);
		
				//Load the tmp_products_featuresets_values.
				$tmp_products_featuresets_values = cTmpproductfeaturesetsvalues::load($tmp_products_id, $products_featuresets_id, $tmp_products_featuresets_id, $this->default_datalanguage);
				
				//Merge the two arrays (sorted).
				$products_featuresets_values = cTmpproductfeaturesetsvalues::mergeProductsFeaturesetsValuesWithTmp($products_featuresets_values, $tmp_products_featuresets_values);
				
				//Build the table output.
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('CURRENT_DATALANGUAGE_ID', $this->current_datalanguage);
				$renderer->assign('DATALANGUAGES', $this->datalanguages);
				$renderer->assign('PRODUCTS_FEATURESETS_VALUES', $products_featuresets_values);
				$html_table = $renderer->fetch('site/adminproducts/products_featuresets_values_table.html');
				
				echo $html_table;
				die;
		}
			
		///////////////////////////////////////////////////////////////////
		// temporary save the change of a product featureset
		///////////////////////////////////////////////////////////////////
		private function ajaxProductFeaturesetSave() {
				$this->datalanguages = cDatalanguages::loadActivated();
				$tmp_products_id = core()->getGetVar('tmp_products_id');
				$sort_order = (int)core()->getGetVar('sort_order');
				$products_featuresets_id = (int)core()->getGetVar('products_featuresets_id');
				$tmp_products_featuresets_id = core()->getGetVar('tmp_products_featuresets_id');
				
				if(empty($tmp_products_featuresets_id)) {
						$tmp_products_featuresets_id = uniqid('', true);
				}
				
				//get all images alt tags as an array
				$titles = array();
				
				foreach($this->datalanguages as $tmp) {
						$titles[$tmp['id']] = core()->getGetVar('products_featureset_title_' . $tmp['id']);
				}
				
				cTmpproductfeaturesets::save($tmp_products_id, $tmp_products_featuresets_id, $products_featuresets_id, $sort_order);
				cTmpproductfeaturesets::saveDescription($tmp_products_id, $tmp_products_featuresets_id, $titles);
				
				$retval = array(
											'tmp_products_featuresets_id' => $tmp_products_featuresets_id
												);
				$retval = json_encode($retval);
				
				echo $retval;
				die();
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Attribute
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////		
		
		///////////////////////////////////////////////////////////////////
		// ajaxProductsAttributeSetDeleteFlag
		///////////////////////////////////////////////////////////////////
		private function ajaxProductsAttributeSetDeleteFlag() {
				$retval = array();
				$this->datalanguages = cDatalanguages::loadActivated();
				
				$products_id = (int)core()->getGetVar('products_id');
				$tmp_products_id = core()->getGetVar('tmp_products_id');
				
				$tmp_products_options_id = core()->getPostVar('tmp_products_options_id');
				$tmp_products_options_value_id = core()->getPostVar('tmp_products_options_value_id');
				$products_options_id = (int)core()->getPostVar('products_options_id');
				$products_options_values_id = (int)core()->getPostVar('products_options_values_id');
				
				$attributes_model = core()->getPostVar('attributes_model');
				var_dump($attributes_model);
				die;
				
				$tmp_attributes_id = core()->getPostVar('tmp_attributes_id');
				$attributes_id = (int)core()->getPostVar('attributes_id');
				
				//if the tmp_attributes_id is not set - create one..
				if(empty($tmp_attributes_id)) {
						$tmp_attributes_id = uniqid('', true);		//we need this for editing..
				}

				$attributes_sort_order = 0;
				
				//if the attributes_id is empty - check if an attribute for this product with this options_value already exist - and then use that one..
				//(this is for the attributes editor convenience/lazyness
				if(empty($attributes_id) && !empty($products_attributes_values_id) && !empty($products_id)) {
						$check_attributes_id = cProductattributes::checkExistenceByOptionsValuesId($products_id, $products_options_values_id);
	
						if(false !== $check_attributes_id) {
								$attributes_id = $check_attributes_id;
						}
				}
				
				//check if a temp entry exists..
				//if one exists -> update!
				if(cTmpproductattributes::checkTmpAttributesExistenceButCurrentTmpAttribute($tmp_products_id, $attributes_model, $products_options_id, $tmp_products_options_id, $products_options_values_id, $tmp_products_options_value_id, $tmp_attributes_id)) {
						cTmpproductattributes::update(
								$tmp_products_id, $tmp_products_options_id, $tmp_products_options_value_id, $products_options_id, $products_options_values_id,
								$attributes_model, $attributes_sort_order, $tmp_attributes_id, $attributes_id, 1
						);
				//if none exists -> insert
				} else {
						cTmpproductattributes::create(
								$tmp_products_id, $tmp_products_options_id, $tmp_products_options_value_id, $products_options_id, $products_options_values_id,
								$attributes_model, $attributes_sort_order, $tmp_attributes_id, $attributes_id, 1);
				}
				
				//load the products attributes
				$products_attributes = cProductattributes::loadProductsAttributes($products_id, $this->default_datalanguage);
				
				//load the products tmp attributes
				$tmp_products_attributes = cTmpproductattributes::load($tmp_products_id);
				
				//merge products attributes and tmp products attributes
				$products_attributes = cTmpproductattributes::mergeAttributesWithTmp($products_attributes, $tmp_products_attributes);
				$products_attributes = cProductattributes::loadOptionsTitles($products_attributes, $this->default_datalanguage, $tmp_products_id);
				$products_attributes = cProductattributes::loadOptionsValuesTitles($products_attributes, $this->default_datalanguage, $tmp_products_id);
				
				//build the table output
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('CURRENT_DATALANGUAGE_ID', $this->current_datalanguage);
				$renderer->assign('DATALANGUAGES', $this->datalanguages);
				$renderer->assign('PRODUCTS_ATTRIBUTES', $products_attributes);
				$html_table = $renderer->fetch('site/adminproducts/products_attributes_table.html');
	
				$retval = array(
										'status' => 'success',
										'html_table' => $html_table
				);
				
				$retval = json_encode($retval);
				echo $retval;
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// ajaxProductsAttributeSave
		///////////////////////////////////////////////////////////////////
		private function ajaxProductsAttributeSave() {
				$retval = array();
				$this->datalanguages = cDatalanguages::loadActivated();
				
				$products_id = (int)core()->getGetVar('products_id');
				$tmp_products_id = core()->getGetVar('tmp_products_id');

				$tmp_products_options_id = core()->getPostVar('tmp_products_options_id');
				$tmp_products_options_value_id = core()->getPostVar('tmp_products_options_value_id');
				$products_options_id = (int)core()->getPostVar('products_options_id');
				$products_options_values_id = (int)core()->getPostVar('products_options_values_id');

				$attributes_model = core()->getPostVar('attributes_model');
				$attributes_sort_order = (int)core()->getPostVar('sort_order');
				
				$tmp_attributes_id = core()->getPostVar('tmp_attributes_id');
				$attributes_id = (int)core()->getPostVar('attributes_id');
				
				//if the tmp_attributes_id is not set - create one..
				if(empty($tmp_attributes_id)) {
						$tmp_attributes_id = uniqid('', true);		//we need this for editing..
				}
				
				//if the attributes_id is empty - check if an attribute for this product with this options_value already exist - and then use that one..
				//(this is for the attributes editor convenience/lazyness
				if(empty($attributes_id) && !empty($products_options_values_id) && !empty($products_id)) {
						$check_attributes_id = cProductattributes::checkExistenceByOptionsValuesId($products_id, $products_options_values_id);
	
						if(false !== $check_attributes_id) {
								$attributes_id = $check_attributes_id;
						}
				}
				
				//check if a temp entry exists..
				//if one exists -> update!
				if(cTmpproductattributes::checkTmpAttributesExistenceButCurrentTmpAttribute($tmp_products_id, $attributes_model, $products_options_id, $tmp_products_options_id, $products_options_values_id, $tmp_products_options_value_id, $tmp_attributes_id)) {
						cTmpproductattributes::update(
								$tmp_products_id, $tmp_products_options_id, $tmp_products_options_value_id, $products_options_id, $products_options_values_id,
								$attributes_model, $attributes_sort_order, $tmp_attributes_id, $attributes_id
						);
				//if none exists -> insert
				} else {
						cTmpproductattributes::create(
								$tmp_products_id, $tmp_products_options_id, $tmp_products_options_value_id, $products_options_id, $products_options_values_id,
								$attributes_model, $attributes_sort_order, $tmp_attributes_id, $attributes_id);
				}
				
				//load the products attributes
				$products_attributes = cProductattributes::loadProductsAttributes($products_id, $this->default_datalanguage);
				
	
				//load the products tmp attributes
				$tmp_products_attributes = cTmpproductattributes::load($tmp_products_id);
				
				//merge products attributes and tmp products attributes
				$products_attributes = cTmpproductattributes::mergeAttributesWithTmp($products_attributes, $tmp_products_attributes);
				$products_attributes = cProductattributes::loadOptionsTitles($products_attributes, $this->default_datalanguage, $tmp_products_id);
				$products_attributes = cProductattributes::loadOptionsValuesTitles($products_attributes, $this->default_datalanguage, $tmp_products_id);
				
				//build the table output
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('CURRENT_DATALANGUAGE_ID', $this->current_datalanguage);
				$renderer->assign('DATALANGUAGES', $this->datalanguages);
				$renderer->assign('PRODUCTS_ATTRIBUTES', $products_attributes);
				$html_table = $renderer->fetch('site/adminproducts/products_attributes_table.html');
	
				$retval = array(
										'status' => 'success',
										'html_table' => $html_table
				);
				
				$retval = json_encode($retval);
				echo $retval;
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// try to add a new products_options_value!
		///////////////////////////////////////////////////////////////////
		private function ajaxProductsOptionsValuesAdd() {
				$retval = array();
				$this->datalanguages = cDatalanguages::loadActivated();
				
				$tmp_products_id = core()->getPostVar('tmp_products_id');
				$products_options_id = (int)core()->getPostVar('products_options_id');
				$tmp_products_options_id = core()->getPostVar('tmp_products_options_id');
				$products_options_values_id = (int)core()->getPostVar('products_options_values_id');
				$tmp_products_options_values_id = core()->getPostVar('tmp_products_options_values_id');
				$sort_order = (int)core()->getPostVar('sort_order');
				$titles = array();
				$errorstring = '';
		
				foreach($this->datalanguages as $tmplang) {
						$titles[$tmplang['id']] = core()->getPostVar('products_options_values_title_' . $tmplang['id']);
				}
				
				//Die erste Bedingung dieser dreifachen if-else abfrage tritt ein,
				//wenn weder ein temporärer noch ein live-daten eintrag für die product optionen values existiert.
				//Es wird ein Eintrag in die temporäre Tabelle hinzugefügt.
				//Dieser wird gespeichert, wenn man im Haupteditor auf Speichern klickt.
				if(empty($tmp_products_options_values_id) && empty($products_options_values_id)) {
						$tmp_products_options_values_id = uniqid('', true);
						
						$errorstring = cProductoptionsvalues::checkExistence($tmp_products_id, $products_options_id, $tmp_products_options_id, $titles);
		
						if(!empty($errorstring)) {
								$retval = array(
												'status' => 'error',
												'message' => $errorstring
								);
						} else {
								cTmpproductoptionsvalues::add($tmp_products_options_values_id, $tmp_products_id, $products_options_id, $tmp_products_options_id, $products_options_values_id, $titles, $sort_order);
						}
				
				//In diesem Fall ist ein temporärer Eintrag hinterlegt.
				//Das bedeutet es wurde ein Eintrag aus der temporären Tabelle zum Bearbeiten ausgewählt.
				//Dieser kann auch einen Live Eintrag beinhalten, der bereits zum Bearbeiten ausgewählt wurde.
				} else if(!empty($tmp_products_options_values_id)) {
						$errorstring = cProductoptionsvalues::checkExistenceButCurrentOne($tmp_products_options_values_id, $tmp_products_id, $products_options_id, $tmp_products_options_id, $products_options_values_id, $titles);
						
						if(!empty($errorstring)) {
								$retval = array(
												'status' => 'error',
												'message' => $errorstring
								);
						} else {
								cTmpproductoptionsvalues::update($tmp_products_options_values_id, $tmp_products_id, $products_options_id, $tmp_products_options_id, $products_options_values_id, $titles, $sort_order);
						}
						
				//In diesem Fall ist die temporäre products_options_values_id leer- das bedeutet, ein Eintrag aus der Live-Datenbank wird bearbeitet,
				//der noch nicht in Bearbeitung ist! Es wird für diesen also ein temporärer Eintrag hinzugefügt,
				//der im Falle einer Speicherung anschließend den live eintrag mit den neuen Einstellungen versieht.
				} else {
						$tmp_products_options_values_id = uniqid('', true);		//we need this for editing..
						
						$errorstring = cProductoptionsvalues::checkExistenceButCurrentOne($tmp_products_options_values_id, $tmp_products_id, $products_options_id, $tmp_products_options_id, $products_options_values_id, $titles);
						
						if(!empty($errorstring)) {
								$retval = array(
												'status' => 'error',
												'message' => $errorstring
								);
						} else {
								cTmpproductoptionsvalues::add($tmp_products_options_values_id, $tmp_products_id, $products_options_id, $tmp_products_options_id, $products_options_values_id, $titles, $sort_order);
						}
				}
				
				//Wenn in der Verarbeitung kein Fehler aufgetreten ist, Tabellenwerte neu abrufen, da neue Einträge existieren
				if(empty($errorstring)) {
						//load the products_options_values
						$products_options_values = cProductoptionsvalues::loadProductOptionsValues($products_options_id, $this->default_datalanguage);
				
						//load the tmp_products_options_values
						$tmp_products_options_values = cTmpproductoptionsvalues::load($tmp_products_id, $products_options_id, $tmp_products_options_id, $this->default_datalanguage);
						
						//merge the two arrays (sorted)
						$products_options_values = cTmpproductoptionsvalues::mergeProductsOptionsValuesWithTmp($products_options_values, $tmp_products_options_values);
						
						//build the table output
						$renderer = core()->getInstance('cRenderer');
						$renderer->setTemplate($this->template);
						$renderer->assign('DATA', $this->data);
						$renderer->assign('CURRENT_DATALANGUAGE_ID', $this->current_datalanguage);
						$renderer->assign('DATALANGUAGES', $this->datalanguages);
						$renderer->assign('PRODUCTS_OPTIONS_VALUES', $products_options_values);
						$html_table = $renderer->fetch('site/adminproducts/products_options_values_table.html');
						
						$retval = array(
												'status' => 'success',
												'tmp_products_options_values_id' => $tmp_products_options_values_id,
												'html_table' => $html_table
						);
				}
		
				$retval = json_encode($retval);
				echo $retval;
				die;
		}
			
		///////////////////////////////////////////////////////////////////
		// load products options values by an ajax call
		// -> returns a html table as output
		///////////////////////////////////////////////////////////////////
		private function ajaxProductsOptionsValuesLoad() {
				$this->datalanguages = cDatalanguages::loadActivated();
				
				$tmp_products_id = core()->getGetVar('tmp_products_id');
				$products_options_id = (int)core()->getPostVar('products_options_id');
				$tmp_products_options_id = core()->getPostVar('tmp_products_options_id');
				
				//load the products_options_values
				$products_options_values = cProductoptionsvalues::loadProductOptionsValues($products_options_id, $this->default_datalanguage);
		
				//load the tmp_products_options_values
				$tmp_products_options_values = cTmpproductoptionsvalues::load($tmp_products_id, $products_options_id, $tmp_products_options_id, $this->default_datalanguage);
				
				//merge the two arrays (sorted)
				$products_options_values = cTmpproductoptionsvalues::mergeProductsOptionsValuesWithTmp($products_options_values, $tmp_products_options_values);
				
				//build the table output
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('CURRENT_DATALANGUAGE_ID', $this->current_datalanguage);
				$renderer->assign('DATALANGUAGES', $this->datalanguages);
				$renderer->assign('PRODUCTS_OPTIONS_VALUES', $products_options_values);
				$html_table = $renderer->fetch('site/adminproducts/products_options_values_table.html');
				
				echo $html_table;
				die;
		}
			
		///////////////////////////////////////////////////////////////////
		// temporary save the change of a product option
		///////////////////////////////////////////////////////////////////
		private function ajaxProductOptionSave() {
				$this->datalanguages = cDatalanguages::loadActivated();
				$tmp_products_id = core()->getGetVar('tmp_products_id');
				$sort_order = (int)core()->getGetVar('sort_order');
				$products_options_id = (int)core()->getGetVar('products_options_id');
				$tmp_products_options_id = core()->getGetVar('tmp_products_options_id');
				
				if(empty($tmp_products_options_id)) {
						$tmp_products_options_id = uniqid('', true);
				}
				
				//get all images alt tags as an array
				$titles = array();
				
				foreach($this->datalanguages as $tmp) {
						$titles[$tmp['id']] = core()->getGetVar('products_option_title_' . $tmp['id']);
				}
				
				cTmpproductoptions::save($tmp_products_id, $tmp_products_options_id, $products_options_id, $sort_order);
				cTmpproductoptions::saveDescription($tmp_products_id, $tmp_products_options_id, $titles);
				
				$retval = array(
											'tmp_products_options_id' => $tmp_products_options_id
												);
				$retval = json_encode($retval);
				
				echo $retval;
				die();
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Files and images.
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		///////////////////////////////////////////////////////////////////
		// delete a file via an ajax request (temporary..
		// real delete action is done in submit function for product)
		///////////////////////////////////////////////////////////////////
		private function ajaxDeleteFile() {
				$tmp_products_id = core()->getGetVar('tmp_products_id');
				$tmp_products_file = core()->getGetVar('tmp_products_file');
				$documents_id = (int)core()->getGetVar('documents_id');
				
				cTmpproductfiles::delete($tmp_products_id, $tmp_products_file, $documents_id);
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// upload a file via an ajax request
		///////////////////////////////////////////////////////////////////
		private function ajaxUploadFile() {
				$this->datalanguages = cDatalanguages::loadActivated();
				
				$tmp_products_id = core()->getGetVar('tmp_products_id');
				$uuid = uniqid('file', true);
				
				//get all file alt tags as an array
				$titles = array();
				
				foreach($this->datalanguages as $tmp) {
						$titles[$tmp['id']] = core()->getPostVar('products_file_title_' . $tmp['id']);
				}
				
				//get all file title tags as an array
				$comments = array();
				
				foreach($this->datalanguages as $tmp) {
						$comments[$tmp['id']] = core()->getPostVar('products_file_comment_' . $tmp['id']);
				}
				
				//get all file external links as an array
				$external_links = array();
				
				foreach($this->datalanguages as $tmp) {
						$external_links[$tmp['id']] = core()->getPostVar('products_file_external_link_' . $tmp['id']);
				}
	
				//get the file data			
				$destination_filename = $tmp_products_id . $uuid;
				$destination_filename = str_replace('.', '', $destination_filename);		//remove dots - so there is no chance it results in .php (chance is small- but possible attack with flooding.. :\)
				$destination_path = 'data/tmp/tmpuploads/';
	
				//upload the file
				$result = cProductfiles::uploadFile('0', $destination_path, $destination_filename);
	
				//set paths for output..
				$result['destination_path'] = $destination_path;
				$result['server'] = '';
	
				//get additional values from post
				$sort_order = (int)core()->getPostVar('sort_order');
				$file_source = core()->getPostVar('file_source');
				$license_type = core()->getPostVar('file_license_type');
				$qualifier = core()->getPostVar('file_qualifier');
				$tmp_products_file = core()->getPostVar('tmp_products_file');
				$documents_id = (int)core()->getPostVar('documents_id');
				
				//if this is an update of an existing file, without upload of a new file.. set the data for it..
				if(isset($result['error']) && !empty($tmp_products_file)) {
						$result['original_filename'] = '';
						$result['file_extension'] = '';
						unset($result['error']);
				}
				
				//if this is an existing document and no new file has been submitted - we have to return some values...
				if(isset($result['error']) && !empty($documents_id)) {
						$result['original_filename'] = '';
						$result['file_extension'] = cDocument::getFileExtensionById($documents_id);
						$result['destination_path'] = 'data/files/product_files/';
						unset($result['error']);
				}
				
				//save the file data
				if(!isset($result['error']) || !empty($tmp_products_file) || !empty($documents_id)) {
						cTmpproductfiles::save($tmp_products_id, $tmp_products_file, $destination_filename  . $result['file_extension'], $result['original_filename'], $result['file_extension'], $titles, $comments, $external_links, $sort_order, $file_source, $license_type, $qualifier, $documents_id);
				}		
				
				$result = json_encode($result);
				echo $result;
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// delete an Image via an ajax request (temporary..
		// real delete action is done in submit function for product)
		///////////////////////////////////////////////////////////////////
		private function ajaxDeleteImage() {
				$tmp_products_id = core()->getGetVar('tmp_products_id');
				$tmp_products_image = core()->getGetVar('tmp_products_image');
				$documents_id = (int)core()->getGetVar('documents_id');
				
				cTmpproductimages::delete($tmp_products_id, $tmp_products_image, $documents_id);
				die;
		}
	
		///////////////////////////////////////////////////////////////////
		// upload an Image via an ajax request
		///////////////////////////////////////////////////////////////////
		private function ajaxUploadImage() {
				$this->datalanguages = cDatalanguages::loadActivated();
		
				$tmp_products_id = core()->getGetVar('tmp_products_id');
				$uuid = uniqid('img', true);
				
				//get all images alt tags as an array
				$alt_tags = array();
				
				foreach($this->datalanguages as $tmp) {
						$alt_tags[$tmp['id']] = core()->getPostVar('products_image_alt_tag_' . $tmp['id']);
				}
				
				//get all images title tags as an array
				$title_tags = array();
				
				foreach($this->datalanguages as $tmp) {
						$title_tags[$tmp['id']] = core()->getPostVar('products_image_title_tag_' . $tmp['id']);
				}
				
				//get the image data			
				$destination_filename = $tmp_products_id . $uuid;
				$destination_filename = str_replace('.', '', $destination_filename);		//remove dots - so there is no chance it results in .php (chance is small- but possible attack with flooding.. :\)
				$destination_path = 'data/tmp/tmpuploads/';
				
				//upload the image
				$result = cImage::upload('0', $destination_path, $destination_filename);
				
				//set paths for output..
				$result['destination_path'] = $destination_path;
				$result['server'] = '';
				
				//get additional values from post
				$sort_order = (int)core()->getPostVar('sort_order');
				$file_source = core()->getPostVar('image_source');
				$license_type = core()->getPostVar('image_license_type');
				$qualifier = core()->getPostVar('image_qualifier');
				$tmp_products_image = core()->getPostVar('tmp_products_image');
				$documents_id = (int)core()->getPostVar('documents_id');
				
				//if this is an update of an existing image, without upload of a new image.. set the data for it..
				if(isset($result['error']) && !empty($tmp_products_image)) {
						$result['original_filename'] = '';
						$result['file_extension'] = '';
						unset($result['error']);
				}
				
				//if this is an existing document image and no new image has been submitted - we have to return some values...
				if(isset($result['error']) && !empty($documents_id)) {
						$result['original_filename'] = '';
						$result['file_extension'] = cDocument::getFileExtensionById($documents_id);
						$result['destination_path'] = '/tmp/images/product_images/';
						unset($result['error']);
				}
				
				//save the image data
				if(!isset($result['error']) || !empty($tmp_products_image) || !empty($documents_id)) {
						cTmpproductimages::save(
								$tmp_products_id, 
								$tmp_products_image, 
								$destination_filename  . $result['file_extension'], 
								$result['original_filename'], 
								$result['file_extension'], 
								$alt_tags, 
								$title_tags, 
								$sort_order, 
								$file_source, 
								$license_type, 
								$qualifier, 
								$documents_id);
				}		
				
				$result = json_encode($result);
				echo $result;
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// delete temp products buying price
		///////////////////////////////////////////////////////////////////
		private function ajaxBuyingPriceSaveTmpDelete() {
				$buying_price_id = (int)core()->getGetVar('buying_price_id');
				$netto = core()->getGetVar('netto');
				$taxclass_id = (int)core()->getGetVar('taxclass_id');
				$suppliers_id = (int)core()->getGetVar('suppliers_id');
				$tmp_products_id = core()->getGetVar('tmp_products_id');
				$old_netto = core()->getGetVar('old_netto');
				$old_taxclass_id = (int)core()->getGetVar('old_taxclass_id');
				$old_suppliers_id = (int)core()->getGetVar('old_suppliers_id');
				$remove = 1;
				
				//save this entry temporary
				cTmpproductbuyingprices::save($buying_price_id, $netto, $taxclass_id, $suppliers_id, $tmp_products_id, $remove, $old_netto, $old_taxclass_id, $old_suppliers_id);
				
				echo 'done';
				die;
		}
	
		///////////////////////////////////////////////////////////////////
		// delete temp products price
		///////////////////////////////////////////////////////////////////
		private function ajaxPriceSaveTmpDelete() {
				$price_id = (int)core()->getGetVar('price_id');
				$channel_id = (int)core()->getGetVar('channel_id');
				$customergroups_id = (int)core()->getGetVar('customergroup_id');
				$netto = core()->getGetVar('netto');
				$taxclass_id = (int)core()->getGetVar('taxclass_id');
				$price_quantity = core()->getGetVar('price_quantity');
				$tmp_products_id = core()->getGetVar('tmp_products_id');
				$old_netto = core()->getGetVar('old_netto');
				$old_taxclass_id = (int)core()->getGetVar('old_taxclass_id');
				$old_price_quantity = core()->getGetVar('old_price_quantity');
				$remove = 1;
				
				//save this entry temporary
				cTmpproductprices::save($price_id, $channel_id, $customergroups_id, $netto, $taxclass_id, $price_quantity, $tmp_products_id, $remove, $old_netto, $old_taxclass_id, $old_price_quantity);
				
				echo 'done';
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// save temp products buying price
		///////////////////////////////////////////////////////////////////
		private function ajaxBuyingPriceSaveTmp() {
				$buying_price_id = (int)core()->getGetVar('buying_price_id');
				$netto = core()->getGetVar('netto');
				$taxclass_id = (int)core()->getGetVar('taxclass_id');
				$suppliers_id = (int)core()->getGetVar('suppliers_id');
				$tmp_products_id = core()->getGetVar('tmp_products_id');
				$old_netto = core()->getGetVar('old_netto');
				$old_taxclass_id = (int)core()->getGetVar('old_taxclass_id');
				$old_suppliers_id = (int)core()->getGetVar('old_suppliers_id');
				$remove = 0;
				
				//save this entry temporary
				$result = cTmpproductbuyingprices::save($buying_price_id, $netto, $taxclass_id, $suppliers_id, $tmp_products_id, $remove, $old_netto, $old_taxclass_id, $old_suppliers_id);
				
				echo 'done: ' . $result;
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// save temp products price
		///////////////////////////////////////////////////////////////////
		private function ajaxPriceSaveTmp() {
				$price_id = (int)core()->getGetVar('price_id');
				$channel_id = (int)core()->getGetVar('channel_id');
				$customergroups_id = (int)core()->getGetVar('customergroup_id');
				$netto = core()->getGetVar('netto');
				$taxclass_id = (int)core()->getGetVar('taxclass_id');
				$price_quantity = core()->getGetVar('price_quantity');
				$tmp_products_id = core()->getGetVar('tmp_products_id');
				$old_netto = core()->getGetVar('old_netto');
				$old_taxclass_id = (int)core()->getGetVar('old_taxclass_id');
				$old_price_quantity = core()->getGetVar('old_price_quantity');
				$remove = 0;
				
				//save this entry temporary
				$result = cTmpproductprices::save($price_id, $channel_id, $customergroups_id, $netto, $taxclass_id, $price_quantity, $tmp_products_id, $remove, $old_netto, $old_taxclass_id, $old_price_quantity);
				
				echo 'done: ' . $result;
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// remove temp products category
		///////////////////////////////////////////////////////////////////	
		private function ajaxRemoveProductsCategoryTemp() {
				$channel_id = (int)core()->getGetVar('channel_id');
				$categories_id = (int)core()->getGetVar('categories_id');
				$tmp_products_id = core()->getGetVar('tmp_products_id');
				$remove = 1;
				
				//save this entry temporary
				cTmpproductstocategories::save($categories_id, $tmp_products_id, $remove);
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// add temp products category
		///////////////////////////////////////////////////////////////////
		function ajaxAddProductsCategoryTemp() {
				$channel_id = (int)core()->getGetVar('channel_id');
				$categories_id = (int)core()->getGetVar('categories_id');
				$tmp_products_id = core()->getGetVar('tmp_products_id');
				$remove = 0;
	
				//save this entry temporary
				cTmpproductstocategories::save($categories_id, $tmp_products_id, $remove);
	
				//load some information about this category..
				$categories_data = cProductcategories::buildStringPlain($categories_id, $this->default_datalanguage);
	
				$retval = array(
						'status' => 'success',
						'categories_string' => $categories_data
				);
	
				$retval = json_encode($retval);
	
				echo $retval;			
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// initialisieren
		///////////////////////////////////////////////////////////////////
		private function initProductsEditorData() {
				$this->producttypes = cProducttypes::loadActive($this->default_datalanguage);
				$this->packagingunits = cPackagingunits::loadActive($this->default_datalanguage);
				$this->productconditions = cProductconditions::loadActive($this->default_datalanguage);
				$this->deliverystatus = cDeliverystatus::loadActive($this->default_datalanguage);
				$this->datalanguages = cDatalanguages::loadActivated();
				$this->channels = cChannel::loadActiveChannels();
				$this->suppliers = cAccount::loadSuppliers();				
				$this->taxclasses = cTaxclasses::loadActive($this->default_datalanguage);
				$this->buying_prices = array();
				$this->prices = array();
				$this->customergroups = cCustomergroups::loadActive();
				$this->products_images = array();
				$this->products_files = array();
				$this->products_channels_data = cProductchannels::loadPreferencesModel($this->channels, $this->datalanguages);
				$this->manufacturers = cAccount::loadManufacturers();
				$this->products_options = cProductoptions::loadOptions($this->default_datalanguage);
				$this->products_featuresets = cProductfeaturesets::loadFeaturesets($this->default_datalanguage);
				
				//init title texts..
				$titles = array();
				
				foreach($this->datalanguages as $lang) {
						foreach($this->channels as $channel) {
								for($i = 1; $i < SYSTEM_MAX_PRODUCT_TITLES; $i++) {
										$titles[$lang['id']][$channel['id']][$i] = '';
								}
						}
				}
				
				//init description texts
				$descriptions = array();
				
				foreach($this->datalanguages as $lang) {
						foreach($this->channels as $channel) {
								for($i = 1; $i < SYSTEM_MAX_PRODUCT_DESCRIPTIONS; $i++) {
										$descriptions[$lang['id']][$channel['id']][$i] = '';
								}
						}
				}
				
				//set if the channel languages are opened or closed initially
				$this->channel_languages_opened = array();
				
				foreach($this->datalanguages as $lang_index => $lang) {
						foreach($this->channels as $channel_index => $channel) {
								$opened = false;
								
								if($channel['id'] == 0) {
										$this->channel_languages_opened[$channel['id']][$lang['id']]['channel_opened'] = true;
										continue;
								}
								
								$this->channel_languages_opened[$channel['id']][$lang['id']]['channel_opened'] = false;
						}
				}
				
				//set the default products data
				$this->data = array(
						'url' => 'index.php?s=cAdminproducts&amp;action=create',
						'data' => array(
								'id' => '',
								'tmp_products_id' => cProducts::generateTmpProductsId($_SESSION['user_id'] . '.'),
								'products_type' => 0,
								'products_number' => '',
								'display_title' => TEXT_NEW_PRODUCT,
								'ean' => '',
								'vpe' => 0,
								'vpe_unit' => 0,
								'box_height' => 0,
								'box_width' => 0,
								'box_depth' => 0,
								'box_weight' => 0,
								'products_height' => 0,
								'products_width' => 0,
								'products_depth' => 0,
								'products_weight' => 0,
								'dimensional_weight' => 0,
								'products_condition' => 0,
								'virtual_article' => 0,
								'bulky_good' => 0,
								'delivery_status' => 0,
								'spedition' => 0,
								'titles' => $titles,
								'descriptions' => $descriptions,
								'products_categories' => false,
								'declaration_erroneous' => 0,
								'declaration_incomplete' => 0,
								'manufacturers_number' => '',
								'manufacturer' => '0',
								'products_stock' => 0
								
						)
				);
				
				$this->data['products_attributes'] = array();
				$this->data['products_features'] = array();
		}
		
		///////////////////////////////////////////////////////////////////
		// getList
		///////////////////////////////////////////////////////////////////
		private function getList() {
				$this->site = (int)core()->getGetVar('site');
	
				if(empty($this->site)) {
						$this->site = (int)core()->getPostVar('site');
				}
				if(empty($this->site)) {
						$this->site = 1;
				}
				
				$index = ($this->site - 1) * PAGE_MAX;
				$sort_fields = array(  array('title' => 'id' )  );
				
				$this->data['list'] = cProducts::loadListWithoutAttributeProducts($index, PAGE_MAX, $sort_fields);
						
				if(false === $this->data['list']) {
						$this->data['result_count'] = 0;
				} else {
						$this->data['result_count'] = count($this->data['list']);
				}
				
				$this->data['total_count'] = cProducts::getTotalCountWithoutAttributeProducts();
				$this->data['searchterm'] = '';
				$this->data['search_mode'] = 'all';
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////////
		// get pagination html
		/////////////////////////////////////////////////////////////////////////////////////////////////
		private function getPagination($module = 'cAdminproducts') {
				$this->data['link_page_first'] = '';
				$this->data['link_page_last'] = '';
				$this->data['link_page_back'] = '';
				$this->data['link_page_forward'] = '';
				$this->data['link_pages'] = array();
				$this->data['pages_total'] = 0;
				
				//build page links if there is any contact
				if($this->data['total_count'] > 0) {
						$this->data['pages_total'] = (int)ceil($this->data['total_count'] / PAGE_MAX);
						
						$page_separator_left = false;
						$page_separator_right = false;
						$pages_middle = array();
						$max_sites_to_display = 7;
						
						// previous button - grayed on first page
						if ($this->site > 1) {
							$this->data['link_page_back'] = 'index.php?s=' . $module . '&amp;site=' . ($this->site - 1);
						}
						
						//first page button
						if ($this->site > 1) {
								$this->data['link_pages'][] = array(
										'url' => 'index.php?s=' . $module . '&amp;site=1',
										'title' => 1,
										'status' => '',
										'info' => 'first_page_button_1'
								);
						} else if($this->site == 1) {
								$this->data['link_pages'][] = array(
											'url' => 'index.php?s=' . $module . '&amp;site=1',
											'title' => 1,
											'status' => 'active',
											'info' => 'first_page_button_2'
									);
						}
						
						if($this->site > 3 && $this->data['pages_total'] > $max_sites_to_display) {
								$this->data['link_pages'][] = array(
										'url' => '',
										'title' => '..',
										'status' => 'disabled',
										'info' => 'first_page_button_3'
								);
								
								$page_separator_left = true;
						}
					
						//the right button
						if($this->data['pages_total'] <= $max_sites_to_display) {
								$page_last_button = '';
						} else {
								if($this->site >= $this->data['pages_total'] - 3) {
										$page_last_button = '';
								} else {
										$page_separator_right = true;
								}
								
								if($this->site == $this->data['pages_total']) {
										$tmp_status = 'active';
								} else {
										$tmp_status = '';
								}
								
								$page_last_button = array(
										'url' => 'index.php?s=' . $module . '&amp;site=' . $this->data['pages_total'],
										'title' => $this->data['pages_total'],
										'status' => $tmp_status,
										'info' => 'page_last_button'
								);
						}
								
						//the middle buttons
						$number_of_buttons = 3;
						
						if($page_separator_left == false) {
								$number_of_buttons++;
						}
						
						if($page_separator_right == false) {
								$number_of_buttons++;
						}
								
						if($this->data['pages_total'] <= $max_sites_to_display && $this->data['pages_total'] > 1) {
								for($i = 2; $i <= $this->data['pages_total']; $i++) {
										if($this->site == $i) {
												$tmp_status = 'active';
										} else {
												$tmp_status = '';
										}
										
										$this->data['link_pages'][] = array(
												'url' => 'index.php?s=' . $module . '&amp;site=' . $i,
												'title' => $i,
												'status' => $tmp_status,
												'info' => 'middle-button'
										);
								}
						} else if ($this->data['pages_total'] <= $max_sites_to_display && $this->data['pages_total'] == 1) {
								//do nothing..
						} else {
								$i = $this->site;
								
								//first pages check						
								if($i == 1) {
										$i = 2;
								} else if($i > 2) {
										$i -= 1;
								}
								
								//last pages check
								$tmp_index = $this->data['pages_total'] - $this->site;
					
								if($tmp_index == 2) {
										$i -= 1;
								}
								
								if($tmp_index == 1) {
										$i -= 2;
								}
								
								if($tmp_index == 0) {
										$i -= 3;
								}
										
								for($n = 0; $n < $number_of_buttons; $n++) {
										if($this->site == ($n + $i)) {
												$tmp_status = 'active';
										} else {
												$tmp_status = '';
										}
										
										$this->data['link_pages'][] = array(
												'url' => 'index.php?s=' . $module . '&amp;site=' . ($n + $i),
												'title' => ($n + $i),
												'status' => $tmp_status,
												'info' => 'last_pages_check'
										);
								}
						}
								
						//insert the last button (and separator if set..)
						if($page_separator_right == true) {
								$this->data['link_pages'][] = array(
										'url' => '',
										'title' => '..',
										'status' => 'disabled',
										'info' => 'last-button'
								);
						}
						
						if($page_last_button != '') {
								$this->data['link_pages'][] = $page_last_button;
						}
						
						
						//next button - grayed in some cases
						if (($this->site < $this->data['pages_total']) && ($this->data['pages_total'] != 1)) {
								$this->data['link_page_forward'] = 'index.php?s=' . $module . '&amp;site=' . ($this->site + 1);
						}
				}
	
				//assign smarty variables..
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('CURRENT_DATALANGUAGE_ID', $this->current_datalanguage);
				$renderer->assign('DATALANGUAGES', $this->datalanguages);
				$this->data['pagination'] = $renderer->fetch('site/pagination.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Suche
		///////////////////////////////////////////////////////////////////
		private function search() {
				$searchterm = trim(core()->getPostVar('searchterm'));
				$search_mode = core()->getPostVar('search_mode');
				
				if(empty($searchterm)) {
						header('Location: index.php?s=cAdminproducts&error=80');
						die;
				}
				
				switch($search_mode) {
						case 'id':
								$search_mode = 'id';
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery('SELECT * FROM ' . $db->table('products') . ' WHERE id = :id');
								$db->bind(':id', (int)$searchterm);
								break;
						default:
								$search_mode = 'all';
								$db = core()->get('db');
								$db->useInstance('systemdb');
								$db->setQuery('SELECT * FROM ' . $db->table('products') . ' 
															WHERE 
																	id = :id OR
																	products_number LIKE :products_number
															GROUP BY id');
								$db->bind(':id', (int)$searchterm);
								$db->bind(':products_number', '%' . $searchterm . '%');
								$result = $db->execute();
								break;
				}
				
				$result = $db->execute();
				$data = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$tmp['titles'] = cProducts::loadTitlesByProducts_id($tmp['id']);
						$data[] = $tmp;
				}
				
				$this->data['list'] = $data;
				$this->data['result_count'] = count($data);
				$this->data['searchterm'] = htmlentities($searchterm, ENT_QUOTES, "UTF-8");
				$this->data['search_mode'] = $search_mode;
				$this->data['total_count'] = $this->data['result_count'];
				
				//$this->breadcrumb[] = array('url' => '', 'title' => TEXT_SEARCH_RESULT . ' ' . $this->data['searchterm']);
				$cAdmin = core()->getInstance('cAdmin');
				$cAdmin->appendBreadcrumb(TEXT_SEARCH_RESULT . ' ' . $this->data['searchterm'], '');
		}
		
		///////////////////////////////////////////////////////////////////
		// create an entry from editor
		///////////////////////////////////////////////////////////////////
		private function create() {
				$products_data = core()->getAllPosts();
				
				$id = cProducts::save($products_data);
	
				if(empty($id)) {
						header('Location: index.php?s=cAdminproducts&error=81');
						die;
				}
	
				header('Location: index.php?s=cAdminproducts&action=edit&id=' . $id . '&success=31');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// update an entry
		///////////////////////////////////////////////////////////////////
		private function update() {
				$products_data = core()->getAllPosts();
				
				if(!isset($products_data['id'])) {
						header('Location: index.php?s=cAdminproducts&error=82');
						die;
				}
				
				if(false === cProducts::checkForExistence((int)$products_data['id'])) {
						header('Location: index.php?s=cAdminproducts&error=83');
						die;
				}
				
				$id = cProducts::save($products_data);
	
				if(empty($id)) {
						header('Location: index.php?s=cAdminproducts&error=84');
						die;
				}
	
				header('Location: index.php?s=cAdminproducts&action=edit&id=' . $id . '&infomessage=32');
				die;
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page content.
		///////////////////////////////////////////////////////////////////
		public function content() {
				switch($this->action) {
						/*case 'confirm_delete':
								$this->drawConfirmDeleteDialog();
								break;*/
						case 'create':
						case 'new':
								$this->drawEditor();
								break;
						case 'edit':
								$this->drawEditor();
								break;
						default:
								$this->getPagination();
								$this->drawList();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Load data for an entry
		///////////////////////////////////////////////////////////////////
		private function editorLoadData() {
				$id = (int)core()->getGetVar('id');
	
				if(empty($id)) {
						header('Location: index.php?s=cAdminproducts&error=85');
						die;
				}
	
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('products') . ' where id = :id');
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				$data = $result->fetchArrayAssoc();
	
				if(empty($data)) {
						header('Location: index.php?s=cAdminproducts&error=86');
						die;
				}
				
				//set a temp products id (for saving ajax data temporary)
				$data['tmp_products_id'] = cProducts::generateTmpProductsId($_SESSION['user_id'] . '.');
				
				//load the title texts
				$data['titles'] = cProducts::loadTitlesByProductsId($id);
				
				//load the descriptions texts
				$data['descriptions'] = cProducts::loadDescriptionsByProductsId($id);
				
				//check if the descriptions panels are opend or closed on init..
				$this->channel_languages_opened = array();
				
				foreach($this->datalanguages as $lang_index => $lang) {
						foreach($this->channels as $channel_index => $channel) {
								$opened = false;
								
								if($channel['id'] == 0) {
										$this->channel_languages_opened[$channel['id']][$lang['id']]['channel_opened'] = true;
										continue;
								}
								
								foreach($data['titles'][$lang['id']][$channel['id']] as $text) {
										if(!empty($text)) {
												$this->channel_languages_opened[$channel['id']][$lang['id']]['channel_opened'] = true;
												$opened = true;
												break;
										}
								}
	
								if($opened) {
										continue;
								}
								
								
								foreach($data['descriptions'][$lang['id']][$channel['id']] as $text) {
										if(!empty($text)) {
												$this->channel_languages_opened[$channel['id']][$lang['id']]['channel_opened'] = true;
												$opened = true;
												break;
										}
								}
								
								if($opened) {
										continue;
								}
								
								$this->channel_languages_opened[$channel['id']][$lang['id']]['channel_opened'] = false;
						}
				}
				
				//set additional data..
				if(isset($data['titles'][$this->default_datalanguage][0][1])) {
						$data['display_title'] = $data['titles'][$this->default_datalanguage][0][1];
				} else {
						$data['display_title'] = $data['titles'][$this->default_datalanguage][0][1] = '- - -';
				}
				
				//load products_categories
				$products_categories = array();
				
				foreach($this->channels as $channel) {
						$tmp = cProducts::loadProductcategoriesByProductsIdAndChannel($id, $channel['id'], $this->default_datalanguage);					
						$products_categories[$channel['id']] = $tmp;
				}
				
				$data['products_categories'] = $products_categories;
				
				//load products buying prices
				$this->buying_prices = cProductbuyingprices::loadAsArray($id, $this->default_datalanguage);
				
				//load products prices
				$this->prices = cProductprices::load($id, $this->default_datalanguage);
				
				//load products images
				$this->products_images = cProductimages::loadByProductsId($id);
				
				//load products files
				$this->products_files = cProductfiles::loadByProductsId($id);
				
				//load channels specific data
				cProductchannels::loadProductsChannelsValueByChannelReferenceArray($id, $this->products_channels_data);		//this uses a reference as parameter - so no return value is needed..
	
				//assign the data to the main object..
				$this->data['data'] = $data;
				$this->data['url'] = 'index.php?s=cAdminproducts&amp;action=update&amp;id=' . (int)$id;
				
				//load attributes
				$this->data['products_attributes'] = cProductattributes::loadProductsAttributes((int)$id, $this->default_datalanguage);
				$this->data['products_attributes'] = cProductattributes::loadOptionsTitles($this->data['products_attributes'], $this->default_datalanguage, $this->data['data']['tmp_products_id']);
				$this->data['products_attributes'] = cProductattributes::loadOptionsValuesTitles($this->data['products_attributes'], $this->default_datalanguage, $this->data['data']['tmp_products_id']);
				
				//load features
				$this->data['products_features'] = cProductfeatures::loadProductsFeatures((int)$id, $this->default_datalanguage);
				$this->data['products_features'] = cProductfeatures::loadFeaturesetsTitles($this->data['products_features'], $this->default_datalanguage, $this->data['data']['tmp_products_id']);
				$this->data['products_features'] = cProductfeatures::loadFeaturesetsValuesTitles($this->data['products_features'], $this->default_datalanguage, $this->data['data']['tmp_products_id']);
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the list view.
		///////////////////////////////////////////////////////////////////
		function drawList() {
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
				$renderer->assign('CURRENT_DATALANGUAGE_ID', 1);		//TODO: Change this in a later version..
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('INFO_MESSAGES', $this->info_messages);
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('SUCCESS_MESSAGES', $this->success_messages);
				$renderer->render('site/adminproducts/list.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// draw the editor
		///////////////////////////////////////////////////////////////////
		function drawEditor() {
				$this->current_datalanguage = 1;
				$this->datalanguages = cDatalanguages::loadActivated();
					
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->assign('SITE_URLS', cSite::loadSiteUrls());
				$renderer->assign('NAVBARS', cAdminnavbaredit::loadNavbarList());
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('PRODUCTTYPES', $this->producttypes);
				$renderer->assign('PACKAGINGUNITS', $this->packagingunits);
				$renderer->assign('PRODUCTCONDITIONS', $this->productconditions);
				$renderer->assign('DELIVERYSTATUS', $this->deliverystatus);
				$renderer->assign('DATALANGUAGES', $this->datalanguages);
				$renderer->assign('CHANNELS', $this->channels);
				$renderer->assign('CHANNEL_LANGUAGES_OPENED', $this->channel_languages_opened);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('SUPPLIERS', $this->suppliers);
				$renderer->assign('TAXCLASSES', $this->taxclasses);
				$renderer->assign('BUYING_PRICES', $this->buying_prices);
				$renderer->assign('PRICES', $this->prices);
				$renderer->assign('CUSTOMERGROUPS', $this->customergroups);
				$renderer->assign('CURRENT_DATALANGUAGE_ID', $this->default_datalanguage);
				$renderer->assign('PRODUCTS_IMAGES', $this->products_images);
				$renderer->assign('PRODUCTS_FILES', $this->products_files);
				$renderer->assign('PRODUCTS_CHANNELS_DATA', $this->products_channels_data);
				$renderer->assign('MANUFACTURERS', $this->manufacturers);
				$renderer->assign('PRODUCTS_OPTIONS', $this->products_options);
				$renderer->assign('PRODUCTS_FEATURESETS', $this->products_featuresets);
				
				//Allgemeine Artikeldaten
				$products_editor_tab_general = $renderer->fetch('site/adminproducts/products_editor_tab_general.html');
				$renderer->assign('PRODUCTS_EDITOR_TAB_GENERAL', $products_editor_tab_general);
				
				//Artikeltexte
				foreach($this->datalanguages as $lang) {
						$renderer->assign('tmp_datalanguage', $lang);
						$products_editor_tab_texts[$lang['id']] = $renderer->fetch('site/adminproducts/products_editor_tab_texts.html');
				}
				
				$renderer->assign('PRODUCTS_EDITOR_TAB_TEXTS', $products_editor_tab_texts);
				
				//Artikelkategorien
				$renderer->assign('PRODUCTS_EDITOR_TAB_CATEGORIES', $renderer->fetch('site/adminproducts/products_editor_tab_categories.html'));
				
				//Artikelpreise
				$renderer->assign('PRODUCTS_EDITOR_TAB_PRICES', $renderer->fetch('site/adminproducts/products_editor_tab_prices.html'));
				$renderer->assign('PRODUCTS_EDITOR_TAB_BUYING_PRICES', $renderer->fetch('site/adminproducts/products_editor_tab_buying_prices.html'));
				
				//Artikelbilder
				$renderer->assign('PRODUCTS_EDITOR_TAB_IMAGES', $renderer->fetch('site/adminproducts/products_editor_tab_images.html'));
				
				//Dateien
				$renderer->assign('PRODUCTS_EDITOR_TAB_FILES', $renderer->fetch('site/adminproducts/products_editor_tab_files.html'));
				
				//Channel-Daten
				$renderer->assign('PRODUCTS_EDITOR_TAB_CHANNELS', $renderer->fetch('site/adminproducts/products_editor_tab_channels.html'));
				
				//Attribute (at first we load the attributes table with all available attributes)
				$renderer->assign('PRODUCTS_ATTRIBUTES', $this->data['products_attributes']);//build the attributes html table
				$products_attributes_html_table = $renderer->fetch('site/adminproducts/products_attributes_table.html');
				$renderer->assign('PRODUCTS_ATTRIBUTES_HTML_TABLE', $products_attributes_html_table);
				//now get the attributes tab..			
				$renderer->assign('PRODUCTS_EDITOR_TAB_ATTRIBUTES', $renderer->fetch('site/adminproducts/products_editor_tab_attributes.html'));
				
				//Feature (at first we load the features table with all available features)
				$renderer->assign('PRODUCTS_FEATURES', $this->data['products_features']);//build the features html table
				$products_features_html_table = $renderer->fetch('site/adminproducts/products_features_table.html');
				$renderer->assign('PRODUCTS_FEATURES_HTML_TABLE', $products_features_html_table);
				//now get the attributes tab..			
				$renderer->assign('PRODUCTS_EDITOR_TAB_FEATURES', $renderer->fetch('site/adminproducts/products_editor_tab_features.html'));
				
				//Load the stock of the products
				$renderer->assign('PRODUCTS_EDITOR_TAB_STOCK', $renderer->fetch('site/adminproducts/products_editor_tab_stock.html'));
				
				//Show Editor
				$renderer->render('site/adminproducts/editor.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Prepare data for the editor.
		///////////////////////////////////////////////////////////////////
		private function initData() {
		}
		
		///////////////////////////////////////////////////////////////////
		// Get content..
		///////////////////////////////////////////////////////////////////
		public function getContent() {
			
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page footer.
		///////////////////////////////////////////////////////////////////
		public function footer() {
				$additional_output = 
						"\n" . '<script src="data/templates/' . $this->template . '/js/products_categories.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/mv_file_upload.jquery.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/products_prices.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/mv_price_calculation.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/products_images.js"></script>' .
						"\n" . '<script src="data/templates/' . $this->template . '/js/products_files.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
}

?>