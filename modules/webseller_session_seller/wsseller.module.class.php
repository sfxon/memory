<?php

class cWsseller extends cModule {
		/////////////////////////////////////////////////////////////////////////
		// Load open sessions by seller_status.
		// Seller Status is a field in the accounts table,
		// that defines if a user is rookie, powerseller or non salesman.
		/////////////////////////////////////////////////////////////////////////
		public static function loadOpenSessions($seller_status) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('webseller_sessions') . ' WHERE status = 1 AND session_type = :session_type');
				$db->bind(':session_type', (int)$seller_status);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		/////////////////////////////////////////////////////////////////////////
		// Status des Verkäufers überprüfen.
		/////////////////////////////////////////////////////////////////////////
		public static function checkSellerStatus() {//If the user is logged in..
				//Check if seller is logged in..
				if(!isset($_SESSION['seller_id'])) {
						header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/login.html');
						die;
				}
				
				//Check if account is allowed to be logged in as seller.
				$seller_status = cWsseller::getSellerStatus($_SESSION['seller_id']);
				
				if($seller_status != 1 && $seller_status != 2) {
						header('Location: //' . cSite::loadSiteUrl(core()->get('site_id')) . 'seller/login.html&error=notallowed');
						die;
				}
				
				return 'loggedin';
		}
		
		/////////////////////////////////////////////////////////////////////////
		// Get the state of the seller.
		/////////////////////////////////////////////////////////////////////////
		public static function getSellerStatus() {
				if(!isset($_SESSION['seller_id'])) {
						return false;
				}
				
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT seller_status FROM ' . $db->table('accounts') . ' WHERE id = :id LIMIT 1;');
				$db->bind(':id', (int)$_SESSION['seller_id']);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(isset($tmp['seller_status'])) {
						return $tmp['seller_status'];
				}
				
				return false;
		}
		
		/////////////////////////////////////////////////////////////////////////
		// Get the state of the seller.
		/////////////////////////////////////////////////////////////////////////
		public static function getSellersLiveSessions($seller_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('webseller_sessions_live') . ' WHERE ' .
								'seller_id = :seller_id AND ' .
								'session_started_on IS NOT NULL AND ' .
								'session_ended_on IS NULL '
				);
				$db->bind(':seller_id', (int)$seller_id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$retval[] = $result->fetchArrayAssoc();
				}
				
				return $retval;
		}
}

?>