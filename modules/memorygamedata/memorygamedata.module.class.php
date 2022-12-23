<?php

class cMemorygamedata extends cModule {
		/////////////////////////////////////////////////////////
		// Load a random number of cards.
		/////////////////////////////////////////////////////////
		public static function loadRandomList($number_of_cards_to_load) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery('SELECT * FROM ' . $db->table('memory_gamedata_cards') . ' WHERE status = 1 ORDER BY RAND() LIMIT ' . (int)$number_of_cards_to_load);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$tmp['titles'] = cMemorygamedata::loadCardsTitlesByCardsId($tmp['id']);
						$tmp['images'] = cMemorygamedata::loadCardsImagesByCardsId($tmp['id']);
						$tmp['animations'] = cMemorygamedata::loadCardsAnimationsByCardsId($tmp['id']);
						$tmp['wrong_animations'] = cMemorygamedata::loadCardsWrongAnimationsByCardsId($tmp['id']);
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		/////////////////////////////////////////////////////////
		// This function loads a list of cards.
		/////////////////////////////////////////////////////////
		public static function loadCardsList() {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT ' .
								'* ' .
						'FROM ' . $db->table('memory_gamedata_cards') . ' ' .
						'ORDER BY id DESC;'
				);
				$result = $db->execute();
				

				$retval = array();
				
				while($result->next()) {
						$tmp = $result->fetchArrayAssoc();
						$tmp['titles'] = cMemorygamedata::loadCardsTitlesByCardsId($tmp['id']);
						$tmp['images'] = cMemorygamedata::loadCardsImagesByCardsId($tmp['id']);
						$tmp['animations'] = cMemorygamedata::loadCardsAnimationsByCardsId($tmp['id']);
						$tmp['wrong_animations'] = cMemorygamedata::loadCardsWrongAnimationsByCardsId($tmp['id']);
						$retval[] = $tmp;
				}
				
