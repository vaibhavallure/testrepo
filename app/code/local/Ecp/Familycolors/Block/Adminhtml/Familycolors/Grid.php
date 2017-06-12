<?php

class Ecp_Familycolors_Block_Adminhtml_Familycolors_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('familycolorsGrid');
        // This is the primary key of the database
        $this->setDefaultSort('colorfamily_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ecp_familycolors/familycolors')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
       $this->addColumn('colorfamily_id', array(
            'header'    => Mage::helper('familycolors')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'colorfamily_id',
        ));

        $this->addColumn('title', array(
            'header'    => Mage::helper('familycolors')->__('Title'),
            'align'     =>'left',
            'index'     => 'title',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }


}
?>
