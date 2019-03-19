
var InstagramView = Class.create();
InstagramView.prototype = {
	initialize:function(parent,url){
		this.parent=parent;
		this.url=url;
	},
	
	show:function(e){
		console.log("On show");
		var id = jQuery(e).attr('media-id');
		var url = this.url+'/id/'+id;
		console.log(url);
		var selector = jQuery("#details-insta-"+id);
		var username = selector.attr('data-user-name');
		var img = selector.attr('data-img-url');
		var cDate = selector.attr('data-create-date');
		var shareUrl = selector.attr('data-share-url');
		shareUrl = shareUrl+id;
		
		var facebook = 'http://www.facebook.com/sharer/sharer.php?s=100&amp;p[url]='+shareUrl+'/&amp;p[image]='+img;
		var envelope = 'mailto:?subject=Thought you might be into this @maria_tash Instagram!&amp;body='+shareUrl;
		var twitter = 'http://www.twitter.com/share?url='+shareUrl+'/&amp;related=maria_tash&amp;text=Shop this Instagram from @maria_tash';
		var pinterest = 'http://www.pinterest.com/pin/create/button/?url='+shareUrl+'/&amp;media='+img+'&amp;description=Shop this Instagram from @maria_tash';
		
		jQuery("#insta-facebook").attr('href',facebook);
		jQuery("#insta-envelope").attr('href',envelope);
		jQuery("#insta-twitter").attr('href',twitter);
		jQuery("#insta-pinterest").attr('href',pinterest);
		jQuery("#insta-share-link").attr('href',shareUrl);
		
		
		jQuery(".fs-post-info a").attr('href',url);
		
		var caption = jQuery('#insta-caption-'+id).html();
		var points = jQuery('#insta-product-mark-'+id).html();
		var products = jQuery('#insta-product-details-'+id).html();
		
		jQuery("#fs-detail-response").attr("data-resource-url",username);
		jQuery("#fs_main_image").attr("src",img);
		jQuery("#point-mark").html(points);
		jQuery('#product-info').html(products);
		jQuery('.fs-detail-title').text(caption);
		jQuery('.fs-detail-date').text(cDate);
		
		if(jQuery(e).parent().next().hasClass('fs-entry-container') || jQuery(e).parent().next().hasClass('jcarousel-item')){
			jQuery('#fs-next-post').removeAttr("disabled");
			jQuery('#fs-next-post').removeClass('fs-button-inactive');
			var mediaId = jQuery(e).parent().next().find('.fancybox').attr('media-id');
			var mediaId1 = jQuery(e).parent().next().find('.fs-timeline-entry').attr('media-id');
			if(mediaId)
				jQuery('#fs-next-post').attr("media-id",mediaId);
			if(mediaId1)
                jQuery('#fs-next-post').attr("media-id",mediaId1);
		}else{
			jQuery('#fs-next-post').attr("disabled","disabled");
			jQuery('#fs-next-post').addClass('fs-button-inactive');
		}
		
		var temp;
		if(jQuery(e).parent().prev().hasClass('fs-entry-container') || jQuery(e).parent().prev().hasClass('jcarousel-item')){
			jQuery('#fs-prev-post').removeAttr("disabled");
			jQuery('#fs-prev-post').removeClass('fs-button-inactive');
            var mediaId = jQuery(e).parent().prev().find('.fancybox').attr('media-id');
            var mediaId1 = jQuery(e).parent().prev().find('.fs-timeline-entry').attr('media-id');
            if(mediaId)
                jQuery('#fs-prev-post').attr("media-id",mediaId);
            if(mediaId1)
                jQuery('#fs-prev-post').attr("media-id",mediaId1);
		}else{
			jQuery('#fs-prev-post').attr("disabled","disabled");
			jQuery('#fs-prev-post').addClass('fs-button-inactive');
		}
		//console.log(jQuery(e).parent().next().find('.fs-timeline-entry').attr('media-id'));
		
		
		jQuery('#showcase').show();
		
		jQuery('#showcase .fs-text-product').hover(function(){
			var id = jQuery(this).attr('data-link-id');
			var selector = jQuery('#showcase #fs_overlink_'+id);
			selector.css("opacity", "1");
			selector.find('#showcase .fs-overlink-text').css(
					{"opacity":"1","display":"block"});
	    }, function(){
	    	var id = jQuery(this).attr('data-link-id');
	    	var selector = jQuery('#showcase #fs_overlink_'+id);
	    	selector.css("opacity", "");
	    	selector.find('#showcase .fs-overlink-text').css(
					{"opacity":"","display":""});
		});

		jQuery('#showcase .fs-overlink').hover(function(){
			var id = jQuery(this).attr('data-link-id');
			console.log(id);
			var selector = jQuery('#showcase #fs_link_'+id);
			//selector.css("opacity", "1");
			selector.css(
					/*{"background-color":"#222","color":"#fff"}*/
					{"color":"red"});
	    }, function(){
	    	var id = jQuery(this).attr('data-link-id');
	    	var selector = jQuery('#showcase #fs_link_'+id);
	    	//selector.css("opacity", "");
	    	selector.css(
					{"background-color":"","color":""});
		});
		
		
		/*jQuery("#"+this.parent).load(url,function () {
			
		})*/
	}
	
	
}
