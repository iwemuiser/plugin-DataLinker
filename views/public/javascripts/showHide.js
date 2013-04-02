function scrollToPosition(){
    jQuery(document).ready(function(){
    	// 'catTopPosition' is the amount of pixels #cat
    	// is from the top of the document
    	var catTopPosition = jQuery('#volksverhaal-item-type-metadata-text').offset().top;
    	// When #scroll is clicked
    	jQuery('#text-scroll').click(function(){
    		// Scroll down to 'catTopPosition'
    		jQuery('html, body').animate({scrollTop:catTopPosition - 50}, 'slow');
    		// Stop the link from acting like a normal anchor link
    		return false;
    	});
    });
}

function toggleSlides(){
    jQuery('.toggler').click(function(e){
        var id=jQuery(this).attr('id');
        var widgetId=id.substring(id.indexOf('-')+1,id.length);
        jQuery('#'+widgetId).slideToggle("fast");
        jQuery(this).toggleClass('sliderExpanded');
        jQuery('.closeSlider').click(function(){
            jQuery(this).parent().hide('slow');
            var relatedToggler='toggler-'+jQuery(this).parent().attr('id');
            jQuery('#'+relatedToggler).removeClass('sliderExpanded');
        });
    });
    
    jQuery('#slidetoggle.down').click(function(e){
        jQuery('.slider').slideUp(20);
        jQuery(this).hide();
        jQuery('#slidetoggle.up').show();
    });
    
    jQuery('#slidetoggle.up').click(function(e){
        jQuery('.slider').slideDown(20);
        jQuery(this).hide();
        jQuery('#slidetoggle.down').show();
    });
};
jQuery(function(){
    toggleSlides();
    scrollToPosition();
});