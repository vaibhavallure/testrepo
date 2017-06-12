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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product attribute add/edit form options tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Ecp_Color_Block_Adminhtml_Catalog_Product_Attribute_Edit_Tab_Options extends Mage_Eav_Block_Adminhtml_Attribute_Edit_Options_Abstract {

    public function _toHtml() {
        return parent::_toHtml();
        // ignore extension
        $attribute = Mage::registry('entity_attribute');
        $colours = Mage::getStoreconfig('ecp_color/color_attr');
        $colours = explode(',', $colours);
        if(in_array($attribute->getAttributeCode(), $colours)){
            $this->setTemplate('ecp/options.phtml');
        }
        return parent::_toHtml();
    }
    
    public function getOptionValues()
    {
        return parent::getOptionValues ();
        // ignore extension
        
        $attribute = Mage::registry('entity_attribute');
        $colours = Mage::getStoreconfig('ecp_color/color_attr');
        $colours = explode(',', $colours);
        if(!in_array($attribute->getAttributeCode(), $colours)){
            return parent::getOptionValues ();
        }
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $attributeType = $this->getAttributeObject()->getFrontendInput();
        $defaultValues = $this->getAttributeObject()->getDefaultValue();
        if ($attributeType == 'select' || $attributeType == 'multiselect') {
            $defaultValues = explode(',', $defaultValues);
        } else {
            $defaultValues = array();
        }

        switch ($attributeType) {
            case 'select':
                $inputType = 'radio';
                break;
            case 'multiselect':
                $inputType = 'checkbox';
                break;
            default:
                $inputType = '';
                break;
        }

        $values = $this->getData('option_values');
        if (is_null($values)) {
            $values = array();
            $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter($this->getAttributeObject()->getId())
                ->setPositionOrder('desc', true)
                ->load();
            
            //$optionCollection = Mage::getModel('ecp_color/color')->getCollection();

            foreach ($optionCollection as $option) {
                $value = array();
                $value['checked'] = '';
                $value['intype'] = $inputType;
                $value['id'] = $option->getId();
                $value['sort_order'] = $option->getSortOrder();
                foreach ($this->getStores() as $store) {
                    $storeValues = $this->getStoreOptionValues($store->getId());
                    if (isset($storeValues[$option->getId()])) {
                        $value['store'.$store->getId()] = htmlspecialchars($storeValues[$option->getId()]);
                    }
                    else {
                        $value['store'.$store->getId()] = '';
                    }
                }
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                $color = Mage::getModel('ecp_color/color')->getCollection()->addFieldToFilter('eav_id',$option->getId())->getFirstItem();
                $value['hex'] = $color->getHex();
                $value['image'] = $color->getImage();
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                $values[] = new Varien_Object($value);
            }
            $this->setData('option_values', $values);
        }

        return $values;
    }

}
