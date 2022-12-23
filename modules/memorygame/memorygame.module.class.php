<?php

class cMemorygame extends cModule {
		var $template = 'blitz2016';
		var $cms_id = 0;
		var $contentData = '';
		var $siteUrl = '';
		var $errors = array();
		var $total_card_count = 16;
		
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				cCMS::setExecutionalHooks();		//We use the CMS module for output.
				
				$core = core();
				core()->setHook('cCore|process', 'process');
				core()->setHook('cCMS|footer', 'footer');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$this->siteUrl = cSite::loadSiteUrl(core()->get('site_id'));
				$errormessage = '';
				$action = core()->getGetVar('action');
				
				if($action == 'process') {
				}
				
				$cards = cMemorygamedata::loadRandomList($this->total_card_count);
				
				$randomized_cards_and_answers = cMemorygamedata::randomizeGameDataAsCards($cards);
				shuffle($randomized_cards_and_answers);
				//$randomized_cards_and_answers = cMemorygamedata::alternateCardsTextAndImage($randomized_cards_and_answers);
				
				$colors = array(
						'blue', 'pink', 'green', 'violet', 'pink', 'yellow', 'blue', 'red',
						'yellow', 'blue', 'red', 'yellow', 'green', 'red', 'green', 'pink',
						'pink', 'green', 'yellow', 'violet', 'pink', 'blue', 'yellow', 'blue',
						'violet', 'pink', 'green', 'blue', 'yellow', 'red', 'green', 'pink'
				);
				
				/*$color_values = array(
						'red' => '#C7332C',
						'blue' => '#009ED5',
						'pink' => '#E72582',
						'green' => '#4DA23E',
						'violet' => '#3E3672',
						'yellow' => '#FFEF10'
				);
				*/
				$color_values = array(
						'red' => '#DCE8FF',
						'blue' => '#DCE8FF',
						'pink' => '#DCE8FF',
						'green' => '#DCE8FF',
						'violet' => '#DCE8FF',
						'yellow' => '#DCE8FF'
				);
						
				
				$second_chance = 0;
				
				if(isset($_GET['action']) && $_GET['action'] == 'restart') {
						$second_chance = 1;
				}

				//Load the CMS Entry for the login page.
				$cms = core()->getInstance('cCMS');
				$content = $cms->loadContentDataByKey('004_GAME');
				$renderer = core()->getInstance('cRenderer');
				$renderer->assign('CARDS', $randomized_cards_and_answers);
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('FINAL_ACTION_WRONG', 'http://' . $this->siteUrl . 'finish_bad.html');
				$renderer->assign('FINAL_ACTION_RIGHT', 'http://' . $this->siteUrl . 'finish_good.html');
				$renderer->assign('FINAL_ACTION_END_GAME_WITH_INFORMATION', 'http://' . $this->siteUrl . 'end_game_with_information.html');
				$renderer->assign('SECOND_CHANCE', $second_chance);
				$renderer->assign('TOTAL_CARD_COUNT', $this->total_card_count);
				$renderer->assign('CARD_COLORS', $colors);
				$renderer->assign('COLOR_VALUES', $color_values);
				/*$renderer->assign('NAME', htmlentities($name));*/
				$this->contentData['text'] = $renderer->fetchFromString($content['text']);

				$cms->setContentData($content);
				
				//Check SEO Url if this module was called directly..
				$s = core()->getGetVar('s');
				
				//SEO URL handling
				if($s == 'cMemorygame') {
						$this->siteUrl = rtrim($this->siteUrl, '/');
						$this->contentUrl = 's=cMemorygame';
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
		
		///////////////////////////////////////////////////////////////////
		// Draw the page footer.
		///////////////////////////////////////////////////////////////////
		public function footer() {
				$additional_output = 
						"\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/mv.js"></script>' .
						"\n" . '<script src="//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/templates/' . $this->template . '/js/jquery.imgpreload.min.js"></script>' .
						"\n";
				$renderer = core()->getInstance('cRenderer');
				$renderer->renderText($additional_output);
		}
}

?>