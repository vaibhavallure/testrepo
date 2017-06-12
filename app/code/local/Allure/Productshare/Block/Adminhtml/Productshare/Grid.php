<?php

class Allure_Productshare_Block_Adminhtml_Productshare_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct ()
    {
        parent::__construct();
        $this->setId("productshareGrid");
        $this->setDefaultSort("ps_id");
        $this->setDefaultDir("DESC");
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection ()
    {
        $collection = Mage::getModel("productshare/productshare")->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns ()
    {
        $this->addColumn("ps_id", 
                array(
                        "header" => Mage::helper("productshare")->__("ID"),
                        "align" => "right",
                        "width" => "50px",
                        "type" => "number",
                        "index" => "ps_id"
                ));
        
        $this->addColumn("status_code", 
                array(
                        "header" => Mage::helper("productshare")->__("Status"),
                        "index" => "status_code"
                ));
        
        $this->addColumn("website_id", 
                array(
                        "header" => Mage::helper("productshare")->__("Website"),
                        "index" => "website_id",
                        "renderer" => "Allure_Productshare_Block_Adminhtml_Productshare_Edit_Renderer_Website"
                ));
        
        $this->addColumn("store_id",
        		array(
        				"header" => Mage::helper("productshare")->__("Store"),
        				"index" => "store_id",
        				"renderer" => "Allure_Productshare_Block_Adminhtml_Productshare_Edit_Renderer_Store"
        		));
        
        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));
        
        return parent::_prepareColumns();
    }

    public function getRowUrl ($row)
    {
        return $this->getUrl("*/*/edit", array(
                "id" => $row->getId()
        ));
    }

    protected function _prepareMassaction ()
    {
        $this->setMassactionIdField('ps_id');
        $this->getMassactionBlock()->setFormFieldName('ps_ids');
        $this->getMassactionBlock()->setUseSelectAll(true);
        $this->getMassactionBlock()->addItem('remove_productshare', 
                array(
                        'label' => Mage::helper('productshare')->__('Remove Productshare'),
                        'url' => $this->getUrl('*/adminhtml_productshare/massRemove'),
                        'confirm' => Mage::helper('productshare')->__('Are you sure?')
                ));
        return $this;
    }
}