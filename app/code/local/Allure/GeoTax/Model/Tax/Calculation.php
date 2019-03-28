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
* @package     Mage_Tax
* @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
 * Tax Calculation Model
*
* @author Magento Core Team <core@magentocommerce.com>
*/
class Allure_GeoTax_Model_Tax_Calculation extends Mage_Tax_Model_Calculation
{
    /**
     * GeoLocation helper
     *
     * @var Allure_GeoLocation_Helper_Data
     */
    protected $_geoLocationHelper;

    /**
     * GeoTax helper
     *
     * @var Allure_GeoTax_Helper_Data
     */
    protected $_geoTaxHelper;

    /**
     * Initialize tax helper
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        parent::__construct();

        $this->_geoLocationHelper 	=  Mage::helper('allure_geolocation');
        $this->_geoTaxHelper 		=  Mage::helper('allure_geotax');
    }

    /**
     * Get request object with information necessary for getting tax rate
     * Request object contain:
     *  country_id (->getCountryId())
     *  region_id (->getRegionId())
     *  postcode (->getPostcode())
     *  customer_class_id (->getCustomerClassId())
     *  store (->getStore())
     *
     * @param   null|false|Varien_Object $shippingAddress
     * @param   null|false|Varien_Object $billingAddress
     * @param   null|int $customerTaxClass
     * @param   null|int $store
     * @return  Varien_Object
     */
    public function getRateRequest(
            $shippingAddress = null,
            $billingAddress = null,
            $customerTaxClass = null,
            $store = null)
    {
         
        if (!$this->_geoTaxHelper->canApplyGeoTax()) {
            return parent::getRateRequest($shippingAddress, $billingAddress, $customerTaxClass, $store);
        }
         
        if ($shippingAddress === false && $billingAddress === false && $customerTaxClass === false) {
            return $this->getRateOriginRequest($store);
        }
        $address = new Varien_Object();
        $customer = $this->getCustomer();
        $basedOn = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_BASED_ON, $store);

        if (($shippingAddress === false && $basedOn == 'shipping')
                || ($billingAddress === false && $basedOn == 'billing')
                ) {
                    $basedOn = 'default';
                } else {
                    if ((($billingAddress === false || is_null($billingAddress) || !$billingAddress->getCountryId())
                            && $basedOn == 'billing')
                            || (($shippingAddress === false || is_null($shippingAddress) || !$shippingAddress->getCountryId())
                                    && $basedOn == 'shipping')
                            ) {
                                if ($customer) {
                                    $defBilling = $customer->getDefaultBillingAddress();
                                    $defShipping = $customer->getDefaultShippingAddress();

                                    if ($basedOn == 'billing' && $defBilling && $defBilling->getCountryId()) {
                                        $billingAddress = $defBilling;
                                    } else if ($basedOn == 'shipping' && $defShipping && $defShipping->getCountryId()) {
                                        $shippingAddress = $defShipping;
                                    } else {
                                        $basedOn = 'default';
                                    }
                                } else {
                                    $basedOn = 'default';
                                }
                            }
                }

