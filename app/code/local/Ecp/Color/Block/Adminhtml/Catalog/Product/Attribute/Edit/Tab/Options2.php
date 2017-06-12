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
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product attribute add/edit form options tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Netismine_ColorSwitcher_Block_Adminhtml_Catalog_Product_Attribute_Edit_Tab_Options 
 extends Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Options
{
	public function __construct()
    { 
        parent::__construct();
        $this->setTemplate('colorswitcher/options.phtml');
    }
	
	public function getOptionValues()
    {
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
            
			$values 			= array();			
			$imagesTable 		= Mage::getSingleton('core/resource')->getTableName('eav/attribute_option_image');
            $optionCollection 	= Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter($this->getAttributeObject()->getId());
			
			$optionCollection->getSelect()
				->joinLeft(array('image_table' => $imagesTable) ,
					'image_table.option_id=main_table.option_id', 'image');
			
			$optionCollection->setPositionOrder('desc', true)
				->load();
				        
            foreach ($optionCollection as $option) {
                $value = array();
                if (in_array($option->getId(), $defaultValues)) {
                    $value['checked'] = 'checked="checked"';
                } else {
                    $value['checked'] = '';
                }

                $value['intype'] = $inputType;
				
				if ($option->getImage()) {
					$helper = Mage::helper('colorswitcher');
					$value['image'] = $helper->getAttributeImageUrl($option->getImage());
				} else {
					$placeholder = 'images/placeholder/thumbnail.jpg';
					$value['image'] = Mage::getDesign()->getSkinUrl($placeholder);
				}
				
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
                $values[] = new Varien_Object($value);
            }
            $this->setData('option_values', $values);
        }

        return $values;
    }
}