				return $retval;
		}
		
		/////////////////////////////////////////////////////////
		// Get cards images.
		/////////////////////////////////////////////////////////		
		public static function loadCardsImagesByCardsId($id) {
				$tmp[1] = cMemorygamedata::loadCardsImagesDataByIdAndCardNumber($id, 1);
				$tmp[2] = cMemorygamedata::loadCardsImagesDataByIdAndCardNumber($id, 2);
				$tmp[3] = cMemorygamedata::loadCardsImagesDataByIdAndCardNumber($id, 3);
			
				return $tmp;
		}
		
		/////////////////////////////////////////////////////////
		// Get cards animations.
		/////////////////////////////////////////////////////////		
		public static function loadCardsAnimationsByCardsId($id) {
				$tmp[1] = cMemorygamedata::loadCardsAnimationsDataByIdAndCardNumber($id, 1);
				$tmp[2] = cMemorygamedata::loadCardsAnimationsDataByIdAndCardNumber($id, 2);
			
				return $tmp;
		}
		
		/////////////////////////////////////////////////////////
		// Get cards wrong animations.
		/////////////////////////////////////////////////////////		
		public static function loadCardsWrongAnimationsByCardsId($id) {
				$tmp[1] = cMemorygamedata::loadCardsWrongAnimationsDataByIdAndCardNumber($id, 1);
				$tmp[2] = cMemorygamedata::loadCardsWrongAnimationsDataByIdAndCardNumber($id, 2);
			
				return $tmp;
		}
		
		/////////////////////////////////////////////////////////
		// Get cards images data.
		/////////////////////////////////////////////////////////		
		public static function loadCardsImagesDataByIdAndCardNumber($memory_cards_id, $card_number) {
				$tmp = cMemorygamedata::getCardsImagesDataByIdAndCardNumber($memory_cards_id, $card_number);
				$tmp['filename_with_path'] = 'data/gamedata/cards/' . $memory_cards_id . '_' . $card_number . $tmp['file_extension'];
				
				if(false !== $tmp) {
						$tmp['file_exists'] = cMemorygamedata::checkCardsImagesFileExists($tmp['filename_with_path']);
				}
				
				return $tmp;
		}
		
		/////////////////////////////////////////////////////////
		// Get cards animations data.
		/////////////////////////////////////////////////////////		
		public static function loadCardsAnimationsDataByIdAndCardNumber($memory_cards_id, $card_number) {
				$tmp = cMemorygamedata::getCardsAnimationsDataByIdAndCardNumber($memory_cards_id, $card_number);
				$tmp['filename_with_path'] = 'data/gamedata/cards/' . 'animation_' . $memory_cards_id . '_' . $card_number . $tmp['file_extension'];
				
				if(false !== $tmp) {
						$tmp['file_exists'] = cMemorygamedata::checkCardsAnimationsFileExists($tmp['filename_with_path']);
				}
				
				return $tmp;
		}
		
		/////////////////////////////////////////////////////////
		// Get cards wrong animations data.
		/////////////////////////////////////////////////////////		
		public static function loadCardsWrongAnimationsDataByIdAndCardNumber($memory_cards_id, $card_number) {
				$tmp = cMemorygamedata::getCardsWrongAnimationsDataByIdAndCardNumber($memory_cards_id, $card_number);
				$tmp['filename_with_path'] = 'data/gamedata/cards/' . 'wrong_animation_' . $memory_cards_id . '_' . $card_number . $tmp['file_extension'];
				
				if(false !== $tmp) {
						$tmp['file_exists'] = cMemorygamedata::checkCardsWrongAnimationsFileExists($tmp['filename_with_path']);
				}
				
				return $tmp;
		}
		
		/////////////////////////////////////////////////////////
		// Check, if a cards images exists on the server.
		/////////////////////////////////////////////////////////		
		public static function checkCardsImagesFileExists($filename_with_path) {				
				if(file_exists($filename_with_path)) {
						return true;
				}
				
				return false;
		}
		
		/////////////////////////////////////////////////////////
		// Check, if a cards images exists on the server.
		/////////////////////////////////////////////////////////		
		public static function checkCardsAnimationsFileExists($filename_with_path) {				
				if(file_exists($filename_with_path)) {
						return true;
				}
				
				return false;
		}
		
		/////////////////////////////////////////////////////////
		// Check, if a cards wrong images exists on the server.
		/////////////////////////////////////////////////////////		
		public static function checkCardsWrongAnimationsFileExists($filename_with_path) {				
				if(file_exists($filename_with_path)) {
						return true;
				}
				
				return false;
		}
		
		/////////////////////////////////////////////////////////
		// This function loads the titles of a card,
		// ordered by language id.
		/////////////////////////////////////////////////////////
		public static function loadCardsTitlesByCardsId($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT ' .
								'* ' .
						'FROM ' . $db->table('memory_gamedata_cards_titles') . ' ' .
						'WHERE gamedata_cards_id = :gamedata_cards_id ' .
						'ORDER BY language_id;'
				);
				$db->bind('gamedata_cards_id', (int)$id);
				$result = $db->execute();
				
				$retval = array();
				
				while($result->next()) {
						$retval[] = $result->fetchArrayAssoc();
				}

				return $retval;
		}
		
		/////////////////////////////////////////////////////////
		// Load an entry by id.
		/////////////////////////////////////////////////////////
		public static function loadCardById($id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * ' .
						'FROM ' . $db->table('memory_gamedata_cards') . ' ' .
						'WHERE ' .
								'id = :id'
				);
				$db->bind(':id', (int)$id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				
				if(false === $tmp) {
						return false;
				}
				
				$tmp['titles'] = cMemorygamedata::loadCardsTitlesByCardsId($id);
				$tmp['images'] = cMemorygamedata::loadCardsImagesByCardsId($id);
				$tmp['animations'] = cMemorygamedata::loadCardsAnimationsByCardsId($id);
				$tmp['wrong_animations'] = cMemorygamedata::loadCardsWrongAnimationsByCardsId($id);
				
				return $tmp;
		}
		
		/////////////////////////////////////////////////////////
		// Save a title..
		// Creates or updates a title..
		/////////////////////////////////////////////////////////
		public static function saveTitle($id, $datalanguage_id, $title1, $title2) {
				$tmp = cMemorygamedata::loadCardsTitleByIdAndDatalanguageId($id, $datalanguage_id);
				
				if(false === $tmp) {
						cMemorygamedata::createCardsTitleInDb($id, $datalanguage_id, $title1, $title2);
				} else {
						cMemorygamedata::updateCardsTitleInDb($id, $datalanguage_id, $title1, $title2);
				}
		}
		
		/////////////////////////////////////////////////////////
		// Update a cards title in the database.
		/////////////////////////////////////////////////////////
		public static function updateCardsTitleInDb($id, $datalanguage_id, $title1, $title2) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('memory_gamedata_cards_titles') . ' SET ' .
						'title1 = :title1 ' .
						'WHERE ' .
								'gamedata_cards_id = :id AND ' .
								'language_id = :language_id;'
				);
				$db->bind(':title1', $title1);
				$db->bind(':id', (int)$id);
				$db->bind(':language_id', (int)$datalanguage_id);
				$result = $db->execute();
				
				return $id;
		}
		
		/////////////////////////////////////////////////////////
		// Create a cards title in the database.
		/////////////////////////////////////////////////////////
		public static function createCardsTitleInDb($id, $datalanguage_id, $title1, $title2) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('memory_gamedata_cards_titles') . ' ' .
								'(gamedata_cards_id, language_id, title1) ' .
						'VALUES ' .
								'(:gamedata_cards_id, :language_id, :title1);'
				);
				$db->bind(':gamedata_cards_id', (int)$id);
				$db->bind(':title1', $title1);
				$db->bind(':language_id', (int)$datalanguage_id);
				$result = $db->execute();
				
				return $db->insertId();
		}
		
		/////////////////////////////////////////////////////////
		// Get a specific cards title in a specific language from database.
		/////////////////////////////////////////////////////////
		public static function loadCardsTitleByIdAndDatalanguageId($id, $datalanguage_id) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('memory_gamedata_cards_titles') . ' ' .
						'WHERE ' .
								'gamedata_cards_id = :id AND ' .
								'language_id = :language_id;'
				);
				$db->bind(':id', (int)$id);
				$db->bind(':language_id', (int)$datalanguage_id);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				return $tmp;
		}
		
		///////////////////////////////////////////////////////////////////
		// Saves images data in database.
		///////////////////////////////////////////////////////////////////
		public static function saveCardsImagesDataInDb($id, $card_number, $file_extension) {
				//check if entry exists
				if(false === cMemorygamedata::getCardsImagesDataByIdAndCardNumber($id, $card_number)) {
						//create
						cMemorygamedata::createCardsImagesData($id, $card_number, $file_extension);
				} else {
						//update
						cMemorygamedata::updateCardsImagesData($id, $card_number, $file_extension);
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Load images data from database.
		///////////////////////////////////////////////////////////////////
		public static function getCardsImagesDataByIdAndCardNumber($id, $card_number) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('memory_gamedata_cards_images') . ' ' .
						'WHERE ' .
								'gamedata_cards_id = :id AND ' .
								'card_number = :card_number;'
				);
				$db->bind(':id', (int)$id);
				$db->bind(':card_number', (int)$card_number);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				return $tmp;
		}
		
		/////////////////////////////////////////////////////////
		// Update a cards title in the database.
		/////////////////////////////////////////////////////////
		public static function updateCardsImagesData($id, $card_number, $file_extension) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('memory_gamedata_cards_images') . ' SET ' .
								'file_extension = :file_extension ' .
						'WHERE ' .
								'gamedata_cards_id = :id AND ' .
								'card_number = :card_number;'
				);
				$db->bind(':file_extension', $file_extension);
				$db->bind(':id', (int)$id);
				$db->bind(':card_number', (int)$card_number);
				$result = $db->execute();
				
				return $id;
		}
		
		/////////////////////////////////////////////////////////
		// Create a cards title in the database.
		/////////////////////////////////////////////////////////
		public static function createCardsImagesData($id, $card_number, $file_extension) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('memory_gamedata_cards_images') . ' ' .
								'(gamedata_cards_id, card_number, file_extension) ' .
						'VALUES ' .
								'(:gamedata_cards_id, :card_number, :file_extension);'
				);
				$db->bind(':gamedata_cards_id', (int)$id);
				$db->bind(':card_number', (int)$card_number);
				$db->bind(':file_extension', $file_extension);
				$result = $db->execute();
				
				return $db->insertId();
		}
		
		///////////////////////////////////////////////////////////////////
		// Saves animations data in database.
		///////////////////////////////////////////////////////////////////
		public static function saveCardsAnimationsDataInDb($id, $card_number, $file_extension) {
				//check if entry exists
				if(false === cMemorygamedata::getCardsAnimationsDataByIdAndCardNumber($id, $card_number)) {
						//create
						cMemorygamedata::createCardsAnimationsData($id, $card_number, $file_extension);
				} else {
						//update
						cMemorygamedata::updateCardsAnimationsData($id, $card_number, $file_extension);
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Saves wrong animations data in database.
		///////////////////////////////////////////////////////////////////
		public static function saveCardsWrongAnimationsDataInDb($id, $card_number, $file_extension) {
				//check if entry exists
				if(false === cMemorygamedata::getCardsWrongAnimationsDataByIdAndCardNumber($id, $card_number)) {
						//create
						cMemorygamedata::createCardsWrongAnimationsData($id, $card_number, $file_extension);
				} else {
						//update
						cMemorygamedata::updateCardsWrongAnimationsData($id, $card_number, $file_extension);
				}
		}
		
		///////////////////////////////////////////////////////////////////
		// Load animations data from database.
		///////////////////////////////////////////////////////////////////
		public static function getCardsAnimationsDataByIdAndCardNumber($id, $card_number) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('memory_gamedata_cards_animations') . ' ' .
						'WHERE ' .
								'gamedata_cards_id = :id AND ' .
								'card_number = :card_number;'
				);
				$db->bind(':id', (int)$id);
				$db->bind(':card_number', (int)$card_number);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				return $tmp;
		}
		
		///////////////////////////////////////////////////////////////////
		// Load wrong animations data from database.
		///////////////////////////////////////////////////////////////////
		public static function getCardsWrongAnimationsDataByIdAndCardNumber($id, $card_number) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'SELECT * FROM ' . $db->table('memory_gamedata_cards_wrong_animations') . ' ' .
						'WHERE ' .
								'gamedata_cards_id = :id AND ' .
								'card_number = :card_number;'
				);
				$db->bind(':id', (int)$id);
				$db->bind(':card_number', (int)$card_number);
				$result = $db->execute();
				
				$tmp = $result->fetchArrayAssoc();
				return $tmp;
		}
		
		/////////////////////////////////////////////////////////
		// Update a cards animations data entry in the database.
		/////////////////////////////////////////////////////////
		public static function updateCardsAnimationsData($id, $card_number, $file_extension) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('memory_gamedata_cards_animations') . ' SET ' .
								'file_extension = :file_extension ' .
						'WHERE ' .
								'gamedata_cards_id = :id AND ' .
								'card_number = :card_number;'
				);
				$db->bind(':file_extension', $file_extension);
				$db->bind(':id', (int)$id);
				$db->bind(':card_number', (int)$card_number);
				$result = $db->execute();
				
				return $id;
		}
		
		/////////////////////////////////////////////////////////
		// Update a cards animations data entry in the database.
		/////////////////////////////////////////////////////////
		public static function updateCardsWrongAnimationsData($id, $card_number, $file_extension) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'UPDATE ' . $db->table('memory_gamedata_cards_wrong_animations') . ' SET ' .
								'file_extension = :file_extension ' .
						'WHERE ' .
								'gamedata_cards_id = :id AND ' .
								'card_number = :card_number;'
				);
				$db->bind(':file_extension', $file_extension);
				$db->bind(':id', (int)$id);
				$db->bind(':card_number', (int)$card_number);
				$result = $db->execute();
				
				return $id;
		}
		
		/////////////////////////////////////////////////////////
		// Create a cards animations data entry in the database.
		/////////////////////////////////////////////////////////
		public static function createCardsAnimationsData($id, $card_number, $file_extension) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('memory_gamedata_cards_animations') . ' ' .
								'(gamedata_cards_id, card_number, file_extension) ' .
						'VALUES ' .
								'(:gamedata_cards_id, :card_number, :file_extension);'
				);
				$db->bind(':gamedata_cards_id', (int)$id);
				$db->bind(':card_number', (int)$card_number);
				$db->bind(':file_extension', $file_extension);
				$result = $db->execute();
				
				return $db->insertId();
		}
		
		/////////////////////////////////////////////////////////
		// Create a cards animations data entry in the database.
		/////////////////////////////////////////////////////////
		public static function createCardsWrongAnimationsData($id, $card_number, $file_extension) {
				$db = core()->get('db');
				$db->useInstance('systemdb');
				$db->setQuery(
						'INSERT INTO ' . $db->table('memory_gamedata_cards_wrong_animations') . ' ' .
								'(gamedata_cards_id, card_number, file_extension) ' .
						'VALUES ' .
								'(:gamedata_cards_id, :card_number, :file_extension);'
				);
				$db->bind(':gamedata_cards_id', (int)$id);
				$db->bind(':card_number', (int)$card_number);
				$db->bind(':file_extension', $file_extension);
				$result = $db->execute();
				
				return $db->insertId();
		}
		
		/////////////////////////////////////////////////////////
		// Creates a randomized array of cards with text or image.
		/////////////////////////////////////////////////////////
		public static function randomizeGameDataAsCards($data) {
				$retval = array();
				
				//build an array of pairs.
				foreach($data as $d) {
						$retval[] = array(
								'card_type' => 'text',
								'card_data' => $d
						);
						$retval[] = array(
								'card_type' => 'image',
								'card_data' => $d
						);
				}
				
				return $retval;
				
				//and now randomize the array
			
		}
		
		/////////////////////////////////////////////////////////
		// Creates a randomized array of cards with text or image.
		/////////////////////////////////////////////////////////
		public static function alternateCardsTextAndImage($data) {
				$current_card_type = 'text';
				$i = 0;
			
				$retval = array();
				
				while(count($data) > 0) {
						foreach($data as $index => $item) {
								if($item['card_type'] == $current_card_type) {
										$retval[$i] = $item;
										unset($data[$index]);
										$i++;
										break;
								}
						}
						
						if($current_card_type == 'text') {
								$current_card_type = 'image';
						} else {
								$current_card_type = 'text';
						}
				}
				
				return $retval;
		}
		
}

?>