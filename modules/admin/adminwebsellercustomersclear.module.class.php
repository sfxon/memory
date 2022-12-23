<?php

class cAdminwebsellercustomersclear extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINWEBSELLERCUSTOMERSCLEAR;
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
				if(false === cAccount::adminrightCheck('cAdminwebsellercustomersclear', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=124');
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
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINWEBSELLERCUSTOMERSCLEAR, 'index.php?s=cAdminwebsellercustomersclear');
				
				$this->initData();
				$this->data['url'] = 'index.php?s=cAdminwebsellercustomersclear&amp;action=confirm';
				$cAdmin->appendBreadcrumb(TEXT_ADMINWEBSELLERCUSTOMERSCLEAR_CONFIRM_DELETE, '');
				$this->navbar_title = TEXT_ADMINWEBSELLERCUSTOMERSCLEAR_CONFIRM_DELETE;
				
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
						
						if($security_code != 'mn5!DK') {
								header('Location: index.php?s=cAdminwebsellercustomersclear&error=' . urlencode('Der eingegebene Security-Code ist falsch.'));
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
				$renderer->render('site/adminwebsellercustomersclear/confirm_dialog.html');
		}
		
		///////////////////////////////////////////////////////////////////////////////
		// Delete all product related data.
		///////////////////////////////////////////////////////////////////////////////
		public function delete() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				
				$db->setQuery('DELETE FROM ' . $db->table('tmp_webseller_sessions'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('tmp_webseller_sessions_products'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('tmp_webseller_sessions_products_images'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('webseller_sessions'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('webseller_sessions_live'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('webseller_sessions_products'));
				$result = $db->execute();
				
				$db->setQuery('DELETE FROM ' . $db->table('webseller_sessions_products_images'));
				$result = $db->execute();
				
				$this->resetAutoIncrementOnTable('webseller_sessions');
				$this->resetAutoIncrementOnTable('webseller_sessions_live');
				
				header('Location: index.php?s=cAdminwebsellercustomersclear&success=' . urlencode('Alle Webseller-Kundendaten wurden gelöscht.'));
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