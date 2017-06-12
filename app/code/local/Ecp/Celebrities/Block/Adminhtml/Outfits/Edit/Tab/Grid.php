<?php

class Ecp_Celebrities_Block_Adminhtml_Outfits_Edit_Tab_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('productGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_filter');
        $this->setDefaultLimit(10);

    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id');

        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $collection->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
        }
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

        parent::_prepareCollection();
        $this->getCollection()->addWebsiteNamesToResult();
        return $this;
    }

    protected function _addColumnFilterToCollection($column)
    {
         // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
            } else {
                if($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareColumns()
    {
//        $this->addColumn('in_products', array(
//                'header_css_class'  => 'a-center',
//                'type'              => 'checkbox',
//                'name'              => 'product',
//                'values'            => $this->_getSelectedProducts(),
//                'align'             => 'center',
//                'index'             => 'entity_id'               
//        ));
        
//        $this->addColumn('position', array(
//            'header'            => Mage::helper('catalog')->__('ID'),
//            'name'              => 'position',
//            'width'             => 60,
//            'type'              => 'number',
//            'validate_class'    => 'validate-number',
//            'index'             => 'entity_id',
//            'editable'          => true,
//            'edit_only'         => true,
//            'disable' => true,
//            ));
        
        $this->addColumn('entity_id',
            array(
                'header'=> Mage::helper('catalog')->__('ID'),                
                'width' => '50px',                
                'type'  => 'number',
                'index' => 'entity_id'                
        ));
        
        $this->addColumn('name',
            array(
                'header'=> Mage::helper('catalog')->__('Name'),
                'index' => 'name',
        ));

        $store = $this->_getStore();
        if ($store->getId()) {
            $this->addColumn('custom_name',
                array(
                    'header'=> Mage::helper('catalog')->__('Name in %s', $store->getName()),
                    'index' => 'custom_name',
            ));
        }

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name',
            array(
                'header'=> Mage::helper('catalog')->__('Attrib. Set Name'),
                'width' => '100px',
                'index' => 'attribute_set_id',
                'type'  => 'options',
                'options' => $sets,
        ));

        $this->addColumn('sku',
            array(
                'header'=> Mage::helper('catalog')->__('SKU'),
                'width' => '80px',
                'index' => 'sku',
        ));

        return parent::_prepareColumns();
    }   
    
//    public function getGridUrl()
//    {
//        return $this->getUrl('ecp_celebrities/adminhtml_outfits_edit_tab_grid/ajax/', array('isAjax'=>true,'_current'=>true));
//    }
    
    public function getGridUrl()
    {
        return $this->_getData('grid_url') ? $this->_getData('grid_url') : $this->getUrl('*/*/productgrid', array('_current'=>true));
    }
    
    protected function _getSelectedProducts()   // Used in grid to return selected customers values.
    {
        $products = array_keys($this->getSelectedProducts());
        return $products;
    }
 
    public function getSelectedProducts() 
    {
        if(Mage::registry('celebrities_outfit_data')){
            $coId = Mage::registry('celebrities_outfit_data')->getData('related_products');
            if(!empty($coId))
                $products = explode(",",$coId);
        }else{
            if($this->getRequest()->getParam('id')){
                $model = Mage::getModel('ecp_celebrities/outfits')->load($this->getRequest()->getParam('id'));
                $products = explode(",",$model->getRelatedProducts());
            }                
        }
          
        // Customer Data
//        $tm_id = $this->getRequest()->getParam('id');
//        if(!isset($tm_id)) {
//            $tm_id = 0;
//        }
//        $products = array(168,167); // This is hard-coded right now, but should actually get values from database.
        
        $custIds = array();
 
        foreach($products as $product) {
//            foreach($product as $cust) {
                $custIds[$product] = array('position'=>$product);
//            }
        }
        return $custIds;
    }
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');


        $this->getMassactionBlock()->addItem('redirect', array(
             'label'=> Mage::helper('catalog')->__(''),
             'url'  => $this->getUrl('* / * /',array('category'=>$this->getRequest()->getParam('id'))),
             //'confirm' => Mage::helper('catalog')->__('Are you sure?')
        ));

        /*$this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('catalog')->__('Delete'),
             'url'  => $this->getUrl('* / * /massDelete'),
             'confirm' => Mage::helper('catalog')->__('Are you sure?')
        ));*/

        $statuses = Mage::getSingleton('catalog/product_status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        /*$this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('catalog')->__('Change status'),
             'url'  => $this->getUrl('* / * /massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('catalog')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('catalog/update_attributes')){
            $this->getMassactionBlock()->addItem('attributes', array(
                'label' => Mage::helper('catalog')->__('Update Attributes'),
                'url'   => $this->getUrl('* /catalog_product_action_attribute/edit', array('_current'=>true))
            ));
        }*/

        return $this;
    }
}