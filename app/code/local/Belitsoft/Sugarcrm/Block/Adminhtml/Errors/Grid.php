<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Block_Adminhtml_Errors_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();

		$this->setUseAjax(true);
		$this->setId('fieldsmap_grid');
		$this->setSaveParametersInSession(true);
		$this->setIdFieldName('error_id');
		$this->setDefaultSort('creation_date');
		$this->setDefaultDir('DESC');
	}
	
	protected function _prepareCollection()
	{
		$collection = Mage::getResourceModel('sugarcrm/error_collection');
		$this->setCollection($collection);
		
		parent::_prepareCollection();

		return $this;
	}
	
	protected function _prepareColumns()
	{
		$this->addColumn('type',
			array(
				'header'	=> $this->__('Magento Object'),
				'index'		=> 'type',
				'type'		=> 'options',
				'width'		=> '100px', 
				'options'	=> $this->_getObjectsOptions(),
			)
		);

		$this->addColumn('entity_id',
			array(
				'header'	=> $this->__('Magento ID'),
				'index'		=> 'entity_id',
				'type'		=> 'number',
			)
		);

		$this->addColumn('operation',
			array(
				'header'	=> $this->__('Operation'),
				'index'		=> 'operation',
				'type'		=> 'options',
				'options'	=> $this->_getOperationsOptions(),
				'renderer'	=> 'sugarcrm/adminhtml_errors_grid_column_renderer_operation',
				'filter_condition_callback'	=> array(
					$this, 
					'_filterOperationCondition'
				)
			)
		);

		$this->addColumn('error',
			array(
				'header'	=> $this->__('Error'),
				'index'		=> 'error',
			)
		);

		$this->addColumn('status',
			array(
				'header'	=> $this->__('Status'),
				'index'		=> 'status',
				'type'		=> 'options',
				'options'	=> $this->_getStatusOptions(),
			)
		);
		
		$this->addColumn('creation_date',
			array(
				'header'	=> $this->__('Created'),
				'type'		=> 'datetime',
				'index'		=> 'creation_date',
				'default'	=> ' ---- ',
			)
		);
		
		$this->addColumn('update_date',
			array(
				'header'	=> $this->__('Re-synced'),
				'type'		=> 'datetime',
				'index'		=> 'update_date',
				'renderer'	=> 'sugarcrm/adminhtml_errors_grid_column_renderer_resyncdate',
				'default'	=> ' ---- ',
			)
		);
		
		
		$this->addColumn('action', 
			array(
				'header'	=> $this->__('Action'), 
				'index'		=> 'action',
				'type'		=> 'action', 
				'width'		=> '100px', 
				'getter'	=> 'getId', 
				'actions'	=> array(
					array(
						'caption'	=> $this->__('Re-sync'),
						'url'		=> $this->getUrl('*/*/resynch', array('error_id' => '$error_id')),
					),
					array(
						'caption'	=> $this->__('Re-sync and delete'),
						'url'		=> $this->getUrl('*/*/resynchdelete', array('error_id' => '$error_id')),
					),
					array(
						'caption'	=> $this->__('Delete'),
						'url'		=> $this->getUrl('*/*/delete', array('error_id' => '$error_id')),
					),
				), 
				'filter'	=> false, 
				'sortable'	=> false, 
				'is_system'	=> true,
			)
		);
		
		return parent::_prepareColumns();
	}
	
	/**
	 * Helper function to load operations's list
	 */
	protected function _getOperationsOptions()
	{
		return Mage::getModel('sugarcrm/source_operations')->toOptionHash();
	}
	
	/**
	 * Helper function to load operations's list
	 */
	protected function _getObjectsOptions()
	{
		return Mage::getModel('sugarcrm/source_objects')->toOptionHash();
	}
	
	/**
	 * Helper function to load operations's list
	 */
	protected function _getStatusOptions()
	{
		return Mage::getModel('sugarcrm/source_status')->toOptionHash();
	}

	/**
	 * Helper function to add operation filter condition
	 *
	 * @param Mage_Core_Model_Mysql4_Collection_Abstract $collection Data collection
	 * @param Mage_Adminhtml_Block_Widget_Grid_Column $column Column information to be filtered
	 */
	protected function _filterOperationCondition($collection, $column)
	{
		if(!$value = $column->getFilter()->getValue()) {
			return;
		}
		
		$this->getCollection()->addOperationFilter($value);
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('error_id');
		$this->getMassactionBlock()->setFormFieldName('errortable');

		$this->getMassactionBlock()->addItem('resynch',
			array(
				'label'	=> $this->__('Re-sync'),
				'url'	=> $this->getUrl('*/*/massResynch')
			)
		);

		$this->getMassactionBlock()->addItem('resynchdelete',
			array(
				'label'	=> $this->__('Re-sync and delete'),
				'url'	=> $this->getUrl('*/*/massResynchdelete')
			)
		);

		$this->getMassactionBlock()->addItem('delete',
			array(
				'label'	=> $this->__('Delete'),
				'url'	=> $this->getUrl('*/*/massDelete')
			)
		);

		return $this;
	}
}