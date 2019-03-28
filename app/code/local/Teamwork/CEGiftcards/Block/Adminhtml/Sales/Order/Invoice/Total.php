<?php

class Teamwork_CEGiftcards_Block_Adminhtml_Sales_Order_Invoice_Total
    extends Teamwork_CEGiftcards_Block_Adminhtml_Sales_Order_Total_Abstract
{

    protected $_invoice = null;

    public function getInvoice()
    {
        if ($this->_invoice === null) {
            if ($this->hasData('invoice')) {
                $this->_invoice = $this->_getData('invoice');
            } elseif (Mage::registry('current_invoice')) {
                $this->_invoice = Mage::registry('current_invoice');
            } elseif ($this->getParentBlock()->getInvoice()) {
                $this->_invoice = $this->getParentBlock()->getInvoice();
            }
        }
        return $this->_invoice;
    }


    protected function _getKeyObject()
    {
        return array(
            'key' => '__teamwork_cegiftcards_invoice_links',
            'object' => $this->getInvoice(),
            'link_collection' => 'teamwork_cegiftcards/order_invoice_link_collection',
        );
    }

}
