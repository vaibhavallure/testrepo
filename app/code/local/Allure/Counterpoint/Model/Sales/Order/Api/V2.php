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
 * @package     Mage_Sales
 * @copyright  Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order API V2
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Allure_Counterpoint_Model_Sales_Order_Api_V2 extends Mage_Sales_Model_Order_Api
{
	
	/**
	 * Retrieve list of orders. Filtration could be applied
	 *
	 * @param null|object|array $filters
	 * @return array
	 */
	public function items($filters = null)
	{
		$orders = array();
		
		$allowedStores = Mage::getStoreConfig('allure_counterpoint/general/stores');
		
		$skipStoreCheck = true;
		
		if ($allowedStores) {
			$allowedStoresList = explode(',', $allowedStores);
			
			if (!in_array(0, $allowedStoresList)) {
				$skipStoreCheck = false;
			}
		}
		
		//TODO: add full name logic
		$billingAliasName = 'billing_o_a';
		$shippingAliasName = 'shipping_o_a';
		
		/** @var $orderCollection Mage_Sales_Model_Mysql4_Order_Collection */
		$orderCollection = Mage::getModel("sales/order")->getCollection();
		$billingFirstnameField = "$billingAliasName.firstname";
		$billingLastnameField = "$billingAliasName.lastname";
		$shippingFirstnameField = "$shippingAliasName.firstname";
		$shippingLastnameField = "$shippingAliasName.lastname";
		$orderCollection->addAttributeToSelect('*')
			->addAddressFields()
			->addExpressionFieldToSelect('billing_firstname', "{{billing_firstname}}",
					array('billing_firstname' => $billingFirstnameField))
			->addExpressionFieldToSelect('billing_lastname', "{{billing_lastname}}",
					array('billing_lastname' => $billingLastnameField))
			->addExpressionFieldToSelect('shipping_firstname', "{{shipping_firstname}}",
					array('shipping_firstname' => $shippingFirstnameField))
			->addExpressionFieldToSelect('shipping_lastname', "{{shipping_lastname}}",
					array('shipping_lastname' => $shippingLastnameField))
			->addExpressionFieldToSelect('billing_name', "CONCAT({{billing_firstname}}, ' ', {{billing_lastname}})",
					array('billing_firstname' => $billingFirstnameField, 'billing_lastname' => $billingLastnameField))
			->addExpressionFieldToSelect('shipping_name', 'CONCAT({{shipping_firstname}}, " ", {{shipping_lastname}})',
					array('shipping_firstname' => $shippingFirstnameField, 'shipping_lastname' => $shippingLastnameField)
		);
												
		/** @var $apiHelper Mage_Api_Helper_Data */
		$apiHelper = Mage::helper('api');
		$filters = $apiHelper->parseFilters($filters, $this->_attributesMap['order']);
		try {
			foreach ($filters as $field => $value) {
				
				if ($field == 'store_id' && !$skipStoreCheck && !in_array($value, $allowedStoresList)) {
					if (count($allowedStoresList) >= 1) {
						$orderCollection->addFieldToFilter($field, array('in' => $allowedStoresList));
					} else {
						$orderCollection->addFieldToFilter($field, $allowedStoresList[0]);
					}
				} else {
					$orderCollection->addFieldToFilter($field, $value);
				}
			}
		} catch (Mage_Core_Exception $e) {
			$this->_fault('filters_invalid', $e->getMessage());
		}
		
		foreach ($orderCollection as $order) {
			$orders[] = $this->_getAttributes($order, 'order');
		}
		return $orders;
	}
}
