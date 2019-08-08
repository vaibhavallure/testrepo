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
    
    
    private function getOptionLabelArray($attributeCode){
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product',$attributeCode);
        $options = $attribute->getSource()->getAllOptions();
        $optionArray = array();
        foreach ($options as $option){
            $optionArray[$option["value"]] = $option["label"];
        }
        return $optionArray;
    }
    
    
    private function saveProductToSalesforce($product){
        $helper         = $this->getHelper();
        try{
            if($product){
                $salesforceId   = $product->getSalesforceProductId();
                $requestMethod  = "GET";
                $urlPath        = $helper::PRODUCT_COMPOSITE_TREE_URL;

                if(empty($salesforceId)){ //update data operation
                    $requestMethod = "POST";
                }else if(!empty($salesforceId)){
                    $helper->salesforceLog("Return from Product Event - Product -".$product->getId());
                    return;
                }

                $requestData = $helper->getProductData($product,true,true);
                $request = array("records" => array());
                array_push($request["records"],$requestData);
                
                //$helper->salesforceLog($request);
                $response    = $helper->sendRequest($urlPath , $requestMethod , $request);

                $helper->salesforceLog(json_decode($response));
                $responseArr = json_decode($response,true);
                //$helper->processResponse($product,$objectType,$sFieldName,$requestMethod,$response);

                if(!$responseArr["hasErrors"] || $responseArr["hasErrors"]=""){
                    $helper->bulkProcessResponse($responseArr,"products");
                    //$helper->processResponse($product,$objectType,$sFieldName,$requestMethod,$response);
                }
            }
        }catch (Exception $e){
            $helper->salesforceLog("Exception in add product into salesforce.");
            $helper->salesforceLog("Message :".$e->getMessage());
        }
    }
    
    
    
    
    /**
     * after new product is add or update the product information
     */
    public function changeProductToSalesforce(Varien_Event_Observer $observer){
        $helper         = $this->getHelper();
        $helper->salesforceLog("changeProductToSalesforce request");
        
        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if(!$isEnable){
            $helper->salesforceLog("Salesforce Plugin Disabled.");
            return;
        }
        
        $product = $observer->getEvent()->getProduct();
        $helper->salesforceLog("product id :".$product->getId());
        
        $this->saveProductToSalesforce($product);
    }
    
    /**
     * delete magento product from salesforce product object
     */
    public function deleteProductToSalesforce(Varien_Event_Observer $observer){
        $helper = $this->getHelper();
        $helper->salesforceLog("deleteProductToSalesforce request.");
        
        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if(!$isEnable){
            $helper->salesforceLog("Salesforce Plugin Disabled.");
            return;
        }
        
        $product = $observer->getEvent()->getProduct();
        if($product){
            $salesforceId = $product->getSalesforceProductId();
            if($salesforceId){
                $objectType     = $helper::PRODUCT_OBJECT;
                $requestMethod  = "DELETE";
                $urlPath = $helper::PRODUCT_URL . "/" . $salesforceId;
                /* $response = $helper->sendRequest($urlPath , $requestMethod , null);
                if($response == ""){
                    $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $product->getId());
                    $helper->salesforceLog("delete the product from salesforce.");
                }else{
                    $helper->addSalesforcelogRecord($objectType,$requestMethod,$product->getId(),$response);
                } */
            }
        }
    }
    
    
    
    
    
    public function addOrderProduct($items , $isTeamwork = false){
        $helper = $this->getHelper();
        $helper->salesforceLog("call come from order.");
        try{
            $isEnable = Mage::helper("allure_salesforce")->isEnabled();
            if(!$isEnable){
                $helper->salesforceLog("Salesforce Plugin Disabled.");
                return;
            }
            
            foreach ($items as $item){
                if($isTeamwork){
                    $productId = Mage::getModel("catalog/product")->getIdBySku($item->getSku());
                    if($productId){
                        $product = Mage::getModel("catalog/product")->load($productId);
                        $salesforceId = $product->getSalesforceProductId();
                        if(!$salesforceId){
                            $this->saveProductToSalesforce($product);
                        }
                    }else{
                        $helper->salesforceLog("tmwork product - ".$item->getSku());
                        $product = Mage::getModel("allure_teamwork/tmproduct")
                        ->load($item->getSku(),"sku");
                        if($product->getId()){
                            $salesforceId = $product->getSalesforceProductId();
                            if(!$salesforceId){
                                $this->saveTeamworkProductToSalesforce($product);
                            }
                        }
                    }
                }else{
                    $productId = Mage::getModel("catalog/product")->getIdBySku($item->getSku());
                    $product = Mage::getModel("catalog/product")->load($productId);
                    $salesforceId = $product->getSalesforceProductId();
                    if(!$salesforceId){
                        $this->saveProductToSalesforce($product);
                    }else {
                        $standardPriceBkId  = $product->getSalesforceStandardPricebk();
                        $wholesalePriceBkId = $product->getSalesforceWholesalePricebk();
                        if(!$standardPriceBkId && !$wholesalePriceBkId){
                            $this->saveProductToSalesforce($product);
                        }
                    }
                    $product = null;
                }
            }
        }catch (Exception $e){
            $helper->salesforceLog("Exception in add product into salesforce for sales order item");
            $helper->salesforceLog("Message :".$ee->getMessage());
        }
        $helper->salesforceLog("call complete.");
    }
    
    
    
    public function saveTeamworkProductToSalesforce($product){
        $helper         = $this->getHelper();
        $helper->salesforceLog("call come for teamwork order product.");
        try{
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
                
                $request = array(
                    "IsActive"                  => true,
                    "ExternalId"                => $product->getId(),
                    "ProductCode"               => $product->getId(),
                    "Family"                    => "simple",
                    "Name"                      => $product->getName(),
                    "StockKeepingUnit"          => $product->getSku()
                );
                
                
                $helper->salesforceLog($request);
                $response    = $helper->sendRequest($urlPath , $requestMethod , $request);
                $responseArr = json_decode($response,true);
                
                $productAttrArray = array();
                $mainStoreId      = 1;
                if($responseArr["success"] || $responseArr == ""){
                    $salesforceProductId = $product->getData("salesforce_product_id");
                    $salesforceProductId = ($salesforceProductId)?$salesforceProductId:$responseArr["id"];
                    $productAttrArray["salesforce_product_id"] = $salesforceProductId;
                    try{
                        $product->setSalesforceProductId($salesforceProductId)
                        ->save();
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
                    $wholesalePrice = $retailerPrice;
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
                                "Pricebook2Id"  => Mage::helper('allure_salesforce')->getGeneralPricebook(),
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
                                "Pricebook2Id"  => Mage::helper('allure_salesforce')->getWholesalePricebook(),
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
                    
                    if(!$responseArr1["hasErrors"] && array_key_exists("hasErrors", $responseArr1)){
                        foreach ($responseArr1["results"] as $result){
                            if($result["referenceId"] == "general"){
                                $generalPricebookId = $result["id"];
                            }
                            elseif ($result["referenceId"] == "wholesale"){
                                $wholesalePricebookId = $result["id"];
                            }
                        }
                        try{
                            $product->setData("salesforce_standard_pricebk",$generalPricebookId)
                            ->setData("salesforce_wholesale_pricebk",$wholesalePricebookId)
                            ->save();
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
                }else{
                    $helper->addSalesforcelogRecord($objectType,$requestMethod,$product->getId(),$response);
                }
            }
        }catch (Exception $e){
            $helper->salesforceLog("Exception in add product into salesforce.");
            $helper->salesforceLog("Message :".$ee->getMessage());
        }
    }
    
}
