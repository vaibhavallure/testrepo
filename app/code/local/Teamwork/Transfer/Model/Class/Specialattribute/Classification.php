<?php
/**
 * Class for DCSS and ACSS special attributes
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Model_Class_Specialattribute_Classification extends Teamwork_Transfer_Model_Class_Specialattribute_Abstract
{
    /**
     * Values of all ACSS and DCSS attributes for each acss/dcss id
     *
     * @var array (<dcss or acss id> => array(<field1>,<field2>,...))
     */
    protected $_replaceData = array();

    /**
     * All ACSS and DCSS attributes (except of mainAttribute) grouped by table (field 'table' in 'get_values_params')
     *
     * @var array (<acss> => array(<table> => array(<attr1,attr2,...)), <dcss> => array(<table> => array(<attr1,attr2,...)))
     */
    protected $_attributesByTable = array();


    /**
     * All attributes processed by a class
     *
     * @var array
     */
    protected $_mapAttributes = array(
        'dcss.code' => array(
            'get_values_params' => array(
                'table'       => 'service_dcss',
                'value_field' => 'code',
                'id_field'    => 'dcss_id',
                )
            ),
        'dcss.class.code' => array(
            'get_values_params' => array(
                'table'                  => 'service_dcss_class',
                'value_field'            => 'code',
                'id_field'               => 'class_id',
                'main_table_foreign_key' => 'class_id',
                )
            ),
        'dcss.class.name' => array(
            'get_values_params' => array(
                'table'                  => 'service_dcss_class',
                'value_field'            => 'name',
                'id_field'               => 'class_id',
                'main_table_foreign_key' => 'class_id',
                )
            ),
        'dcss.department.code' => array(
            'get_values_params' => array(
                'table'                  => 'service_dcss_department',
                'value_field'            => 'code',
                'id_field'               => 'department_id',
                'main_table_foreign_key' => 'department_id',
                )
            ),
        'dcss.department.name' => array(
            'get_values_params' => array(
                'table'                  => 'service_dcss_department',
                'value_field'            => 'name',
                'id_field'               => 'department_id',
                'main_table_foreign_key' => 'department_id',
                )
            ),
        'dcss.subclass1.code' => array(
            'get_values_params' => array(
                'table'             => 'service_dcss_subclass1',
                'value_field'            => 'code',
                'id_field'               => 'subclass1_id',
                'main_table_foreign_key' => 'subclass1_id',
                )
            ),
        'dcss.subclass1.name' => array(
            'get_values_params' => array(
                'table'                  => 'service_dcss_subclass1',
                'value_field'            => 'name',
                'id_field'               => 'subclass1_id',
                'main_table_foreign_key' => 'subclass1_id',
                )
            ),
        'dcss.subclass2.code' => array(
            'get_values_params' => array(
                'table'                  => 'service_dcss_subclass2',
                'value_field'            => 'code',
                'id_field'               => 'subclass2_id',
                'main_table_foreign_key' => 'subclass2_id',
                )
            ),
        'dcss.subclass2.name' => array(
            'get_values_params' => array(
                'table'                  => 'service_dcss_subclass2',
                'value_field'            => 'name',
                'id_field'               => 'subclass2_id',
                'main_table_foreign_key' => 'subclass2_id',
                )
            ),

        'acss.code' => array(
            'get_values_params' => array(
                'table'       => 'service_acss',
                'value_field' => 'code',
                'id_field'    => 'acss_id',
                )
            ),
        'acss.level1.code' => array(
            'get_values_params' => array(
                'table'                  => 'service_acss_level1',
                'value_field'            => 'code',
                'id_field'               => 'level1_id',
                'main_table_foreign_key' => 'level1_id',
                )
            ),
        'acss.level1.name' => array(
            'get_values_params' => array(
                'table'                  => 'service_acss_level1',
                'value_field'            => 'name',
                'id_field'               => 'level1_id',
                'main_table_foreign_key' => 'level1_id',
                )
            ),
        'acss.level2.code' => array(
            'get_values_params' => array(
                'table'                  => 'service_acss_level2',
                'value_field'            => 'code',
                'id_field'               => 'level2_id',
                'main_table_foreign_key' => 'level2_id',
                )
            ),
        'acss.level2.name' => array(
            'get_values_params' => array(
                'table'                  => 'service_acss_level2',
                'value_field'            => 'name',
                'id_field'               => 'level2_id',
                'main_table_foreign_key' => 'level2_id',
                )
            ),
        'acss.level3.code' => array(
            'get_values_params' => array(
                'table'                  => 'service_acss_level3',
                'value_field'            => 'code',
                'id_field'               => 'level3_id',
                'main_table_foreign_key' => 'level3_id',
                )
            ),
        'acss.level3.name' => array(
            'get_values_params' => array(
                'table'                  => 'service_acss_level3',
                'value_field'            => 'name',
                'id_field'               => 'level3_id',
                'main_table_foreign_key' => 'level3_id',
                )
            ),
        'acss.level4.code' => array(
            'get_values_params' => array(
                'table'                  => 'service_acss_level4',
                'value_field'            => 'code',
                'id_field'               => 'level4_id',
                'main_table_foreign_key' => 'level4_id',
                )
            ),
        'acss.level4.name' => array(
            'get_values_params' => array(
                'table'                  => 'service_acss_level4',
                'value_field'            => 'name',
                'id_field'               => 'level4_id',
                'main_table_foreign_key' => 'level4_id',
                )
            )
        );

    /**
     * Attribute we select in any case
     * It forms the 'base select query' while we form replace data
     * Other ACSS attributes are selected by means of joining to 'base' query
     *
     * @var string
     */
    protected $_mainAcssAttribute = 'acss.code';

    /**
     * Attribute we select in any case
     * It forms the 'base select query' while we form replace data
     * Other DCSS attributes are selected by means of joining to 'base' query
     *
     * @var string
     */
    protected $_mainDcssAttribute = 'dcss.code';

    /**
     * Map model used
     *
     * @var Teamwork_Service_Model_Mapping
     */
    protected $_mapModel;

    /**
     * Special function for taking values of class attributes
     *
     * @param string $mapAttrName
     * @param array  $objectData
     * @param array  $auxiliaryParams
     *
     * @return string
     */
    public function getValues($mapAttrName, $objectData, $auxiliaryParams)
    {
        $this->_mapModel = $auxiliaryParams['map_model'];
        $department      = ($this->_mapModel->isAcssAttribute($mapAttrName)) ? Teamwork_Service_Model_Mapping::CONST_ACSS : Teamwork_Service_Model_Mapping::CONST_DCSS;
        $classId         = $objectData[$department];

        return ($classId) ? $this->_getAttributeValueByClassId($department, $classId, $mapAttrName) : null;
    }

    /**
     * Returns value of given class attribute
     *
     * @param  string department
     * @param  string $classId
     * @param  string $mapAttrName
     *
     * @return string|null
     */
    public function _getAttributeValueByClassId($department, $classId, $mapAttrName)
    {
        //  Initialize replace data if needed
        if (!isset($this->_replaceData[$department][$classId][$mapAttrName]))
        {
            $this->_initReplaceData($department, $classId, $mapAttrName);
        }

        return isset($this->_replaceData[$department][$classId][$mapAttrName]) ? $this->_replaceData[$department][$classId][$mapAttrName] : null;
    }

    /**
     * Joins table and field names
     *
     * @param  string $tableName
     * @param  string $fieldName
     *
     * @return string
     */
    protected function _field($tableName, $fieldName)
    {
        return  $tableName . '.' . $fieldName;
    }

    /**
     * Initializes replace data for given department and class id (_replaceData[$department][$classId] is values of all ACSS/DCSS fields)
     *
     * @param string $classId
     */
    protected function _initReplaceData($department, $classId, $fullMapAttrName = Null)
    {
        $mainAttribute = $this->_getMainAttribute($department);
        $mainParams    = $this->getValuesParams($mainAttribute);
        $mainTable     = Mage::getSingleton('core/resource')->getTableName($mainParams['table']);

        $select = $this->_db->select()
        ->from(
            $mainTable,
            array($mainAttribute => $this->_field($mainTable, $mainParams['value_field']))
            );

        $otherCssAttrtibutes = array_diff($this->_getMapFields($department, $fullMapAttrName), array($mainAttribute));
        foreach ($this->_getAttributesByTable($department, $otherCssAttrtibutes) as $table => $mapAtributes)
        {
            // get all attributes we want to select from current table
            $attributesToSelect = array();
            $table = Mage::getSingleton('core/resource')->getTableName($table);
            foreach ($mapAtributes as $mapAttrName)
            {
                if ($params = $this->getValuesParams($mapAttrName))
                {
                    $valueField = $this->_field($table, $params['value_field']);
                    $attributesToSelect[$mapAttrName] = $valueField;
                }
            }

            // join these attributes
            $mainTableKeyField = $this->_field($mainTable, $params['main_table_foreign_key']);
            $idField           = $this->_field($table,     $params['id_field']);
            $joinCondition     = $mainTableKeyField . ' = '. $idField;

            $select->joinLeft($table, $joinCondition, $attributesToSelect);
        }


        $classIdField = $this->_field($mainTable, $mainParams['id_field']);
        $select->where($this->_db->quoteInto($classIdField . ' = ?', $classId));
        
        $rawRes = $this->_db->fetchRow($select);
        if ($rawRes)
        {
            if (!array_key_exists($department, $this->_replaceData))
            {
                $this->_replaceData[$department] = array();
            }
            if (!array_key_exists($classId, $this->_replaceData[$department]))
            {
                $this->_replaceData[$department][$classId] = array();
            }
            foreach($rawRes as $k => $v)
            {
                $this->_replaceData[$department][$classId][$k] = $v; 
            }
        }

    }

    /**
     * Sorts attributes by table name
     *
     * @param  array $attributes
     *
     * @return array (<table> => array(<attr1,attr2,...))
     */
    protected function _getAttributesByTable($department, $attributes)
    {
        if (!array_key_exists($department, $this->_attributesByTable))
        {
            $this->_attributesByTable[$department] = array();
        }

        foreach ($attributes as $mapAttrName)
        {
            $params = $this->getValuesParams($mapAttrName);
            $this->_attributesByTable[$department][$params['table']] = array();
            if (!array_key_exists($params['table'], $this->_attributesByTable[$department]))
            {
                $this->_attributesByTable[$department][$params['table']] = array();
            }
            if (!in_array($mapAttrName, $this->_attributesByTable[$department][$params['table']]))
            {
                $this->_attributesByTable[$department][$params['table']][] = $mapAttrName;
            }
        }

        return $this->_attributesByTable[$department];
    }

    /**
     * Returns all ACSS or DCSS map fields (depending on $department)
     *
     * @param const $department
     *
     * @return array all acss/dcss fields
     */
    protected function _getMapFields($department, $additionalMapAttrName = Null)
    {
        $result = ($department == Teamwork_Service_Model_Mapping::CONST_ACSS) ? $this->_mapModel->getAcss() : $this->_mapModel->getDcss();
        if (!empty($additionalMapAttrName)
            && (substr($additionalMapAttrName, 0, strlen(Teamwork_Service_Model_Mapping::CONST_DCSS)) == Teamwork_Service_Model_Mapping::CONST_DCSS
                || substr($additionalMapAttrName, 0, strlen(Teamwork_Service_Model_Mapping::CONST_ACSS)) == Teamwork_Service_Model_Mapping::CONST_ACSS))
        {
            $result = array_merge($result, array($additionalMapAttrName));
        }
        return $result;
    }

    /**
     * Returns main ACSS or DCSS attribute
     *
     * @param const $department
     *
     * @return string name of the main acss/dcss attribute
     */
    protected function _getMainAttribute($department)
    {
        return ($department == Teamwork_Service_Model_Mapping::CONST_ACSS) ? $this->_mainAcssAttribute : $this->_mainDcssAttribute;
    }


}
