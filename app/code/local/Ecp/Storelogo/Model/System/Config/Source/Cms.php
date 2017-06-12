<?php

/**
 * Used in creating options for Yes|No config value selection
 *
 */
class Ecp_Storelogo_Model_System_Config_Source_Cms
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        /*return array(
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('Yes')),
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('No')),
        );*/
        $model = Mage::getModel('cms/block')->getCollection()
                ->addFieldToFilter('is_active',1);
        $toOptionArray = array();
        foreach($model as $item){
            $toOptionArray[] = array('value' => $item->getIdentifier(),'label' => $item->getTitle());
        }

        return $toOptionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        /*return array(
            0 => Mage::helper('adminhtml')->__('No'),
            1 => Mage::helper('adminhtml')->__('Yes'),
        );*/
        $model = Mage::getModel('cms/block')->getCollection()
                ->addFieldToFilter('is_active',1);
        $toArray = array();
        foreach($model as $item){
            $toArray[$item->getIdentifier()] = $item->getTitle();
        }

        return $toArray;
    }

}
