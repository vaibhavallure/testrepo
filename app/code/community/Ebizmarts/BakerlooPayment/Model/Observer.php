<?php

class Ebizmarts_BakerlooPayment_Model_Observer
{

    public function orderHasLayawayPayment(Varien_Event_Observer $observer)
    {
        $order = $observer->getOrder();
        $data = $observer->getPayload();

        Mage::getModel('bakerloo_payment/layaway')->processInstallments($order, json_decode(json_encode($data), true));
    }


    public function orderCancelAfter(Varien_Event_Observer $observer)
    {

        $order = $observer->getOrder();
        $installments = Mage::getModel('bakerloo_payment/layaway')->getInstallments($order);

        if ($order->getPayment()->getMethod() == Ebizmarts_BakerlooPayment_Model_Layaway::CODE and empty($installments)) {
            Mage::getModel('bakerloo_payment/layaway')->orderCancelled($order);
        }
    }

    public function paymentMethodFieldset($observer)
    {
        $block = $observer->getEvent()->getBlock();

        //Add bakerloo payment information fieldset to customer groups
        if ($block instanceof Mage_Adminhtml_Block_Customer_Group_Edit_Form) {
            $fieldset = $block->getForm()->addFieldset('bakerloo_payment_methods_fieldset', array('legend'=>Mage::helper('customer')->__('POS')));

            $fieldset->addField(
                'bakerloo_payment_methods',
                'multiselect',
                array(
                    'name'     => 'bakerloo_payment_methods',
                    'label'    => Mage::helper('bakerloo_payment')->__('POS Payment Methods'),
                    'title'    => Mage::helper('bakerloo_payment')->__('POS Payment Methods'),
                    'note'     => Mage::helper('bakerloo_payment')->__('All payment methods are enabled by default or if none is selected.'),
                    'required' => false,
                    'values'   => Mage::getSingleton('bakerloo_payment/source_paymentmethods')->getAllOptions(),
                    'value'    => Mage::registry('current_group')->getBakerlooPaymentMethods(),
                )
            );
        }
    }

    public function saveBakerlooData($observer)
    {
        $group = $observer->getEvent()->getObject();

        $postPayment = Mage::app()->getRequest()->getParam('bakerloo_payment_methods');

        if (!is_null($postPayment)) {
            $group->setBakerlooPaymentMethods(implode(',', $postPayment));
        }
    }

    /**
     * Check for invoice status on PayPal's API.
     *
     * @param $observer
     */
    public function payPalInvoiceDetails($observer)
    {
        $order = Mage::registry('current_order');

        if (!is_null($order)) {
            if ('bakerloo_paypalhere' == $order->getPayment()->getMethod()) {
                $payment = $order->getPayment()->getMethodInstance();
                $info    = $payment->getInfoInstance();

                $transactionId = Mage::helper('bakerloo_payment')->getPayPalTxId($info->getPoNumber());

                if ($transactionId != 'Cash') {
                    $transactionDetails = $payment->getTransactionDetails($transactionId);

                    $_transactionDetails = Mage::helper('bakerloo_payment')->processNvpData($transactionDetails);

                    if (is_array($_transactionDetails) && !empty($_transactionDetails)) {
                        if (isset($_transactionDetails['ACK']) && ($_transactionDetails['ACK'] != 'Failure')) {
                            Mage::register('bakerloo_paypalhere_transaction_details', $_transactionDetails);
                        }
                    }
                }
            }
        }
    }

    public function paymentConfigChanged(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('core');

        if (!$helper->isModuleEnabled('Magestore_Customercredit') or !$helper->isModuleOutputEnabled('Magestore_Customercredit')) {
            Mage::getModel('core/config')->saveConfig('payment/bakerloo_magestorecredit/active', 0);
        }
    }

    public function paymentMethodIsActive(Varien_Event_Observer $observer)
    {
        $method = $observer->getEvent()->getMethodInstance();

        if ($method instanceof Ebizmarts_BakerlooPayment_Model_Storecredit) {
            $quote = $observer->getEvent()->getQuote();
            $balance = $quote ? $quote->getCustomerBalanceInstance() : null;

            if ($balance and $balance->isFullAmountCovered($quote)) {
                $quote->setUseCustomerBalance(false);
            }
        }
    }

    /**
     * Observer event sales_order_payment_place_start
     * and set configurations for bakerloo_layaway
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function paymentPlaceStart(Varien_Event_Observer $observer) {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $observer->getPayment();

        if ($payment->getMethod() == Ebizmarts_BakerlooPayment_Model_Layaway::CODE) {
            $complete = Mage::registry('pos_layaway_complete');

            if (!$complete) {
                Mage::app()->getStore($payment->getOrder()->getStoreId())->setConfig('payment/bakerloo_layaway/invoice', 0);
                Mage::app()->getStore($payment->getOrder()->getStoreId())->setConfig('payment/bakerloo_layaway/ship', 0);
            }
        }

        return $this;
    }
}
