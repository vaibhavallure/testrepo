<?php

class Ecp_Sizechart_Model_Attribute_Source_Sizechart extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    protected $_options = array('label'=>'');
        
    public function getAllOptions()
    {
        
        $sizechartCollection = Mage::getModel('ecp_sizechart/sizechart')->getCollection();
        
//        if(is_null($this->_options)){
            foreach($sizechartCollection as $key=>$value) {
                $this->_options[] = 
                    array(
                        'label' => $value['title'],
                        'value' => $value['sizechart_id']
                    );                
            }
        //}Mage::log(print_r($this->_options,true),null,'chart.log');
        return $this->_options;
    }
    
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
    
    public function addValueSortToCollection($collection, $dir = 'asc')
    {
        
    }
    
    public function getFlatColums()
    {
        
    }
    
    public function getFlatUpdateSelect($store)
    {
        
    }
}
