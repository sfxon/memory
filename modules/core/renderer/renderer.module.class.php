<?php

/////////////////////////////////////////////////////////////////////////////////
// The systems database initialisation.
// We use this database information for all our system modules.
// In this database we save things like session_data, languages_data, ...
// It is very essential for all other modules.
/////////////////////////////////////////////////////////////////////////////////
//
// TODO: Check if it would be a better idea, to define a class variable
// for the database instance. So one could use another instance than the systems one.
//
/////////////////////////////////////////////////////////////////////////////////
class cRenderer extends cModule {
		var $parts = array();
		
		var $Smarty;
		var $smarty_template_dir;
		var $smarty_compile_dir;
		var $smarty_config_dir;
		var $smarty_cache_dir;
		
		var $templateName;
		
		////////////////////////////////////////////////////////////////////////
		// Constructor.
		// Does some settings and installes the session handlers.
		////////////////////////////////////////////////////////////////////////
		function __construct() {
				$this->templateName = '';				
				
				$this->startSmarty();
		}
		
		////////////////////////////////////////////////////////////////////////
		// Desctruct.
		////////////////////////////////////////////////////////////////////////
		function __destruct() {
		}
		
		/////////////////////////////////////////////////////////////////////////
		// StartSmarty
		/////////////////////////////////////////////////////////////////////////
		function startSmarty() {
				$smarty_dir = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'smarty' . DIRECTORY_SEPARATOR;
				require($smarty_dir . 'Smarty.class.php');
				
				$this->Smarty = new Smarty();
				$this->Smarty->template_dir 	= $smarty_dir . 'templates';
				$this->Smarty->compile_dir   	= $smarty_dir . 'templates_c';
				$this->Smarty->config_dir    	= $smarty_dir . 'config';
				$this->Smarty->cache_dir     	= $smarty_dir . 'cache';
		}
		
		/////////////////////////////////////////////////////////////////////////
		// Define where to set this module in the boot (hook) chain.
		/////////////////////////////////////////////////////////////////////////
		/*public function setBootHooks() {
				core()->setBootHook('cLang');
		}
		
		////////////////////////////////////////////////////////////////////////
		// This is executed when the system boots and the chain reached this module.
		////////////////////////////////////////////////////////////////////////
		public function boot() {
				$renderer = new cRenderer();
				core()->set('renderer', $renderer);
		}*/
		
		//////////////////////////////////////////////////////////////////////////
		// Set core hooks. This hooks are executed always!
		//////////////////////////////////////////////////////////////////////////
		public static function setCoreHooks() {
				core()->setHook('cCore|render', 'header');
				core()->setHook('cRenderer|header', 'begin_page');
				core()->setHook('cRenderer|begin_page', 'header_bar');
				core()->setHook('cRenderer|header_bar', 'content');
				core()->setHook('cRenderer|content', 'footer');
				core()->setHook('cRenderer|footer', 'end_page');
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Returns an array of all modules.
		//
		// Return Value Array Shema:
		//		array(
		//				array(
		//						'module' => 'path/and/module/name',
		//						'version' => '1.6'		//Minimum version of dependent module that is needed to run this module.
		//				), 
		//				array(..)
		//		);
		//
		//		The systems core logic checks all dependencies in the auto loader.
		//			
		//////////////////////////////////////////////////////////////////////////
		public static function getDependenciesAsArray() {
				return array(
						array(
								'module' => '/core/lang/cLang'
						)
				);
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Returns the version of a module.
		// Returns 0 (zero) if you define no version for your module.
		//////////////////////////////////////////////////////////////////////////
		public static function getVersion() {
				return 0.1;
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Set the current to use template folder.
		//////////////////////////////////////////////////////////////////////////
		public function setTemplate($template_name) {
				$this->templateName = $template_name;
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Renders one site. Adds the result to the end of the output queue.
		//////////////////////////////////////////////////////////////////////////
		public function render($template_file) {
				$output = $this->Smarty->fetch('data/templates/' . $this->templateName . '/' . $template_file);
				$this->parts[] = $output;
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Renders one site. Adds the result to the end of the output queue.
		//////////////////////////////////////////////////////////////////////////
		public function renderText($text) {
				$this->parts[] = $text;
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Renders one site, but returns the result.
		//////////////////////////////////////////////////////////////////////////
		public function fetch($template_file) {
				$output = $this->Smarty->fetch('data/templates/' . $this->templateName . '/' . $template_file);
				return $output;
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Renders an input string, but returns the result.
		//////////////////////////////////////////////////////////////////////////
		public function fetchFromString($input) {
				$output = $this->Smarty->fetch('string:' . $input);
				return $output;
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Outputs all contents in parts to the default device.
		//////////////////////////////////////////////////////////////////////////
		public function display() {
				foreach($this->parts as $part) {
						echo $part;
				}
		}
		
		/////////////////////////////////////////////////////////////////////////
		// Assign a variable to the template.
		/////////////////////////////////////////////////////////////////////////
		public function assign($variable_name, $value) {
				$this->Smarty->assign($variable_name, $value);
		}
		
}

?>