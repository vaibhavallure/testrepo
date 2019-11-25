<?php  

class Allure_Appointments_Block_Adminhtml_Appointments extends Mage_Adminhtml_Block_Widget_Grid_Container {
	public function __construct()
	{
		
		$this->_blockGroup = 'appointments';
		$this->_controller = 'adminhtml_appointments';
		$this->_headerText = Mage::helper('appointments')->__('Manage Appointments');
		$this->_addButtonLabel = Mage::helper('appointments')->__('Book New Appointment');
            $this->_addButton('new_stores_book', array(
                'label'     => Mage::helper('catalogrule')->__('Book New Appointment'),
                'onclick'   => "location.href='".$this->getUrl('*/*/newsystem/store/')."'",
                'class'     => '',
            ));

		$store_name =Mage::helper("appointments")->storeAppearName(Mage::getStoreConfig('appointments/popup_setting/store'));

        $this->_addButton('new_special', array(
            'label'     => Mage::helper('catalogrule')->__('Book '.$store_name.' Appointment'),
            'onclick'   => "location.href='".$this->getUrl('*/*/newspecial')."'",
            'class'     => '',
        ));

		parent::__construct();

        $this->_removeButton('add');

    }
	
	protected function _prepareLayout()
	{
		
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
		return false;
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