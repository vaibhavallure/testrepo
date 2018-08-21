<?php
/**
 * Created by PhpStorm.
 * User: swapnil
 * Date: 8/20/18
 * Time: 3:55 PM
 */
class Allure_Virtualstore_Block_Adminhtml_Website_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId("websiteGrid");
        $this->setDefaultSort("website_id");
        $this->setDefaultDir("DESC");
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel("allure_virtualstore/website")->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {
        $this->addColumn("website_id", array(
            "header" => Mage::helper("virtualstore")->__("Website ID"),
            "align" =>"right",
            "width" => "50px",
            "type" => "number",
            "index" => "website_id",
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
        return $this->getUrl("*/*/edit", array("website_id" => $row->getId()));
    }



    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('website_id');
        $this->getMassactionBlock()->setFormFieldName('website_ids');
        $this->getMassactionBlock()->setUseSelectAll(true);
        $this->getMassactionBlock()->addItem('remove_website', array(
            'label'=> Mage::helper('virtualstore')->__('Remove Websites'),
            'url'  => $this->getUrl('*/website/massRemove'),
            'confirm' => Mage::helper('virtualstore')->__('Are you sure?')
        ));
        return $this;
    }
}