<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_advr
 * @version   1.0.40
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Advr_Model_System_Config_Source_SalesSource
{
    const SALES_SOURCE_ORDER   = 0;

    const SALES_SOURCE_INVOICE = 1;

    public function toOptionArray()
    {
        $result = array(
            array(
                'value' => self::SALES_SOURCE_ORDER,
                'label' => Mage::helper('advr')->__('Orders')
            ),
            array(
                'value' => self::SALES_SOURCE_INVOICE,
                'label' => Mage::helper('advr')->__('Invoices')
            ),
        );

        return $result;
    }
}
