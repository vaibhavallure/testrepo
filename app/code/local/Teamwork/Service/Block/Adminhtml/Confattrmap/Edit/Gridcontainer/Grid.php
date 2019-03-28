<?php

class Teamwork_Service_Block_Adminhtml_Confattrmap_Edit_Gridcontainer_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{
    
    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        $collection = Teamwork_Service_Block_Adminhtml_Confattrmap_Edit_Form::getAttributeAssignedConfProducts(intval(Mage::registry('model')->getData('chq_internal_id')));
        $collection->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id');

        if ($store->getId()) {
            //$collection->setStoreId($store->getId());
            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $adminStore
            );
            $collection->joinAttribute(
                'custom_name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
        }
        else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }

        $this->setCollection($collection);

        Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
        $this->getCollection()->addWebsiteNamesToResult();
        return $this;
    }
    
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->removeColumn('action');
        $this->removeColumn('qty');
        $col = $this->getColumn('type');
        $col->setData('sortable', false);
        $col->setData('options', array(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE => Mage::helper('catalog')->__('Configurable Product')));
    }
    
    public function getRowUrl($row)
    {
        return Mage_Adminhtml_Block_Widget::getRowUrl($row);
    }
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('catalog')->__('Delete'),
             'url'  => $this->getUrl('*/*/productMassDelete', array('_current'=>true)),
             'confirm' => Mage::helper('catalog')->__('Are you sure?')
        ));

        Mage::dispatchEvent('adminhtml_catalog_product_grid_prepare_massaction', array('block' => $this));
        return $this;
    }
    
     /**
     * We don't need RSS link
     *
     * @param   string $url
     * @param   string $label
     * @return  Mage_Adminhtml_Block_Widget_Grid
     */
    public function addRssList($url, $label)
    {
        return $this;
    }
    
    public function getGridUrl()
    {
        return $this->getUrl('*/*/productGrid', array('_current'=>true));
    }

}
