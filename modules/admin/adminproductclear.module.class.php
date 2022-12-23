<?php

class cAdminproductclear extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINPRODUCTCLEAR;
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
				if(false === cAccount::adminrightCheck('cAdminproductclear', 'USE_MODULE', (int)$_SESSION['user_id'])) {
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
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINPRODUCTIMPORT, 'index.php?s=cAdminproductclear');
				
				$this->initData();
				$this->data['url'] = 'index.php?s=cAdminproductclear&amp;action=confirm';
				$cAdmin->appendBreadcrumb(TEXT_ADMINPRODUCTIMPORT_CONFIRM_DELETE, '');
				$this->navbar_title = TEXT_ADMINPRODUCTIMPORT_CONFIRM_DELETE;
				
				switch($this->action) {
						case 'confirm':
								$this->delete();
								break;
						default:
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Prepare data for the editor.
		///////////////////////////////////////////////////////////////////
		function initData() {
				if($this->action == 'confirm') {
						//check the security code!
						$security_code = core()->getPostVar('security_code');
						
						if($security_code != 'a8B2f') {
								header('Location: index.php?s=cAdminproductclear&error=' . urlencode('Der eingegebene Security-Code ist falsch.'));
								die;
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page content.
		///////////////////////////////////////////////////////////////////
		public function content() {
				switch($this->action) {
						default:
								$this->drawConfirmDialog();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the list view.
		///////////////////////////////////////////////////////////////////
		function drawConfirmDialog() {
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
				$renderer->assign('INFO_MESSAGES', $this->info_messages);
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('SUCCESS_MESSAGES', $this->success_messages);
				$renderer->render('site/adminproductclear/confirm_dialog.html');
		}
		
		///////////////////////////////////////////////////////////////////////////////
		// Delete all product related data.
		///////////////////////////////////////////////////////////////////////////////
		public function delete() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				
				$db->setQuery('DELETE FROM ' . $db->table('product_categories'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('product_categories_description'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('product_categories_images'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('product_categories_images_descriptions'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('products'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('products_attributes'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('products_buying_prices'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('products_channel_options'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('products_descriptions'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('products_features'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('products_featuresets'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('products_featuresets_descriptions'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('products_featuresets_values'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('products_featuresets_values_descriptions'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('products_files'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('products_files_descriptions'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('products_images'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('products_images_descriptions'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('products_options'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('products_options_descriptions'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('products_options_values'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('products_options_values_descriptions'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('products_prices'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('products_titles'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('products_to_categories'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('tmp_product_categories_images'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('tmp_product_categories_images_descriptions'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_attributes'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_buying_prices'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_features'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_featuresets'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_featuresets_descriptions'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_featuresets_values'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_featuresets_values_descriptions'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_files'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_files_descriptions'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_images'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_images_descriptions'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_options'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_options_descriptions'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_options_values'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_options_values_descriptions'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_prices'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('tmp_products_to_categories'));
				$result = $db->execute();
				
				$this->resetAutoIncrementOnTable('product_categories');
				$this->resetAutoIncrementOnTable('products');
				$this->resetAutoIncrementOnTable('products_attributes');
				$this->resetAutoIncrementOnTable('products_buying_prices');
				$this->resetAutoIncrementOnTable('products_features');
				$this->resetAutoIncrementOnTable('products_featuresets');
				$this->resetAutoIncrementOnTable('products_featuresets_values');
				$this->resetAutoIncrementOnTable('products_options');
				$this->resetAutoIncrementOnTable('products_options_values');
				$this->resetAutoIncrementOnTable('products_prices');
				
				header('Location: index.php?s=cAdminproductclear&success=' . urlencode('Alle Artikeldaten wurden gelöscht.'));
				die;
		}
		
		///////////////////////////////////////////////////////////////////////////////
		// Reset an auto increment on a specific table..
		///////////////////////////////////////////////////////////////////////////////
		public function resetAutoIncrementOnTable($tablename) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('ALTER TABLE ' . $db->table($tablename) . ' AUTO_INCREMENT = 1');
				$result = $db->execute();
		}
}
?>