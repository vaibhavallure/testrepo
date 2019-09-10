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
				'header' => $helper->__('Appointment Start'),
				'type' => 'datetime',
				'index'  => 'appointment_start',
		));
		
		$this->addColumn('appointment_end', array(
				'header' => $helper->__('Appointment End'),
				'type' => 'datetime',
				'index'  => 'appointment_end',
		));
		
		$this->addColumn('booking_time', array(
				'header' => $helper->__('Booking Time'),
				'type' => 'datetime',
				'index'  => 'booking_time',
		));
        $this->addColumn('last_notified', array(
            'header' => $helper->__('Last Notifed (EST)'),
            'type' => 'datetime',
            'index'  => 'last_notified',
            'renderer' => 'appointments/adminhtml_render_notified'
        ));
		$this->addColumn('piercing_qty', array(
				'header' => $helper->__('No of People'),
				'index'  => 'piercing_qty'
		));

        $this->addColumn('no_of_piercing', array(
            'header' => $helper->__('No of Piercings'),
            'index'  => 'no_of_piercing',
            'renderer' => 'appointments/adminhtml_render_piercing'
        ));

        $this->addColumn('no_of_checkup', array(
            'header' => $helper->__('No of Checkups'),
            'index'  => 'no_of_checkup',
            'renderer' => 'appointments/adminhtml_render_checkup'
        ));

        $this->addColumn('last_notified', array(
            'header' => $helper->__('Last Notifed (EST)'),
            'type' => 'datetime',
            'index'  => 'last_notified',
            'renderer' => 'appointments/adminhtml_render_notified'
        ));
		$this->addColumn('app_status', array(
				'header' => $helper->__('Status'),
				'type' => 'options',
				'width'     => '80',
				'options' => Mage::getModel('appointments/appointments')->getStatus(),
				'index' => 'app_status',
				'sortable' => false,
		));
		
		//if (!Mage::app()->isSingleStoreMode()) {
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
		//}
		
		$this->addColumn('piercer_id', array(
				'header' => $helper->__('Piercer'),
				'index'  => 'piercer_id',
		        'type' => 'options',// aws02- add line
		        'options' => Mage::helper("appointments")->getPiercersAsOptions(), // aws02- add line
				//'renderer' => 'appointments/adminhtml_appointments_edit_renderer_piercername' //aws02 - comment the line
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


    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('allure_appointments_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);



        $this->getMassactionBlock()->addItem('app_update_status', array(
            'label' => Mage::helper('appointments')->__('Change status '),
            'url' => Mage::helper('adminhtml')->getUrl('*/adminhtml_appointments/changeStatus', array('redirect' => 'allure_appointments')),
            'confirm' => Mage::helper('appointments')->__('Are you sure to change status for the selected Appointment'),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('appointments')->__('Status '),
                    'values' => Mage::getModel('appointments/appointments')->getStatus()
                )
            )
        ));
        $this->getMassactionBlock()->addItem('send_reminder', array(
            'label' => Mage::helper('appointments')->__('Send Reminder'),
            'url' => Mage::helper('adminhtml')->getUrl('*/adminhtml_appointments/sendReminder', array('redirect' => 'allure_appointments')),
            'confirm' => Mage::helper('appointments')->__('send reminder to selected appointments'),
            'additional' => array(
                'visibility' => array(
                    'name' => 'reminder_type',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('appointments')->__('Reminder Type '),
                    'values' => Mage::getModel('appointments/appointments')->getReminderType()
                )
            )
        ));

        return $this;
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