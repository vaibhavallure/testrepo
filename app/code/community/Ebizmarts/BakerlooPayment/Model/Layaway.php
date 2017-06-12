<?php

class Ebizmarts_BakerlooPayment_Model_Layaway extends Ebizmarts_BakerlooPayment_Model_Method_Abstract
{
    const CODE = "bakerloo_layaway";

    protected $_code = "bakerloo_layaway";
    protected $_model = "bakerloo_payment/installment";

    /**
     * @return Ebizmarts_BakerlooPayment_Helper_Data
     */
    public function getHelper()
    {
        return Mage::helper('bakerloo_payment');
    }

    public function assignData($data)
    {

        $allPayments = $data->getData('addedPayments');

        $this->getInfoInstance()->setPosPaymentInfo(serialize($allPayments));
        return $this;
    }

    public function processInstallments(Mage_Sales_Model_Order $order, $data)
    {
        //check order payment method is layaway
        $paymentMethod = $order->getPayment()->getMethod();
        if ($paymentMethod != Ebizmarts_BakerlooPayment_Model_Layaway::CODE) {
            Mage::throwException(Mage::helper('bakerloo_payment')->__("This order was not paid with POS Layaway."));
        }

        if (Mage::registry('pos_credit_amount')) {
            Mage::throwException(Mage::helper('bakerloo_payment')->__("Installment with store credit not supported."));
        }

        //if order is already paid in full,
        // - don't take more installments
        // - if returns, make credit note
        $orderState = $order->getState();
        if ($orderState == Mage_Sales_Model_Order::STATE_COMPLETE
            or $orderState == Mage_Sales_Model_Order::STATE_CANCELED
            or $orderState == Mage_Sales_Model_Order::STATE_CLOSED
            or $orderState == Mage_Sales_Model_Order::STATE_HOLDED
        ) {
            Mage::throwException(Mage::helper('bakerloo_payment')->__(sprintf("Order %d can't take installments. Order state is %s", $order->getId(), $orderState)));
        }

        $payments = isset($data['payment']['addedPayments']) ? $data['payment']['addedPayments'] : array();
        $orderPayment = $order->getPayment();

        foreach ($payments as $_payment) {
            $installment = null;

            if ($this->isNewPayment($_payment)) {
                $installment = $this->addInstallment($order, $_payment);
            } else {
                $installment = $this->getInstallmentByPaymentId($_payment['payment_id']);
            }

            if (isset($_payment['refunds']) and !is_null($installment)) {
                $this->addRefunds($order, $_payment, $installment);
            }

            $this->updateOrderTotals($order);
            $orderPayment->setAmountPaid($order->getTotalPaid())
                ->setBaseAmountPaid($order->getBaseTotalPaid())
                ->setAmountRefunded($order->getTotalRefunded())
                ->setAmountOrdered($order->getGrandTotal())
                ->setBaseAmountOrdered($order->getBaseGrandTotal())
                ->save();

            if ($order->getTotalDue() == 0) {
                $this->completePayment($order);
            }
        }

        $orderPayment->getMethodInstance()
            ->getInfoInstance()
            ->setPosPaymentInfo(serialize($payments))
            ->save();

        return $data;
    }

    public function getInstallmentByPaymentId($id)
    {
        return Mage::getModel('bakerloo_payment/installment')->load($id, 'payment_id');
    }

    public function addRefunds(Mage_Sales_Model_Order $order, $data, Ebizmarts_BakerlooPayment_Model_Installment $installment)
    {
        if (!$installment->getId()) {
            return;
        }

        $refunds = $data['refunds'];

        foreach ($refunds as $_refund) {
            if ($_refund['refund_id'] > 0) {
                continue;
            }

            $refundAmount  = (float)$_refund['amountToRefund'];
            $currentRefund = (float)$installment->getAmountRefunded();
            $totalRefund   = $currentRefund + $refundAmount;
            $installmentAmount = (float)$installment->getAmountPaid();

            if (($totalRefund) > $installmentAmount) {
                Mage::throwException(sprintf("Cannot refund installment. Refund amount exceeds amount paid."));
            }

            $installment->setAmountRefunded($totalRefund)
                ->save();

            $payment = Mage::getModel('sales/order_payment')->load($installment->getPaymentId());
            $payment->setAmountRefunded($totalRefund)
                ->save();
        }
    }

