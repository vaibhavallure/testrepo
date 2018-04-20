<?php
/**
 * Special attribute abstract class
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Model_Class_Specialattribute_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * Database connection object
     *
     * @var Varien_Db_Adapter_Pdo_Mysql
     */
    protected $_db;

    /**
     * All attributes processed by a class
     *
     * @var array
     */
    protected $_mapAttributes;

    /**
     * Class Item Object to get info about any item/style
     *
     * @var Teamwork_Transfer_Model_Class_Item
     */
    protected $_classItemObject;

    /**
     * Constructor that initializes db
     */
    public function _construct()
    {
        $this->_classItemObject = $this->getData('class_item_object');
        $this->_db = Mage::getSingleton('core/resource')->getConnection('core_write');
    }
    

    /**
     * Returns all attributes processed by a class
     *
     * @return array names of all map attributes served by a class
     */
    public function getAllAttributes()
    {
        return array_keys($this->_mapAttributes);
    }

    /**
     * Gets params for 'getValue' function
     *
     * @param  string $mapAttrName
     * @return array
     */
    public function getValuesParams($mapAttrName)
    {
        return $this->_mapAttributes[$mapAttrName]['get_values_params'];
    }
}