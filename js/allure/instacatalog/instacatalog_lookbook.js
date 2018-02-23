//codes test
if (typeof Allure == "undefined") {
	   					var Allure = {};
				}
				Allure.InstaFormKey = "'.Mage::getSingleton("core/session")->getFormKey().'";
				Allure.InstaSearchUrl = "'.$search_action.'";
				jQuery(document).on("click",".image-annotate-canvas",function(event){
    				var $ = jQuery;
    				if($(".image-annotate-edit-delete").length){
    				}else{
						$("#image-annotate-add").trigger("click");
    					var left = event.offsetX; 
						var top= event.offsetY; 
    					var formSelector = $(".image-annotate-edit-area.ui-resizable.ui-draggable");
    					var fHeight = parseInt(formSelector.css("height"));
    					var topF = event.pageY + fHeight + 5;
						$(".image-annotate-edit-area.ui-resizable.ui-draggable").css({"left":left+"px","top":top+"px"});
						$("#image-annotate-edit-form").css({"left":event.pageX+"px","top":topF+"px"});
					}
    		
				 });
    		
    		var xhr = null;
    		jQuery(document).on("keyup","#image-annotate-text",function(){
    			var value = jQuery(this).val();
    			var left = parseInt(jQuery("#image-annotate-edit-form").css("left"));
    			var top = parseInt(jQuery("#image-annotate-edit-form").css("top"));
    			top = top + 85;
    			console.log(left);
    		
    		if( xhr != null ) {
                xhr.abort();
                xhr = null;
    			jQuery("#search-list-view").remove();
        	}
    		xhr = jQuery.ajax({
	        	url: Allure.InstaSearchUrl,
	       	 	dataType : "json",
				type : "POST",
				data: {"query":value,"form_key":Allure.InstaFormKey},
	        	success: function(data) {
					var html = '<ul id="search-list-view" style="left:'+left+'px;top:'+top+'px">';
					var list ="";
					for(var i=0;i<data.data.result.length;i++){
						//console.log(data.data.result[i].entity_id);
						list = list+'<li id="id-'+data.data.result[i].entity_id+'" data-sku="'+data.data.result[i].sku+'">'+data.data.result[i].name+'</li>';
 					}
					html = html+list+'</div>';
					jQuery(html).insertAfter("#image-annotate-edit-form");
	        	}
	    		});
    		});
						
						jQuery(document).on("click","#search-list-view li",function(){
							console.log(jQuery(this).attr("data-sku"));
							jQuery("#image-annotate-text").val(jQuery(this).attr("data-sku"));
						});