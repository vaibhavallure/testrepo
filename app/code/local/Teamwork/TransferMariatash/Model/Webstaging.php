<?php
class Teamwork_TransferMariatash_Model_Webstaging extends Teamwork_CEGiftcards_Transfer_Model_Webstaging
{
	public function isValidForChq($completedOnly)
    {
        /**/$createdAtLimitation = '2018-03-01';
        if( $this->_order->getCreatedAt() < $createdAtLimitation )
        {
            return false;
        }/**/
        if( !(Mage::helper('teamwork_transfer/webstaging')->isChqZoneUsedAsProcessing()) )
        {
            return false;
        }
        $allowAuthorizeOnly = Mage::helper('teamwork_transfer/webstaging')->allowAuthorizeOnlyPayment( $this->_order->getPayment()->getMethod(), $this->_getChannelId() );
        $authorizedAmount = floatval($this->_order->getPayment()->getAmountAuthorized());
        $paidAmount = floatval( $this->_order->getPayment()->getAmountPaid() );
        
        $completedOnly = ($completedOnly == 'false') ? false : true;
        switch($completedOnly)
        {
            case true:
                if( $this->_order->getStatus() == Mage_Sales_Model_Order::STATE_COMPLETE )
                {
                    return true;
                }
            break;
            case false:
                if( (!(float)$this->_order->getGrandTotal() || ($paidAmount || ($allowAuthorizeOnly && $authorizedAmount))) && $this->_order->getStatus() != Mage_Sales_Model_Order::STATUS_FRAUD )
                {
                    return true;
                }
            break;
        }
        return false;
    }
}