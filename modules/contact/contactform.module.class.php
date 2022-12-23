<?php

class cContactform extends cModule {
		var $template = 'blitz2016';
		var $id = 0;
		var $contentData = '';
		var $siteUrl = '';
		var $errors = array();
		var $contactFormData = array();
		var $mail_from;
	
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				cCMS::setExecutionalHooks();		//We use the CMS module for output.
				
				$core = core();
				core()->setHook('cCore|init', 'init');
				core()->setHook('cCore|process', 'process');
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Initialisation - test some settings..
		/////////////////////////////////////////////////////////////////////////////////
		public function init() {
				$id = (int)core()->getGetVar('id');
				
				if(0 == $id) {		
						header('Location: ' . cCore::getCurrentDomain() . '/index.php');
						die;
				}
				
				//Try to load the contact form..
				$this->contactFormData = $this->loadContactForm($id);
				
				if(false === $this->contactFormData) {
						header('Location: ' . cCore::getCurrentDomain() . '/index.php');
						die;
				}
				
				//Check SEO Url
				$params = array('s' => 'cContactform', 'id' => $this->contactFormData['id']);
				cSeourls::checkSeoUrl($this->siteUrl, 'cCMS', $params);
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Load contact form information from database.
		/////////////////////////////////////////////////////////////////////////////////
		public function loadContactForm($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT id, title, template, template_processing_successful, mail_subject, mail_from, mail_to ' .
						'FROM ' . $db->table('contactform') . ' ' .
						'WHERE ' .
								'id = :id ' .
						'LIMIT 1'
				);
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(!isset($tmp['id'])) {
						return false;					
				}
				
				$tmp['details'] = $this->loadActiveContactFormDetails($id);
				
				return $tmp;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Load contact form details from database
		/////////////////////////////////////////////////////////////////////////////////
		public function loadActiveContactFormDetails($contactform_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT id, status, title, name, value, description, required, error_text_variable, input_type, contactform_details_parent_id, sort_order, input_processing ' .
						'FROM ' . $db->table('contactform_details') . ' ' .
						'WHERE ' .
								'contactform_id = :contactform_id ' .
						'ORDER BY sort_order;'
				);
				$db->bind(':contactform_id', (int)$contactform_id);
				$result = $db->execute();
				
				$tmp = array();
				
				while($result->next()) {
						$tmp[] = $result->fetchArrayAssoc();
				}
				
				return $tmp;
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Verarbeitung..
		/////////////////////////////////////////////////////////////////////////////////
		public function process() {
				$errormessage = '';
				$action = core()->getGetVar('action');
				
				$this->getPostValues();
				$processing_successful = false;
				$this->mail_from = $this->contactFormData['mail_from'];		//This is used, if no detail (input) field is specified to hold the from mail adress
				
				if($action == 'process') {
						$this->processPostValues();
						$this->escapePostValuesForOutput();
						
						if(count($this->errors) == 0) {
								$this->sendEmail();
								$this->saveCSV();
								$processing_successful = true;
						}
				}

				//Load the CMS Entry for the login page.
				$cms = core()->getInstance('cCMS');
				$page_content = '';
				$site_id = (int)core()->get('site_id');
				$this->template = cSite::loadSiteTemplate($site_id);
				
				if($processing_successful) {
						$page_content = $cms->loadContentDataByCmsId($this->contactFormData['template_processing_successful']);
				} else {
						//Render the content (if there are set some CMS variables..)
						$renderer = core()->getInstance('cRenderer');
						$renderer->assign('ERRORS', $this->errors);
						$page_content = $cms->loadContentDataByCmsId($this->contactFormData['template']);
						$form = $this->renderForm();
						
						$template_url = $cms->getTemplateUrl();
						
						$renderer = core()->getInstance('cRenderer');
						$renderer->setTemplate($this->template);
						$renderer->assign('ERRORS', $this->errors);
						$renderer->assign('FORM', $form);
						$renderer->assign('TEMPLATE_URL', $template_url);
						$page_content['text'] = $renderer->fetchFromString($page_content['text']);
				}
				
				$cms->setContentData($page_content);
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Save csv file.
		/////////////////////////////////////////////////////////////////////////////////////////
		protected function saveCSV() {
				//build csv file heading
				$csv_heading = '';
				$csv_field_separator = ';';
				$csv_field_delimiter = '"';
			
				foreach($this->contactFormData['details'] as $detail) {
						if($detail['input_type'] == 0) {		//Skip static texts..
								continue;
						}
						
						if($detail['input_type'] == 5) {		//Skip selects option
								continue;
						}
						
						if($detail['input_type'] == 7) {		//Skip button
								continue;
						}
						
						if($detail['input_type'] == 8) {		//Skip selects end
								continue;
						}
						
						if(strlen($csv_heading) > 0) {
								$csv_heading .= $csv_field_separator;
						}
						
						$csv_heading .= $csv_field_delimiter . htmlspecialchars($this->parseForCSV($detail['title'])) . $csv_field_delimiter;
				}
				
				$csv_heading .= "\n";
				
				//build body..
				$csv_body = '';
				
				foreach($this->contactFormData['details'] as $detail) {
						if($detail['input_type'] == 0) {		//Skip static texts..
								continue;
						}
						
						if($detail['input_type'] == 5) {		//Skip selects option
								continue;
						}
						
						if($detail['input_type'] == 7) {		//Skip button
								continue;
						}
						
						if($detail['input_type'] == 8) {		//Skip selects end
								continue;
						}
						
						if(strlen($csv_body) > 0) {
								$csv_body .= $csv_field_separator;
						}
						
						$csv_body .= $csv_field_delimiter . htmlspecialchars($this->parseForCSV($detail['post_value'])) . $csv_field_delimiter;
				}
				
				$csv_body .= "\n";
				
				//Save data in csv file.
				$path = 'data/contacts/';				
				$filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $this->contactFormData['title']);		//Remove all unwanted things from filename..
				$filename = date('Y-m-d-') . $filename . '.csv';		//Build final filename
				$fp = '';
				
				if(!file_exists($path . $filename)) {
						$fp = fopen($path . $filename, 'w');
						fwrite($fp, $csv_heading);
				} else {
						$fp = fopen($path . $filename, 'a');
				}
				
				fwrite($fp, $csv_body);
				
				fclose($fp);
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Send the contact email.
		/////////////////////////////////////////////////////////////////////////////////////////
		protected function sendEmail() {
				$datetime = date('d.m.Y, H:i') . ' Uhr';
								
				$email_betreff = $this->contactFormData['mail_subject'];
				//$email_betreff = 'Kontaktanfrage - Kugelschreiber';
				
				$anfrage_mail =
						'Kontaktanfrage über Kontaktformular ' . $this->contactFormData['title'] . ' auf ' . cSite::loadSiteUrl(core()->get('site_id')) . ': ' . "\n\n" .
						'Zeit: ' . $datetime . "\n\n";
				
				//Add all fields
				$anfrage_mail .= $this->buildMailFormBody();
				$anfrage_mail = utf8_decode($anfrage_mail);		//This is, because mail in utf8 is shown wrong in some clients..
				
				//Build the headers..
				$mail_header = 'From: ' . $this->mail_from . "\r\n";
				
				if($this->mail_from != $this->contactFormData['mail_from']) {		//Ad reply to address, if the address is different from our setup address..
						$mail_header .= 'Reply-To: ' . $this->mail_from . "\r\n";
				}
				
				$mail_header .= 'X-Mailer: PHP/' . phpversion();
				
				//Send the email
				mail($this->contactFormData['mail_to'], $email_betreff, $anfrage_mail, $mail_header);
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Erstellt den Inhalt der E-Mail. Hier werden die Werte ausgegeben,
		// die der Kunde eingegeben hat.
		/////////////////////////////////////////////////////////////////////////////////////////
		protected function buildMailFormBody() {
				$retval = '';
			
				foreach($this->contactFormData['details'] as $detail) {
						if($detail['input_type'] == 0) {		//Skip static texts..
								continue;
						}
						
						if($detail['input_type'] == 5) {		//Skip selects option
								continue;
						}
						
						if($detail['input_type'] == 7) {		//Skip button
								continue;
						}
						
						if($detail['input_type'] == 8) {		//Skip selects end
								continue;
						}
						
						if($detail['input_type'] == 3) {
								$retval .= "\n" . $detail['title'] . ':' . "\n" . htmlspecialchars($detail['post_value']) . "\n\n";
						}else {
								$retval .= $detail['title'] . ': ' . htmlspecialchars($detail['post_value']) . "\n";
						}
				}
				
				return $retval;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Checks if a contact form template exists.
		/////////////////////////////////////////////////////////////////////////////////////////
		protected function contactFormTemplateExists() {
				if($this->contactFormData['template'] == '') {
						return false;
				}
				
				$foldername = dirname( dirname( dirname(__FILE__)  ) ) . '/data/templates/' . $this->template . '/site/contactform/';
				
				if(file_exists($foldername . $this->contactFormData['template'])) {
						return true;
				}
				
				return false;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		// Parse a string for a csv file.
		/////////////////////////////////////////////////////////////////////////////////////////
		public function parseForCSV($input) {
				$input = str_replace('"', '', $input);
				$input = str_replace(',', '', $input);
				$input = str_replace("\n", ' | ', $input);
				$input = str_replace("\r", '', $input);
				return $input;
		}
		
		////////////////////////////////////////////////////////////////////////////////////////
		// Initialise POST Values.
		////////////////////////////////////////////////////////////////////////////////////////
		protected function getPostValues() {
				foreach($this->contactFormData['details'] as $index => $detail) {
						if($detail['input_type'] > 0) {		//Skip static text..
								if(!empty($detail['name'])) {						
										$this->contactFormData['details'][$index]['post_value'] = core()->getPostVar( $detail['name'] );
								}
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////
		// Alle Post Variablen für die Ausgabe escapen.
		///////////////////////////////////////////////////////////////////////////////////////
		public function escapePostValuesForOutput() {
				foreach($this->contactFormData['details'] as $index => $detail) {
						if($detail['input_type'] > 0) {
								if(!empty($detail['name'])) {
										$this->contactFormData['details'][$index]['post_value'] = htmlspecialchars($detail['post_value']);
								}
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////
		// Returns a post value by the fields id..
		///////////////////////////////////////////////////////////////////////////////////////
		public function getParentsPostValue($contactform_details_id) {
				foreach($this->contactFormData['details'] as $index => $detail) {
						if($detail['id'] == $contactform_details_id) {
								return $detail['post_value'];
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////
		// Alle Post Values aller relevanten Felder verarbeiten.
		///////////////////////////////////////////////////////////////////////////////////////
		public function processPostValues() {
				foreach($this->contactFormData['details'] as $index => $detail) {
						//process fields that have set a parent id..
						//(set their post_value to the value of the parents field..
						if(0 != (int)$detail['contactform_details_parent_id']) {
								$this->contactFormData['details'][$index]['post_value'] = $this->getParentsPostValue($detail['contactform_details_parent_id']);
						}
						
						//All other input fields..
						if($detail['input_type'] > 0) {		//Skip static text
								if(!empty($detail['name'])) {
										//Check if a value is required - but the field not set..
										if(NULL === $detail['post_value'] && $detail['required'] != 0) {
												$this->errors[$detail['name']] = 1;
										}
										
										//Get the processing script
										$processing = explode('|', $detail['input_processing']);
										
										foreach($processing as $process) {
												$process_parts = explode(':', $process);
												
												//Be careful - always use the indexed form for processing- because there may be multiple processing on one item,
												//and all data is saved in the big array.
												//So use it this: $this->contactFormData['details'][$index]['post_value'] 
												//not just like that: $details['post_value']
												//THIS IS VERY IMPORTANT!!
												switch($process_parts[0]) {
														case 1:		//TRIM
																$this->contactFormData['details'][$index]['post_value'] = $this->processPostValue_trim($this->contactFormData['details'][$index]['post_value']);
																break;
														case 2:		//STRLEN (test strlen)
																$this->processPostValue_strlen_min($this->contactFormData['details'][$index]['post_value'], $process_parts[1], $index);
																break;
														case 3:		//Use this as email from address..
																$this->processPostValue_set_mail_from($this->contactFormData['details'][$index]['post_value']);
																break;
												}
										}
								}
						}
				}
		}
		
		////////////////////////////////////////////////////////////////////////////////////
		// Process a post value - Remove white spaces at beginning and end of string.
		////////////////////////////////////////////////////////////////////////////////////
		protected function processPostValue_trim($input) {
				return trim($input);
		}
		
		////////////////////////////////////////////////////////////////////////////////////
		// Process a post value - Test the min strlen is reached.
		////////////////////////////////////////////////////////////////////////////////////
		protected function processPostValue_strlen_min($input, $min_length, $index) {
				if( strlen($input) < $min_length) {
						$this->errors[ $this->contactFormData['details'][$index]['name'] ] = 1;
				}
		}
		
		////////////////////////////////////////////////////////////////////////////////////
		// Set "from" mail address
		////////////////////////////////////////////////////////////////////////////////////
		protected function processPostValue_set_mail_from($mail) {
				$this->mail_from = $mail;
		}
		
		////////////////////////////////////////////////////////////////////////////////////
		// Render the form.
		////////////////////////////////////////////////////////////////////////////////////
		protected function renderForm() {
				//Preparation
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($this->template);
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('FORM_ACTION', $this->getFormAction());
				
				//Render Form beginning.
				$form = $renderer->fetch('site/contactform/elements/form_start.html');
				$form .= "\n";
				
				foreach($this->contactFormData['details'] as $index => $detail) {
						$renderer->assign('DETAIL', $detail);
						
						switch($detail['input_type']) {
								case 0:
										$form .= $detail['title'];
										break;
								case 1:
										$form .= $renderer->fetch('site/contactform/elements/input.html');
										break;
								case 2:
										$form .= $renderer->fetch('site/contactform/elements/password.html');
										break;
								case 3:
										$form .= $renderer->fetch('site/contactform/elements/text_field.html');
										break;
								case 4:
										$form .= $renderer->fetch('site/contactform/elements/select_begin.html');
										break;
								case 5:
										$form .= $renderer->fetch('site/contactform/elements/select_option.html');
										break;
								case 6:
										$form .= $renderer->fetch('site/contactform/elements/checkbox.html');
										break;
								case 7:
										$form .= $renderer->fetch('site/contactform/elements/button.html');
										break;
								case 8:
										$form .= $renderer->fetch('site/contactform/elements/select_end.html');
										break;
								case 9:
										$form .= $renderer->fetch('site/contactform/elements/checkbox2.html');
										break;
								default:
										break;
						}
						
						$form .= "\n";
				}
				
				//Render Form end
				$form .= $renderer->fetch('site/contactform/elements/form_end.html');
				
				return $form;
		}
		
		///////////////////////////////////////////////////////////////////////////////////
		// Get form action.
		///////////////////////////////////////////////////////////////////////////////////
		protected function getFormAction() {
				$querystring = 's=cContactform';
				$querystring .= '&id=' . $this->contactFormData['id'];
				$site_url = cSite::loadSiteUrl(core()->get('site_id'));
				$final_url = '';
				
				//SEO URL handling
				$seo_url = cSeourls::loadSeourlByQueryString($querystring);
				
				if(false !== $seo_url) {
						//Remove beginning trailing in seo_url - if it exists..
						if(strpos($seo_url, '/') === 0) {
								if(strlen($seo_url) > 1) {
										$seo_url = substr($seo_url, 1, strlen($seo_url) - 1);
								} else {
										$seo_url = '';
								}
						}
					
						$final_url = '//' . $site_url . $seo_url . '?action=process';
				} else {
						//This is not a seo url..
						$final_url = '//' . $site_url . 'index.php?' . str_replace('&', '&amp;', $querystring) . '&amp;action=process';
				}
				
				return $final_url;
		}
}

?>