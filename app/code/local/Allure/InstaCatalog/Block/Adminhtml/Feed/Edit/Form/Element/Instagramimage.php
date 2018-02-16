<?php

class Allure_InstaCatalog_Block_Adminhtml_Feed_Edit_Form_Element_Instagramimage extends Varien_Data_Form_Element_Abstract
{
    public function __construct($data)
    {
        parent::__construct($data);
        $this->setType('hidden');
    }

 public function getElementHtml()
 {
 	//return '<img id="LookbookImage" src="'.$this->getValue().'" />';
    $block_class =  Mage::getBlockSingleton('allure_instacatalog/adminhtml_feed');
    $upload_action  = Mage::getUrl('adminhtml/allure_instaCatalog_lookbook/upload').'?isAjax=true';
    $media_url  = Mage::getBaseUrl('media');
    $upload_folder_path = str_replace("/",DS, Mage::getBaseDir("media").DS);
    $helper = Mage::helper('allure_instacatalog');
    $min_image_width = $helper->getMinImageWidth();
    $min_image_height = $helper->getMinImageHeight();
    $sizeLimit      = $helper->getMaxUploadFilesize();
    $allowed_extensions = implode('","',explode(',',$helper->getAllowedExtensions())); 
    
    $search_action  = Mage::getUrl('adminhtml/allure_instaCatalog_feed/searchAjax').'?isAjax=true';
    
    $html = '
    		<script type="text/javascript">
                //<![CDATA[
                jQuery(document).ready(function() {
    
                  InitHotspotBtn();
    
                    img_uploader = new qq.FileUploader({
                        element: document.getElementById(\'maket_image\'),
                        action: "'.$upload_action.'",
                        params: {"form_key":"'.$block_class->getFormKey().'"},
                        multiple: true,
                        allowedExtensions: ["'.$allowed_extensions.'"],
                        sizeLimit: '. $sizeLimit .',
                        onComplete: function(id, fileName, responseJSON){
                                    if (responseJSON.success)
                                    {
                                        if (jQuery(\'#LookbookImageBlock\'))
                                        {
                                          jQuery.each(jQuery(\'#LookbookImageBlock\').children(),function(index) {
                                            jQuery(this).remove();
                                          });
                                        }
                                       jQuery(\'#LookbookImageBlock\').append(\'<img id="LookbookImage"';
    $html .= ' src="'.$media_url.'lookbook/\'+responseJSON.filename+\'" alt="\'+responseJSON.filename+\'"';
    $html .= ' width="\'+responseJSON.dimensions.width+\'" height="\'+responseJSON.dimensions.height+\'"/>\');
                    
                                        if (jQuery(\'#advice-required-entry-image\'))
                                        {
                                            jQuery(\'#advice-required-entry-image\').remove();
                                        }
                                        jQuery(\'#LookbookImage\').load(function(){
                                           jQuery(this).attr(\'width\',responseJSON.dimensions.width);
                                           jQuery(this).attr(\'height\',responseJSON.dimensions.height);
                                           InitHotspotBtn();
                                        });
                                        jQuery(\'#feed_image\').val(\'lookbook/\'+responseJSON.filename);
                                        jQuery(\'#image\').removeClass(\'validation-failed\');
                                    }
    
                        }
                    });
                });
                //]]>
                
    			//codes test
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
						if($("#search-list-view").length){
    						var tofSearch = topF + 85;
    						$("#search-list-view").css({"left":event.pageX+"px","top":tofSearch+"px"});
    					}
 					}
    		
				 });
    		
    		var xhr = null;
    		jQuery(document).on("keyup","#image-annotate-text",function(){
    			var vale = jQuery(this).val();
    			var left = parseInt(jQuery("#image-annotate-edit-form").css("left"));
    			var top = parseInt(jQuery("#image-annotate-edit-form").css("top"));
    			top = top + 85;
    			console.log(left);
    		
	    		if( xhr != null ) {
	                xhr.abort();
	                xhr = null;
	    			jQuery("#search-list-view").remove();
	        	}
    			jQuery("#instagram-search-loader").show();
	    		xhr = jQuery.ajax({
		        	url: "'.$search_action.'",
		       	 	dataType : "json",
					type : "POST",
					data: {"query":vale,"form_key":"'.$block_class->getFormKey().'"},
		        	success: function(data) {
						jQuery("#instagram-search-loader").hide();
						var html = \'<ul id="search-list-view" style="left:\'+left+\'px;top:\'+top+\'px">\';
						var list ="";
						for(var i=0;i<data.data.result.length;i++){
							//console.log(data.data.result[i].entity_id);
							list = list+\'<li id="\'+data.data.result[i].entity_id+\'" data-sku="\'+data.data.result[i].sku+\'">\'+data.data.result[i].name+\'</li>\';
	 					}
						html = html+list+\'</div>\';
						jQuery(html).insertAfter("#image-annotate-edit-form");
		        	}
		    	});
    		});
						
			jQuery(document).on("click","#search-list-view li",function(){
				console.log(jQuery(this).attr("data-sku"));
				jQuery("#image-annotate-text").val(jQuery(this).attr("data-sku"));
				jQuery("#image-annotate-text").attr("data-product",jQuery(this).attr("id"));
			});
    		
    		
                </script>
                <div id="LookbookImageBlock">';
    
    if ($this->getValue()) {
    	$img_src = $media_url.$this->getValue();
    	$img_path = $upload_folder_path.$this->getValue();
    	/* if (file_exists($img_src)) {
    		//$dimensions = Mage::helper('allure_instacatalog')->getImageDimensions($img_path);
    		$html .= '<img id="LookbookImage" src="'.$img_src.'" alt="'.basename($img_src).'" width="640" height="640"/>';
    	}else */if ($this->getValue()){
    		$img_src = $this->getValue();
    		$html .= '<img id="LookbookImage" src="'.$this->getValue().'" alt="'.basename($img_src).'" width="640" height="640"/>';
    	}
    	else{
    		$html .= '<h4 id="LookbookImage" style="color:red;">File '.$this->getValue().' doesn\'t exists.</h4>';
    	}
    }
    
    $html .= '</div>
                <div id="maket_image" style="display:none;">
                    <noscript>
                        <p>Please enable JavaScript to use file uploader.</p>
                        <!-- or put a simple form for upload here -->
                    </noscript>
                </div>';
    
    $html.= parent::getElementHtml();
    
    if ($min_image_width!=0 && $min_image_height!=0){
    	$html.= '<p class="note" style="clear:both; float:left;display:none;">Please make sure that the image your load is at least '
    			.$min_image_width.'x'.$min_image_height.' pixels</p>';
    }
    
    return $html;
    }
    }
    