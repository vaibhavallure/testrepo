<?php
/**
 * Identifier special attribute class
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Model_Class_Specialattribute_Identifier extends Teamwork_Transfer_Model_Class_Specialattribute_Abstract
{
   /**
    * Prefix for all attributes served by this class
    *
    * @var array
    */
   protected $_identifierAttributePrefix = 'item.identifier';

   /**
    * Name of field that replaces 'id' field of 'special attributes' while mapping
    *
    * @var array (<specialAttribute> => <ReplaceData>)
    */
   protected $_replaceData;

   /**
    * All class ids existing in service_identifier table
    *
    * @var array
    */
   protected $_classIds;

   /**
    * Common params for all identifier attributes
    *
    * @var array
    */
   protected $_commonParams = array(
       'table'               => 'service_identifier',
       'item_id_field'       => 'item_id',
       'class_field'         => 'idclass',
       'value_field'         => 'value'
    );

    /**
     * This function rewrites parent one
     * It looks for all distinct class_id's in 'service_identifier' table
     * It returns one attribute per each class_id. Attribute name = identifierAttributePrefix + class_id
     *
     * @return array attributes with name like '_identifierAttributePrefix + class_id'
     */
    public function getAllAttributes()
    {
       if (!isset($this->_classIds))
       {
            $this->_classIds = array();

            $table      = $this->_commonParams['table'];
            $classField = $this->_commonParams['class_field'];
            $select     = $this->_db->select()->distinct(true)->from(Mage::getSingleton('core/resource')->getTableName($table), $classField);

            foreach ($this->_db->fetchCol($select) as $classId)
            {
                $this->_classIds[] = $this->_identifierAttributePrefix . strval($classId);
            }
       }

       return $this->_classIds;
    }

   /**
    * Special function for taking values of identifier attributes
    *
    * @param string $mapAttrName
    * @param array  $objectData
    * @param array  $auxiliaryParams
    *
    * @return string
    */
   public function getValues($mapAttrName, $objectData, $auxiliaryParams)
   {
       $itemId = $objectData[Teamwork_Service_Model_Mapping::FIELD_ITEM_ID];
       return ($itemId) ? $this->_getIdentifierByItemId($itemId, $mapAttrName) : null;
   }

   /**
    * Returns given item identifier
    *
    * @param string $itemId
    * @param string $mapAttrName
    *
    * @return string
    */
   public function _getIdentifierByItemId($itemId, $mapAttrName)
   {
       //  Initialize replace data if needed
       if (!isset($this->_replaceData[$itemId]))
       {
           $this->_initReplaceData($itemId, $mapAttrName);
       }

       $classId = $this->_extractClassId($mapAttrName);
       return isset($this->_replaceData[$itemId][$classId]) ? $this->_replaceData[$itemId][$classId] : null;
   }


    /**
     * Gets class id from attribute name
     *
     * @param  string $mapAttrName
     *
     * @return string
     */
    protected function _extractClassId($mapAttrName)
    {
        return substr($mapAttrName,strlen($this->_identifierAttributePrefix));
    }

   /**
    * Initializes replace data (all identifiers of a given item)
    *
    * @param string $itemId
    */
   protected function _initReplaceData($itemId)
   {
        $table       = $this->_commonParams['table'];
        $itemIdField = $this->_commonParams['item_id_field'];
        $fields      = array($this->_commonParams['class_field'], $this->_commonParams['value_field']);

        $select = $this->_db->select()
            ->from(Mage::getSingleton('core/resource')->getTableName($table), $fields)
            ->where($itemIdField . ' = ?', $itemId);

        $this->_replaceData[$itemId] = array();
        foreach ($this->_db->fetchPairs($select) as $classId => $identifier)
        {
            $this->_replaceData[$itemId][strval($classId)] = $identifier;
        }
   }
}
