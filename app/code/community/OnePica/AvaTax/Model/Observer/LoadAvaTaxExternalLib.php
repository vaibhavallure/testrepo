<?php
/**
 * OnePica_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0), a
 * copy of which is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 * @copyright  Copyright (c) 2015 One Pica, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * Avatax Observer LoadAvaTaxExternalLib
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 */
class OnePica_AvaTax_Model_Observer_LoadAvaTaxExternalLib extends OnePica_AvaTax_Model_Observer_Abstract
{
    /**
     * Avalara lib classes
     *
     * @var array
     */
    protected static $_classes = array(
        'AccountServiceSoap',
        'Address',
        'AddressLocationType',
        'AddressServiceSoap',
        'AddressType',
        'AdjustTaxRequest',
        'AdjustTaxResult',
        'ApplyPaymentRequest',
        'ApplyPaymentResult',
        'ATConfig',
        'ATObject',
        'AvalaraSoapClient',
        'BaseResult',
        'BoundaryLevel',
        'BRBuyerTypeEnum',
        'CancelCode',
        'CancelTaxRequest',
        'CancelTaxResult',
        'CommitTaxRequest',
        'CommitTaxResult',
        'DetailLevel',
        'DocStatus',
        'DocumentType',
        'DynamicSoapClient',
        'Enum',
        'GetAllParameterBagItemsResult',
        'GetParameterBagItemsRequest',
        'GetParameterBagItemsResult',
        'GetTaxHistoryRequest',
        'GetTaxHistoryResult',
        'GetTaxRequest',
        'GetTaxResult',
        'IsAuthorizedResult',
        'JurisdictionType',
        'Line',
        'LocationType',
        'Message',
        'ParameterBag',
        'ParameterBagItem',
        'PingResult',
        'PostTaxRequest',
        'PostTaxResult',
        'ReconcileTaxHistoryRequest',
        'ReconcileTaxHistoryResult',
        'SearchTaxHistoryResult',
        'ServiceMode',
        'SeverityLevel',
        'TaxDetail',
        'TaxLine',
        'TaxOverride',
        'TaxOverrideType',
        'TaxRequest',
        'TaxServiceSoap',
        'TaxType',
        'TextCase',
        'ValidAddress',
        'ValidateRequest',
        'ValidateResult',
    );

    /**
     * Load AvaTax External Lib
     *
     * @return $this
     */
    public function loadAvaTaxExternalLib()
    {
        spl_autoload_register(array($this, 'loadLib'), true, true);
        return $this;
    }

    /**
     * This function can autoloads classes to work with Avalara API
     *
     * @param string $class
     */
    public static function loadLib($class)
    {
        if (in_array($class, self::$_classes)) {
            /** @var OnePica_AvaTax_Helper_Data $helper */
            $helper = Mage::helper('avatax/lib');
            $helper->loadFunctions();
            $helper->loadClass($class);
        }
    }

    /**
     * This an observer function for the event 'controller_front_init_before' and 'default'
     * It prepends our autoloader, so we can load the extra libraries.
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $this->loadAvaTaxExternalLib();
        return $this;
    }
}
