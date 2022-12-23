<?php

class cLogout extends cModule {
		var $template = 'blitz2016';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				if(!isset($_SESSION['user_id'])) {
						header('Location: index.php?s=cLogin');		//Goto login screen..
						die;
				}
				
				cCMS::setExecutionalHooks();		//We use the CMS module for output.
				
				$core = core();
				
				//Now set our own hooks below the CMS hooks.
				//core()->setHook('cCMS|init', 'init');
				core()->setHook('cCore|process', 'process');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				//Destroy the session: The officially implemented handler keeps track on also deleting everything in $_SESSION..
				//Be careful - if you ever change it!
				$b = session_destroy();
				
				header('Location: index.php?s=cLoggedout');
				die;
				
				//Load the CMS Entry for the login page.
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('LOGOUT');
				
				//Render the content (if there are set some CMS variables..)
				$renderer = core()->getInstance('cRenderer');
				$this->contentData['text'] = $renderer->fetchFromString($content);
				$cms->setContentData($content);
		}
}

?>