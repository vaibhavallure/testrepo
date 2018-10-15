<?php

class Allure_Counterpoint_Block_Adminhtml_Sales_Order_Payment extends Mage_Adminhtml_Block_Sales_Order_Payment
{
    protected function _toHtml()
    {
        $orderMethod = $this->getParentBlock()->getOrder()->getCreateOrderMethod();
        if($orderMethod == 1 || $orderMethod == 2 ){
            $paymentsData = $this->getParentBlock()->getOrder()->getPayment()->getAdditionalData();
            $paymentsData = unserialize($paymentsData);
            if(count($paymentsData) > 1){
                $params = Mage::app()->getRequest()->getParams();
                $paymentId = $this->getParentBlock()->getOrder()->getPayment()->getId();
                if(array_key_exists('invoice_id', $params)){
                    $paymentArr = $paymentsData[$params['invoice_id']];
                    $custPaymentId = $paymentArr['payment_id'];
                    if($paymentId!=$custPaymentId){
                        $paymentObj = Mage::getModel("sales/order_payment")->load($custPaymentId);
                        $this->setPayment($paymentObj);
                    }
                }else{
                    $i = 0;
                    $payAmt = array();
                    foreach ($paymentsData as $data){
                        $amtP = round($data['amt'],2);
                        if($amtP >= 0){
                            $amtP = "$".$amtP;
                        }else{ 
                            $amtP = (-1)*$amtP;
                            $amtP = "-$".$amtP;
                        }
                        $payAmt[$i] = $amtP;
                        $paymentObj = Mage::getModel("sales/order_payment")
                                        ->load($data['payment_id']);
                        $paymentInfoBlock = Mage::helper('payment')->getInfoBlock($paymentObj);
                        $this->setChild('info_'.$i, $paymentInfoBlock);
                        $i++;
                    }
                    $info = "";
                    $cnt = 1;
                    for ($j=0;$j<$i;$j++){
                        $info = $info ."Payment :- ".$cnt."  ".$this->getChildHtml('info_'.$j)." ( ".$payAmt[$j]." )"."<br><br>";
                        $cnt++;
                    }
                    return $info;
                }
            }
        }
        return $this->getChildHtml('info');
    }

}
