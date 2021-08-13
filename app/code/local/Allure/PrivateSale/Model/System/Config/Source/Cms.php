<?php
class Allure_PrivateSale_Model_System_Config_Source_Cms
{

    public function toOptionArray()
    {
        $options = array();

        $options[] = array(
            'label' => Mage::helper('privatesale')->__('-- None --'),
            'value' => ''
        );

        $cmspages = Mage::getModel('cms/block')->getCollection();


        foreach ($cmspages as $block) {
            $options[] = array(
                'label' => $block->getTitle(),
                'value' => $block->getIdentifier()
            );
        }

            return $options;
        }

}