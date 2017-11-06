<?php

/**
 * Order information for print
 *
 * @category   Mage
 * @package    Mage_Sales
 */
class Allure_MultiCheckout_Block_Sales_Order_Print extends Mage_Sales_Block_Items_Abstract
{

    protected function _prepareLayout ()
    {
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->__('Print Order #%s', $this->getOrder()
                ->getRealOrderId()));
        }
        $this->setChild('payment_info', $this->helper('payment')
            ->getInfoBlock($this->getOrder()
            ->getPayment()));
        
        $this->setChild('second_payment_info',
                $this->helper('payment')
                    ->getInfoBlock($this->getSecondOrder()
                    ->getPayment()));
    }

    public function getPaymentInfoHtml ()
    {
        return $this->getChildHtml('payment_info');
    }

    public function getSecondPaymentInfoHtml ()
    {
        return $this->getChildHtml('second_payment_info');
    }

    public function getOrder ()
    {
        return Mage::registry('current_order');
    }

    public function getSecondOrder ()
    {
        return Mage::registry('second_current_order');
    }

    protected function _prepareItem (Mage_Core_Block_Abstract $renderer)
    {
        $renderer->setPrintStatus(true);
        
        return parent::_prepareItem($renderer);
    }
}

