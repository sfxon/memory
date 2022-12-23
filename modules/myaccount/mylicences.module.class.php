<?php

class cMylicenses extends cModule {
		var $template = 'blitz2016';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
		var $errors = array();
		var $successes = array();
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				if(!empty($_SESSION['user_id'])) {
						cCMS::setExecutionalHooks();		//We use the CMS module for output.
						
						$core = core();
						core()->setHook('cCore|process', 'process');
				}
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$success = core()->getGetVar('success');								
				$errormessage = '';
				$action = core()->getGetVar('action');				
				$this->siteUrl = cSite::loadSiteUrl(core()->get('site_id'));				
				
				//Check the user..
				$userdata = cAccount::loadUserData($_SESSION['user_id']);
				
				if(false === $userdata) {
						header('Location: http://' . $this->siteUrl . 'index.php?error=25');
						die;
				}
				
				//Check the user licences..				
				if(false === cLicences::CheckUserHasLicences($_SESSION['user_id'])) {
						header('Location: http://' . $this->siteUrl . 'no-licences/index.html');
						die;
				}
				
				//process input				
				if($action == 'process') {
						die('process input');
				}

				//Load the CMS Entry for the login page.
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('MYLICENCES');
				
				//Render the content (if there are set some CMS variables..)
				$renderer = core()->getInstance('cRenderer');
				$renderer->assign('SUCCESSES', $this->successes);
				$renderer->assign('ERRORS', $this->errors);
				
				$this->contentData['text'] = $renderer->fetchFromString($content);
				$cms->setContentData($content);
				
				//Check SEO Url if this module was called directly..
				$s = core()->getGetVar('s');
				
				//Check SEO Url
				$process = core()->getGetVar('process');
				$params = array('s' => 'cMylicences', 'process' => $process);
				cSeourls::checkSeoUrl($this->siteUrl, 'cCMS', $params);
		}
}

?>