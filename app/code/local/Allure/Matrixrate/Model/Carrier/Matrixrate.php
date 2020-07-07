<?php
/**
 * Webshopapps Shipping Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * Shipping MatrixRates
 *
 * @category   Webshopapps
 * @package    Webshopapps_Matrixrate
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Karen Baker <sales@webshopapps.com>
*/

class Allure_Matrixrate_Model_Carrier_Matrixrate
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'matrixrate';
    protected $_default_condition_name = 'package_weight';

    protected $_conditionNames = array();
    
    protected $_result = null;

    public function __construct()
    {
        parent::__construct();
        foreach ($this->getCode('condition_name') as $k=>$v) {
            $this->_conditionNames[] = $k;
        }
    }
    

    /**
     * Enter description here...
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        
        $matrixRateHelper = Mage::helper("matrixrate");
        $isShowMatrixRate = $matrixRateHelper->isShowMatrixRate();
        if(!$isShowMatrixRate) return false;     
        
        $routeName = Mage::app()->getRequest()->getRouteName();
        if($routeName == "adminhtml"){
            $isAllowToBackend = $matrixRateHelper->isAllowMatrixrateToBackend();
            if(!$isAllowToBackend) return false;
        }else{
            $isAllowToFrontend = $matrixRateHelper->isAllowMatrixrateToFrontend();
            if(!$isAllowToFrontend) return false;
        }
        
        /* $arr = array('sales_order','sales_order_create','sales_shipping');
        $controllerName = Mage::app()->getRequest()->getControllerName();
        if(in_array($controllerName, $arr)){
            return false;
        } */
        
        // exclude Virtual products price from Package value if pre-configured
        if (!$this->getConfigFlag('include_virtual_price') && $request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getProduct()->isVirtual() || $item->getProductType() == 'downloadable' || $item->getProductType() == 'giftcards') {
                            $request->setPackageValue($request->getPackageValue() - $child->getBaseRowTotal());
                        }
                    }
                } elseif ($item->getProduct()->isVirtual() || $item->getProductType() == 'downloadable' || $item->getProductType() == 'giftcards') {
                    $request->setPackageValue($request->getPackageValue() - $item->getBaseRowTotal());
                }
            }
        }
        
  		// Free shipping by qty
        $freeQty = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $freeQty += $item->getQty() * ($child->getQty() - (is_numeric($child->getFreeShipping()) ? $child->getFreeShipping() : 0));
                        }
                    }
                } elseif ($item->getFreeShipping()) {
                    $freeQty += ($item->getQty() - (is_numeric($item->getFreeShipping()) ? $item->getFreeShipping() : 0));
                }
            }
        }

        if (!$request->getMRConditionName()) {
            $request->setMRConditionName($this->getConfigData('condition_name') ? $this->getConfigData('condition_name') : $this->_default_condition_name);
        }

         // Package weight and qty free shipping
        $oldWeight = $request->getPackageWeight();
        $oldQty = $request->getPackageQty();

		if ($this->getConfigData('allow_free_shipping_promotions') && !$this->getConfigData('include_free_ship_items')) {
			$request->setPackageWeight($request->getFreeMethodWeight());
			$request->setPackageQty($oldQty - $freeQty);
		}

		$this->_result = Mage::getModel('shipping/rate_result');
     	$ratearray = $this->getRate($request);

     	$freeShipping=false;

     	if (is_numeric($this->getConfigData('free_shipping_threshold')) &&
	        $this->getConfigData('free_shipping_threshold')>0 &&
	        $request->getPackageValue()>$this->getConfigData('free_shipping_threshold')) {
	         	$freeShipping=true;
	    }
    	if ($this->getConfigData('allow_free_shipping_promotions') &&
	        ($request->getFreeShipping() === true || 
	        $request->getPackageQty() == $this->getFreeBoxes()))
        {
         	$freeShipping=true;
        }
        /* if ($freeShipping)
        {
		  	$method = Mage::getModel('shipping/rate_result_method');
			$method->setCarrier('matrixrate');
			//$method->setCarrierTitle($this->getConfigData('title'));
			$method->setMethod('matrixrate_free');
			$method->setPrice('0.00');
			$method->setMethodTitle($this->getConfigData('free_method_text'));
			$result->append($method);

			if ($this->getConfigData('show_only_free')) {
				return $result;
			}
		} */
		
		//country id
		$countryId = $request->getDestCountryId();
		$isDomestic = (strtolower($countryId) == "us") ? 0 :1;

	   foreach ($ratearray as $rate)
		{
		   if (!empty($rate) && $rate['price'] >= 0) {
			  $method = Mage::getModel('shipping/rate_result_method');

			  if($isDomestic == $rate['is_international']) {
			    
				$method->setCarrier('matrixrate');
				//$method->setCarrierTitle($this->getConfigData('title'));

				$method->setMethod('matrixrate_'.$rate['pk'].'#'.$rate['is_signature'].'#'.$rate['is_international']);

				$method->setMethodTitle(Mage::helper('matrixrate')->__($rate['shipping_name']));

				$shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);
				$method->setCost($rate['cost']);
				$method->setShippingName($rate['shipping_name']);

				/*free international shipping MT-1471*/
                  if($freeShipping && $rate['is_international'])
                      $method->setPrice(0.00);
                  else
                      $method->setPrice($shippingPrice);

                  

				$this->_result->append($method);
			  }
			}
		}

		$this->_updateFreeMethodQuote($request);

		return $this->_result;
    }

    public function getRate(Mage_Shipping_Model_Rate_Request $request)
    {
        return Mage::getResourceModel('matrixrate_shipping/carrier_matrixrate')->getNewRate($request,$this->getConfigFlag('zip_range'));
    }
    
    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        //return array('matrixrate'=>$this->getConfigData('name'));
        
        $arr  = array();
        $shippingRates = Mage::getResourceModel('matrixrate_shipping/carrier_matrixrate_collection');
        foreach ($shippingRates as $rate){
            $value = $rate->getPk()."#".$rate->getIsSignature()."#".$rate->getIsInternational();
            $arr[$value] = $rate->getShippingName();
        }
        return $arr;
        
    }
    

    public function getCode($type, $code='')
    {
        $codes = array(

            'condition_name'=>array(
                'package_weight' => Mage::helper('shipping')->__('Weight vs. Destination'),
                'package_value'  => Mage::helper('shipping')->__('Price vs. Destination'),
                'package_qty'    => Mage::helper('shipping')->__('# of Items vs. Destination'),
            ),

            'condition_name_short'=>array(
                'package_weight' => Mage::helper('shipping')->__('Weight'),
                'package_value'  => Mage::helper('shipping')->__('Order Subtotal'),
                'package_qty'    => Mage::helper('shipping')->__('# of Items'),
            ),

        );

        if (!isset($codes[$type])) {
            throw Mage::exception('Mage_Shipping', Mage::helper('shipping')->__('Invalid Matrix Rate code type: %s', $type));
        }

        if (''===$code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            throw Mage::exception('Mage_Shipping', Mage::helper('shipping')->__('Invalid Matrix Rate code for type %s: %s', $type, $code));
        }

        return $codes[$type][$code];
    }



}
