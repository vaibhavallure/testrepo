<?php
class Allure_Appointments_Block_Adminhtml_Appointments_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('allure_appointments_grid');
		$this->setDefaultSort('increment_id');
		$this->setDefaultDir('DESC');
		$this->setUpdatedTime(Mage::getModel('core/date')->date('Y-m-d h:m:s'));
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
		
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('appointments/appointments')->getCollection();
		$collection->setOrder('id','DESC');
				$this->setCollection($collection);
				parent::_prepareCollection();
				return $this;
	}

	protected function _prepareColumns()
	{
		$helper = Mage::helper('appointments');

		$this->addColumn('id', array(
				'header' => $helper->__('App. Id'),
				'width' => '50px',
				'index'  => 'id'
		));

		$this->addColumn('firstname', array(
				'header' => $helper->__('Name'),
				'index'  => 'firstname',
				'renderer' => 'appointments/adminhtml_appointments_edit_renderer_name'
		));
		
		$this->addColumn('email', array(
				'header' => $helper->__('Email'),
				'index'  => 'email'
		));
		
		$this->addColumn('phone', array(
				'header' => $helper->__('Phone'),
				'index'  => 'phone'
		));
		date_default_timezone_set(Mage::getStoreConfig('general/locale/timezone'));
		$this->addColumn('appointment_start', array(
				'header' => $helper->__('Appointment Start Time'),
				'type' => 'datetime',
				'index'  => 'appointment_start',
		));
		
		$this->addColumn('appointment_end', array(
				'header' => $helper->__('Appointment End Time'),
				'type' => 'datetime',
				'index'  => 'appointment_end',
		));
		
		$this->addColumn('booking_time', array(
				'header' => $helper->__('Booking Time'),
				'type' => 'datetime',
				'index'  => 'booking_time',
		));
		$this->addColumn('piercing_qty', array(
				'header' => $helper->__('No of People in Group'),
				'index'  => 'piercing_qty'
		));
		
		$this->addColumn('app_status', array(
				'header' => $helper->__('Status'),
				'type' => 'options',
				'width'     => '80',
				'options' => Mage::getModel('appointments/appointments')->getStatus(),
				'index' => 'app_status',
				'sortable' => false,
		));
		
		if (!Mage::app()->isSingleStoreMode()) {
		    if (Mage::helper('core')->isModuleEnabled('Allure_Virtualstore')){
		        $storeOptions = Mage::getSingleton('allure_virtualstore/adminhtml_store')->getStoreOptionHash();
		    }else{
		        $storeOptions = Mage::getSingleton('adminhtml/system_store')->getStoreOptionHash();
		    }
		    
			$this->addColumn('store_id', array(
					'header' => $helper->__('Store'),
					'type' => 'options',
			        'options' => $storeOptions,//Mage::getSingleton('adminhtml/system_store')->getStoreOptionHash(),
					'index' => 'store_id',
					'sortable' => false,
			));
		}
		
		$this->addColumn('piercer_id', array(
				'header' => $helper->__('Piercer'),
				'index'  => 'piercer_id',
				'renderer' => 'appointments/adminhtml_appointments_edit_renderer_piercername'
		));
		$this->addColumn('action',array(
				'header'    => $helper->__('Modify'),
				'width'     => '5%',
				'type'      => 'action',
				'getter'     => 'getId',
				'renderer' => 'appointments/adminhtml_appointments_edit_renderer_modify',
				'filter'    => false,
				'sortable'  => false,
				'is_system' => true,
		)
		);
		
		
		$this->addExportType('*/*/exportCsv', $helper->__('CSV'));
		$this->addExportType('*/*/exportExcel', $helper->__('Excel XML'));
		
		return parent::_prepareColumns();
	}

	public function filterCallback($collection, $column)
	{
		$value = $column->getFilter()->getValue();
		if (is_null(@$value))
			return;
			else
				$collection->addFieldToFilter($column->getIndex(), array('finset' => $value));
	}
	
	public function getGridUrl()
	{
		return $this->getUrl('*/*/grid', array('_current'=>true,'_secure' => true));
	}
	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/view', array('id'=>$row->getId(),'_secure' => true));
	}
}