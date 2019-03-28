<?php

class Teamwork_CEGiftcards_Block_Sales_Order_View_Total_Invoice
    extends Teamwork_CEGiftcards_Block_Sales_Order_View_Total_Abstract
{

    protected function _getObjectData()
    {
        return array(
            'object' => $this->getParentBlock()->getInvoice(),
            'link_collection' => 'teamwork_cegiftcards/order_invoice_link_collection',
        );
    }

}
