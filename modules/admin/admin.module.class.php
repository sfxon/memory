<?php

class cAdmin extends cModule {
		var $template = 'admin';
		var $metaTitle = '';
		var $breadcrumb = array();
		var $contentData = array();
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				cAdmin::setSmallBodyExecutionalHooks();
				core()->setHook('cRenderer|content', 'content');
		}
		
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setSmallBodyExecutionalHooks() {
				core()->setHook('cCore|init', 'init');
				core()->setHook('cRenderer|header', 'header');
				core()->setHook('cRenderer|begin_page', 'begin_page');
				core()->setHook('cRenderer|header_bar', 'header_bar');
				core()->setHook('cRenderer|footer', 'footer');
				core()->setHook('cRenderer|end_page', 'end_page');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Initialize this module.
		/////////////////////////////////////////////////////////////////////////////////
		public function init() {
				//check, if user is logged in..
				if(!isset($_SESSION['user_id'])) {
						header('Location: index.php?error=notallowed');
						die;
				}
				
				//check the rights..
				if(false === cAccount::adminrightCheck('cAdmin', 'USE_MODULE', (int)$_SESSION['user_id'])) {
						header('Location: index.php?error=notallowed');
						die;
				}
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Render the header (html header!)
		/////////////////////////////////////////////////////////////////////////////////
		public function header() {
				$renderer = core()->getInstance('cRenderer');
				if(isset($this->contentData['meta_title'])) {
						$renderer->assign('META_TITLE', $this->contentData['meta_title']);
				}
				
				$renderer->setTemplate($this->template);
				$renderer->render('site/header.html');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Render the Beginning of the page
		/////////////////////////////////////////////////////////////////////////////////
		public function begin_page() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->render('site/begin_page.html');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Render the Header Bar (for example: logo, header navigation)
		// e.g.: Things that are always of the same style on every page.
		/////////////////////////////////////////////////////////////////////////////////
		public function header_bar() {
				$s = core()->get('s');
				
				if(empty($this->breadcrumb) && $s = 'cAdmin') {
						$this->appendBreadcrumb(TEXT_TITLE_DASHBOARD, '');
				}
				
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('BREADCRUMB', $this->breadcrumb);
				$renderer->render('site/header_bar.html');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Render the content. This is the middle part of the page.
		/////////////////////////////////////////////////////////////////////////////////
		public function content() {
				//Load RSS News Feed
				$rssnews = cDashboardrssnews::getRssNews($this->template);
			
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('RSS_NEWS', $rssnews);
				$renderer->render('site/dashboard/dashboard.html');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Render the pages footer.
		/////////////////////////////////////////////////////////////////////////////////
		public function footer() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->render('site/footer.html');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Render the pages footer.
		/////////////////////////////////////////////////////////////////////////////////
		public function end_page() {
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->render('site/end_page.html');

				$renderer->display();
		}
		
		////////////////////////////////////////////////////////////////////////////////
		// Add entries to the breadcrumb.
		// @param		parts:	Appends one part to the breadcrumb array.
		////////////////////////////////////////////////////////////////////////////////
		public function appendBreadcrumb($title, $url) {
				$this->breadcrumb[] = array(
						'title' => $title,
						'url' => $url
				);
		}
}

?>