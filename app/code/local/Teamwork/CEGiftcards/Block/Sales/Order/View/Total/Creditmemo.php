<?php

class Teamwork_CEGiftcards_Block_Sales_Order_View_Total_Creditmemo
    extends Teamwork_CEGiftcards_Block_Sales_Order_View_Total_Abstract
{
    protected function _getObjectData()
    {
        return array(
            'object' => $this->getParentBlock()->getCreditmemo(),
            'link_collection' => 'teamwork_cegiftcards/order_creditmemo_link_collection',
        );
    }

}
