<?php

class Teamwork_Transfer_Model_Adminhtml_Config_Source_Reindexeachecm
{
    public function toOptionArray()
    {
        return array(
            array(
                'label' => 'No',
                'value' => 0
            ),
            array(
                'label' => 'Once, after last ECM from group',
                'value' => 1
            ),
            array(
                'label' => 'After every single ECM',
                'value' => 2
            ),
        );
    }
}
