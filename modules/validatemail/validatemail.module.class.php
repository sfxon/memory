<?php

class cValidatemail extends cModule {
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
				core()->setHook('cCore|process', 'process');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$vc = core()->getGetVar('vc');
				$user_id = (int)core()->getGetVar('user_id');
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('accounts') . ' ' .
						'SET email_validated = 1 ' .
						'WHERE ' .
								'id = :id AND ' .
								'email_validation_code = :email_validation_code'
				);
				$db->bind(':email_validation_code', $vc);
				$db->bind(':id', (int)$user_id);
				$result = $db->execute();
			
				
				$errormessage = '';
				$action = core()->getGetVar('action');
				$this->siteUrl = cSite::loadSiteUrl(core()->get('site_id'));
				
				//Load the CMS Entry for the login page.
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('EMAIL_VALIDATION_SUCCESSFULL');
				
				//Render the content (if there are set some CMS variables..)
				$renderer = core()->getInstance('cRenderer');
				$this->contentData['text'] = $renderer->fetchFromString($content);
				$cms->setContentData($content);
				
				//Check SEO Url
				$params = array('s' => 'cValidatemail');
				cSeourls::checkSeoUrl($this->siteUrl, 'cCMS', $params);
		}
}

?>