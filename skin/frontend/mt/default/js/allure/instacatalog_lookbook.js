jQuery(document).ready(function(){
		jQuery('.fs-text-product').hover(function(){
			var id = jQuery(this).attr('data-link-id');
			var selector = jQuery('#fs_overlink_'+id);
			selector.css("opacity", "1");
			selector.find('.fs-overlink-text').css(
					{"opacity":"1","display":"block"});
	    }, function(){
	    	var id = jQuery(this).attr('data-link-id');
	    	var selector = jQuery('#fs_overlink_'+id);
	    	selector.css("opacity", "");
	    	selector.find('.fs-overlink-text').css(
					{"opacity":"","display":""});
		});
	});