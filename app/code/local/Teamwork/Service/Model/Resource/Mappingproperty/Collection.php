<?php

class Teamwork_Service_Model_Resource_Mappingproperty_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{   
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_service/mappingproperty');
    }
    
    public function _initSelect()
    {
        parent::_initSelect();
        
        $this->getSelect()
            ->joinLeft(
                array('chqmappingfields' => $this->getTable('teamwork_service/service_chq_mappingfields')),
                'main_table.field_id = chqmappingfields.entity_id', array(
                'type_id' => 'chqmappingfields.type_id'
                )
            );
        
        return $this;
    }


}