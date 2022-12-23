<?php

class cContact extends cModule {
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
				
				$company = '';
				$name = '';
				$email = '';
				$phone = '';
				$message = '';
				
				$mail_sent = false;
				
				if($action == 'process') {
						$company = trim(core()->getPostVar('company'));
						$name = trim(core()->getPostVar('name'));
						$email = trim(core()->getPostVar('email'));
						$phone = core()->getPostVar('phone');
						$message = core()->getPostVar('message');
												
						if(0 == strlen($company)) {
								$this->errors['company'] = 1;
						}
						
						if(0 == strlen($name)) {
								$this->errors['name'] = 1;
						}
						
						if(strlen($email) < 3) {
								$this->errors['email'] = 1;
						}
						
						if(count($this->errors) == 0) {
								$datetime = date('d.m.Y, H:i') . ' Uhr';
								
								$email_betreff = 'Kontaktanfrage - Kugelschreiber';
								
								$anfrage_mail =
										'Kontaktanfrage über Kontaktformular: ' . "\n\n" .
										'Zeit: ' . $datetime . "\n\n" .
										'Firma: ' . htmlentities($company) . "\n\n" .
										'Ansprechpartner: ' . htmlentities($name) . "\n\n" .
										'E-Mail: ' . htmlentities($email) . "\n\n" .
										'Telefon: ' . htmlentities($phone) . "\n\n\n" .
										'Nachricht: ' . "\n" .
										htmlentities($message);
								$anfrage_mail = utf8_decode($anfrage_mail);
								
								$mail_header = 'From: ' . $email . "\r\n" .
								'Reply-To: ' . $email . "\r\n" .
								'X-Mailer: PHP/' . phpversion();
								
								mail('', $email_betreff, $anfrage_mail, $mail_header);
								
								//build csv file..
								$csv_heading = '"id","Unternehmen","Vor- & Nachname","E-Mail","Telefon","Ihre Nachricht"' . "\n";
								
								$csv_company = $this->parseForCSV($company);
								$csv_name = $this->parseForCSV($name);
								$csv_email = $this->parseForCSV($email);
								$csv_phone = $this->parseForCSV($phone);
								$csv_message = $this->parseForCSV($message);
								
								$csv_text = '"0","' . $csv_company . '","' . $csv_name . '","' . $csv_email . '","' . $csv_phone . '","' . $csv_message . '"' . "\n";
								
								$path = 'data/contacts/';
								$filename = date('Y-m-d-') . 'kontaktanfrage.csv';
								$fp = '';
								
								if(!file_exists($path . $filename)) {
										$fp = fopen($path . $filename, 'w');
										fwrite($fp, $csv_heading);
								} else {
										$fp = fopen($path . $filename, 'a');
								}
								
								fwrite($fp, $csv_text);
								
								fclose($fp);
								
								$mail_sent = true;
						}
				}

				//Load the CMS Entry for the login page.
				$cms = core()->getInstance('cCMS');
				
				if($mail_sent) {
						$content = $cms->loadContentDataByKey('CONTACT_FORM_SENT');
				} else {
						$content = $cms->loadContentDataByKey('CONTACT');
				}
				
				//Render the content (if there are set some CMS variables..)
				$renderer = core()->getInstance('cRenderer');
				$renderer->assign('ERRORS', $this->errors);
				$renderer->assign('COMPANY', htmlentities($company));
				$renderer->assign('NAME', htmlentities($name));
				$renderer->assign('EMAIL', htmlentities($email));
				$renderer->assign('PHONE', htmlentities($phone));
				$renderer->assign('MESSAGE', htmlentities($message));
				
				$this->contentData['text'] = $renderer->fetchFromString($content);

				$cms->setContentData($content);
				
				//Check SEO Url if this module was called directly..
				$s = core()->getGetVar('s');
				
				//SEO URL handling
				if($s == 'cContact') {
						$this->siteUrl = cSite::loadSiteUrl(core()->get('site_id'));
						$this->contentUrl = 's=cContact';
						$this->seoUrl = cSeourls::loadSeourlByQueryString($this->contentUrl);
						
						$this->finalContentUrl = '//' . $this->siteUrl . '?' . $this->contentUrl . '&' . $this->paramString;
						
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
}

?>