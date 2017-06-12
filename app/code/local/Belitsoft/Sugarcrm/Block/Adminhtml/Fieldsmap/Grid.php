<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Block_Adminhtml_Fieldsmap_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		
		$this->setId('fieldsmap_grid');
		$this->setSaveParametersInSession(true);        
		$this->setFilterVisibility(false);
		$this->setPagerVisibility(false);
	}
	
	protected function _prepareCollection()
	{
		try {
			$collection = Mage::getResourceModel('sugarcrm/fieldsmap_collection');
			$collection->setGridView();
			$this->setCollection($collection);
			$this->_defaultLimit = intval(count($collection->getSugarConnection()->getModuleFields()));
		} catch (Exception $e) {
			die;
		}
	
		parent::_prepareCollection();
		
		return $this;
	}
	
	protected function _prepareColumns()
	{
		$this->addColumn('sugarcrm_label',
			array(
				'header'	=> $this->__('SugarCRM Field Label'),
				'index'		=> 'sugarcrm_label',
				'width'		=> '250px',
				'sortable'	=> false,
				'filter'	=> false,
				'default'	=> '----',
			)
		);

		$this->addColumn('sugarcrm_field',
			array(
				'header'	=> $this->__('SugarCRM Field Name'),
				'index'		=> 'sugarcrm_field',
				'width'		=> '150px',
				'sortable'	=> false,
				'filter'	=> false,
				'default'	=> '----',
			)
		);
		
		$this->addColumn('sugarcrm_types',
			array(
				'header'	=> $this->__('SugarCRM Field Type'),
				'index'		=> 'sugarcrm_type',
				'width'		=> '50px',
				'sortable'	=> false,
				'filter'	=> false,
				'default'	=> '----',
			)
		);
		
		$this->addColumn('sugarcrm_options',
			array(
				'header'	=> $this->__('SugarCRM Field Options'),
				'index'		=> 'sugarcrm_options',
				'width'		=> '150px',
				'sortable'	=> false,
				'filter'	=> false,
				'default'	=> '----',
			)
		);
		
		$this->addColumn('mage_customer_field_label',
			array(
				'header'	=> $this->__('Magento Customer Field Label'),
				'index'		=> 'mage_customer_field_label',
				'width'		=> '250px',
				'sortable'	=> false,
				'filter'	=> false,
				'default'	=> '----',
			)
		);
		
		$this->addColumn('custom_model',
			array(
				'header'	=> $this->__('Custom Model'),
				'index'		=> 'custom_model',
				'width'		=> '250px',
				'sortable'	=> false,
				'filter'	=> false,
				'default'	=> '----',
			)
		);
		
		$this->addColumn('eval_code',
			array(
				'header'	=> $this->__('Code'),
				'index'		=> 'eval_code',
				'sortable'	=> false,
				'filter'	=> false,
				'default'	=> '----',
			)
		);
		
		return parent::_prepareColumns();
	}

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('module'=>Mage::getSingleton('sugarcrm/connection')->getModuleName(), 'id'=>$row->getId(), '_current'=>true));
	}
}