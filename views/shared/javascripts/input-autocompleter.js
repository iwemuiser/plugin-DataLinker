// Parse an item and create an title/value hash table with all the properties available
function getFields(results) {
    console.log(results);
    if(results.value == undefined) {
	    return results;
    }
    else{
        return results.value;
    }
}

function ajax_delay(str){
 setTimeout("str",2000);
}

function autoautomulti(){
    buttonrefresh();
    jQuery("textarea[name*='Elements[52]']").each( function( index, el){
        jQuery( el ).autocomplete({
    		minLength: 1,
    		source: function( request, response ) {
    			jQuery.ajax({
    			    beforeSend: function (request)
    				{
    					request.setRequestHeader("Accept", "application/json;odata=verbose;charset=utf-8");
    				},
    				url: "http://bookstore.ewi.utwente.nl/afact/motif.json?style=list&code=" + request.term, //NO CROSS SITE SCRIPTING YET
//    				url: "http://127.0.0.1/testing/resting/motif.json?style=list&code=" + request.term, 
    				dataType: "json",
    				success: function( data ) {
    					response( jQuery.map( data.data, function( item ) {
    						return {
                                fields: getFields(item)
    						}
    					}));
    				},
    				error: function( data ) {
    				    return "None found";
    				}
    			});
    		},
    		focus: function( event, ui ) {
                jQuery( el ).val( ui.item.fields.id );
                return false;
            },
            // Run this when the item is selected
            select: function( event, ui ) {
                jQuery( el ).val( ui.item.fields.id );
                return false;   
    		},
    		appendTo: jQuery('#menu-container')
    	}).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
    	    if (jQuery( el )[0].value.toUpperCase() == item.fields.id){
    		    return jQuery("<li style='color:green'>").append("<a>" + item.fields.id + " - " + item.fields.title + "</a>")
    			.appendTo( ul );
    	    }
    	    else{
    		    return jQuery("<li>").append( "<a>" + item.fields.id + " - " + item.fields.title + "</a>")
    			.appendTo( ul );
    		}
        };
    });
};


function buttonrefresh(){
    jQuery('.add-element').click(function(){
        setTimeout(autoautomulti,400);
    });
    jQuery('#item-type').change(function(){
        setTimeout(autoautomulti,1000);
    });
}

jQuery(document).ready(function() {
    buttonrefresh()
});