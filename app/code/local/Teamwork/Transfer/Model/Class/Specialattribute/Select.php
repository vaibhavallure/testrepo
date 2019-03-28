<?php
/**
 * Select special attribute class
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Model_Class_Specialattribute_Select extends Teamwork_Transfer_Model_Class_Specialattribute_Abstract
{
    /**
     * All attributes processed by a class
     *
     * @var array
     */
    protected $_mapAttributes = array(
        'manufacturer' => array(
            'get_values_params' => array(
                'table'       => 'service_manufacturer',
                'id_field'         => 'manufacturer_id',
                'value_field'      => 'name'
                )
            ),
        'brand' => array(
            'get_values_params' => array(
                'table'       => 'service_brand',
                'id_field'         => 'brand_id',
                'value_field'      => 'name'
                )
            )
        );

    /**
     * Name of field that replaces 'id' field of 'special attributes' while mapping
     *
     * @var array (<specialAttribute> => array(<option1> => <value1>, <option2> => <value2>, ...))
     */
    protected $_replaceData;

    /**
     * Special function for taking values of 'select' attributes
     * It assumes that object already has a '$mapAttrName' field containing attribute option id.
     * It takes this id as a lookup and calls _getSelectValueByLookup to find value for this lookup
     *
     * @param string $mapAttrName
     * @param array  $objectData
     * @param array  $auxiliaryParams
     *
     * @return string
     */
    public function getValues($mapAttrName, $objectData, $auxiliaryParams)
    {
        $lookup = $objectData[$mapAttrName];
        return ($lookup) ? $this->_getSelectValueByLookup($mapAttrName, $lookup) : null;
    }

    /**
     * Returns value of select attribute by lookup (i.e. option id)
     *
     * @param string $mapAttrName
     * @param string $lookup
     *
     * @return string
     */
    public function _getSelectValueByLookup($mapAttrName, $lookup)
    {
        //  Initialize replace data if needed
        if (!isset($this->_replaceData[$mapAttrName]))
        {
            $this->_initReplaceData($mapAttrName);
        }

        return isset($this->_replaceData[$mapAttrName][$lookup]) ? $this->_replaceData[$mapAttrName][$lookup] : $lookup;
    }


    /**
     * Initializes replace data for given select/multiselect attribute ('id=>value' pairs for each attribute 'option')
     *
     * @param string $mapAttrName
     */
    protected function _initReplaceData($mapAttrName)
    {
        $params   = $this->getValuesParams($mapAttrName);
        $fields   = array($params['id_field'], $params['value_field']);
        $select   = $this->_db->select()->from(Mage::getSingleton('core/resource')->getTableName($params['table']), $fields);

        $this->_replaceData[$mapAttrName] = $this->_db->fetchPairs($select);
    }
}
