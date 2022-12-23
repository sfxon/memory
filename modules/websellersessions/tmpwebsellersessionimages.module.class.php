<?php

class cTmpwebsellersessionimages extends cModule {
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Save a logo that has been previously uploaded to the temp folder.
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		public static function saveLogo($tmp_websellersessions_id, $tmp_logo_images_filename, $original_logo_filename, $logo_file_extension) {
				//Check if an entry for this websellersessions_id exists
				$tmpwebsellersession_data = cTmpwebsellersessionimages::loadTmpLogoImage($tmp_websellersessions_id);
				
				if(false === $tmpwebsellersession_data) {
						//No entry in database - insert new one.
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'INSERT INTO ' . $db->table('tmp_webseller_sessions') . ' ' .
										'(tmp_websellersessions_id, tmp_logo_images_filename, original_logo_filename, logo_file_extension) ' .
								'VALUES ' .
										'(:tmp_websellersessions_id, :tmp_logo_images_filename, :original_logo_filename, :logo_file_extension)'
						);
						$db->bind(':tmp_websellersessions_id', $tmp_websellersessions_id);
						$db->bind(':tmp_logo_images_filename', $tmp_logo_images_filename);
						$db->bind(':original_logo_filename', $original_logo_filename);
						$db->bind(':logo_file_extension', $logo_file_extension);
						$db->execute();
						
						
				} else {
						//Entry in database exists: update!
						$db = core()->get('db');
						$db->useInstance('systemdb');
						$db->setQuery(
								'UPDATE ' . $db->table('tmp_webseller_sessions') . ' SET ' .
										'tmp_logo_images_filename = :tmp_logo_images_filename, ' .
										'original_logo_filename = :original_logo_filename, ' .
										'logo_file_extension = :logo_file_extension ' .
								'WHERE ' .
										'tmp_websellersessions_id = :tmp_websellersessions_id'
						);
						$db->bind(':tmp_logo_images_filename', $tmp_logo_images_filename);
						$db->bind(':original_logo_filename', $original_logo_filename);
						$db->bind(':logo_file_extension', $logo_file_extension);
						$db->bind(':tmp_websellersessions_id', $tmp_websellersessions_id);
						$db->execute();
				}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Load temp logo images data from temp table.
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		public static function loadTmpLogoImage($tmp_websellersessions_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('tmp_webseller_sessions') . ' ' .
						'WHERE ' .
								'tmp_websellersessions_id = :tmp_websellersessions_id ' .
						'LIMIT 1'
				);
				$db->bind(':tmp_websellersessions_id', $tmp_websellersessions_id);
				$result = $db->execute();
				
				return $result->fetchArrayAssoc();
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Copy temorary image to live..
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		public static function copyLogoImageFromTempToLive($user_id, $websellersessions_id, $tmp_websellersessions_id) {
				//$data, $tmp_logo_image
				$tmp_data = cTmpwebsellersessionimages::loadTmpLogoImage($tmp_websellersessions_id);
				
				//copy the file
				$src = 'data/tmp/tmpuploads/' . $tmp_data['tmp_logo_images_filename'];
				$dst = 'data/webseller/sessions/' . $user_id . '/' . $websellersessions_id . '/logo' . $tmp_data['logo_file_extension'];
				
				copy($src, $dst);
				
				//Update the websellersession_table with all needed data.
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('webseller_sessions') . ' SET ' .
								'original_logo_filename = :original_logo_filename, ' .
								'logo_file_extension = :logo_file_extension ' . 
						'WHERE ' .
								'id = :id '
				);
				$db->bind(':original_logo_filename', $tmp_data['original_logo_filename']);
				$db->bind(':logo_file_extension', $tmp_data['logo_file_extension']);
				$db->bind(':id', (int)$websellersessions_id);
				$result = $db->execute();
		}
}

?>