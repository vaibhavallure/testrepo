<?php
/**
 * Order Model
 *
 * @category    Signifyd Connect
 * @package     Signifyd_Connect
 * @author      Signifyd
 */
class Allure_Signifyd_Model_Rewrite_Order extends Signifyd_Connect_Model_Order
{
    /**
     * Method to hold the order whe the guaranty is declined and magento config is set to cancel the order
     * @param Mage_Sales_Model_Order $order
     * @param $reason
     * @return bool
     */
    public function cancelOrder(Mage_Sales_Model_Order $order, $reason)
    {
        Mage::log("In cancelOrder ***",7,"split_orders.log",true);
        $isCanceled = parent::cancelOrder($order, $reason);
        if($isCanceled){
            //patch allure-signifyd
            if(Mage::helper("core")->isModuleEnabled("Allure_Orders")){
                $storeId = $order->getStoreId();
                $ordHelper = Mage::helper("allure_orders");
                $isChangeCancelOrderStatus = $ordHelper->isOrderCancelStatusEnabled($storeId);
                if($isChangeCancelOrderStatus){
                    $cancelStatus = $ordHelper->getCancelOrderStatus($storeId);
                    if($cancelStatus){
                        $order->setStatus($cancelStatus);
                        $order->save();
                    }
                }
            }
        }
        return $isCanceled;
    }

    

    /**
     * Method to generate the invoice after a order was placed
     * @param $order
     * @return bool
     */
    public function generateInvoice(Mage_Sales_Model_Order $order)
    {
        Mage::log("In generateInvoice ***",7,"split_orders.log",true);
        $isInvoice = parent::generateInvoice($order);
        if($isInvoice){
            if ($order->hasInvoices()) {
                Mage::getModel("allure_orders/splitOrder")->orderSplitProcess(array($order->getId()));
            }
        }
        return $isInvoice;
    }
}