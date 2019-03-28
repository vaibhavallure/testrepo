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
 * @copyright  Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Allure_Pickinstore_Model_Attribute
{
    protected $_options;

    public function toOptionArray($isMultiselect=false)
    {
        $attributeSetCollection = Mage::getResourceModel('eav/entity_attribute_set_collection');
        $attributeSetCollection->setEntityTypeFilter('4');
        $options=array();
        $options[]= array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('--Please Select--'));
        foreach ($attributeSetCollection as $attributeSet){
            $options[]=array('value'=>$attributeSet->getAttributeSetId(), 'label'=> Mage::helper('adminhtml')->__($attributeSet->getAttributeSetName()));
        }
        return $options;
    }
}
