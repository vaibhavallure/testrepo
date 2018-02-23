<?php
/**
 * Static attributes rewrite
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Model_Class_Specialattribute_Static extends Teamwork_Transfer_Model_Class_Specialattribute_Abstract
{
    protected $_mapAttributes = array(
        'vendor.ordercost' => array(
            'get_values_params' => array(
                'field' => 'order_cost'
            )
        ),
        'item.vendor.ordercost' => array(
            'get_values_params' => array(
                'field' => 'order_cost'
            )
        ),
        'vendor.vendorno' => array(
            'get_values_params' => array(
                'field' => 'vendor_no'
            )
        ),
        'item.vendor.vendorno' => array(
            'get_values_params' => array(
                'field' => 'vendor_no'
            )
        ),
    );
    
    public function getValues($mapAttrName, $objectData, $auxiliaryParams)
    {
        $params = $this->getValuesParams($mapAttrName);
        return $objectData[$params['field']];
    }
}