<?php

class cAdminsetupwebseller extends cModule {
		var $template = 'admin';
		var $navbar_title = TEXT_MODULE_TITLE_ADMINSETUPWEBSELLER;
		var $navbar_id = 0;
		
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				//If the user is not logged in..
				if(!isset($_SESSION['user_id'])) {
						header('Location: index.php/account');
						die;
				}
				
				//check the rights..
				if(false === cAccount::adminrightCheck('cAdminsetupwebseller', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?s=cAdmin&error=98');
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
				$cAdmin->appendBreadcrumb(TEXT_MODULE_TITLE_ADMINSETUPWEBSELLER, '');
				$this->navbar_title = TEXT_MODULE_TITLE_ADMINSETUPWEBSELLER;
				
				$this->data['url'] = 'index.php?s=cAdminsetupwebseller&amp;action=update';
				
				switch($this->action) {
						case 'update':
								$this->initData();
								$this->getData();
								$this->update();
								break;
								
						default:
								$this->initData();
								$this->getData();
								break;
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Prepare data for the editor.
		///////////////////////////////////////////////////////////////////
		function initData() {
				$this->data['data']['mail_from'] = '';
				$this->data['data']['mail_reply_to'] = '';
				$this->data['data']['subject'] = '';
				$this->data['data']['font_size_text'] = '';
				$this->data['data']['font_size_button'] = '';
		}
		
		///////////////////////////////////////////////////////////////////
		// Draw the page content.
		///////////////////////////////////////////////////////////////////
		public function content() {
				switch($this->action) {
						default:
								$this->drawEditor();
								break;
				}
		}
		
		
		
		///////////////////////////////////////////////////////////////////
		// Draw the editor.
		///////////////////////////////////////////////////////////////////
		function drawEditor() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('DATA', $this->data);
				$renderer->assign('NAVBAR_TITLE', $this->navbar_title);
				$renderer->render('site/adminsetupwebseller/editor.html');
		}
		
		///////////////////////////////////////////////////////////////////
		// Update an entry.
		///////////////////////////////////////////////////////////////////
		public function update() {
				$module = 'webseller';
				
				$this->data['data']['mail_from'] = core()->getPostVar('mail_from');
				$this->data['data']['mail_reply_to'] = core()->getPostVar('mail_reply_to');
				$this->data['data']['subject'] = core()->getPostVar('subject');
				$this->data['data']['font_size_text'] = core()->getPostVar('font_size_text');
				$this->data['data']['font_size_button'] = core()->getPostVar('font_size_button');
				
				cSetup::saveSettingByModuleAndKey($module, 'mail_from', $this->data['data']['mail_from']);
				cSetup::saveSettingByModuleAndKey($module, 'mail_reply_to', $this->data['data']['mail_reply_to']);
				cSetup::saveSettingByModuleAndKey($module, 'subject', $this->data['data']['subject']);
				cSetup::saveSettingByModuleAndKey($module, 'font_size_text', $this->data['data']['font_size_text']);
				cSetup::saveSettingByModuleAndKey($module, 'font_size_button', $this->data['data']['font_size_button']);
	
				header('Location: index.php?s=cAdminsetupwebseller&success=33');
				die;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Loads a database entry.
		/////////////////////////////////////////////////////////////////////////////////
		public function getData() {
				$module = 'webseller';

				$mail_from = cSetup::loadSettingByModuleAndKey($module, 'mail_from');
				$mail_reply_to = cSetup::loadSettingByModuleAndKey($module, 'mail_reply_to');
				$subject = cSetup::loadSettingByModuleAndKey($module, 'subject');
				$font_size_text = cSetup::loadSettingByModuleAndKey($module, 'font_size_text');
				$font_size_button = cSetup::loadSettingByModuleAndKey($module, 'font_size_button');
				
				if(false !== $mail_from) {
						$this->data['data']['mail_from'] = $mail_from['setup_value'];
				}
				
				if(false !== $mail_reply_to) {
						$this->data['data']['mail_reply_to'] = $mail_reply_to['setup_value'];
				}
				
				if(false !== $subject) {
						$this->data['data']['subject'] = $subject['setup_value'];
				}
				
				if(false !== $font_size_text) {
						$this->data['data']['font_size_text'] = $font_size_text['setup_value'];
				}
				
				if(false !== $font_size_button) {
						$this->data['data']['font_size_button'] = $font_size_button['setup_value'];
				}
		}
}

?>