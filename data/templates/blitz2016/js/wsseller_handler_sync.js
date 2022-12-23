var session_handler_url = '';
var live_session_id = '';
var sync_time = 1000;		//Time in millicseconds! (second * 1000)
var sync_counter = 0;

$(function() {
		init_customer_view_container_col();
		mv_wsseller_start_sync();
		init_session_reset_button();
});

/////////////////////////////////////////////////////////////
// Initialisiere Session Reset Button
/////////////////////////////////////////////////////////////
function init_session_reset_button() {
		$('#seller-logout-customer-action').on('click', function() {
				sync_counter = 0;
		
				var params = { live_session_id: live_session_id, action: "ajax_logout_customer" };
				params = $.param(params);
				
				$.ajax({
						method: "POST",
						url: session_handler_url + '?' + params,
						data: { sync_counter: sync_counter }
				}) .done(function( msg ) {
						/*mv_wsseller_handle_call_result(msg, session_handler_url + '?' + params);*/
						var container_html = '<iframe src="' + $('#template_url').val() + 'login/index.html?wcs=' + live_session_id + '"></iframe>';
						$('#customer-view-container').attr('data-attr-state-json', 'customer_login');
						$('#customer-view-container').html(container_html);
				});
		});
}

/////////////////////////////////////////////////////////////
// Initialisiere die Höhe des Kunden-Fensters.
/////////////////////////////////////////////////////////////
function init_customer_view_container_col() {
		var offset = $('#customer-view-container-col').offset();
		var offset_top = offset.top;
		var window_height = $( window ).height();
		$('#customer-view-container-col').css('height', (window_height - offset_top) + 'px');
}

/////////////////////////////////////////////////////////////
// Starte Synchronisierung..
/////////////////////////////////////////////////////////////
function mv_wsseller_start_sync() {
		mv_wsseller_init_debug_actions();
		mv_debug_set_info('Debug-Aktionen initialisiert.');
		
		mv_wsseller_init_sync();
		mv_debug_add_info('Synchronisation gestartet.');
}

/////////////////////////////////////////////////////////////
// Init debug actions.
/////////////////////////////////////////////////////////////
function mv_wsseller_init_debug_actions() {
		$('#seller-debug-show').on('click', function() {
				$('#seller-debug-information').toggle();
		});
}

/////////////////////////////////////////////////////////////
// Add debug text.
/////////////////////////////////////////////////////////////
function mv_debug_add_info(text) {
		$('#seller-debug-information').append('<br />' + text);
}

/////////////////////////////////////////////////////////////
// Set debug text. (clears all other text..)
/////////////////////////////////////////////////////////////
function mv_debug_set_info(text) {
		$('#seller-debug-information').html(text);
}

/////////////////////////////////////////////////////////////
// Init seller url
/////////////////////////////////////////////////////////////
function mv_init_seller_url() {
		session_handler_url = $('#session_handler_url').val();
}

/////////////////////////////////////////////////////////////
// Init seller url
/////////////////////////////////////////////////////////////
function mv_init_live_session_id() {
		live_session_id = $('#live_session_id').val();
}

/////////////////////////////////////////////////////////////
// Init seller url
/////////////////////////////////////////////////////////////
function mv_wsseller_init_sync() {
		mv_init_seller_url();
		mv_init_live_session_id();
		
		//start sync for the first time..
		mv_wsseller_sync();
}

/////////////////////////////////////////////////////////////
// Init seller url
/////////////////////////////////////////////////////////////
function mv_wsseller_sync() {
		mv_wsseller_call_home();		//The real call home.
		setTimeout(mv_wsseller_sync, sync_time);
}

/////////////////////////////////////////////////////////////
// Calling home via ajax. Gathering information about
// the current session.
/////////////////////////////////////////////////////////////
function mv_wsseller_call_home() {
		sync_counter += 1;
		
		var params = { live_session_id: live_session_id, action: "ajax_sync" };
		params = $.param(params);
		
		$.ajax({
				method: "POST",
				url: session_handler_url + '?' + params,
				data: { sync_counter: sync_counter }
		}) .done(function( msg ) {
				mv_wsseller_handle_call_result(msg, session_handler_url + '?' + params);
		});
}

/////////////////////////////////////////////////////////////
// Tries to parse the result of the ajax call (expects json).
/////////////////////////////////////////////////////////////
function mv_wsseller_handle_call_result(data, url) {
		var array_data = '';
		
		try {
				array_data = $.parseJSON(data);
				mv_wsseller_handle_data_array(array_data, data, url);				
		}catch(err) {
				mv_debug_add_info('Fehler beim Aufruf einer URL. Es wurde kein JSON String von der Seite zurückgegeben.');
				mv_debug_add_info( err.toString() );
				mv_debug_add_info('<hr />');
				mv_debug_add_info(data.toString());
				mv_debug_add_info('<hr />');
				mv_debug_add_info(url.toString());
		}
}

