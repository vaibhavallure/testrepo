<?php
/**
 * Created by PhpStorm.
 * User: swapnil
 * Date: 8/14/18
 * Time: 6:11 PM
 */
class Allure_Virtualstore_Block_Adminhtml_Virtualstore_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {

        parent::__construct();
        $this->setId("virtualstoreGrid");
        $this->setDefaultSort("store_id");
        $this->setDefaultDir("DESC");
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel("allure_virtualstore/virtualstore")->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {
        $this->addColumn("store_id", array(
            "header" => Mage::helper("virtualstore")->__("Store ID"),
            "align" =>"right",
            "width" => "50px",
            "type" => "number",
            "index" => "store_id",
        ));

        $this->addColumn("code", array(
            "header" => Mage::helper("virtualstore")->__("Code"),
            "index" => "code",
        ));

        $this->addColumn("name", array(
            "header" => Mage::helper("virtualstore")->__("Name"),
            "index" => "name",
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('virtualstore')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('virtualstore')->__('Excel'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl("*/*/edit", array("store_id" => $row->getId()));
    }



    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('store_id');
        $this->getMassactionBlock()->setFormFieldName('store_ids');
        $this->getMassactionBlock()->setUseSelectAll(true);
        $this->getMassactionBlock()->addItem('remove_virtualstore', array(
            'label'=> Mage::helper('virtualstore')->__('Remove Stores'),
            'url'  => $this->getUrl('*/virtualstore/massRemove'),
            'confirm' => Mage::helper('virtualstore')->__('Are you sure?')
        ));
        return $this;
    }
}