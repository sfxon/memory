$(function() {
		var right_answers = 0;
		var wrong_answers = 0;
		var card_count = 0;
		var logging = false;
		var second_chance = $('#second_chance').val();
		var total_card_count = $('#total_card_count').val();
		var card_change_to_right_time = 6;
	
		//////////////////////////////////////////////////////////////////////////
		// Check the cards, if there is already one activated.
		//////////////////////////////////////////////////////////////////////////
		function check_cards_for_activated_card() {
				mvlog('check_cards_for_activated_card');
				
				if($('.card.activated').length) {
						return true;
				}
				
				return false;	//Return the ID..
				//return $('.card.activated');
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Klicked card action handler
		//////////////////////////////////////////////////////////////////////////
		function mv_mark_cards_as_done(wrong_or_wright, id_text, id_image) {
				mvlog('mv_mark_cards_as_done');
				
				$('#card-text-' + id_text).addClass('cleared');		//Set the text card as cleared
				$('#card-text-' + id_text + ' .card_text_info').hide();		//Hide the text on the card
				$('.card.activated').removeClass('activated');		//REmove the text "activated"
				
				$('#card-image-' + id_image).addClass('cleared');
				
				if(wrong_or_wright == 'wright') {
						//right answer
						$('#card-text-' + id_text + ' img').attr('src', 'data/templates/blitz2016/images/empty-text-background.png');		//set card transparent
						$('#card-image-' + id_image + ' img').attr('src', 'data/templates/blitz2016/images/empty-text-background.png');		//set card transparent
				} else {
						//wrong answer
						$('#card-text-' + id_text + ' img').attr('src', 'data/templates/blitz2016/images/bg_wall.png');		//set card as wall
						$('#card-image-' + id_image + ' img').attr('src', 'data/templates/blitz2016/images/bg_wall.png');		//set card transparent
				}
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Klicked card action handler
		//////////////////////////////////////////////////////////////////////////
		function mv_end_game_with_information_text() {
				$('#load-waiter').fadeIn(400, function() {
						final_action = $('#final_action_end_game_with_information').val();
						window.location = final_action;
				});
		}
		
		
		function clicked_card_action(item) {
				mvlog('clicked_card_action');
				
				if(check_cards_for_activated_card() === true) {
						//get the id of the new activated card..
						var tmp = $('.card.activated');
						
						var img_item = '';
						var txt_item = '';
						
						var prev_activated = new Array();
						prev_activated['right_answer'] = $(tmp).attr('data-attr-card-id-right-answer');
						prev_activated['type'] = $(tmp).attr('data-attr-card-type');
						prev_activated['id'] = $(tmp).attr('data-attr-card-id');
						prev_activated['item']= tmp;
						
						var new_activated = new Array();
						new_activated['right_answer'] = $(item).attr('data-attr-card-id-right-answer');
						new_activated['type'] = $(item).attr('data-attr-card-type');
						new_activated['id'] = $(item).attr('data-attr-card-id');
						new_activated['item'] = item;
						
						//check if id matches wrong or right of the other card..
						if(prev_activated['type'] == new_activated['type']) {
								//if the cards are of the same type - do no further action
								$('.card.activated').removeClass('activated');
								mv_reset_image_card(tmp);
								mv_reset_image_card(item);
								mv_reset_text_card(tmp);
								mv_reset_text_card(item);
								return false;
						}
						
						//check the type of the prev_activated_answer
						if(prev_activated['type'] == 'image') {
								img_item = prev_activated;
								txt_item = new_activated;
						} else {
								img_item = new_activated;
								txt_item = prev_activated;
						}
						
						if(img_item['right_answer'] == txt_item['id']) {		//This is the right answer
								if(second_chance == 1) {
										mv_mark_cards_as_done('wright', txt_item['id'], img_item['id']);
										right_answers++;
										card_count++;
										mv_clear_timeouts(img_item['item']);
										mv_clear_timeouts(txt_item['item']);
								} else {
										mv_end_game_with_information_text();
										return false;
								}
						} else if(img_item['right_answer'] == txt_item['right_answer']) {	//This is the faked answer
								if(second_chance == 0) {
										mv_mark_cards_as_done('wrong', txt_item['id'], img_item['id']);
										wrong_answers++;
										card_count++;
										mv_clear_timeouts(img_item['item']);
										mv_clear_timeouts(txt_item['item']);
								}
						} else {		//This is totally wrong..
								/*$('#card-text-' + txt_item['id']).addClass('cleared');*/
								$('.card.activated').removeClass('activated');
								mv_reset_image_card(img_item['item']);
								mv_reset_text_card(txt_item['item']);
						}
						
						//if we got the maximum number of cards.
						if(card_count == total_card_count) {
								mv_finish_action();
						}
						
						return false;
				}
				
				$(item).addClass('activated');
				
				//clear the animation timeout
				clearTimeout($.data(item, "timer"));
				/*clearTimeout($.data(mv_item, "timerwright"));*/
		}

		//////////////////////////////////////////////////////////////////////////
		// Spiel beenden.
		//////////////////////////////////////////////////////////////////////////		
		function end_game_wrong() {
				$('#load-waiter').fadeIn(400, function() {
						final_action = $('#final_action_wrong').val();
						window.location = final_action;
				});
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Finale Aktion..
		//////////////////////////////////////////////////////////////////////////
		function mv_finish_action() {
				var final_action = '';
				
				//do the final action, based on the amount of true or wrong answers..
				if(wrong_answers > (total_card_count / 2)) {
						setTimeout(function(){ end_game_wrong() }, 2500);
					
						
				} else {
						final_action = $('#final_action_right').val();
						window.location = final_action;
				}
				
				
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Karte aktivieren
		//////////////////////////////////////////////////////////////////////////
		$('.card').on('click', function() {
				mvlog('on(click)');
			
				if($(this).hasClass('cleared')) {
						return;
				}
				
				clicked_card_action(this);
		});
		
		//////////////////////////////////////////////////////////////////////////
		// Diese Funktion zeigt das richtige Bild an..
		//////////////////////////////////////////////////////////////////////////
		function handler_change_image_to_wright_image(mv_item) {
				mvlog('handler_change_image_to_wright_image');
				
				var data_type = $(mv_item).attr('data-attr-card-type');
				
				if(data_type != 'image') {
						return;
				}
				
				var animation_url = $(mv_item).attr('data-attr-animation');
				var identifier = '#card-image-' + $(mv_item).attr('data-attr-card-id') + ' img';
				$(identifier).attr('src', animation_url);
				
				if(second_chance == 1) {
						$(mv_item).addClass('new_animated_once');
				}
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Handler für die Hover Animation der Text-Karte.
		//////////////////////////////////////////////////////////////////////////
		function handle_hover_event_text(mv_item) {
				var animation_url = $(mv_item).attr('data-attr-image-show');		//Das Hintergrundbild für die aufgedeckte Karte laden
				var identifier = '#card-text-' + $(mv_item).attr('data-attr-card-id');
				var identifier_img = identifier + ' img';
						
				$(identifier_img).attr('src', animation_url);
				$(identifier + ' .the-text').css('display', 'table');
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Handler für die Hover Animation
		// Diese Funktion zeigt das falsche Bild an.
		//////////////////////////////////////////////////////////////////////////
		function handle_hover_event(mv_item) {
				mvlog('handle_hover_event');
				
				//Wenn die Karte noch nicht geklärt wurde.
				if($(mv_item).hasClass('cleared')) {
						return;
				}
			
				//Wenn das eine Bildkarte ist..
				var data_type = $(mv_item).attr('data-attr-card-type');
				
				if(data_type != 'image') {
						handle_hover_event_text(mv_item);
						return;
				}
				
				var animation_url = $(mv_item).attr('data-attr-wrong-animation');
				var identifier = '#card-image-' + $(mv_item).attr('data-attr-card-id') + ' img';
				
				//now start the hover timer nr. 2 - that replaces this image after another 10 seonds
				//if(second_chance == 1) {		//start it only, if this is not the second run!*/
						$.data(mv_item, "timerwright", setTimeout($.proxy(function() {
								handler_change_image_to_wright_image(mv_item);
						}, this), 8000));
				//}
				
				if($(mv_item).hasClass('new_animated_once')) {
						animation_url = $(mv_item).attr('data-attr-animation');
				}
				
				$(identifier).attr('src', animation_url);
				//}
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Handler zum zurücksetzen einer Animation (wenn diese nicht aktiv ist!)
		//////////////////////////////////////////////////////////////////////////
		function mv_reset_text_card_if_not_activated(mv_item) {
				mvlog('mv_reset_image_card_if_not_activated');
				
				//Wenn diese Karte angeklickt wurde - die Animation nicht stoppen!
				if($(mv_item).hasClass('activated')) {
						clearTimeout($.data(mv_item, "timerwright")); //Remove the wright timer (we moved out!)
						return;
				}
				
				mv_reset_text_card(mv_item);
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Handler zum zurücksetzen einer Animation (wenn diese nicht aktiv ist!)
		//////////////////////////////////////////////////////////////////////////
		function mv_reset_image_card_if_not_activated(mv_item) {
				mvlog('mv_reset_image_card_if_not_activated');
				
				//Wenn das eine Bildkarte ist..
				var data_type = $(mv_item).attr('data-attr-card-type');
				
				if(data_type != 'image') {
						mv_reset_text_card_if_not_activated(mv_item);
						return;
				}
				
				//Wenn diese Karte angeklickt wurde - die Animation nicht stoppen!
				if($(mv_item).hasClass('activated')) {
						clearTimeout($.data(mv_item, "timerwright")); //Remove the wright timer (we moved out!)
						return;
				}
				
				mv_reset_image_card(mv_item);
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Handler zum zurücksetzen einer Animation.
		//////////////////////////////////////////////////////////////////////////
		function mv_reset_text_card(mv_item) {
				mvlog('mv_reset_text_card');
				
				mv_clear_timeouts(mv_item);
				
				//reset the image..
				var data_type = $(mv_item).attr('data-attr-card-type');
				
				if(data_type != 'text') {
						return;
				}
				
				var animation_url = $(mv_item).attr('data-attr-image');
				var identifier = '#card-text-' + $(mv_item).attr('data-attr-card-id');
				var identifier_image = identifier + ' img';
				$(identifier_image).attr('src', animation_url);
				$(identifier + ' .the-text').hide();
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Handler zum zurücksetzen einer Animation.
		//////////////////////////////////////////////////////////////////////////
		function mv_reset_image_card(mv_item) {
				mvlog('mv_reset_image_card');
				
				mv_clear_timeouts(mv_item);
				
				//reset the image..
				var data_type = $(mv_item).attr('data-attr-card-type');
				
				if(data_type != 'image') {
						return;
				}
				
				var animation_url = $(mv_item).attr('data-attr-image');
				var identifier = '#card-image-' + $(mv_item).attr('data-attr-card-id') + ' img';
				$(identifier).attr('src', animation_url);
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Handler zum zurücksetzen einer Animation.
		//////////////////////////////////////////////////////////////////////////
		function mv_clear_timeouts(mv_item) {
				mvlog('mv_clear_timeouts');
				mvlog(mv_item);
				
				//clear the timeouts
				clearTimeout($.data(mv_item, "timer"));
				clearTimeout($.data(mv_item, "timerwright"));
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Logging
		//////////////////////////////////////////////////////////////////////////
		function mvlog(text) {
				if(true == logging) {
						console.log(text);
				}
		}
		
		//////////////////////////////////////////////////////////////////////////
		// Handler für die Hover Animation setzen
		//////////////////////////////////////////////////////////////////////////
		$(function() {
				$(".card").hover(function() {
						mvlog('hover' + this);
						
						var mv_item = this;
						
						$.data(this, "timer", setTimeout($.proxy(function() {
								handle_hover_event(mv_item);
						}, this), card_change_to_right_time));
				}, function() {
						mvlog('unhover' + this);
						
						//Wenn diese Karte noch nicht geklärt wurde.
						if($(this).hasClass('cleared')) {
								return;
						}
						
						mv_reset_image_card_if_not_activated(this);
						mv_reset_text_card_if_not_activated(this);
				});
		});
		
		
});