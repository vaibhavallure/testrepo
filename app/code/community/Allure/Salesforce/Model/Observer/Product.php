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
                $amount = $product->getAmount();      //amount - select
                $frSize = $product->getFrSize();      //fr_size - select
                $sideEar = $product->getSideEar();     //side_ear - select
                $direction = $product->getDirection(); //direction - select
                $neckLength = $product->getNeckLengt(); //neck_lengt - select
                $noseBend = $product->getNoseBend();    //nose_bend - select
                $cLength = $product->getCLength();      //c_length - select
                $size = $product->getSize();            //size - select
                $gauge = $product->getGauge();           //gauge - select
                $postOption = $product->getPostOptio(); //post_optio - select
                $rise = $product->getRise();            //rise - select
                $sLength = $product->getSLength();    //s_length - select
                $placement = $product->getPlacement(); //placement - select
                $material = $product->getMaterial(); //material - multiselect
                
                $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
                $attributeSetModel->load($product->getAttributeSetId());
                $attributeSetName = $attributeSetModel->getAttributeSetName();
                
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
                    "Vendor_Item_No__c"         => $product->getVendorItemNo(),
                    "Location__c"               => $attributeSetName,
                );
                
                if($metalColor){
                    $metalColor = $this->getOptionLabel("metal", $metalColor);
                    $request["Metal_Color__c"] = $metalColor;
                }
                
                if($taxClassId){
                    $request["Tax_Class_Id__c"] = $taxClassId;
                }
                
                if($gemstone){
                    $gemstone = $this->getOptionLabel("gemstone", $gemstone);
                    $request["Gemstone__c"] = $gemstone;
                }
                
                if($amount){
                    $amount = $this->getOptionLabel("amount", $amount);
                    $request["Amount__c"] = $amount;
                }
                
                if($frSize){
                    $frSize = $this->getOptionLabel("fr_size", $frSize);
                    $request["FR_SIZE__c"] = $frSize;
                }
                
                if($sideEar){
                    $sideEar = $this->getOptionLabel("side_ear", $sideEar);
                    $request["SIDE_EAR__c"] = $sideEar;
                }
                
                if($direction){
                    $direction = $this->getOptionLabel("direction", $direction);
                    $request["DIRECTION__c"] = $direction;
                }
                
                if($neckLength){
                    $neckLength = $this->getOptionLabel("neck_lengt", $neckLength);
                    $request["NECK_LENGT__c"] = $neckLength;
                }
                
                if($noseBend){
                    $noseBend = $this->getOptionLabel("nose_bend", $noseBend);
                    $request["NOSE_BEND__c"] = $noseBend;
                }
                
                if($cLength){
                    $cLength = $this->getOptionLabel("c_length", $cLength);
                    $request["C_LENGTH__c"] = $cLength;
                }
                
                if($size){
                    $size = $this->getOptionLabel("size", $size);
                    $request["SIZE__c"] = $size;
                }
                
                if($gauge){
                    $gauge = $this->getOptionLabel("gauge", $gauge);
                    $request["GAUGE__c"] = $gauge;
                }
                
                if($postOption){
                    $postOption = $this->getOptionLabel("post_optio", $postOption);
                    $request["POST_OPTIO__c"] = $$postOption;
                }
                
                if($rise){
                    $rise = $this->getOptionLabel("rise", $rise);
                    $request["RISE__c"] = $rise;
                }
                
                if($sLength){
                    $sLength = $this->getOptionLabel("s_length", $sLength);
                    $request["S_Length__c"] = $sLength;
                }
                
                if($placement){
                    $placement = $this->getOptionLabel("placement", $placement);
                    $request["PLACEMENT__c"] = $placement;
                }
                
                if($material){
                    $tMaterial = array();
                    $materialArr = $this->getOptionLabelArray("material");
                    foreach (explode(",", $material) as $mat){
                        $tMaterial[] = $materialArr[$mat];
                    }
                    $request["Material__c"] = implode(",", $tMaterial);
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
                                "Pricebook2Id"  => Mage::helper('allure_salesforce')->getGeneralPricebook(),//$helper::RETAILER_PRICEBOOK_ID,
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
                                "Pricebook2Id"  => Mage::helper('allure_salesforce')->getWholesalePricebook(),//$helper::WHOLESELLER_PRICEBOOK_ID,
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
        }catch (Exception $e){
            $helper->salesforceLog("Exception in add product into salesforce.");
            $helper->salesforceLog("Message :".$ee->getMessage());
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