/////////////////////////////////////////////////////////////
// Handles the result of the ajax call.
// Checks the status.
// Processes all needed operations.
/////////////////////////////////////////////////////////////
function mv_wsseller_handle_data_array(array_data, data, url) {
		if(false === mv_wsseller_check_data_array(array_data, data, url)) {
				return false;
		}
		
		//Switch through the success code.. This triggers some basic actions.
		switch(array_data['success_code']) {
				case 'customer_login':				//Customer Login Screen.
						mv_show_customer_login_screen(array_data);
						break;
				case 'customer_logged_in':		//This is the first screen in the machines states.
						mv_show_customer_logged_in_screen(array_data);
						break;
				case 'state':								//Handle pages and actions.
						mv_handle(array_data);
						break;
		}
		
		mv_format_enduser_frame(array_data);
}
	
/////////////////////////////////////////////////////////////
// Handles the result of the ajax call.
// Checks the status.
// Processes all needed operations.
/////////////////////////////////////////////////////////////
function mv_wsseller_check_data_array(array_data, data, url) {
		//Check if we got an success code.
		if(array_data['success_code'] === undefined) {
				mv_debug_add_info('Es wurde kein Erfolgscode vom Ajax Call zurückgegeben.');
				mv_debug_add_info(array_data.toString());
				mv_debug_add_info(data.toString());
				mv_debug_add_info(url.toString());
				
				//check if we got an error code..
				if(array_data['error_message'] !== undefined) {
						alert('Es ist ein Fehler aufgetreten. Sollte der Fehler wiederholt auftreten, informieren Sie bitte einen Administrator: ' + arrray_data['error_message'].toString());
				}
				
				return false;
		}
}

/////////////////////////////////////////////////////////////
// Shows the customer login screen..
/////////////////////////////////////////////////////////////
function mv_show_customer_login_screen(array_data) {
		var state = $('#customer-view-container').attr('data-attr-state-json');
		
		if(state != 'customer_login') {
				var container_html = '<iframe src="' + array_data['message']['customer_login_url'] + '"></iframe>';
				$('#customer-view-container').attr('data-attr-state-json', 'customer_login');
				$('#customer-view-container').html(container_html);
		}
}

/////////////////////////////////////////////////////////////
// Shows the first screen..
/////////////////////////////////////////////////////////////
function mv_show_customer_logged_in_screen(array_data) {
		var state = $('#customer-view-container').attr('data-attr-state-json');
		
		if(state != 'customer_logged_in') {
				var container_html = '<iframe src="' + array_data['message']['customer_url'] + '"></iframe>';
				$('#customer-view-container').attr('data-attr-state-json', 'customer_logged_in');
				$('#customer-view-container').html(container_html);
		}
}

/////////////////////////////////////////////////////////////
// Formats the screen, that represents the session,
// as the customer sees it.
// It changes the width/height,
// and the scroll position.
/////////////////////////////////////////////////////////////
function mv_format_enduser_frame(array_data) {
		var final_screen_width_percent = 100;
		var user_screen_width = 1200;
		var user_screen_height = 800;
		
		if(array_data.message.user_screen_width != undefined) {
				var user_screen_width = parseInt(array_data.message.user_screen_width);
		}
		
		if(array_data.message.user_screen_height != undefined) {
				var user_screen_height = parseInt(array_data.message.user_screen_height);
		}
		
		var seller_screen_width = $('#customer-view-container').width();
				
		//Set width of the container to the width of the users container..
		$('#customer-view-container iframe').css('width', user_screen_width + 'px');
	
		//If this container is bigger than our container, calculate the difference!
		if(seller_screen_width < user_screen_width) {
				final_screen_width_percent = (100 / user_screen_width) * seller_screen_width;
		}
		
		//Now zoom!
		final_screen_width_percent_decimal = final_screen_width_percent / 100;
		
		/*$('#customer-view-container iframe').css('position', 'absolute');*/
		$('#customer-view-container iframe').css('-moz-transform-origin', '0 0');
		$('#customer-view-container iframe').css('zoom',final_screen_width_percent_decimal);
		$('#customer-view-container iframe').css('-moz-transform', 'scale(' + final_screen_width_percent_decimal + ')');
		$('#customer-view-container iframe').css('background-color', 'black');
		
		//Calculate the height:
		var user_screen_height = parseInt(array_data.message.user_screen_height);
		
		final_user_screen_height = (user_screen_height / 100) * final_screen_width_percent;
		
		$('#customer-view-container iframe').css('height', final_user_screen_height + 'px');
}

/////////////////////////////////////////////////////////////
// Handle a state machines state.
/////////////////////////////////////////////////////////////
function mv_handle(array_data) {
		var current_websites_state = $('#customer-view-container').attr('data-attr-state-json');
		
		if(array_data.message.state != current_websites_state) {
				mv_get_page(array_data.message.state);
		}
}

/////////////////////////////////////////////////////////////
// Handle a state machines state.
/////////////////////////////////////////////////////////////
function mv_get_page(state_id) {
		var url = $('#template_url').val();
		url += 'mein-logo-shop.html';

		var container_html = '<iframe src="' + url + '"></iframe>';
		$('#customer-view-container').attr('data-attr-state-json', state_id);
		$('#customer-view-container').html(container_html);
}