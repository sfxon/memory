<?php

class cStartgame extends cModule {
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
				$errormessage = '';
				$action = core()->getGetVar('action');
				
				$name = '';
				
				if($action == 'process') {
						$name = trim(core()->getPostVar('name'));
						$_SESSION['name'] = $name;
						
						header('Location: ' . $this->siteUrl . 'start.html');
						die;
				}

				//Load the CMS Entry for the login page.
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('003_START_GAME');
				//$content = $content['text'];
				
				//Render the content (if there are set some CMS variables..)
				$renderer = core()->getInstance('cRenderer');
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('NAME', htmlentities($name));
				
				$this->contentData['text'] = $renderer->fetchFromString($content['text']);

				$cms->setContentData($content);
				
				//Check SEO Url if this module was called directly..
				$s = core()->getGetVar('s');
				
				//SEO URL handling
				if($s == 'cStartgame') {
						$this->siteUrl = cSite::loadSiteUrl(core()->get('site_id'));
						$this->siteUrl = rtrim($this->siteUrl, '/');
						$this->contentUrl = 's=cStartgame';
						$this->seoUrl = cSeourls::loadSeourlByQueryString($this->contentUrl);
						
						$this->finalContentUrl = '//' . $this->siteUrl . '?' . $this->contentUrl;
						
						if(false !== $this->seoUrl) {				
								//get all get params and append the one, that are not set yet..
								$do_not_get_this_params = array('s', 'seourl', 'process');
								$this->paramString = cSite::getAllGetParamsAsString( $do_not_get_this_params );
								
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
				}
		}
}

?>