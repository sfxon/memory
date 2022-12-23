<?php

class cLogin extends cModule {
		var $template = 'blitz2016';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				//If the user is logged in..
				if(isset($_SESSION['user_id'])) {
						header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'myaccount/index.html');
						die;
				}
				
				//We use the CMS module for output.
				cCMS::setExecutionalHooks();		
				
				$core = core();
				
				//Now set our own hooks below the CMS hooks.
				//core()->setHook('cCMS|init', 'init');
				core()->setHook('cCore|process', 'process');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$errormessage = '';
				$action = core()->getGetVar('action');

				if($action == 'process') {
						$username = core()->getPostVar('login_name');
						$password = core()->getPostVar('login_password');	
						
						if(false === $this->doLogin($username, $password)) {
								$renderer = core()->getInstance('cRenderer');
								$renderer->setTemplate($this->template);
								$renderer->assign('ERRORMESSAGE', ERROR_TEXT_LOGIN_INCORRECT);
								$errormessage = $renderer->fetch('site/errormessage.html');								
						}
				}

				//Load the CMS Entry for the login page.
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('ADMIN_LOGIN');
				$content = str_replace('{$ERRORMESSAGE}', $errormessage, $content);
				$cms->setContentData($content);
				
				//Set the site url. We need this for the form to have the right action url!
				$site_url = cSeourls::loadSeourlByQueryString('s=cLogin');
				$site_url = ltrim($site_url, '/');
				$cms->setSiteUrl(cSite::loadSiteUrl(core()->get('site_id')) . $site_url);
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Processing a login.
		/////////////////////////////////////////////////////////////////////////////////
		public function doLogin($email, $password) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT id, password FROM ' . $db->table('accounts') . ' WHERE LOWER(email) = LOWER(:email) LIMIT 1');
				$db->bind(':email', $email);
				
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['id'])) {
						$password_hash = $tmp['password'];
						
						if(true === password_verify($password, $password_hash)) {		//Aaaaaaand - you are logged in!
								//core()->set('user_id', (int)$tmp['id']);
								$_SESSION['user_id'] = (int)$tmp['id'];
								header('Location: http://' . cSite::loadSiteUrl(core()->get('site_id')) . 'myaccount/index.html');
								die;
						}
				}
				
				core()->set('user_id', '');
				return false;
		}
}

?>