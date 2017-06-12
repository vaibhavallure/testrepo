<?php

/**
 * Adminhtml configuration upgrade block
 *
 * @category   Remarkety
 * @package    Remarkety_Mgconnector
 * @author     Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Remarkety_Mgconnector_Block_Adminhtml_Configuration_Upgrade extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Prepare block
     */
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'upgrade_id';
        $this->_blockGroup = 'mgconnector';
        $this->_controller = 'adminhtml_configuration';
        $this->_mode = 'upgrade';

        $ver = Mage::getConfig()->getModuleConfig("Remarkety_Mgconnector")->version;
        $this->_headerText = $this->__('Upgrade Remarkety extension (version: %s)', $ver);

        $this->_removeButton('back');
        $this->_removeButton('reset');
        $this->_addButton('save', array(
            'label'     => Mage::helper('adminhtml')->__('Complete Installation'),
            'onclick'   => 'editForm.submit();',
            'class'     => 'save',
        ), 1);
    }
}