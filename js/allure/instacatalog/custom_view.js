jQuery(document).ready(function(){
	//InitHotspotBtn();

	jQuery(document).on('click','.post .img',function(){
		jQuery(this).next().find('.postentry-controls a').trigger('click');
	});
	
	jQuery(document).on("click",".image-annotate-canvas",function(event){
		var $ = jQuery;
		if($(".image-annotate-edit-delete").length){
		}else{
			$("#image-annotate-add").trigger("click");
			var left = event.offsetX-10; 
			var top= event.offsetY-10; 
			var formSelector = $(".image-annotate-edit-area.ui-resizable.ui-draggable");
			var fHeight = parseInt(formSelector.css("height"));
			var topF = event.pageY + fHeight + 5;
			$(".image-annotate-edit-area.ui-resizable.ui-draggable").css({"left":left+"px","top":top+"px"});

			
			$("#image-annotate-edit-form").css({"left":event.pageX+"px","top":topF+"px"});
			if($("#search-list-view").length){
				var tofSearch = topF + 85;
				$("#search-list-view").css({"left":event.pageX+"px","top":tofSearch+"px"});
			}
		}
	 });


	var xhr = null;
	jQuery(document).on("keyup","#image-annotate-text",function(){
		var value = jQuery(this).val();
		var left = parseInt(jQuery("#image-annotate-edit-form").css("left"));
		var top = parseInt(jQuery("#image-annotate-edit-form").css("top"));
		var width = jQuery("#image-annotate-edit-form").css("width");
		top = top + 85;
		console.log(left);
	
		if( xhr != null ) {
            xhr.abort();
            xhr = null;
			jQuery("#search-list-view").remove();
    	}
		jQuery("#instagram-search-loader").show();
		xhr = jQuery.ajax({
        	url: Allure.SearchProductUrl,
       	 	dataType : "json",
			type : "POST",
			data: {"query":value,"form_key":Allure.TagProductFormKey},
        	success: function(data) {
				jQuery("#instagram-search-loader").hide();
				var html = '<ul id="search-list-view" style="width:'+width+';z-index:10000;left:'+left+'px;top:'+top+'px">';
				var list ="";
				for(var i=0;i<data.data.result.length;i++){
					//console.log(data.data.result[i].entity_id);
					list = list+'<li id="'+data.data.result[i].entity_id+'" data-sku="'+data.data.result[i].sku+'">'+data.data.result[i].name+'</li>';
					}
				html = html+list+'</div>';
				jQuery(html).insertAfter("#image-annotate-edit-form");
        	}
    	});
	});
				
	jQuery(document).on("click","#search-list-view li",function(){
		console.log(jQuery(this).attr("data-sku"));
		jQuery("#image-annotate-text").val(jQuery(this).attr("data-sku"));
		jQuery("#image-annotate-text").attr("data-product",jQuery(this).attr("id"));
		jQuery("#search-list-view").hide()
	});
	
	
});


function InitHotspotBtn(taggedProduct) {
    if (jQuery("img#LookbookImage").attr("id")) {
        var str = "input_field_id: 'feed_hotspots',";
        var arr = [];
        if(taggedProduct!=null && taggedProduct!=""){
        	arr['notes']=JSON.parse(taggedProduct); 
        }
        arr['editable']=true; 
        arr['useAjax']=false; 
        arr['input_field_id']='feed_hotspots'; 
		var annotObj = jQuery("img#LookbookImage").annotateImage(arr);
       
       //jQuery("img#LookbookImage").before('<div class="products-link"><a href="'.$products_link.'" title="'+'Products List'+'" target="_blank">'+ 'Products List'+'</a></div>');
       
       var top = Math.round(jQuery("img#LookbookImage").height()/2);
       jQuery(".image-annotate-canvas").append('<div class="hotspots-msg" style="top:' + top + 'px;">'+'</div>');

	   var width = jQuery('#LookbookImage').attr('width');
	   var height = jQuery('#LookbookImage').attr('height');
       //jQuery(".image-annotate-canvas").css({'width':width+'px','height':height+'px','background-size':'100%'});
       
       jQuery(".image-annotate-canvas").css({'background-size':'100%'});

       //jQuery(".image-annotate-view").css({'width':width+'px','height':height+'px'});
       //jQuery(".image-annotate-edit").css({'width':width+'px','height':height+'px'});
       
       jQuery(".image-annotate-canvas").hover(
             function () {
                   ShowHideHotspotsMsg();
             },
             function () {
                   ShowHideHotspotsMsg();
             }
           );
       return annotObj;
   }
   else
   {
       return false;
   }
};  

function saveTaggedProduct(){
	var feedId = jQuery("#feed_id").val();
	var hotpots = jQuery("#feed_hotspots").val();
	//var feedStatus = jQuery('#feed-status').is(':checked');
	jQuery.ajax({
    	url: Allure.TagProductUrl,
   	 	dataType : "json",
		type : "POST",
		data: {"feed_id":feedId,hotspots:hotpots,/* "status":feedStatus, */"form_key":Allure.TagProductFormKey},
    	success: function(data) {
        	console.log(data);
        	if(data.success==1){
            	jQuery('#instagram-link-'+feedId).attr('data-tag-product',hotpots);
            	jQuery('#tag-count-'+feedId).text(data.length);
            	jQuery('#feed-status-'+feedId).prop('checked',data.feedstatus);
        	}
    	}
	});
}

function saveFeedStatusAjax(e,type){
	var feedId = 0;
	if(type==1){
		feedId = jQuery(e).attr('data-id');
	}else{
		feedId = jQuery("#feed_id").val();
	}
	var feedStatus = jQuery(e).is(':checked');
	jQuery.ajax({
    	url: Allure.FeedStatusUrl,
   	 	dataType : "json",
		type : "POST",
		data: {"feed_id":feedId,"status":feedStatus,"form_key":Allure.TagProductFormKey},
    	success: function(data) {
        	if(data.success==1){
            	jQuery('#feed-status-'+feedId).prop('checked',data.feedstatus);
        	}
    	}
	});  
}

function checkSKU(){
    result = "";
    request = new Ajax.Request(Allure.ProductGetUrl,
    {
        method: 'post',
        asynchronous: false,
        onComplete: function(transport){
            if (200 == transport.status) {
                result = transport.responseText;
                return result;
            }
            if (result.error) {
                alert("Unable to check product SKU");
                return false;                                                                                                
            }
        },
        parameters: Form.serialize($("annotate-edit-form"))
    }
);
return result;
};
        