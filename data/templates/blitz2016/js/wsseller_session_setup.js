$(function() {
		$('#session_id').on('change', function() {
				var email = $(this).find(':selected').attr('data-attr-email');
				$('#email').val(email);
				
				var phone = $(this).find(':selected').attr('data-attr-phone');
				$('#phone').val(phone);
		});
});