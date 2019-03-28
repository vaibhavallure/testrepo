<?php

class Teamwork_Transfer_Model_Adminhtml_Config_Source_Stylecustomtextfields
{
    public function toOptionArray()
    {
        $collection = Mage::getModel('teamwork_service/chqmappingfields')->getCollection()
            ->addFieldToFilter('type_id', Teamwork_Transfer_Model_Class_Item::CHQ_PRODUCT_TYPE_STYLE)
            ->addFieldToFilter('type', 'Text')
            ->setOrder('label', 'ASC');

        $options = array(
            array(
                'label' => '-- Always use Default --',
                'value' => 0
            )
        );

        foreach($collection as $value)
        {
            $options[] = array(
                'label' => $value->getData('label'),
                'value' => strtolower(substr($value->getData('value'), strlen(Teamwork_Service_Model_Mapping::CONST_STYLE)+1)),
            );
        }
        return $options;
    }
}
