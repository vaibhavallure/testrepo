<?php

class Allure_Catalog_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Get adminhtml helper
     *
     * @return Allure_Catalog_Helper_Adminhtml
     */
    public function getAdminhtmlHelper ()
    {
        return Mage::helper('allure_catalog/adminhtml');
    }

    /**
     * Get config
     *
     * @return Allure_Catalog_Model_Config
     */
    public function getConfig ()
    {
        return Mage::getSingleton('allure_catalog/config');
    }

    /**
     *
     * @param int $stockId
     *
     * @return string
     */
    public function getWebsiteTitleByStockId ($stockId)
    {
        $website = Mage::getModel("core/website")->load($stockId, 'stock_id');
        if ($website) {
            return $website->getName();
        } else {
            return null;
        }
    }

    public function getStockIds ()
    {
        $collection = Mage::getModel("core/website")->getCollection()->addFieldToFilter(
                'stock_id', array(
                        'neq' => 0
                ));
        $ids = array();
        foreach ($collection as $stock) {
            array_push($ids, $stock->getStockId());
        }
        return $ids;
    }

    public function getStoreIdsByUsingStockIds ()
    {
        $stockIds = $this->getStockIds();
        $storeIds = array();
        foreach ($stockIds as $stockId) {
            $websiteId = Mage::getModel("core/website")->load($stockId,
                    'stock_id')->getWebsiteId();
            $storeId = Mage::getModel("core/store")->load($websiteId,
                    'website_id')->getStoreId();
            $storeIds[$stockId] = $storeId;
        }
        return $storeIds;
    }

    public function getProductPriceHelper ()
    {
        return $this->getProductHelper()->getPriceHelper();
    }

    public function getProductHelper ()
    {
        return Mage::helper('allure_catalog/catalog_product');
    }

    /**
     * check product has custom option present or not
     * for parent child
     */
    public function isCustomOptionsAvailable ($productId)
    {
        $product = Mage::getModel("catalog/product")->load($productId);
        $isOptions = false;
        if (count($product->getOptions()) > 0) {
            $isOptions = true;
        }
        return $isOptions;
    }
    public function isGiftCard ($productId)
    {
        $product = Mage::getSingleton("catalog/product")->load($productId);
        $isGiftCard = false;
       
        $sku=explode('|', $product->getSku());
        $haystack=array('STORECARD'); //Added becuse Giftcard is working as normal product
        if(in_array($sku[0], $haystack))
            $isGiftCard=true;
        
        return $isGiftCard;
    }
    public function getOptionNumber($metalName){
        
        $productModel = Mage::getModel('catalog/product');
        $str_attr_label='metal';
     
        $attr = $productModel->getResource()->getAttribute($str_attr_label);
        $optionsValue = $attr->getSource()->getOptionId($metalName);
        return $optionsValue;
        
    }
    
    public function getOptionText($optiomId){
        
        $productModel = Mage::getModel('catalog/product');
        $str_attr_label='metal';
        
        $attr = $productModel->getResource()->getAttribute($str_attr_label);
        $optionsText = $attr->getSource()->getOptionText($optiomId);
        
        return $optionsText;
        
    }

    public function getChildGemstone($parentProduct)
    {
        $_children = $parentProduct->getTypeInstance()->getUsedProducts($parentProduct);
        $gemstoneWeight="";
        foreach ($_children as $child) {


          $childAttrs = $child->getAttributes();
            foreach ($childAttrs as $childAttr) {

                $attrCode = $childAttr->getData('attribute_id');

              if ($childAttr->getData('attribute_code') == "stone_weight_classification") {

                  if ($childAttr->getData('is_visible_on_front')) {
                        $attrCode = $childAttr->getData('attribute_id');
                        if ($attrCode) {
                            $attribute = $child->getResource()->getAttribute($attrCode);
                            if ($attribute) {
                                $content = $attribute->getFrontend()->getValue($child);
                                if (!empty($content) && (strcasecmp($content, 'yes') != 0 && strcasecmp($content, 'no') != 0)) {
                                    //aws02 - start
                                    $attrLabel = strtolower($attribute->getData('store_label'));
                                    $visibility = ("width" != trim($attrLabel)) ? true : false;
                                    //aws02 - end
                                    if ($visibility) {
                                        $gemstoneWeight= '<tr style="display:' . $visibility . '">
                                        <th class="info-text-two">' . $attribute->getData('store_label') . '</th>
                                        <td class="para-normal">' . Mage::helper('catalog/output')->productAttribute($child, $content, $attrCode) . '</td>
                                      </tr>';
                                    }
                                }
                            }
                        }
                    }
                  break;
                }
            }

            if($gemstoneWeight)
                 break;
        }
        return $gemstoneWeight;
    }
}