<?php

class Allure_Reports_Block_Adminhtml_Catalog_Catalog extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_controller = 'allure_reports_sales_catalog';
        $this->_headerText = Mage::helper('reports')->__('Total Ordered Report');
        parent::__construct();
        $this->setTemplate('report/grid/container.phtml');
        $this->_removeButton('add');
        $this->addButton('filter_form_submit', array(
            'label'     => Mage::helper('reports')->__('Show Report'),
            'onclick'   => 'filterFormSubmit()'
        ));
    }

    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/catalogreport', array('_current' => true));
    }
    
    protected function _prepareLayout()
    {
        return	$this->setChild( 'grid',$this->getLayout()
            ->createBlock( 'allure_reports/adminhtml_catalog_sales_grid')
            ->setSaveParametersInSession(true));
        
    }
    
}
