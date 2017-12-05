<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml low stock products report grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Allure_Inventory_Block_Adminhtml_Reports_Minmax_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
//    protected $_saveParametersInSession = true;

    public function __construct()
    {
        parent::__construct();
        $this->setId('gridminmax');
        $this->setUseAjax(false);
    }
   
    protected function _prepareCollection()
    {
     
        if ($this->getRequest()->getParam('website')) {
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else if ($this->getRequest()->getParam('group')) {
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else if ($this->getRequest()->getParam('store')) {
            $storeId = (int)$this->getRequest()->getParam('store');
        } else {
            $storeId = '';
        }

        $entityTypeId = Mage::getModel('eav/entity')
        ->setType('catalog_product')
        ->getTypeId();
        $prodNameAttrId = Mage::getModel('eav/entity_attribute')
        ->loadByCode($entityTypeId, 'name')
        ->getAttributeId();
        
        $collection = Mage::getModel('inventory/minmaxlog')->getCollection();
        $collection->getSelect()->join('admin_user', 'main_table.user_id = admin_user.user_id',array('username'));
       // $collection->getSelect()->joinLeft('catalog_product_entity', 'catalog_product_entity.entity_id = main_table.product_id', array('sku'));
        //$collection->getSelect()->join('catalog_product_entity_varchar', 'main_table.product_id = admin_user.user_id',array('username'));
       
        
        $collection->getSelect()->joinLeft(
        				array('prod' => 'catalog_product_entity'),
        				'prod.entity_id = main_table.product_id',
        				array('sku')
        				)
        				->joinLeft(
        						array('cpev' => 'catalog_product_entity_varchar'),
        						'cpev.entity_id = prod.entity_id AND cpev.attribute_id='.$prodNameAttrId.'',
        						array('name' => 'value')
        						);
        $collection->getSelect()->group('main_table.id');
        $collection->getSelect()->order('main_table.id DESC');
      
       
      /*   echo  $collection->getSelect();
        die; */
        
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
    	
    	$this->addColumn('id', array(
    			'header'    =>Mage::helper('reports')->__('Id'),
    			'sortable'  =>false,
    			'index'     =>'id',
    			'filter'    =>'adminhtml/widget_grid_column_filter_range',
    	));
    
    	
    	/* 
    	$this->addColumn('image', array(
    			'header'    => Mage::helper('reports')->__('Image'),
    			'align'     =>'left',
    			'index'     => 'image',
    			'renderer'  => 'inventory/adminhtml_inventory_renderer_image'
    	)); */
    	
       /*  $this->addColumn('name', array(
            'header'    =>Mage::helper('reports')->__('Product Name'),
            'sortable'  =>false,
            'index'     =>'name'
        )); */
        $this->addColumn('sku', array(
        		'header'    =>Mage::helper('reports')->__('Sku'),
        		'sortable'  =>false,
        		'index'     =>'sku'
        ));
	
        $this->addColumn('old_min', array(
            'header'    =>Mage::helper('reports')->__('Previous Min Qty'),
            'sortable'  =>false,
            'index'     =>'old_min'
        ));
        $this->addColumn('min', array(
            'header'    =>Mage::helper('reports')->__('Added Min Qty'),
            'sortable'  =>false,
            'index'     =>'min'
        ));
        
        $this->addColumn('old_max', array(
            'header'    =>Mage::helper('reports')->__('Previous Max Qty'),
            'sortable'  =>false,
            'index'     =>'old_max'
        ));
        $this->addColumn('max', array(
            'header'    =>Mage::helper('reports')->__('Added Max Qty'),
            'sortable'  =>false,
            'index'     =>'max'
        ));
        
        $this->addColumn('old_cost', array(
            'header'    =>Mage::helper('reports')->__('Previous Cost'),
            'sortable'  =>false,
            'index'     =>'old_cost'
        ));
        
        $this->addColumn('cost', array(
            'header'    =>Mage::helper('reports')->__('Added Cost'),
            'sortable'  =>false,
            'index'     =>'cost'
        ));
       
        
        
        $this->addColumn('username', array(
        		'header'    =>Mage::helper('reports')->__('Added By'),
        		'sortable'  =>false,
        		'index'     =>'username'
        ));
       
        $this->addColumn('stock_id', array(
        		'header'    =>Mage::helper('reports')->__('Store'),
        		'sortable'  =>True,
        		'index'     =>'stock_id',
        		'type'      => 'options',
        		'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash()
        
        ));
        
        $this->addColumn('updated_at', array(
        		'header'    =>Mage::helper('reports')->__('Updated At'),
        		'sortable'  =>false,
        		'index'     =>'updated_at',
        		'filter_index'=>'main_table.updated_at',
        		"type" =>   "datetime",
        ));
        
      
        $this->addExportType('*/*/exportSalesminmaxCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportSalesminmaxExcel', Mage::helper('reports')->__('Excel'));

        return parent::_prepareColumns();
    }
}
