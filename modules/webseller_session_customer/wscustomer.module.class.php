<?php

class cWscustomer extends cModule {
		public static function checkCustomerStatus() {
				//Check if live session id is set.
				if(!isset($_SESSION['wscustomer_session_id_live'])) {
						header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'login/index.html?error=notloggedin');
						die;
				}
				
				//Check if live session id is valid.
				$live_session_id = $_SESSION['wscustomer_session_id_live'];
				
				$live_session_data = cWebsellersessionslive::loadLiveData($live_session_id);
				
				if(false === $live_session_data) {
						unset( $_SESSION['wscustomer_session_id_live'] );		//Unset session id, because we are not really logged in!
						header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'login/index.html?error=notloggedin');
						die;
				}
				
				if(isset($_SESSION['seller_id'])) {
						//Check if account is allowed to be logged in as seller.
						$seller_status = cWsseller::getSellerStatus($_SESSION['seller_id']);
						
						if($seller_status != 1 && $seller_status != 2) {
								header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/login.html&error=notallowed');
								die;
						}
						
						//Check that this seller is allowed to handle this session!
						if((int)$_SESSION['seller_id'] !== (int)$live_session_data['seller_id']) {
								unset($_SESSION['wscustomer_session_id_live']);
								cWebsellersessionslive::writeLog('Seller is not allowed to use this session.', $live_session_data, array('SESSION: ' . print_r($_SESSION, true)));
								header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/session_setup.html?err=NOTUSERSSESSION');
								die;
						}
				}
				
				return 'loggedin';
		}
	
		/////////////////////////////////////////////////////////////////////////////////////////////
		// Checks a customers session by id..
		/////////////////////////////////////////////////////////////////////////////////////////////
		public static function checkSessionSetupExists($session_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT id, status FROM ' . $db->table('webseller_sessions') . ' ' .
						'WHERE ' .
								'id = :id ' .
						'LIMIT 1'
				);
				$db->bind(':id', (int)$session_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(!isset($tmp['id'])) {
						return false;
				}
				
				return true;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		// Checks a customers session by id..
		/////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadSessionData($session_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('webseller_sessions') . ' ' .
						'WHERE ' .
								'id = :id ' .
						'LIMIT 1'
				);
				$db->bind(':id', (int)$session_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(!isset($tmp['id'])) {
						return false;
				}
				
				return $tmp;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		// Returns an url with the logo image path of a webseller session.
		/////////////////////////////////////////////////////////////////////////////////////////////
		public static function getLogoImageUrl($session_data) {
				return '//' . cSite::loadSiteUrl(core()->get('site_id')) . 'data/webseller/sessions/' . (int)$session_data['webseller_session']['user_id'] . '/' . (int)$session_data['webseller_session']['id'] . '/logo' . $session_data['webseller_session']['logo_file_extension'];
		}
}

?>