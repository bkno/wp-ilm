jQuery(function($) {
    
	/*$('#et_pb_toggle_builder').text('Launch Page Builder');*/
	
	/* Hide Divi project CTP from post type switcher options */
    $('#post-type-select option').each(function() {
        if ($(this).val() == 'project') {
            $(this).hide();
        }
    });

});