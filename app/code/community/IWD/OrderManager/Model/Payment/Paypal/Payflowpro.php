<?php
class IWD_OrderManager_Model_Payment_Paypal_Payflowpro extends Mage_Paypal_Model_Payflowpro
{
    public function reauthorize(Varien_Object $payment, $amount)
    {
        $request = $this->_buildPlaceRequest($payment, $amount);
        $request->setTrxtype(self::TRXTYPE_AUTH_ONLY);

        $lastTransId = $payment->getLastTransId();
        $request->setOrigid($lastTransId);

        $request->setTender('C');
        $request->setDoreauthorization(1);
        $this->_setReferenceTransaction($payment, $request);
        $response = $this->_postRequest($request);
        $this->_processErrors($response);

        switch ($response->getResultCode()){
            case self::RESPONSE_CODE_APPROVED:
                $payment->setTransactionId($response->getPnref())->setIsTransactionClosed(0);
                $order = $payment->getOrder();
                if(!empty($order)){
                    $message = Mage::helper('sales')->__('Authorized amount of %s.', $order->getBaseCurrency()->formatTxt($amount));
                    $order->addStatusHistoryComment($message)->setIsVisibleOnFront(true)->setIsCustomerNotified(false)->save();
                }
                break;
            case self::RESPONSE_CODE_FRAUDSERVICE_FILTER:
                $payment->setTransactionId($response->getPnref())->setIsTransactionClosed(0);
                $payment->setIsTransactionPending(true);
                $payment->setIsFraudDetected(true);
                break;
        }
        return $this;
    }
}