    public function isNewPayment($data)
    {
        $id = isset($data['payment_id']) ? $data['payment_id'] : null;
        if (!is_null($id) and $id > 0) {
            return false;
        }

        return true;
    }

    public function hasRefunds(stdClass $data)
    {
        $hasRefunds = false;

        if (isset($data['refunds']) and is_array($data['refunds']) and !empty($data['refunds'])) {
            $hasRefunds = true;
        }

        return $hasRefunds;
    }

    public function addInstallment(Mage_Sales_Model_Order $order, $data)
    {

        $this->checkCanAdd($order);

        $totalDue = $order->getTotalDue();
        $installmentAmount = isset($data['amount']) ? $data['amount'] : 0;

        //magestore credit deduct amount on quote total calculation
        if ($installmentAmount > $totalDue and $data['method'] != 'bakerloo_magestorecredit' and $data['method'] != 'bakerloo_storecredit') {
            Mage::throwException(Mage::helper('bakerloo_payment')->__("Installment amount greater than total due."));
        }

        if ($installmentAmount > 0) {
            $payment = $this->generatePayment($data, $order);
            $order->addPayment($payment)
                ->save();
            $payment->save();

            if ($payment->getId()) {
                $posOrder = Mage::getModel('bakerloo_restful/order')->load($order->getId(), 'order_id');

                $this->updateOrderTotals($order);
                $orderPayment = $order->getPayment();
                $orderPayment->setAmountPaid($order->getTotalPaid())
                    ->save();

                $installment = Mage::getModel($this->_model)
                    ->setOrderId($order->getId())
                    ->setParentId($orderPayment->getId())
                    ->setOrderIncrementId($order->getIncrementId())
                    ->setPosOrderId($posOrder->getId())
                    ->setAmountPaid($payment->getAmountPaid())
                    ->setAmountRefunded(0.0000)
                    ->setPaymentId($payment->getId())
                    ->setCurrency($order->getOrderCurrencyCode()) // all installments will be paid in the same currency as the order
                    ->setPaymentMethod($payment->getMethod())
                    ->save();

                if ($data['method'] == 'bakerloo_magestorecredit' and !Mage::registry('pos_credit_amount')) {
                    $order->setCustomercreditDiscount($order->getCustomercreditDiscount() + $data['amount'])
                        ->setGrandTotal($order->getGrandTotal() - $data['amount'])
                        ->setBaseGrandTotal($order->getBaseGrandTotal() - $data['amount']);
                }

                $data['payment_id'] = (int)$installment->getPaymentId();

                if (isset($data['customer_signature'])) {
                    $data['customer_signature'] = null;
                }
                if (isset($data['customer_signature_type'])) {
                    $data['customer_signature_type'] = null;
                }
                if (isset($data['customer_signature_file'])) {
                    $data['customer_signature_file'] = null;
                }

                $data = serialize($data);

                if ($data) {
                    $installment->setPaymentData($data)->save();
                }
            }
        }

        $installment = isset($installment) ? $installment : null;
        return $installment;
    }

    public function generatePayment($data, $order = null)
    {
        //generate payment with all information in $data

        /* @var $payment Mage_Sales_Model_Order_Payment */
        $payment = Mage::getModel('sales/order_payment')
            ->setMethod($data['method'])
            ->setCCExpMonth($data['cc_exp_month'])
            ->setCCExpYear($data['cc_exp_year'])
            ->setAmountPaid($data['amount'])
            ->setAmountRefunded(0.0000);

        if ($data['method'] == 'bakerloo_magestorecredit' and !Mage::registry('pos_credit_amount')) {
            Mage::getModel('customercredit/transaction')->addTransactionHistory(
                $order->getCustomerId(),
                Magestore_Customercredit_Model_TransactionType::TYPE_CHECK_OUT_BY_CREDIT,
                Mage::helper('customercredit')->__('check out by credit for order #') . $order->getIncrementId(),
                $order->getId(),
                - $data['amount']
            );
            Mage::getModel('customercredit/customercredit')->changeCustomerCredit(- $data['amount'], $order->getCustomerId());
        }

        return $payment;
    }

