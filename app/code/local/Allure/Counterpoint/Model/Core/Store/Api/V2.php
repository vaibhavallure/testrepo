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
 * @package     Mage_Core
 * @copyright  Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Directory Country Api V2
 *
 * @category   Mage
 * @package    Mage_Core
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Allure_Counterpoint_Model_Core_Store_Api_V2 extends Allure_Counterpoint_Model_Core_Store_Api
{
	/**
	 * Retrieve stores list
	 *
	 * @return array
	 */
	public function items()
	{
		// Retrieve stores
		$stores = Mage::app()->getStores();
		
		// Make result array
		$result = array();
		
		$allowedStores = Mage::getStoreConfig('allure_counterpoint/general/stores');
		
		$skipStoreCheck = true;
		
		if ($allowedStores) {
			$allowedStoresList = explode(',', $allowedStores);
			
			if (!in_array(0, $allowedStoresList)) {
				$skipStoreCheck = false;
			}
		}
		
		//Mage::log(debug_backtrace(), Zend_Log::DEBUG, 'api_debug.log', true);
		
		foreach ($stores as $store) {
			
			if (!$skipStoreCheck && !in_array($store->getId(), $allowedStoresList)) {
				continue;
			}
			
			$result[] = array(
					'store_id'    => $store->getId(),
					'code'        => $store->getCode(),
					'website_id'  => $store->getWebsiteId(),
					'group_id'    => $store->getGroupId(),
					'name'        => $store->getName(),
					'sort_order'  => $store->getSortOrder(),
					'is_active'   => $store->getIsActive()
			);
		}
		
		return $result;
	}
}