                switch ($basedOn) {
                    case 'billing':
                        $address = $billingAddress;
                        break;
                    case 'shipping':
                        $address = $shippingAddress;
                        break;
                    case 'origin':
                        $address = $this->getRateOriginRequest($store);
                        break;
                    case 'default':
                        $address
                        ->setCountryId(Mage::getStoreConfig(
                        Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_COUNTRY,
                        $store))
                        ->setRegionId(Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_REGION, $store))
                        ->setPostcode(Mage::getStoreConfig(
                        Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_POSTCODE,
                        $store));
                        break;
                }

                if (is_null($customerTaxClass) && $customer) {
                    $customerTaxClass = $customer->getTaxClassId();
                } elseif (($customerTaxClass === false) || !$customer) {
                    $customerTaxClass = Mage::getModel('customer/group')
                    ->getTaxClassId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
                }

                $customerSession = Mage::getSingleton('customer/session');

                if (!$customer || !$address->getId()) {
                    $geoAddress = Mage::getSingleton('core/session')->getGeoAddress();
                     
                    if ($geoAddress) {
                        $address = $geoAddress;
                        $geolocation = Mage::getSingleton('allure_geolocation/geoLocation');
                        if($this->_geoTaxHelper->getDebugMode()){
                             Mage::log(sprintf("IP::%s, COUNTRY:: %s, TAXCLASS:: %s",$geolocation->getIpAddress(), $geolocation->getCountryCode(), $customerTaxClass), Zend_Log::DEBUG, 'allure_geotax.log', $this->_geoTaxHelper->getDebugMode());
                        }
                    }
                }

                $customerSession->setTaxCountry($address->getCountryId());

                $request = new Varien_Object();
                $request
                ->setCountryId($address->getCountryId())
                ->setRegionId($address->getRegionId())
                ->setPostcode($address->getPostcode())
                ->setStore($store)
                ->setCustomerClassId($customerTaxClass);
                return $request;
    }
    
    
    /**
     * custom method
     * get tax rate object data
     */
    public function getTaxPercentOfProduct($request, $tax, $price){
        $resource     = Mage::getSingleton('core/resource');
        $readAdapter  = $resource->getConnection('core_read');
        $writeAdapter = $resource->getConnection('core_write');
        
        $countryId  = $request->getCountryId();
        $regionId   = $request->getRegionId();
        $postcode   = $request->getPostcode();
        
        $select = $readAdapter->select()
        ->from(array('rate' => Mage::getSingleton('core/resource')->getTableName('tax/tax_calculation_rate')))
        ->where('rate.tax_country_id = ?', $countryId)
        ->where("rate.tax_region_id IN(?)", array(0, (int)$regionId));
        
        $expr = $writeAdapter->getCheckSql(
            'zip_is_range is NULL',
            $writeAdapter->quoteInto(
                "rate.tax_postcode IS NULL OR rate.tax_postcode IN('*', '', ?)",
                $this->createSearchPostCodeTemplates($postcode)
                ),
            $writeAdapter->quoteInto('? BETWEEN rate.zip_from AND rate.zip_to', $postcode)
            );
        $select->where($expr);
        
        $rateInfo = $readAdapter->fetchAll($select);
        
        if(count($rateInfo)){
            $rateObj = $rateInfo[0];
            if($rateObj["is_min_tax_amount"]){
                $minAmountTax = $rateObj["min_tax_amount"];
                if($price <= $minAmountTax){
                    return 0;
                }
            }
        }
        return $tax;
    }
    
    
    
    public function getTaxRateObject($request){
        $resource     = Mage::getSingleton('core/resource');
        $readAdapter  = $resource->getConnection('core_read');
        $writeAdapter = $resource->getConnection('core_write');
        
        $countryId  = $request->getCountryId();
        $regionId   = $request->getRegionId();
        $postcode   = $request->getPostcode();
        
        $select = $readAdapter->select()
        ->from(array('rate' => Mage::getSingleton('core/resource')->getTableName('tax/tax_calculation_rate')))
        ->where('rate.tax_country_id = ?', $countryId)
        ->where("rate.tax_region_id IN(?)", array(0, (int)$regionId));
        
        $expr = $writeAdapter->getCheckSql(
            'zip_is_range is NULL',
            $writeAdapter->quoteInto(
                "rate.tax_postcode IS NULL OR rate.tax_postcode IN('*', '', ?)",
                $this->createSearchPostCodeTemplates($postcode)
                ),
            $writeAdapter->quoteInto('? BETWEEN rate.zip_from AND rate.zip_to', $postcode)
            );
        $select->where($expr);
        
        $rateInfo = $readAdapter->fetchAll($select);
        
        if(count($rateInfo)){
            return $rateInfo[0];
        }
        return null;
    }
    
    
    
    /**
     * custom method
     */
    protected  function createSearchPostCodeTemplates($postcode){
        $len = Mage::helper('tax')->getPostCodeSubStringLength();
        $strlen = strlen($postcode);
        if ($strlen > $len) {
            $postcode = substr($postcode, 0, $len);
            $strlen = $len;
        }
        
        $strArr = array((string)$postcode, $postcode . '*');
        if ($strlen > 1) {
            for ($i = 1; $i < $strlen; $i++) {
                $strArr[] = sprintf('%s*', substr($postcode, 0, - $i));
            }
        }
        return $strArr;
    }
    
}