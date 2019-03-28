<?php

class Allure_Appointments_Block_Adminhtml_Pricing_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct ()
    {
        parent::__construct();
        $this->setId("pricingGrid");
        $this->setDefaultSort("price_id");
        $this->setDefaultDir("DESC");
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection ()
    {
        $collection = Mage::getModel("appointments/pricing")->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns ()
    {
        $this->addColumn("price_id", 
                array(
                        "header" => Mage::helper("appointments")->__("ID"),
                        "align" => "right",
                        "width" => "50px",
                        "type" => "number",
                        "index" => "price_id"
                ));
        
        $this->addColumn("type", 
                array(
                        "header" => Mage::helper("appointments")->__("Type of piercing"),
                        "index" => "type"
                ));
        
        $this->addColumn("service_cost",
            array(
                "header" => Mage::helper("appointments")->__("Service Cost"),
                "index" => "service_cost"
            ));
        
        $this->addColumn("jewelry_start_at",
            array(
                "header" => Mage::helper("appointments")->__("Jewelry Start At"),
                "index" => "jewelry_start_at"
            ));
        
  
        if (Mage::helper('core')->isModuleEnabled('Allure_Virtualstore')){
            $storeOptions = Mage::getSingleton('allure_virtualstore/adminhtml_store')->getStoreOptionHash();
        }else{
            $storeOptions = Mage::getSingleton('adminhtml/system_store')->getStoreOptionHash();
        }
        $this->addColumn('store_id', array(
            'header' => Mage::helper("appointments")->__('Store'),
            'type' => 'options',
            'options' => $storeOptions,
            'index' => 'store_id',
            'sortable' => false,
        ));
        
        $this->addExportType('*/*/exportCsv', Mage::helper('appointments')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('appointments')->__('Excel'));
        
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
        $this->setMassactionIdField('price_id');
        $this->getMassactionBlock()->setFormFieldName('price_ids');
        $this->getMassactionBlock()->setUseSelectAll(true);
        $this->getMassactionBlock()->addItem('remove_pricing', 
                array(
                        'label' => Mage::helper('appointments')->__('Remove Price'),
                        'url' => $this->getUrl('*/adminhtml_pricing/massRemove'),
                        'confirm' => Mage::helper('appointments')->__('Are you sure?')
                ));
        return $this;
    }
}