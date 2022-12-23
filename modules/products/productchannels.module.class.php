<?php

class cProductchannels extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Alle Channel-Daten laden..
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadPreferencesModel($channels_array, $datalanguages) {
				$models = array();
				
				foreach($channels_array as $channel) {
						$tmp = cChannelpreferences::loadModelByChanneltypesId($channel['channel_type'], $datalanguages);
						$models[$channel['id']] = $tmp;
				}
				
				return $models;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load the values for the channels of a product
		// Notice: The parameter is parsed as reference - so no return value is needed
		//         The data is saved directly in the array..
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadProductsChannelsValueByChannelReferenceArray($products_id, &$data) {
				//loop through channels
				if(is_array($data)) {
						foreach($data as $channel_id => $channel_data) {
								//loop through channels data model
								if(is_array($channel_data)) {
										foreach($channel_data as $channel_preferences_model_id => $channel_preferences_model_data) {
												$value = cProductchannels::loadValueByProductChannelPreference($products_id, $channel_id, $channel_preferences_model_data['id']);
												
												if(false === $value) {
														$value = $channel_preferences_model_data['default_value'];
												}
												
												$data[$channel_id][$channel_preferences_model_id]['current_value'] = $value;
										}
								}
						}
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// Load the current products setting for a channels data model item
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadValueByProductChannelPreference($products_id, $channel_id, $channel_preferences_model_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('products_channel_options') . ' ' .
						'WHERE ' .
								'products_id = :products_id AND ' .
								'channel_id = :channel_id AND ' .
								'channel_preferences_model_id = :channel_preferences_model_id'
				);
				$db->bind(':products_id', (int)$products_id);
				$db->bind(':channel_id', (int)$channel_id);
				$db->bind(':channel_preferences_model_id', (int)$channel_preferences_model_id);
				$result = $db->execute();
		
				$tmp = $result->fetchArrayAssoc();
				
				if($tmp === false) {
						return false;
				}
				
				return $tmp['value'];
		}
}

?>