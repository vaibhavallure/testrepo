<?php

class Ecp_Quickview_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction(){
       /* // Get initial data from request
        $categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId  = (int) $this->getRequest()->getParam('id');
        $specifyOptions = $this->getRequest()->getParam('options');

        // Prepare helper and params
        $viewHelper = Mage::helper('ecp_quickview/product_view');

        $params = new Varien_Object();
        $params->setCategoryId($categoryId);
        $params->setSpecifyOptions($specifyOptions);*/

        // Get initial data from request
        $categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId  = (int) $this->getRequest()->getParam('id');
        $specifyOptions = $this->getRequest()->getParam('options');

        $viewHelper = Mage::helper('catalog/product_view');

        $params = new Varien_Object();
        $params->setCategoryId($categoryId);
        $params->setSpecifyOptions($specifyOptions);

        $selectedColor = $this->getRequest()->getParam("colorOptionId", false);
        $optionHelper = Mage::helper('allure_catalog');

        /*For Default select*/
        $selectValue1=$selectValue2="";
        $selAtr1=$selAtr2="";
        if(!empty($this->getRequest()->getParam("selectValue1", false))) {
            $selectValue1 = $this->getRequest()->getParam("selectValue1", false);
        }
        if(!empty($this->getRequest()->getParam("selectValue2", false))) {
            $selectValue2 = $this->getRequest()->getParam("selectValue2", false);

        }





        if (!$selectedColor) {

            $optionId = $this->getRequest()->getParam("optionId", false);

            if ($optionId) {
                $selectedColor = $optionId;
            } else {
                $metal = $this->getRequest()->getParam("metal", false);

                if ($metal) {
                    $selectedColor = $optionHelper->getOptionNumber($metal);
                }
            }
        }




        $core_ses=Mage::getSingleton('core/session');

