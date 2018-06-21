<?php
/**
 * @author aws02
 */
class Allure_Salesforce_Model_Observer_Product{
    
    /**
     * retunr Allure_Salesforce_Helper_SalesforceClient
     */
    private function getHelper(){
        return Mage::helper("allure_salesforce/salesforceClient");
    }
    
    private function getOptionLabel($attributeCode,$attributeValue){
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product',$attributeCode);
        $options = $attribute->getSource()->getAllOptions();
        foreach ($options as $option){
            if($option["value"] == $attributeValue){
                return $option["label"];
            }
            //$optionArray[$option["value"]] = $option["label"];
        }
        return null;
    }
    
    /**
     * after new product is add or update the product information
     */
    public function changeProductToSalesforce(Varien_Event_Observer $observer){
        $product = $observer->getEvent()->getProduct();
        Mage::log($product->getId(),Zend_Log::DEBUG,'abc.log',true);
        if($product){
            $helper         = $this->getHelper();
            $objectType     = $helper::PRODUCT_OBJECT;
            $sFieldName     = $helper::S_PRODUCTID;
            
            $salesforceId   = $product->getSalesforceProductId();
            $requestMethod  = "GET";
            $urlPath        = $helper::PRODUCT_URL;
            if($salesforceId){ //update data operation
                $urlPath      .=  "/" .$salesforceId;
                $requestMethod = "PATCH";
            }else{ //insert data operation
                $requestMethod = "POST";
            }
            
            $metalColor = $product->getMetal();
            $taxClassId = $product->getTaxClassId();
            $gemstone   = $product->getGemstone();
            
            $request = array(
                "IsActive"                  => ($product->getStatus())?true:false,
                //"Diamond_Color__c"          => "",
                "DisplayUrl"                => $product->getUrlKey(),
                "ExternalId"                => $product->getId(),
                //"Gemstone__c"               => $product->getGemstone(),
                "Jewelry_Care__c"           => $product->getJewelryCare(),
                //"Metal_Color__c"            => $product->getMetal(),
                "ProductCode"               => $product->getId(),
                "Description"               => $product->getDescription(), 
                "Family"                    => $product->getTypeId(),
                "Name"                      => $product->getName(),
                "StockKeepingUnit"          => $product->getSku(),
                "Return_Policy__c"          => $product->getReturnPolicy(),
                //"Tax_Class_Id__c"           => $product->getTaxClassId(),
                "Vendor_Item_No__c"         => $product->getVendorItemNo()
            );
            
            if($metalColor){
               $metal_attr = "metal"; 
               $metalColor = $this->getOptionLabel($metal_attr, $metalColor);
               $request["Metal_Color__c"] = $metalColor;
            }
            
            if($taxClassId){
                $request["Tax_Class_Id__c"] = $taxClassId;
            }
            
            if($gemstone){
                $gemstone_attr = "gemstone";
                $gemstone = $this->getOptionLabel($gemstone_attr, $gemstone);
                $request["Gemstone__c"] = $gemstone;
            }
            
            Mage::log($request,Zend_Log::DEBUG,'abc.log',true);
            $response    = $helper->sendRequest($urlPath , $requestMethod , $request);
            $responseArr = json_decode($response,true);
            Mage::log($response,Zend_Log::DEBUG,'abc.log',true);
            $helper->processResponse($product,$objectType,$sFieldName,$requestMethod,$response);
            
            if($responseArr["success"] || $responseArr == ""){
                $salesforceProductId = $product->getData("salesforce_product_id");
                $helper->salesforceLog("salesforce id = ".$salesforceProductId);
                $pricebkUrlPath = $helper::PRODUCT_PRICEBOOK_URL;
                $requestMethod = "GET";
                $standardPriceBkId  = $product->getSalesforceStandardPricebk();
                $wholesalePriceBkId = $product->getSalesforceWholesalePricebk();
                $retailerPrice  = $product->getPrice();
                $wholesalePrice = 0;
                foreach ($product->getData('group_price') as $gPrice){
                    if($gPrice["cust_group"] == 2){ //wholesaler group : 2
                        $wholesalePrice = $gPrice["price"];
                    }
                }
                $sRequest = array();
                if($standardPriceBkId && $wholesalePriceBkId){
                    $requestMethod = "PATCH";
                    $pricebkUrlPath = $helper::PRODUCT_UPDATE_PRICEBK_URL;
                    $sRequest["allOrNone"] = false;
                    $sRequest["records"] = array(
                        array(
                            "attributes"    => array(
                                "type"          => "PricebookEntry"
                            ),
                            "id"  => $standardPriceBkId,
                            "UnitPrice"     => $retailerPrice
                        )
                    );
                    
                    if($wholesalePrice){
                        $sTemp = array(
                            "attributes"    => array(
                                "type"          => "PricebookEntry"
                            ),
                            "id"  => $wholesalePriceBkId,
                            "UnitPrice"     => $wholesalePrice
                        );
                        array_push($sRequest["records"],$sTemp);
                    }
                    
                }else{
                    $requestMethod = "POST";
                    $sRequest["records"] = array(
                        array(
                            "attributes"    => array(
                                "type"          => "PricebookEntry",
                                "referenceId"   => "general"
                            ),
                            "Pricebook2Id"  => $helper::RETAILER_PRICEBOOK_ID,
                            "Product2Id"    => $salesforceProductId,
                            "UnitPrice"     => $retailerPrice
                        )
                    );
                    
                    if($wholesalePrice){
                        $sTemp = array(
                            "attributes"    => array(
                                "type"          => "PricebookEntry",
                                "referenceId"   => "wholesale"
                            ),
                            "Pricebook2Id"  => $helper::WHOLESELLER_PRICEBOOK_ID,
                            "Product2Id"    => $salesforceProductId,
                            "UnitPrice"     => $wholesalePrice
                        );
                        array_push($sRequest["records"],$sTemp);
                    }
                }
                $objectType = $helper::PRODUCT_PRICEBOOK_OBJECT;
                
                $response1    = $helper->sendRequest($pricebkUrlPath , $requestMethod , $sRequest);
                Mage::log($response1,Zend_Log::DEBUG,'abc.log',true);
                $responseArr1 = json_decode($response1,true);
                
                $generalPricebookId     = $product->getData("salesforce_standard_pricebk");
                $wholesalePricebookId   = $product->getData("salesforce_wholesale_pricebk");
                
                if($generalPricebookId){
                    return ;
                }
                
                if(!$responseArr1["hasErrors"]){
                    foreach ($responseArr1["results"] as $result){
                        if($result["referenceId"] == "general"){
                            $product->setData("salesforce_standard_pricebk",$result["id"]);
                        }
                        elseif ($result["referenceId"] == "wholesale"){
                            $product->setData("salesforce_wholesale_pricebk",$result["id"]);
                        }
                    }
                    try{
                        $product->save();
                        $helper->salesforceLog("Pricebook Data added. Product Id :".$product->getId());
                    }catch (Exception $e){
                        $helper->salesforceLog("Exception in prodcut pricebook saving data.");
                        $helper->salesforceLog("Message :".$e->getMessage());
                    }
                }
                //$helper->processResponse($product,$objectType,$sFieldName,$requestMethod,$response);
                
            }
        }
       
    }
    
    /**
     * delete magento product from salesforce product object
     */
    public function deleteProductToSalesforce(Varien_Event_Observer $observer){
        $product = $observer->getEvent()->getProduct();
        if($product){
            $salesforceId = $product->getSalesforceProductId();
            if($salesforceId){
                $helper = $this->getHelper();
                $requestMethod  = "DELETE";
                $urlPath = $helper::PRODUCT_URL . "/" . $salesforceId;
                $helper->sendRequest($urlPath , $requestMethod , null);
            }
        }
    }
}