    public function completePayment(Mage_Sales_Model_Order $order)
    {
        Mage::register('pos_layaway_complete', true);
        $invoice = null;

        $invoiceConfig = (int)$order->getPayment()->getMethodInstance()->getConfigData("invoice");
        $shipmentConfig = (int)$order->getPayment()->getMethodInstance()->getConfigData("ship");

        //if can invoice, invoice
        if ($order->canInvoice() and $invoiceConfig) {
            $order = Mage::getModel('sales/order')->load($order->getId());

            /* @var $invoice Mage_Sales_Model_Order_Invoice */
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE)
                ->setTransactionId(time());

            $order->setTotalPaid(0)
                ->setBaseTotalPaid(0);
            $order->getPayment()
                ->setTotalPaid(0)
                ->setBaseTotalPaid(0)
                ->place();

            $invoice->register();

            //Do no send invoice email
            $invoice->setEmailSent(false);
            $invoice->getOrder()->setCustomerNoteNotify(false);

            $transaction = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder());

            //if can ship, ship
            if ($order->canShip() and $shipmentConfig) {
                $shipment = Mage::getModel('sales/service_order', $invoice->getOrder())
                    ->prepareShipment();
                $shipment->register();

                if ($shipment) {
                    $shipment->setEmailSent($invoice->getEmailSent());
                    $transaction->addObject($shipment);
                }
            }
            $transaction->save();
        }

        Mage::unregister('pos_layaway_complete');
        return $invoice;
    }

    public function updateOrderTotals(Mage_Sales_Model_Order $order)
    {
        $totalPaid = 0;
        $totalRefunded = 0;
        $payments = Mage::getModel('bakerloo_payment/installment')
            ->getCollection()
            ->addFieldToFilter('payment_method', array('neq' => 'bakerloo_magestorecredit')) //Magestore Storecredit sets amount paid as discount
            ->addFieldToFilter('payment_method', array('neq' => 'bakerloo_storecredit'))
            ->addFieldToFilter('order_id', array('eq' => $order->getId()));

        foreach ($payments as $payment) {
            $totalPaid += (float)$payment->getAmountPaid();
            $totalRefunded += (float)$payment->getAmountRefunded();
        }

        $amount = $totalPaid - $totalRefunded;
        $baseAmount = $this->getHelper()->convertToBaseCurrency($amount, $order->getBaseCurrency(), $order->getOrderCurrency());

        $order->setTotalPaid($amount)
            ->setBaseTotalPaid($baseAmount)
            ->setTotalRefunded(0)
            ->save();
    }

    public function checkCanAdd(Mage_Sales_Model_Order $order)
    {
        $orderState = $order->getState();
        if ($orderState == Mage_Sales_Model_Order::STATE_COMPLETE
            or $orderState == Mage_Sales_Model_Order::STATE_CANCELED
            or $orderState == Mage_Sales_Model_Order::STATE_CLOSED
            or $orderState == Mage_Sales_Model_Order::STATE_HOLDED
        ) {
            return false;
        }

        if ($order->getTotalDue() == 0) {
            return false;
        }

        return true;
    }

    protected function _getTotalPaid(Mage_Sales_Model_Order $order)
    {

        $payments = $order->getPaymentsCollection();

        $totalPaid = 0;

        foreach ($payments as $payment) {
            if ($payment->getMethod() !== $this->_code and !$payment->isDeleted())
                $totalPaid += $payment->getAmountPaid();
        }

        return $totalPaid;
    }

    protected function _getTotalInvoiced(Mage_Sales_Model_Order $order)
    {
        $totalInvoiced = 0;
        $invoices = $order->getInvoiceCollection();

        foreach ($invoices as $invoice) {
            /* @var $invoice Mage_Sales_Model_Order_Invoice */
            $totalInvoiced += $invoice->getGrandTotal();
        }

        return $totalInvoiced;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Core_Model_Abstract|Mage_Sales_Model_Order
     *
     * In v1, if an order is cancelled, all payments should be cancelled and refunded.
     */
    public function orderCancelled(Mage_Sales_Model_Order $order)
    {
        $totalPaid = $this->_getTotalPaid($order);
        $totalInvoiced = $this->_getTotalInvoiced($order);

        if ($totalPaid > $totalInvoiced) {
            Mage::getSingleton('adminhtml/session')->addWarning(
                Mage::helper('bakerloo_payment')->__("Some installments have not been invoiced yet.")
            );
        }

        return $order;
    }

    public function getInstallments(Mage_Sales_Model_Order $order)
    {
        return Mage::getModel($this->_model)->getCollection()
            ->addFieldToFilter('order_id', array('eq' => $order->getId()))
            ->getItems();
    }
}