        if (!$selectedColor || $core_ses->getData("view_product_id")!=$productId) {


            $core_ses->setData("view_product_id",$productId);
            $core_ses->unsetData("selAtr1");
            $core_ses->unsetData("selectValue1");
            $core_ses->unsetData("selAtr2");
            $core_ses->unsetData("selectValue2");
            $core_ses->unsetData("child");



            $product = Mage::getModel('catalog/product')->load($productId);
            if ($product->isConfigurable()) {
                $productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
                $simpleProducts = $product->getTypeInstance()->getUsedProductCollection()->addAttributeToSelect('sku');
                $flag=FALSE;
                foreach ($productAttributeOptions as $productAttribute) {

                    if($productAttribute['attribute_code'] == 'metal'/* 'metal_color' */){

                        foreach ($productAttribute['values'] as $single) {

                            $selectedColorLabel=$single['label'];
                            $selectedColor = $single['value_index'];
                            foreach ($simpleProducts as $simple){
                                $sku=explode('|', $simple->getSku());

                                if(strtolower($selectedColorLabel)==strtolower($sku[1])){
                                    $stockItem = Mage::getModel('cataloginventory/stock_item')
                                        ->loadByProduct($simple->getId());
                                    if($stockItem->getId()){
                                        if($stockItem->getQty() > 0){
                                            $selectedColor=$single['value_index'];
                                            $flag=true;
                                            //break 2;
                                            break 2;
                                        }
                                    }
                                }
                            }

                            if($flag){
                                break;
                            }
                        }

                        if (!$flag) {
                            $selectedColor=$productAttribute['values'][0]['value_index'];
                        }
                    }
                }







                if ($selectedColor) {

                    $selectedColorText = $optionHelper->getOptionText($selectedColor);

                    $resource = Mage::getSingleton('core/resource');
                    $readConnection = $resource->getConnection('core_read');
                    $query="SELECT rel.parent_id,rel.child_id,atr.product_super_attribute_id,atr.attribute_id,itm.qty,cpen.value FROM `catalog_product_relation` rel JOIN catalog_product_super_attribute atr on atr.product_id = rel.parent_id join cataloginventory_stock_item itm on itm.product_id = rel.child_id JOIN catalog_product_entity_int cpen ON (cpen.attribute_id=atr.attribute_id AND cpen.entity_id=rel.child_id) where rel.parent_id = ".$productId." and itm.qty > 0 GROUP BY atr.attribute_id";
                    $results = $readConnection->fetchAll($query);

                    if(count($results)>=2)
                    {


                        $cnt=0;
                        foreach ($results as $res) {

                            $childid= $res['child_id'];


                            $query = "SELECT attribute_code FROM `eav_attribute` WHERE `attribute_id` = " . $res['attribute_id'];


                            $results = $readConnection->fetchAll($query);

                            if ($results[0]['attribute_code'] != 'metal') {
                                if ($res['value'] != $selectedColor) {
                                    $cnt++;

                                    if ($cnt == 1) {
                                        $selectValue1 = $res['value'];
                                        $selAtr1 = $res['attribute_id'];
                                    }
                                    if (($cnt >= 2) && ($selectValue1 != $res['value'])) {
                                        $selectValue2 = $res['value'];
                                        $selAtr2 = $res['attribute_id'];

                                        break;
                                    }
                                }
                            }
                        }
                    }


                    //Mage::log(" Selected val1 " . $selectValue1, Zend_Log::DEBUG, "abc.log", true);



                    if($selectValue1 && !$selectValue2)
                    {

                        $core_ses->setData("selAtr1",$selAtr1);
                        $core_ses->setData("selectValue1",$selectValue1);
                        $core_ses->setData("child",$childid);



                        $core_ses->unsetData("selAtr2");
                        $core_ses->unsetData("selectValue2");


//                        $this->_redirectUrl(rtrim(Mage::getBaseUrl(), '/') . $this->getRequest()->getRequestString() . '?metal=' . $selectedColorText.'&selectValue1='.$selectValue1.'&selAtr1='.$selAtr1.'&child='.$childid);
                        $this->_redirectUrl(rtrim(Mage::getBaseUrl(), '/') . $this->getRequest()->getRequestString() . '?metal=' . $selectedColorText);

                    }
                    else if($selectValue1 && $selectValue2)
                    {

                        $core_ses->setData("selAtr1",$selAtr1);
                        $core_ses->setData("selectValue1",$selectValue1);
                        $core_ses->setData("selAtr2",$selAtr2);
                        $core_ses->setData("selectValue2",$selectValue2);
                        $core_ses->setData("child",$childid);

//                        $this->_redirectUrl(rtrim(Mage::getBaseUrl(), '/') . $this->getRequest()->getRequestString() . '?metal=' . $selectedColorText.'&selectValue1='.$selectValue1.'&selAtr1='.$selAtr1.'&selectValue2='.$selectValue2.'&selAtr2='.$selAtr2.'&child='.$childid);
                        $this->_redirectUrl(rtrim(Mage::getBaseUrl(), '/') . $this->getRequest()->getRequestString() . '?metal=' . $selectedColorText);

                    }
                    else
                    {


                        $this->_redirectUrl(rtrim(Mage::getBaseUrl(), '/') . $this->getRequest()->getRequestString() . '?metal=' . $selectedColorText);

                    }
                    return;
                }
            }
        }

        // Render page
        try {
            $viewHelper->prepareAndRender($productId, $this, $params);
        } catch (Exception $e) {
            if ($e->getCode() == $viewHelper->ERR_NO_PRODUCT_LOADED) {
                if (isset($_GET['store'])  && !$this->getResponse()->isRedirect()) {
                    $this->_redirect('');
                } elseif (!$this->getResponse()->isRedirect()) {
                    echo 'ERROR!!! '.$e->getMessage();
                    //$this->_forward('noRoute');
                }
            } else {
                Mage::logException($e);
                $this->_forward('noRoute');
            }
        }
    }    
}
