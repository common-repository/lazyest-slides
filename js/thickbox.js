function lazyestSlidesThickBox() {
	
	jQuery(window).load(function()  {
		var maxWidth = parseInt( parent.jQuery('#TB_overlay').width() ) -20;			
		var maxHeight = parseInt( parent.jQuery('#TB_overlay').height() );
		var imgWidth = parseInt( jQuery( '#lazyest_thickbox #img' ).width() ) + 16; // add 16 for padding
		var imgHeight = parseInt( jQuery( '#lazyest_thickbox #img' ).height() ) +16;
		var otherHeight = parseInt( jQuery('#lazyest_thickbox h1').height() ) +			
			parseInt( jQuery('#lazyest_thickbox #description').height() ) +
			parseInt( jQuery('#lazyest_thickbox #count').height() ) + 80;								
		var elementsHeight = otherHeight + imgHeight;
		if ( imgWidth > maxWidth ) {
			resizeWidth = maxWidth;
			resizeHeight = Math.round( ( resizeWidth / imgWidth ) * imgHeight );
			imgHeight = resizeHeight;
			jQuery( '#lazyest_thickbox #img' ).css({ width: resizeWidth+'px', height: resizeHeight+'px' });
			elementsHeight = imgHeight + otherHeight;			
			imgWidth = resizeWidth;
		}				
		if ( elementsHeight > maxHeight ) {				
			elementsHeight = maxHeight;
			resizeHeight = maxHeight - otherHeight;
			resizeWidth = Math.round( ( resizeHeight / imgHeight ) * ( imgWidth ) );
			imgWidth = resizeWidth;
			jQuery( '#img' ).css({height: resizeHeight+'px', width: resizeWidth+'px' } );								
			elementsHeight = otherHeight + resizeHeight;			
			imgWidth = resizeWidth;
		}										
		var animateDuration = Math.round( Math.max( imgWidth, elementsHeight, 600 )  / 2 );		
		var intLeft = 0 - Math.round( imgWidth+16 ) / 2;
		var intTop = 0 - Math.round( elementsHeight / 2 );				
		if ( parent.jQuery('#wpadminbar').length )
			intTop = intTop + 28;		
		var newWidth = (imgWidth+36)+'px';	
		var newHeight = elementsHeight+'px';
		var newTop = intTop+'px';
		var newLeft = intLeft+'px';			
		jQuery(function() {
			parent.jQuery('#TB_iframeContent').animate( { width: newWidth, height: newHeight }, { duration: animateDuration, queue: false } );
			parent.jQuery('#TB_window').animate( { width: newWidth, marginLeft: newLeft, height: newHeight, marginTop: newTop }, { duration: animateDuration, queue: false } );				
			jQuery('#lazyest_thickbox img').animate( { opacity: 1 }, { duration: animateDuration, queue: false } );				
		});
		jQuery('#lazyest_thickbox div.nav a').width('50%').height('100%');
	});
	
	jQuery('#lazyest_thickbox div.nav a').click( function() {
		jQuery('#lazyest_thickbox img').animate( { opacity: 0 }, { duration: 400, queue: false } );	
		return true;
	});	
	
	jQuery('#lazyest_thickbox #add-reply a').click(function() {
		parent.window.location = jQuery('#lazyest_thickbox #add-reply a').attr('href');		
		parent.tb_remove();
	});
};

