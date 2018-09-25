<?php
/**
 * MultiCurrency Helper
 * @author:AL024
 */
class Allure_MultiCurrency_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getConfig($key)
    {
        return Mage::getStoreConfig('multicurrency/currency_attr/'.$key);
    }

    public function isEnabled()
    {
        return $this->isModuleEnabled() && $this->getConfig('status') && Mage::helper('multicurrency')->isEnabled();
    }

    public function isEnabledOnFrontEnd()
    {
        return !Mage::app()->getStore()->isAdmin() && $this->isEnabled();
    }

    public function getMapping()
    {
        return $this->getConfig('mapping');
    }

    public function getMappingArray()
    {
        return unserialize($this->getMapping());
    }


    /**
     * getCurrencyAttribute accept currency code and return corresponding custom price attribute code
     */
    public function getCurrencyAttribute($countryCode)
    {


        foreach ($this->getMappingArray() as $ma)
        {
            if($countryCode==$ma['countryCode']) {

                $entity = 'catalog_product';
                $code = $ma['attrCode'];
                $attr = Mage::getResourceModel('catalog/eav_attribute')->loadByCode($entity,$code);

                if($attr->getId())
                    return $ma['attrCode'];
                else
                    return false;
            }
        }

        return false;
    }

    public function getCurrencyCode($countryCode)
    {

        foreach ($this->getMappingArray() as $ma)
        {
            if($countryCode==$ma['countryCode']) {
                return $ma['currencyCode'];
            }
        }

        return false;
    }

    public function getCurrentCountryCurrencyCode()
    {

        foreach ($this->getMappingArray() as $ma)
        {
            if(Mage::getSingleton('core/session')->getGeoCountry()==$ma['countryCode']) {
                return $ma['currencyCode'];
            }
        }

        return false;
    }
    /**
     * currencyAttrAvailable checks if custom price attribute present for current currency code
     */
    public function currencyAttrAvailable()
    {
       if($this->getCurrencyAttribute(Mage::getSingleton('core/session')->getGeoCountry()))
           return $this->getCurrencyAttribute(Mage::getSingleton('core/session')->getGeoCountry());
       else
           return false;
    }

    /**
     * getCustomAttrPrice accept product object and return corresponding custom price
     */
    public function getCustomAttrPrice($product)
    {

        if($this->currencyAttrAvailable()) {
            $product=Mage::getModel("catalog/product")->load($product->getId());
            $price = $product->getData($this->currencyAttrAvailable());
            if($price && $price>0)
                    return (float)$price;
        }
        return false;
    }


    public function getCustomAttrPriceByProductId($product_id)
    {

        if($this->currencyAttrAvailable()) {
            $product=Mage::getModel("catalog/product")->load($product_id);
            $price = $product->getData($this->currencyAttrAvailable());
            if($price && $price>0)
                return (float)$price;
        }
        return false;
    }



    /**
     * isValidCustomAttrPrice check if product contain valid custom price attribute
     */
    public function isValidCustomAttrPrice($product)
    {
        if($this->getCustomAttrPrice($product))
            return true;
        else
            return false;
    }


    public function getDebugMode()
    {
        return $this->getConfig('debug');
    }


}
	 