<?php
/**
 * @author  Giovani Castillo <giovani.castillo@entrepids.com>
 * @package Ecp
 */
class Ecp_Quickview_Block_Modal extends Mage_Core_Block_Template{

    public function  _toHtml() {
        $this->setTemplate('ecp/quickview/modal.phtml');
        parent::_toHtml();
    }
}
