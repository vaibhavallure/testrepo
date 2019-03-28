<?php  

class Allure_Appointments_Block_Adminhtml_Hidedates extends Mage_Adminhtml_Block_Widget_Grid_Container {
	public function __construct()
	{
		
		$this->_blockGroup = 'appointments';
		$this->_controller = 'adminhtml_hidedates';
		$this->_headerText = Mage::helper('appointments')->__('Manage dates');
	
		parent::__construct();
	}
	
	protected function _prepareLayout()
	{
		$addButtonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')
		->setData(
				array(
						'label'   => Mage::helper('appointments')->__('Add Hide Date'),
						'onclick' => "setLocation('" . $this->getUrl('*/*/new',array('_secure' => true)) . "')",
						'class'   => 'add',
				)
				)
				;
		//$this->setChild('add_new_button', $addButtonBlock);
	
		/**
		 * Display store switcher if system has more one store
		 */
		if (!Mage::app()->isSingleStoreMode()) {
			$storeSwitcherBlock = $this->getLayout()->createBlock('adminhtml/store_switcher')
			->setUseConfirm(false)
			->setSwitchUrl($this->getUrl('*/*/*', array('store' => null,'_secure' => true)))
			;
			$this->setChild('store_switcher', $storeSwitcherBlock);
		}
		//$this->setChild('grid', $this->getLayout()->createBlock('appointments/adminhtml_appointmentservice_grid', 'adminhtml_appointmentservice.grid'));
		return parent::_prepareLayout();
	}
	
	public function getAddNewButtonHtml()
	{
		return $this->getChildHtml('add_new_button');
	}
	
	public function getGridHtml()
	{
		return $this->getChildHtml('grid');
	}
	
	public function getStoreSwitcherHtml()
	{
		return $this->getChildHtml('store_switcher');
	}
	
}