<?php
/**
 * Multiselect special attribute class
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Model_Class_Specialattribute_Collection extends Teamwork_Transfer_Model_Class_Specialattribute_Select
{
    /**
     * All attributes processed by a class
     *
     * @var array
     */
    protected $_mapAttributes = array(
        'collections' => array(
            'get_values_params' => array(
                'table'       => 'service_collection',
                'id_field'    => 'collection_id',
                'value_field' => 'name',

                'object_lookup_field'      => 'Teamwork_Service_Model_Mapping::FIELD_STYLE_ID',
                'multiselect_table'        => 'service_style_collection',
                'multiselect_lookup_field' => 'style_id',
                'multiselect_value_field'  => 'collection_id'
                )
            ),
        'item.collections' => array(
            'get_values_params' => array(
                'table'       => 'service_collection',
                'id_field'    => 'collection_id',
                'value_field' => 'name',

                'object_lookup_field'      => 'Teamwork_Service_Model_Mapping::FIELD_ITEM_ID',
                'multiselect_table'        => 'service_item_collection',
                'multiselect_lookup_field' => 'item_id',
                'multiselect_value_field'  => 'collection_id'
                )
            )
        );


   /**
     * Special function for taking values of multiselect attributes
     *
     * @param string $mapAttrName
     * @param array  $objectData
     * @param array  $auxiliaryParams
     *
     * @return array
     */
    public function getValues($mapAttrName, $objectData, $auxiliaryParams)
    {
        $result  = array();
        $lookups = $this->_getAllLookups($mapAttrName, $objectData);
        foreach ($lookups as $lookup)
        {
            $result[] = $this->_getSelectValueByLookup($mapAttrName, $lookup);
        }
        return $result;
    }

   /**
     * Special function for getting all lookup values of multiselect attributes
     *
     * @param string $mapAttrName
     * @param array  $objectData
     *
     * @return array
     */
    protected function _getAllLookups($mapAttrName, $objectData)
    {
        if ($params = $this->getValuesParams($mapAttrName))
        {
            $table             = $params['multiselect_table'];
            $lookupField       = $params['multiselect_lookup_field'];
            $valueField        = $params['multiselect_value_field'];
            $objectLookupValue = $objectData[ constant($params['object_lookup_field']) ];

            if ($table && $lookupField && $valueField && $objectLookupValue)
            {
                $select = $this->_db->select()
                    ->from(Mage::getSingleton('core/resource')->getTableName($table), array($valueField))
                ->where($lookupField . ' = ?', $objectLookupValue);

                return $this->_db->fetchCol($select);
            }
        }

        return array();
    }
}
