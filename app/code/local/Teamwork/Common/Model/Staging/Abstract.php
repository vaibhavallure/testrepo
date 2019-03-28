<?php
/**
 * Abstract model for entities having GUID (GUID field is different for styles, items, categories etc.)
 */
class Teamwork_Common_Model_Staging_Abstract extends Mage_Core_Model_Abstract
{
    protected $_guidField;

    public function _construct()
    {
        parent::_construct();
        if( !isset($this->_guidField) )
        {
            Mage::throwException(get_class($this) . ' must have a _guidField property!');
        }
    }

    /**
     * Useful method for loading models by multiple attributes
     *
     * @param  array $attributes
     *
     * @return Teamwork_Common_Model_Abstract
     */
    public function loadByAttributes($attributes)
    {
        $this->setData($this->getResource()->loadByAttributes($attributes));
        return $this;
    }

    /**
     * Returns entity GUID
     * Method has sense because $this->_guidField is unique for entities (styles, items, attributes etc.)
     *
     * @return string
     */
    public function getGuid()
    {
        return $this->getData($this->_guidField);
    }
    
    public function getGuidField()
    {
        return $this->_guidField;
    }

    /**
     * Useful method for loading by GUID (style_id, category_id etc.) and channel id
     *
     * @param  string $channelId
     * @param  string $guid
     *
     * @return Teamwork_Common_Model_Abstract_Channeled
     */
    public function loadByGuid($guid)
    {
        $this->setData($this->getResource()->loadByAttributes(
            array(
                $this->_guidField => $guid,
            )
        ));
        return $this;
    }
    
    public function loadCollectionByVarienFilter(Varien_Object $filter,$customFilter=array(),$orderField='entity_id',$orderDirection='DESC')
    {
        $collection = $this->getCollection();
        if($filter->getData())
        {
            foreach($filter->getData() as $attributeName => $attributeFilter)
            {
                if($attributeFilter === '' || $attributeFilter === NULL)
                {
                    $attributeFilter = array( array('null' => true), array('eq' => array('')) );
                }
                $collection->addFieldToFilter($attributeName, $attributeFilter);
            }
        }
        
        foreach($customFilter as $fields )
        {
            $collection->addFieldToFilter($fields[0], $fields[1]);
        }
        
        return $collection->setOrder($orderField, $orderDirection)->load();
    }
    
    protected function _beforeSave()
    {
        $helper = Mage::helper('teamwork_common/staging_abstract');
        
        $columns = $helper->getTableDescriptionByResource( $this->getResource() );
        foreach($columns as $columnName => $column)
        {
            if( is_array($this->getData($columnName)) )
            {
                $this->setData($columnName, serialize($this->getData($columnName)));
            }
            
            if( $helper->isGuidColumn($column) && $this->getData($columnName) )
            {
                $this->setData($columnName, strtolower($this->getData($columnName)));
            }
        }
        
        return parent::_beforeSave();
    }
}