<?php

class cMyaccount extends cModule {
		var $template = 'blitz2016';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
		var $errors = array();
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				cCMS::setExecutionalHooks();		//We use the CMS module for output.
				
				$core = core();
				core()->setHook('cCore|process', 'process');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				//Load the CMS Entry for the login page.
				$cms = core()->getInstance('cCMS');
				$this->siteUrl = cSite::loadSiteUrl(core()->get('site_id'));				
				$userdata = cAccount::loadUserData($_SESSION['user_id']);
				
				if(false === $userdata) {
						header('Location: ' . $this->siteUrl . 'index.php?error=25');
						die;
				}
				
								
				$content = $cms->loadContentDataByKey('MYACCOUNT');
				
				//Render the content (if there are set some CMS variables..)
				$renderer = core()->getInstance('cRenderer');
				$this->contentData['text'] = $renderer->fetchFromString($content);
				$cms->setContentData($content);
				
				//Check SEO Url if this module was called directly..
				$s = core()->getGetVar('s');
				
				//SEO URL handling
				/*if($s == 'cMyaccount') {
						$this->siteUrl = cSite::loadSiteUrl(core()->get('site_id'));
						$this->contentUrl = 's=cMyaccount';
						$this->seoUrl = cSeourls::loadSeourlByQueryString($this->contentUrl);
						
						//get all get params and append the one, that are not set yet..
						$do_not_get_this_params = array('s', 'seourl', 'process');
						$this->paramString = cSite::getAllGetParamsAsString( $do_not_get_this_params );
						
						$this->finalContentUrl = '//' . $this->siteUrl . '?' . $this->contentUrl . '&' . $this->paramString;
						
						if(false !== $this->seoUrl) {
								//Build final urls
								$this->finalContentUrl = '//' . $this->siteUrl . $this->seoUrl . '?' . $this->paramString;
								
								//Check if the current complete request is the same as our generated request
								$current_url  = '//' . core()->getCurrentDomain() . $_SERVER['REQUEST_URI'];
								
								//remove ? in both urls, if it is the last char..
								$this->finalContentUrl = rtrim($this->finalContentUrl, "?");
								$current_url = rtrim($current_url, "?");
								
								if(0 !== strcasecmp($current_url, $this->finalContentUrl)) {
										//This is not the seo url! Redirect to the seo url!
										header('Location: ' . $this->finalContentUrl, 301);
										die;
								}
								
								$cms->setSiteUrl($this->siteUrl);
						}
				}*/
				//Check SEO Url
				$process = core()->getGetVar('process');
				$params = array('s' => 'cMyaccount', 'process' => $process);
				cSeourls::checkSeoUrl($this->siteUrl, 'cCMS', $params);
		}
}

?>