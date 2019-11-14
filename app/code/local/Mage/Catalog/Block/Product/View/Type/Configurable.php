<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright  Copyright (c) 2006-2019 Magento, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog super product configurable part block
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Block_Product_View_Type_Configurable extends Mage_Catalog_Block_Product_View_Abstract
{
    /**
     * Prices
     *
     * @var array
     */
    protected $_prices      = array();

    /**
     * Prepared prices
     *
     * @var array
     */
    protected $_resPrices   = array();

    /**
     * Get helper for calculation purposes
     *
     * @return Mage_Catalog_Helper_Product_Type_Composite
     */
    protected function _getHelper()
    {
        return $this->helper('catalog/product_type_composite');
    }

    /**
     * Get allowed attributes
     *
     * @return array
     */
    public function getAllowAttributes()
    {
        return $this->getProduct()->getTypeInstance(true)
            ->getConfigurableAttributes($this->getProduct());
    }

    /**
     * Check if allowed attributes have options
     *
     * @return bool
     */
    public function hasOptions()
    {
        $attributes = $this->getAllowAttributes();
        if (count($attributes)) {
            foreach ($attributes as $attribute) {
                /** @var Mage_Catalog_Model_Product_Type_Configurable_Attribute $attribute */
                if ($attribute->getData('prices')) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get Allowed Products
     *
     * @return array
     */
    public function getAllowProducts()
    {
        if (!$this->hasAllowProducts()) {
            $products = array();
            $skipSaleableCheck = Mage::helper('catalog/product')->getSkipSaleableCheck();
            $allProducts = $this->getProduct()->getTypeInstance(true)
                ->getUsedProducts(null, $this->getProduct());
            foreach ($allProducts as $product) {
                if ($product->isSaleable()
                    || $skipSaleableCheck
                    || (!$product->getStockItem()->getIsInStock()
                        && Mage::helper('cataloginventory')->isShowOutOfStock())) {
                    $products[] = $product;
                }
            }
            $this->setAllowProducts($products);
        }
        return $this->getData('allow_products');
    }

    /**
     * retrieve current store
     *
     * @deprecated
     * @return Mage_Core_Model_Store
     */
    public function getCurrentStore()
    {
        return $this->_getHelper()->getCurrentStore();
    }

    /**
     * Returns additional values for js config, con be overriden by descedants
     *
     * @return array
     */
    protected function _getAdditionalConfig()
    {
        return array();
    }

    /**
     * Composes configuration for js
     *
     * @return string
     */
    public function getJsonConfig()
    {
        $_currency = Mage::app()->getStore()->getCurrentCurrency();
        $_currency_usd = Mage::app()->getStore()->getBaseCurrency();
        $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
        $currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();

        $attributes = array();
        $options    = array();
        $store      = $this->getCurrentStore();
        $taxHelper  = Mage::helper('tax');
        $currentProduct = $this->getProduct();
        $showOutOfStockProducts=Mage::getStoreConfig("cataloginventory/options/show_out_of_stock");

        $preconfiguredFlag = $currentProduct->hasPreconfiguredValues();
        if ($preconfiguredFlag) {
            $preconfiguredValues = $currentProduct->getPreconfiguredValues();
            $defaultValues       = array();
        }
        $productStock = array();
        foreach ($this->getAllowProducts() as $product) {
            $productId  = $product->getId();
            $productStock[$productId] = $product->getStockItem()->getIsInStock();
            foreach ($this->getAllowAttributes() as $attribute) {
                $productAttribute   = $attribute->getProductAttribute();

                if (!$productAttribute) continue;

                $productAttributeId = $productAttribute->getId();
                $attributeValue     = $product->getData($productAttribute->getAttributeCode());
                if (!isset($options[$productAttributeId])) {
                    $options[$productAttributeId] = array();
                }

                if (!isset($options[$productAttributeId][$attributeValue])) {
                    $options[$productAttributeId][$attributeValue] = array();
                }
                $options[$productAttributeId][$attributeValue][] = $productId;
            }
        }

        $this->_resPrices = array(
            $this->_preparePrice($currentProduct->getFinalPrice())
        );

        foreach ($this->getAllowAttributes() as $attribute) {
            $productAttribute = $attribute->getProductAttribute();

            if (!$productAttribute) continue;

            $attributeId = $productAttribute->getId();
            $info = array(
                'id'        => $productAttribute->getId(),
                'code'      => $productAttribute->getAttributeCode(),
                'label'     => $attribute->getLabel(),
                'options'   => array()
            );

            $optionPrices = array();
            $prices = $attribute->getPrices();
            if (is_array($prices)) {
                foreach ($prices as $value) {
                    if(!$this->_validateAttributeValue($attributeId, $value, $options)) {
                        continue;
                    }
                    $currentProduct->setConfigurablePrice(
                        $this->_preparePrice($value['pricing_value'], $value['is_percent'])
                    );
                    $currentProduct->setParentId(true);
                    Mage::dispatchEvent(
                        'catalog_product_type_configurable_price',
                        array('product' => $currentProduct)
                    );
                    $configurablePrice = $currentProduct->getConfigurablePrice();

                    if (isset($options[$attributeId][$value['value_index']])) {
                        $productsIndexOptions = $options[$attributeId][$value['value_index']];
                        $productsIndex = array();
                        foreach ($productsIndexOptions as $productIndex) {

                            //Existing code

                            /* if ($productStock[$productIndex]) {
                             $productsIndex[] = $productIndex;
                             } */

                            //Allure code

                            if(!$showOutOfStockProducts){
                                if ($productStock[$productIndex]) {
                                    $productsIndex[] = $productIndex;
                                }
                            } else{
                                $productsIndex[] = $productIndex;
                            }
                        }
                    } else {
                        $productsIndex = array();
                    }
                    if ($currentProduct->getSku() == 'STORECARD') {
                        if ($_currency->getCurrencyCode() == $_currency_usd->getCurrencyCode()) {
                            $labeldisp = $_currency_usd->formatTxt($value['label']);
                        }else{
                            $labeldisp = $_currency_usd->formatTxt($value['label'])." (".$_currency->formatTxt(Mage::helper('directory')->currencyConvert($value['label'],$baseCurrencyCode, $currentCurrencyCode)).")";
                        }
                    }else{
                        $labeldisp =  $value['label'];
                    }

                    $info['options'][] = array(
                        'id'        => $value['value_index'],
                        'label'     => $labeldisp,
                        'price'     => $configurablePrice,
                        'oldPrice'  => $this->_prepareOldPrice($value['pricing_value'], $value['is_percent']),
                        'products'  => $productsIndex,
                    );
                    $optionPrices[] = $configurablePrice;
                }
            }
            /**
             * Prepare formated values for options choose
             */
            foreach ($optionPrices as $optionPrice) {
                foreach ($optionPrices as $additional) {
                    $this->_preparePrice(abs($additional-$optionPrice));
                }
            }
            if($this->_validateAttributeInfo($info)) {
                $attributes[$attributeId] = $info;
            }

            // Add attribute default value (if set)
            if ($preconfiguredFlag) {
                $configValue = $preconfiguredValues->getData('super_attribute/' . $attributeId);
                if ($configValue) {
                    $defaultValues[$attributeId] = $configValue;
                }
            }
        }

        $taxCalculation = Mage::getSingleton('tax/calculation');
        if (!$taxCalculation->getCustomer() && Mage::registry('current_customer')) {
            $taxCalculation->setCustomer(Mage::registry('current_customer'));
        }

        $_request = $taxCalculation->getDefaultRateRequest();
        $_request->setProductClassId($currentProduct->getTaxClassId());
        $defaultTax = $taxCalculation->getRate($_request);

        $_request = $taxCalculation->getRateRequest();
        $_request->setProductClassId($currentProduct->getTaxClassId());
        $currentTax = $taxCalculation->getRate($_request);

        $taxConfig = array(
            'includeTax'        => $taxHelper->priceIncludesTax(),
            'showIncludeTax'    => $taxHelper->displayPriceIncludingTax(),
            'showBothPrices'    => $taxHelper->displayBothPrices(),
            'defaultTax'        => $defaultTax,
            'currentTax'        => $currentTax,
            'inclTaxTitle'      => Mage::helper('catalog')->__('Incl. Tax')
        );

        $config = array(
            'attributes'        => $attributes,
            'template'          => str_replace('%s', '#{price}', $store->getCurrentCurrency()->getOutputFormat()),
            'basePrice'         => $this->_registerJsPrice($this->_convertPrice($currentProduct->getFinalPrice())),
            'oldPrice'          => $this->_registerJsPrice($this->_convertPrice($currentProduct->getPrice())),
            'productId'         => $currentProduct->getId(),
            'chooseText'        => Mage::helper('catalog')->__('Choose an Option...'),
            'taxConfig'         => $taxConfig
        );

        if ($preconfiguredFlag && !empty($defaultValues)) {
            $config['defaultValues'] = $defaultValues;
        }

        $config = array_merge($config, $this->_getAdditionalConfig());

        return Mage::helper('core')->jsonEncode($config);
    }

    /**
     * Validating of super product option value
     *
     * @param array $attributeId
     * @param array $value
     * @param array $options
     * @return boolean
     */
    protected function _validateAttributeValue($attributeId, &$value, &$options)
    {
        if(isset($options[$attributeId][$value['value_index']])) {
            return true;
        }

        return false;
    }

    /**
     * Validation of super product option
     *
     * @param array $info
     * @return boolean
     */
    protected function _validateAttributeInfo(&$info)
    {
        if(count($info['options']) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Calculation real price
     *
     * @deprecated
     * @param float $price
     * @param bool $isPercent
     * @return mixed
     */
    protected function _preparePrice($price, $isPercent = false)
    {
        return $this->_getHelper()->preparePrice($this->getProduct(), $price, $isPercent);
    }

    /**
     * Calculation price before special price
     *
     * @deprecated
     * @param float $price
     * @param bool $isPercent
     * @return mixed
     */
    protected function _prepareOldPrice($price, $isPercent = false)
    {
        return $this->_getHelper()->prepareOldPrice($this->getProduct(), $price, $isPercent);
    }

    /**
     * Replace ',' on '.' for js
     *
     * @deprecated
     * @param float $price
     * @return string
     */
    protected function _registerJsPrice($price)
    {
        return $this->_getHelper()->registerJsPrice($price);
    }

    /**
     * Convert price from default currency to current currency
     *
     * @deprecated
     * @param float $price
     * @param boolean $round
     * @return float
     */
    protected function _convertPrice($price, $round = false)
    {
        return $this->_getHelper()->convertPrice($price, $round,null);
    }



    /*allure code------------------------------*/
    public function getInStockAttribute()
    {

        $selectedColor = $this->getRequest()->getParam("colorOptionId", false);
        $optionHelper = Mage::helper('allure_catalog');
        $productId=$this->getProduct()->getId();


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




        $product =$this->getProduct();

        //don't allow for wholesale customer
        if ($product->getTypeId() == 'giftcards'){
            if(Mage::getSingleton('customer/session')->getCustomerGroupId() == 2){
                $this->_redirectUrl(Mage::getBaseUrl());
            }
        }



        if ($product->isConfigurable()) {
        if (!$selectedColor) {


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




                    if($selectValue1 && !$selectValue2)
                    {

                        echo "var id = 'amconf-image-".$selectedColor."';";

                        echo "\n if($(id)){
                            $(id).simulate('click');
                                }";
                        echo '\n spConfig.setInitialState("'.$selAtr1.'",'.$selectValue1.');';

                    }
                    else if($selectValue1 && $selectValue2)
                    {

                        echo "var id = 'amconf-image-".$selectedColor."';";

                        echo "\n if($(id)){
                            $(id).simulate('click');
                                }";
                        echo "\n spConfig.setInitialState('attribute".$selAtr1."','".$selectValue1."');";
                        echo "\n spConfig.setInitialState('attribute".$selAtr2."','".$selectValue2."');";

                    }
                    else
                    {
                        echo "var id = 'amconf-image-".$selectedColor."';";


                        echo "\n if($(id)){
                            $(id).simulate('click');
                                }";
                    }
                }
            }

    }

}
