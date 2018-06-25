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
        $helper         = $this->getHelper();
        $helper->salesforceLog("changeProductToSalesforce request");
        
        $product = $observer->getEvent()->getProduct();
        $helper->salesforceLog("product id :".$product->getId());
        if($product){
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
            
            $helper->salesforceLog($request);
            $response    = $helper->sendRequest($urlPath , $requestMethod , $request);
            $responseArr = json_decode($response,true);
            //$helper->processResponse($product,$objectType,$sFieldName,$requestMethod,$response);
            
            $productAttrArray = array();
            $mainStoreId      = 1;
            
            if($responseArr["success"] || $responseArr == ""){
                $salesforceProductId = $product->getData("salesforce_product_id");
                $salesforceProductId = ($salesforceProductId)?$salesforceProductId:$responseArr["id"];
                
                $productAttrArray["salesforce_product_id"] = $salesforceProductId;
                
                try{
                    Mage::getResourceSingleton('catalog/product_action')
                    ->updateAttributes(array($product->getId()),$productAttrArray,$mainStoreId);
                    $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $product->getId());
                }catch (Exception $ee){
                    $helper->salesforceLog("Exception in add or update product into salesforce");
                    $helper->salesforceLog("Message :".$ee->getMessage());
                }
                
                $helper->salesforceLog("product_id:".$product->getId()." salesforce_id = ".$salesforceProductId);
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
                            "attributes"    => array("type" => "PricebookEntry"),
                            "id"            => $standardPriceBkId,
                            "UnitPrice"     => $retailerPrice
                        )
                    );
                    
                    if($wholesalePrice){
                        $sTemp = array(
                            "attributes"    => array("type" => "PricebookEntry"),
                            "id"            => $wholesalePriceBkId,
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
                $objectType1 = $helper::PRODUCT_PRICEBOOK_OBJECT;
                
                $response1    = $helper->sendRequest($pricebkUrlPath , $requestMethod , $sRequest);
                $responseArr1 = json_decode($response1,true);
                
                $generalPricebookId     = $product->getData("salesforce_standard_pricebk");
                $wholesalePricebookId   = $product->getData("salesforce_wholesale_pricebk");
                
                /* if($generalPricebookId){
                    return ;
                } */
                
                
                
                if(!$responseArr1["hasErrors"] && array_key_exists("hasErrors", $responseArr1)){
                    foreach ($responseArr1["results"] as $result){
                        if($result["referenceId"] == "general"){
                            //$product->setData("salesforce_standard_pricebk",$result["id"]);
                            $productAttrArray["salesforce_standard_pricebk"] = $result["id"];
                        }
                        elseif ($result["referenceId"] == "wholesale"){
                            //$product->setData("salesforce_wholesale_pricebk",$result["id"]);
                            $productAttrArray["salesforce_wholesale_pricebk"] = $result["id"];
                        }
                    }
                    try{
                        Mage::getResourceSingleton('catalog/product_action')
                        ->updateAttributes(array($product->getId()),$productAttrArray,$mainStoreId);
                        //$product->save();
                        $helper->deleteSalesforcelogRecord($objectType1, $requestMethod, $product->getId());
                        $helper->salesforceLog("Pricebook Data added. Product Id :".$product->getId());
                    }catch (Exception $e){
                        $helper->salesforceLog("Exception in prodcut pricebook saving data.");
                        $helper->salesforceLog("Message :".$e->getMessage());
                    }
                }elseif($responseArr1[0]["success"]){
                    $helper->deleteSalesforcelogRecord($objectType1, $requestMethod, $product->getId());
                    $helper->salesforceLog("Price update data successfully.");
                }
                else{
                    $helper->addSalesforcelogRecord($objectType1,$requestMethod,$product->getId(),$response1);
                }
                
                //$helper->processResponse($product,$objectType,$sFieldName,$requestMethod,$response);
                
            }else{
                $helper->addSalesforcelogRecord($objectType,$requestMethod,$product->getId(),$response);
            }
        }
    }
    
    /**
     * delete magento product from salesforce product object
     */
    public function deleteProductToSalesforce(Varien_Event_Observer $observer){
        $helper = $this->getHelper();
        $helper->salesforceLog("deleteProductToSalesforce request.");
        $product = $observer->getEvent()->getProduct();
        if($product){
            $salesforceId = $product->getSalesforceProductId();
            if($salesforceId){
                $objectType     = $helper::PRODUCT_OBJECT;
                $requestMethod  = "DELETE";
                $urlPath = $helper::PRODUCT_URL . "/" . $salesforceId;
                $response = $helper->sendRequest($urlPath , $requestMethod , null);
                if($response == ""){
                    $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $product->getId());
                    $helper->salesforceLog("delete the product from salesforce.");
                }else{
                    $helper->addSalesforcelogRecord($objectType,$requestMethod,$product->getId(),$response);
                }
            }
        }
    }
}
