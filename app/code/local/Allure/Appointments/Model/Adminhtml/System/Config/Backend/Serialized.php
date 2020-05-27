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
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2019 Magento, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Allure_Appointments_Model_Adminhtml_System_Config_Backend_Serialized extends Mage_Core_Model_Config_Data
{
    protected function _afterLoad()
    {
        if (!is_array($this->getValue())) {
            $serializedValue = $this->getValue();
            $unserializedValue = false;
            if (!empty($serializedValue)) {
                try {
                    $unserializedValue = Mage::helper('core/unserializeArray')
                        ->unserialize($serializedValue);
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
            $this->setValue($unserializedValue);
        }
    }

    protected function _beforeSave()
    {
        if (is_array($this->getValue())) {
            /*NEW Code to remove \n from appointment store configuration*/
            $store_data = $this->getValue();
            foreach ($store_data as $key => $value){
                $field_data = $store_data[$key];
                foreach ($field_data as $key_field => $store_info){
                    $store_info = str_replace(array("\n", "\r"), '', $store_info);
                    $store_info = nl2br($store_info);
                    $field_data[$key_field] = $store_info;
                }
                $store_data[$key] = $field_data;
            }
            $this->setValue(serialize($store_data));
        }
    }
}
