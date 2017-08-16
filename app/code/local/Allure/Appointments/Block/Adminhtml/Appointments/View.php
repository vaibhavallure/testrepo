<?php

class Allure_Appointments_Block_Adminhtml_Appointments_View extends Mage_Adminhtml_Block_Widget_Form_Container
{
    
    public function __construct ()
    {
        $appointment = Mage::registry('allure_appointment');
        parent::__construct();
        $this->_objectId = "id";
        $this->_blockGroup = "appointments";
        $this->_controller = "adminhtml_appointments";
        $this->_mode        = 'view';
        //$this->_updateButton("save", "label", Mage::helper("appointments")->__("Save Item"));
        //$this->_updateButton("delete", "label", Mage::helper("appointments")->__("Delete Item"));
        $this->_removeButton('save');
        $this->_removeButton('reset');
        $this->_removeButton('delete');
        if($appointment->getAppStatus()==Allure_Appointments_Model_Appointments::STATUS_CANCELLED){
            $this->_addButton('revert', array(
                'label'     => Mage::helper('adminhtml')->__('Undo Cancel'),
                'class'     => 'delete',
                'onclick'   => 'deleteConfirm(\''
                . Mage::helper('core')->jsQuoteEscape(
                    Mage::helper('adminhtml')->__('Are you sure you want to Reschedule this?')
                    )
                .'\', \''
                . $this->getUncancelUrl()
                . '\')',
            ));
        }
        else{
            $this->_addButton('modify', array(
                'label'     => Mage::helper('adminhtml')->__('Modify'),
                'class'     => 'save',
                'onclick'   => 'confirmSetLocation(\''
                . Mage::helper('core')->jsQuoteEscape(
                    Mage::helper('adminhtml')->__('Are you sure you want to modify this?')
                    )
                .'\', \''
                . $this->getModifyUrl()
                . '\')',
            ));
            
            $this->_addButton('cancel', array(
                'label'     => Mage::helper('adminhtml')->__('Cancel'),
                'class'     => 'delete',
                'onclick'   => 'deleteConfirm(\''
                . Mage::helper('core')->jsQuoteEscape(
                    Mage::helper('adminhtml')->__('Are you sure you want to cancel this?')
                    )
                .'\', \''
                . $this->getCancelUrl()
                . '\')',
            ));
            $this->_addButton('save', array(
                'label'     => Mage::helper('adminhtml')->__('Send Confirmation'),
                'class'     => 'save',
                'onclick'   => 'confirmSetLocation(\''
                . Mage::helper('core')->jsQuoteEscape(
                    Mage::helper('adminhtml')->__('Are you sure that you want to send appointment confirmation to customer?')
                    )
                .'\', \''
                . $this->getConfirmationUrl()
                . '\')',
            ));
            
        }
        
    }
  
    public function getModifyUrl()
    {
        $this->_objectId=$this->getRequest()->getParam($this->_objectId);
        $email=Mage::getModel('appointments/appointments')->load($this->_objectId)->getEmail();
        return $this->getUrl('admin_appointments/adminhtml_index/index/id/'.$this->_objectId.'/email/'.$email, array('_secure' => true));
    }
   
    public function getCancelUrl()
    {
        return $this->getUrl('*/*/cancel', array($this->_objectId => $this->getRequest()->getParam($this->_objectId),'_secure' => true));
    }
    public function getUncancelUrl()
    {
        return $this->getUrl('*/*/undoCancel', array($this->_objectId => $this->getRequest()->getParam($this->_objectId),'_secure' => true));
    }
    
    public function getHeaderText ()
    {
        return Mage::helper("appointments")->__("Appointment View");
    }
    public function getConfirmationUrl()
    {
        $this->_objectId=$this->getRequest()->getParam('id');
        $key=Mage::getSingleton('core/session')->getFormKey();
       // $this->_objectId=$this->getRequest()->getParam($this->_objectId);
        return $this->getUrl('admin_appointments/adminhtml_index/sendconfirmation', array('id'=>$this->_objectId,'form_key'=>$key,'_secure' => true));
    }
}