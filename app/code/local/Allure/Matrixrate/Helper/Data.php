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
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Shipping
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
  * @copyright  Copyright (c) 2010 Zowta Ltd (http://www.webshopapps.com)
  * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
  * @author     Karen Baker <sales@webshopapps.com>
*/

/**
 * Shipping data helper
 */
class Allure_Matrixrate_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_MATRIXRATE_SHOWN_STATUS               = 'allure_matrixrate/settings/status';
    const XML_MATRIXRATE_ALLOW_TO_FRONTEND          = 'allure_matrixrate/settings/allow_to_frontend';
    const XML_MATRIXRATE_ALLOW_TO_BACKEND           = 'allure_matrixrate/settings/allow_to_backend';
    const XML_ALLOW_DEFAULT_SHIPPING_TO_FRONDEND    = 'allure_matrixrate/settings/allow_default_shipping_to_frontend';
    const XML_ALLOW_DEFAULT_SHIPPING_TO_BACKEND     = 'allure_matrixrate/settings/allow_default_shipping_to_backend';

    /**
     * Check matrixrate shown status
     * @return mixed|string|NULL
     */
    public function isShowMatrixRate()
    {
        return Mage::getStoreConfig(self::XML_MATRIXRATE_SHOWN_STATUS);
    }
    
    /**
     * Check matrixrate shipping method allow to frontend
     * @return mixed|string|NULL
     */
    public function isAllowMatrixrateToFrontend()
    {
        return Mage::getStoreConfig(self::XML_MATRIXRATE_ALLOW_TO_FRONTEND);
    }

    /**
     * Check matrixrate shipping method allow to backend
     * @return mixed|string|NULL
     */
    public function isAllowMatrixrateToBackend()
    {
        return Mage::getStoreConfig(self::XML_MATRIXRATE_ALLOW_TO_BACKEND);
    }
    
    /**
     * Check other shipping method allow to frontend
     * @return mixed|string|NULL
     */
    public function isAllowDefaultShippingMethodsToFrontend()
    {
        return Mage::getStoreConfig(self::XML_ALLOW_DEFAULT_SHIPPING_TO_FRONDEND);
    }
    
    /**
     * Check other shipping method allow to frontend
     * @return mixed|string|NULL
     */
    public function isAllowDefaultShippingMethodsToBackend()
    {
        return Mage::getStoreConfig(self::XML_ALLOW_DEFAULT_SHIPPING_TO_BACKEND);
    }
    
}
