<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Block_Adminhtml_Synchtable_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();

		$this->setUseAjax(true);
		$this->setId('fieldsmap_grid'); /* !!! Don't change grid ID */
		$this->setSaveParametersInSession(true);
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
	}
	
	protected function _prepareCollection()
	{
		$collection = Mage::getResourceModel('sugarcrm/synchmap_collection');
		$collection->showCustomerInfo();
		$this->setCollection($collection);
		
		parent::_prepareCollection();

		return $this;
	}
	
	protected function _prepareColumns()
	{
		$this->addColumn('id',
			array(
				'header'	=> $this->__('ID'), 
				'width'		=> '80px', 
				'type'		=> 'text', 
				'index'		=> 'id'
			)
		);
		
		$this->addColumn('bean',
			array(
				'header'	=> $this->__('SugarCRM Module'),
				'index'		=> 'bean',
				'type'		=> 'options',
				'default'	=> '----',
				'options'	=> $this->_getBeans(),
				'filter_condition_callback'	=> array(
					$this, 
					'_filterBeansCondition'
				)
			)
		);
		
		$this->addColumn('sid',
			array(
				'header'	=> $this->__('SugarCRM Module ID'),
				'index'		=> 'sid',
				'default'	=> '----',
			)
		);
		
		
		$this->addColumn('model',
			array(
				'header'	=> $this->__('Magento Model'),
				'index'		=> 'model',
				'type'		=> 'options',
				'options'	=> $this->_getModels(),
			)
		);
		
		$this->addColumn('cid',
			array(
				'header'	=> $this->__('Magento ID'),
				'index'		=> 'cid',
				'default'	=> '----',
			)
		);
		
		$this->addColumn('firstname',
			array(
				'header'	=> $this->__('Customer First Name'),
				'index'		=> 'customer_firstname',
				'default'	=> '----',
			)
		);

		$this->addColumn('lastname',
			array(
				'header'	=> $this->__('Customer Last Name'),
				'index'		=> 'customer_lastname',
				'default'	=> '----',
			)
		);
		
		$this->addColumn('action', 
			array(
				'header'	=> $this->__('Action'), 
				'index'		=> 'action',
				'type'		=> 'action', 
				'width'		=> '70px', 
				'getter'	=> 'getId', 
				'actions'	=> array(
					array(
						'caption'	=> $this->__('Edit'), 
						'url'		=> array(
							'base' => '*/*/edit'
						), 
						'field'		=> 'id'
					),
					array(
						'caption'	=> $this->__('Delete'), 
						'url'		=> array(
							'base' => '*/*/delete'
						), 
						'field'		=> 'id'
					)
				), 
				'filter'	=> false, 
				'sortable'	=> false, 
				'is_system'	=> true,
			)
		);
		
		return parent::_prepareColumns();
	}
	
	/**
	 * Helper function to reveive on row click url
	 *
	 * @param Belitsoft_Sugarcrm_Model_Synchmap $row Current rows dataset
	 * @return string URL for current row's onclick event
	 */
	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}

	/**
	 * Helper function to receive grid functionality urls for current grid
	 *
	 * @return string Requested URL
	 */
	public function getGridUrl()
	{
		return $this->getUrl('*/*/index', array('_current' => true));
	}

	/**
	 * Helper function to add bean filter condition
	 *
	 * @param Mage_Core_Model_Mysql4_Collection_Abstract $collection Data collection
	 * @param Mage_Adminhtml_Block_Widget_Grid_Column $column Column information to be filtered
	 */
	protected function _filterBeansCondition($collection, $column)
	{
		if(!$value = $column->getFilter()->getValue()) {
			return;
		}
		
		$this->getCollection()->addBeanFilter($value);
	}
	
	/**
	 * Helper function to load bean's list
	 */
	protected function _getBeans()
	{
		return Mage::getModel('sugarcrm/source_beans')->toOptionHash();//Mage::getResourceModel('sugarcrm/synchmap_collection')->getBeans();
	}
	
	/**
	 * Helper function to load Magento model's list
	 */
	protected function _getModels()
	{
		return Mage::getModel('sugarcrm/source_models')->toOptionHash();
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('id');
		$this->getMassactionBlock()->setFormFieldName('synchtable');

		$this->getMassactionBlock()->addItem('delete',
			array(
				'label'	=> $this->__('Delete'),
				'url'	=> $this->getUrl('*/*/massDelete')
			)
		);

		return $this;
	}
}