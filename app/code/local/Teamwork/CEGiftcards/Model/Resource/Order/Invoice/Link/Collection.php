<?php

class Teamwork_CEGiftcards_Model_Resource_Order_Invoice_Link_Collection
    extends Teamwork_CEGiftcards_Model_Resource_Order_Link_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('teamwork_cegiftcards/order_invoice_link');
    }

    public function addInvoiceFilter($invoice)
    {
        return $this->addObjectFilter($invoice);
    }

    public function addObjectFilter($object)
    {
        return $this->_addIdFilter($object, "invoice_id");
    }

}
