$('#confirm_quit_session').on('click', function(event) {
		if(confirm('Wollen Sie diese Sitzung wirklich beenden?')) {
				return true;
		}
		
		event.preventDefault();
		return false;
});