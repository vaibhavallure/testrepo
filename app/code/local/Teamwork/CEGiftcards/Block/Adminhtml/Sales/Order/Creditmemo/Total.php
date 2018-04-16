<?php

class Teamwork_CEGiftcards_Block_Adminhtml_Sales_Order_Creditmemo_Total
    extends Teamwork_CEGiftcards_Block_Adminhtml_Sales_Order_Total_Abstract
{

    protected function _getKeyObject()
    {
        return array(
            'key' => '__teamwork_cegiftcards_creditmemo_links',
            'object' => $this->getParentBlock()->getCreditmemo(),
            'link_collection' => 'teamwork_cegiftcards/order_creditmemo_link_collection',
        );
    }

}
