<?php

/**
 * Adminhtml customer grid block
 *
 * @category   Tryon
 */
class Ecp_Tryon_Block_Adminhtml_Tryon_Subproducts extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        
        parent::__construct();
        $this->setId('productGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_filter');
        $this->setDefaultLimit(10);
    }
    
    public function getGoBackButtonHtml()
    {
        return $this->getChildHtml('go_back_button');
    }

    protected function _prepareLayout() {
        
        $code = explode('-',$this->getRequest()->getParam('code'));
        
        $this->setChild('go_back_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => Mage::helper('adminhtml')->__('Back to SubRegions'),
                            'onclick' => 'document.location.href=\''.$this->getUrl('*/*',array('code'=>$code[0])).'\''
                        ))
        );
        
        return parent::_prepareLayout();
        
    }
    
    public function getMainButtonsHtml()
    {
        $html = '';
        if($this->getFilterVisibility()){
            $html.= $this->getGoBackButtonHtml();
            $html.= $this->getResetFilterButtonHtml();
            $html.= $this->getSearchButtonHtml();
        }
        return $html;
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection() {
        $store = $this->_getStore();
        $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('sku')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('attribute_set_id')
                //->addAttributeToSelect('tryonids')
                //->addAttributeToFilter('tryonids',array('like'=>'%'.$this->getRequest()->getParam('code').'%'))
                ->addAttributeToSelect('type_id');

        $this->setCollection($collection);

        parent::_prepareCollection();
        //$this->getCollection()->addWebsiteNamesToResult();
        return $this;
    }

    protected function _addColumnFilterToCollection($column) {
        // Set custom filter for in product flag
        if ($column->getId() == 'product') {
            $productIds = $this->_getSelectedSubproducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareColumns() {

        $this->addColumn('entity_id', array(
            'header' => Mage::helper('catalog')->__('ID'),
            'width' => '50px',
            'type' => 'number',
            'index' => 'entity_id'
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('catalog')->__('Name'),
            'index' => 'name',
        ));

        $store = $this->_getStore();
        if ($store->getId()) {
            $this->addColumn('custom_name', array(
                'header' => Mage::helper('catalog')->__('Name in %s', $store->getName()),
                'index' => 'custom_name',
            ));
        }

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                ->load()
                ->toOptionHash();

        $this->addColumn('set_name', array(
            'header' => Mage::helper('catalog')->__('Attrib. Set Name'),
            'width' => '100px',
            'index' => 'attribute_set_id',
            'type' => 'options',
            'options' => $sets,
        ));

        $this->addColumn('sku', array(
            'header' => Mage::helper('catalog')->__('SKU'),
            'width' => '80px',
            'index' => 'sku',
        ));

        $store = $this->_getStore();


        /* $this->addColumn('action',
          array(
          'header'    => Mage::helper('catalog')->__('Action'),
          'width'     => '50px',
          'type'      => 'action',
          'getter'     => 'getId',
          'actions'   => array(
          array(
          'caption' => Mage::helper('catalog')->__('Update products per region'),
          'url'     => array(
          'base'=>'* / * /updateproducts',
          'params'=>array('code'=>$this->getRequest()->getParam('code'))
          ),
          'field'   => 'id'
          )
          ),
          'filter'    => false,
          'sortable'  => false,
          'index'     => 'stores',
          )); */

        /* if (Mage::helper('catalog')->isModuleEnabled('Mage_Rss')) {
          $this->addRssList('rss/catalog/notifystock', Mage::helper('catalog')->__('Notify Low Stock RSS'));
          } */

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {

        $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToFilter('tryonids', array('like' => '%' . $this->getRequest()->getParam('code') . '%'));

        $this->getRequest()->setParam('internal_product', implode(',', $collection->getColumnValues('entity_id')));

        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');


        $this->getMassactionBlock()->addItem('redirect', array(
            'label' => Mage::helper('catalog')->__('Update products per subregion'),
            'url' => $this->getUrl('*/*/updateproducts', array('code' => $this->getRequest()->getParam('code'))),
            'confirm' => Mage::helper('catalog')->__('Are you sure?')
        ));

        /* $this->getMassactionBlock()->addItem('delete', array(
          'label'=> Mage::helper('catalog')->__('Delete'),
          'url'  => $this->getUrl('* / * /massDelete'),
          'confirm' => Mage::helper('catalog')->__('Are you sure?')
          )); */

        $statuses = Mage::getSingleton('catalog/product_status')->getOptionArray();

        array_unshift($statuses, array('label' => '', 'value' => ''));
        /* $this->getMassactionBlock()->addItem('status', array(
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
          } */

        return $this;
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/ajax/', array('isAjax' => true, '_current' => true));
    }

    protected function _getSelectedSubproducts() {   // Used in grid to return selected customers values.
        return 'asdasd';
        $products = array_keys($this->getSelectedSubproducts());
        return $products;
    }

    public function getSelectedSubproducts() {
        if (Mage::registry('celebrities_outfit_data')) {
            $coId = Mage::registry('celebrities_outfit_data')->getData('related_products');
            if (!empty($coId))
                $products = explode(",", $coId);
        }else {
            if ($this->getRequest()->getParam('id')) {
                $model = Mage::getModel('ecp_celebrities/outfits')->load($this->getRequest()->getParam('id'));
                $products = explode(",", $model->getRelatedSubproducts());
            }
        }

        // Customer Data
//        $tm_id = $this->getRequest()->getParam('id');
//        if(!isset($tm_id)) {
//            $tm_id = 0;
//        }
//        $products = array(168,167); // This is hard-coded right now, but should actually get values from database.

        $custIds = array();

        foreach ($products as $product) {
//            foreach($product as $cust) {
            $custIds[$product] = array('position' => $product);
//            }
        }
        return $custIds;
    }

}