<?php

class cWssellerlogout extends cModule {
		//////////////////////////////////////////////////////////////////////////////////
		// Hook us into the system
		//////////////////////////////////////////////////////////////////////////////////
		public static function setExecutionalHooks() {
				$state = cWsseller::checkSellerStatus();		//Redirects to another page, if an error occures.
				
				if($state != 'loggedin') {
						die('Seller is not logged in');
				}
				
				//We use the CMS module for output.
				cCMS::setExecutionalHooks();		
				
				$core = core();
				
				//Now set our own hooks below the CMS hooks.
				//core()->setHook('cCMS|init', 'init');
				core()->setHook('cCore|process', 'process');
		}
		
		public function process() {
				//1st: Logout the customer
				$state_json = cWebsellersessionslive::makeStateJson('customer_login', array());
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('webseller_sessions_live') . ' SET ' .
								'state = :state ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':state', $state_json);
				$db->bind(':id', (int)$_SESSION['wscustomer_session_id_live']);
				$result = $db->execute();
				
				//2nd: Logout the seller..
				unset($_SESSION['seller_id']);
				unset($_SESSION['wscustomer_session_id_live']);
				header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/login.html');
				die;
		}
		
		
}

?>