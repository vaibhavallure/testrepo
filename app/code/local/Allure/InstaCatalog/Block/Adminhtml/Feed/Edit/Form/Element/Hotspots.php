<?php

class Allure_InstaCatalog_Block_Adminhtml_Feed_Edit_Form_Element_Hotspots extends Varien_Data_Form_Element_Abstract
{
    public function __construct($data)
    {
        parent::__construct($data);
        $this->setType('hidden');
    }

    public function getElementHtml()
    {
	    //$hotspot_icon  = Mage::getBaseUrl('media').'lookbook/icons/default/hotspot-icon.png';	
    	//$hotspot_icon = Mage::getBaseUrl('skin').'frontend/mt/default/images/hotspot-icon.png';	
        $products_link = Mage::helper("adminhtml")->getUrl('adminhtml/catalog_product/');
        $helper = Mage::helper('allure_instacatalog');
    
        $html = '
        <style>
        		
            /* .image-annotate-area,  .image-annotate-edit-area {
                background: url(hotspot_icon) no-repeat center center;
               	background-color: #ff5;
			    border-radius: 20px;
			    color: #222;
			    box-sizing: border-box;
			    box-shadow: 0 0px 10px #fff, 0 0px 10px #cfc, 0 0px 10px #cfc;
            }    */                                                          
        </style>
                <script type="text/javascript">
                //<![CDATA[                    
                        function InitHotspotBtn() {
                             if (jQuery("img#LookbookImage").attr("id")) {
                				var annotObj = jQuery("img#LookbookImage").annotateImage({                				    
                					editable: true,
                					useAjax: false,';
   if ($this->getValue()) $html .= 'notes: '. $this->getValue() . ',';
   
       $html .= '                   input_field_id: "feed_hotspots"                            
                				});
                                
                                jQuery("img#LookbookImage").before(\'<div class="products-link"><a href="'.$products_link.'" title="'.$helper->__('Products List').'" target="_blank">'. $helper->__('Products List').'</a></div>\');
                                
                                var top = Math.round(jQuery("img#LookbookImage").height()/2);
                                jQuery(".image-annotate-canvas").append(\'<div class="hotspots-msg" style="top:\' + top + \'px;">'. /*$helper->__('Rollover on the image to see Products').*/'</div>\');
                        
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
                                                
                        function checkSKU(){
                                    result = "";
                                    request = new Ajax.Request(
                                    "'. Mage::getUrl("adminhtml/allure_instaCatalog_lookbook/getproduct").'",
                                    {
                                        method: \'post\',
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
                //]]>
                </script>';

        $html.= parent::getElementHtml();

        return $html;
    }
}
               
  
 
