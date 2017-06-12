<?php

class Ecp_Familycolors_Block_Adminhtml_Familycolors extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
    {
      
        
        
        //var_dump($this);
        $this->_controller = 'familycolors';
        $this->_blockGroup = 'familycolors_adminhtml';
        $this->_headerText = Mage::helper('familycolors')->__('Item Manager');
        $this->_addButtonLabel = Mage::helper('familycolors')->__('Add Item');

        parent::__construct();
    }

    protected function _prepareCollection()
    {
     
        $collection = Mage::getModel('ecp_familycolors/familycolors')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
       
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }


}