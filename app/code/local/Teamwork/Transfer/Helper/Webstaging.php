<?php
/**
 * Webstaging helper
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Helper_Webstaging extends Mage_Core_Helper_Abstract
{
    /**
     * Get payment paid amount. Created to make convinient rewrite by the modules, which modificates standard order payment mechanism.
     *
     * @param Mage_Sales_Model_Order_Payment
     *
     * @return float
     */
    public function getPaymentPaid($payment)
    {
        $paid = (float)($payment->getBaseAmountPaid() ? $payment->getBaseAmountPaid() : $payment->getBaseAmountAuthorized());/**/
        return round($paid, 6);
    }
    
    public function getChqFormatedDate($date, $timeFormat="Y-m-dTH:i:s")
    {
        if( !empty($date) )
        {
            $date = date($timeFormat, strtotime($date));
            if( substr($date, 0, 4) != '0000' )
            {
                return $date;
            }
        }
        return '';
    }
    
    public function allowAuthorizeOnlyPayment($paymentName, $channelId)
    {
        $paymentMethodInfo = Mage::getModel('teamwork_service/settings')->getPaymentMethodByName($paymentName, $channelId);
        if( !empty($paymentMethodInfo) )
        {
            return $paymentMethodInfo['allow_authorize_only'];
        }
        return false;
    }
    
    public function isChqZoneUsedAsProcessing()
    {
        if( Mage::getStoreConfig(Teamwork_Transfer_Helper_Config::XML_PATH_CHQ_AS_PROCESSING_ZONE) )
        {
            return true;
        }
        return false;
    }
    
    public function refundInChq($paymentName, $channelId)
    {
        $paymentMethodInfo = Mage::getModel('teamwork_service/settings')->getPaymentMethodByName($paymentName, $channelId);
        if( !empty($paymentMethodInfo) )
        {
            return $paymentMethodInfo['refund_in_teamwork'];
        }
        return false;
    }
}