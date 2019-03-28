<?php

class Allure_Inventory_Block_Adminhtml_Reports_Transfer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

	public function __construct ()
	{
		parent::__construct();
	}

	protected function _prepareCollection ()
	{
		$entityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
		$prodNameAttrId = Mage::getModel('eav/entity_attribute')->loadByCode($entityTypeId, 'name')->getAttributeId();
		
		$collection = Mage::getModel('inventory/transfer')->getCollection();
		$collection->getSelect()->join('admin_user', 'main_table.user_id = admin_user.user_id',
				array(
					'username'
				)
		);
		
		$collection->getSelect()
			->joinLeft(array(
			'prod' => 'catalog_product_entity'
		), 'prod.entity_id = main_table.product_id', array(
			'sku'
		))
			->joinLeft(array(
			'cpev' => 'catalog_product_entity_varchar'
		), 'cpev.entity_id = prod.entity_id AND cpev.attribute_id=' . $prodNameAttrId . '',
			array(
				'name' => 'value'
			)
		);
		$collection->getSelect()->group('main_table.id');
		$collection->getSelect()->order('main_table.updated_at DESC');
		
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns ()
	{
		$this->addColumn('id',
			array(
				'header' => Mage::helper('reports')->__('Id'),
				'sortable' => false,
				'index' => 'id',
				'filter' => 'adminhtml/widget_grid_column_filter_range'
			)
		);
		
		$this->addColumn('sku',
			array(
				'header' => Mage::helper('reports')->__('SKU'),
				'sortable' => false,
				'index' => 'sku'
			)
		);
		
		$this->addColumn('transfer_from',
			array(
				'header' => Mage::helper('reports')->__('From Store'),
				'sortable' => True,
				'index' => 'transfer_from',
				'type' => 'options',
				'options' => Mage::getModel('core/website')->getCollection()
					->toOptionHash()
			
			)
		);
		
		$this->addColumn('transfer_to',
			array(
				'header' => Mage::helper('reports')->__('To Store'),
				'sortable' => true,
				'index' => 'transfer_to',
				'type' => 'options',
				'options' => Mage::getModel('core/website')->getCollection()
					->toOptionHash()
			
			)
		);
		
		$this->addColumn('qty',
			array(
				'header' => Mage::helper('reports')->__('Transferred Quantity'),
				'sortable' => true,
				'index' => 'qty'
			)
		);
		
		$this->addColumn('username',
			array(
				'header' => Mage::helper('reports')->__('Transferred By'),
				'sortable' => false,
				'index' => 'username'
			)
		);
		
		$this->addColumn('updated_at',
			array(
				'header' => Mage::helper('reports')->__('Updated At'),
				'sortable' => false,
				'index' => 'updated_at',
				'filter_index' => 'main_table.updated_at',
				"type" => "datetime"
			)
		);
		
		$this->addExportType('*/*/exportTransferCsv', Mage::helper('reports')->__('CSV'));
		$this->addExportType('*/*/exportTransferExcel', Mage::helper('reports')->__('Excel'));
		
		return parent::_prepareColumns();
	}
}
