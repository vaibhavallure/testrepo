
var InstagramView = Class.create();
InstagramView.prototype = {
	initialize:function(parent,url){
		this.parent=parent;
		this.url=url;
	},
	
	show:function(e){
		var id = jQuery(e).attr('media-id');
		var url = this.url+'/id/'+id;

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
		
		var caption = jQuery('#insta-caption-'+id).html();
		var points = jQuery('#insta-product-mark-'+id).html();
		var products = jQuery('#insta-product-details-'+id).html();
		
		jQuery(".fs-post-info a").attr('href',url);
		
		jQuery("#fs-detail-response").attr("data-resource-url",username);
		jQuery("#fs_main_image").attr("src",img);
		jQuery("#point-mark").html(points);
		jQuery('#product-info').html(products);
		jQuery('.fs-detail-title').text(caption);
		jQuery('.fs-detail-date').text(cDate);
		
		if(jQuery(e).parent().next().hasClass('fs-entry-container')){
			jQuery('#fs-next-post').removeAttr("disabled");
			jQuery('#fs-next-post').removeClass('fs-button-inactive');
			var mediaId = jQuery(e).parent().next().find('.fs-timeline-entry').attr('media-id');
			jQuery('#fs-next-post').attr("media-id",mediaId)
		}else{
			jQuery('#fs-next-post').attr("disabled","disabled");
			jQuery('#fs-next-post').addClass('fs-button-inactive');
		}
		
		var temp;
		if(jQuery(e).parent().prev().hasClass('fs-entry-container')){
			jQuery('#fs-prev-post').removeAttr("disabled");
			jQuery('#fs-prev-post').removeClass('fs-button-inactive');
			var mediaId = jQuery(e).parent().prev().find('.fs-timeline-entry').attr('media-id');
			jQuery('#fs-prev-post').attr("media-id",mediaId)
		}else{
			jQuery('#fs-prev-post').attr("disabled","disabled");
			jQuery('#fs-prev-post').addClass('fs-button-inactive');
		}
		//console.log(jQuery(e).parent().next().find('.fs-timeline-entry').attr('media-id'));
		
		
		jQuery('#showcase').show();
		
		
		/*jQuery("#"+this.parent).load(url,function () {
			
		})*/
	}
	
	
}
