<?php
/**
 * Created by PhpStorm.
 * User: swapnil
 * Date: 8/14/18
 * Time: 6:11 PM
 */
class Allure_Virtualstore_Block_Adminhtml_Store_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {

        parent::__construct();
        $this->setId("storeGrid");
        $this->setDefaultSort("store_id");
        $this->setDefaultDir("DESC");
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel("allure_virtualstore/store")->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {
        $this->addColumn("store_id", array(
            "header" => Mage::helper("allure_virtualstore")->__("Store ID"),
            "align" =>"right",
            "width" => "50px",
            "type" => "number",
            "index" => "store_id",
        ));

        $this->addColumn("code", array(
            "header" => Mage::helper("allure_virtualstore")->__("Code"),
            "index" => "code",
        ));

        $this->addColumn("name", array(
            "header" => Mage::helper("allure_virtualstore")->__("Name"),
            "index" => "name",
        ));

        $this->addColumn("website_id", array(
            "header" => Mage::helper("allure_virtualstore")->__("Website"),
            "index" => "website_id",
//            "filter_index" => "website_id",
            'type' => 'options',
            'options' => Mage::getModel('allure_virtualstore/website')->getWebsite(),
        ));

        $this->addColumn("group_id", array(
            "header" => Mage::helper("allure_virtualstore")->__("Group"),
            "index" => "group_id",
//            'renderer' => new Allure_Virtualstore_Block_Adminhtml_Renderer_CustomGroup(),
            'type' > 'options',
            'options' => Mage::getModel('allure_virtualstore/group')->getGroup(),
        ));


        $this->addColumn("sort_order", array(
            "header" => Mage::helper("allure_virtualstore")->__("Sort Order"),
            "index" => "sort_order",
        ));

        $this->addColumn("is_active", array(
            "header" => Mage::helper("allure_virtualstore")->__("Is Active"),
            "index" => "is_active",
        ));

        $this->addColumn("is_copy_old_product", array(
            "header" => Mage::helper("allure_virtualstore")->__("Is Copy Old Product"),
            "index" => "is_copy_old_product",
        ));

//        $this->addColumn("currency", array(
//            "header" => Mage::helper("allure_virtualstore")->__("Currency"),
//            "index" => "currency",
//        ));
//
//        $this->addColumn("timezone", array(
//            "header" => Mage::helper("allure_virtualstore")->__("Timezone"),
//            "index" => "timezone",
//        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('allure_virtualstore')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('allure_virtualstore')->__('Excel'));

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
            'label'=> Mage::helper('allure_virtualstore')->__('Remove Stores'),
            'url'  => $this->getUrl('*/virtualstore/massRemove'),
            'confirm' => Mage::helper('allure_virtualstore')->__('Are you sure?')
        ));
        return $this;
    }
}