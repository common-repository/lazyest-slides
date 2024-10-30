jQuery(document).ready(function(){
	
	jQuery('label.select').click(function(){
		var labelFor = jQuery(this).attr('for');
		var inputName = jQuery('#'+labelFor).attr('name');
		jQuery("input[name='"+inputName+"']").each(function() {
			var inputId = jQuery(this).attr('id');
			var forLabel = jQuery("label[for='"+inputId+"']");						
			if ( forLabel.hasClass('selected') )
				forLabel.removeClass('selected');
			else
				forLabel.addClass('selected');
		});				
	});
	
});