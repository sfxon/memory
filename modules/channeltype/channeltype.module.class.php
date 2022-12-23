<?php

class cChanneltype extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// load channeltypes
		///////////////////////////////////////////////////////////////////////////////////////////////////
		function loadChanneltypes() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('channeltypes'));
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$retval[$tmp['id']] = $tmp;
				}
				
				return $retval;
		}
												
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// mv_channeltype_get_by_id($tmp_data['channel_type']);
		///////////////////////////////////////////////////////////////////////////////////////////////////
		function getById($channel_type) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' .  $db->table('channeltypes') . ' WHERE id = :id');
				$db->setChar(':id', $channel_type);
				$result = $db->execute();
				
				$data = $result->fetcharrayassoc();
				
				if(empty($data)) {
						return array(
									'id' => 0,
									'title' => ERROR_TEXT_CHANNEL_TYPE_NOT_FOUND
									);
				}
				
				return $data;
		}
}
?>