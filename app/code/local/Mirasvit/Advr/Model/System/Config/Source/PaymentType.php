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



class Mirasvit_Advr_Model_System_Config_Source_PaymentType extends Varien_Object
{
    public function toOptionArray()
    {
        $methods = array();

        foreach (array_keys($this->getCollection()) as $paymentCode) {
            $paymentTitle = Mage::getStoreConfig('payment/' . $paymentCode . '/title');
            if ($paymentTitle && $paymentCode) {
                $methods[$paymentCode] = array(
                    'label' => $paymentTitle,
                    'value' => $paymentCode,
                );
            }
        }

        return $methods;

    }

    public function toOptionHash()
    {
        $methods = array();

        foreach (array_keys($this->getCollection()) as $paymentCode) {
            $paymentTitle = Mage::getStoreConfig('payment/' . $paymentCode . '/title');
            if ($paymentTitle && $paymentCode) {
                $methods[$paymentCode] = $paymentTitle;
            }
        }

        return $methods;
    }

    protected function getCollection()
    {
        return Mage::getSingleton('payment/config')->getActiveMethods();

    }
}