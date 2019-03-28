<?php

class Teamwork_CEGiftcards_Model_Order_Invoice_Total extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
	    // filter applied GC's by quote id. Not by order id, because order id does not exist when invoice is made during checkout (i.g., authorize.net payment with 'authorize and capture' option is used)
        $appliedGCs = Mage::getModel('teamwork_cegiftcards/giftcard_link')->getCollection()->addQuoteFilter($invoice->getOrder()->getQuoteId());
        $gt = $invoice->getGrandTotal();
        $bgt = $invoice->getBaseGrandTotal();
       // $willBeUsed = 0;
       // $willBeUsedBase = 0;
        foreach($appliedGCs as $appliedGC) {
            $invoiceLinkCollection = false;
            $invoiceLinkObject = false;
            if ($gt) {
                //get already used amount
                $alreadyUsed = 0;
                $invoiceLinkCollection = Mage::getModel('teamwork_cegiftcards/order_invoice_link')->getCollection()->addGCLinkFilter($appliedGC);
                foreach ($invoiceLinkCollection as $record){
                    $alreadyUsed += $record->getData('amount_used');
                }
                $mayApply = $appliedGC->getData('amount') - $alreadyUsed;
                if ($mayApply) {
                    if ($mayApply < $gt) {
                        $gt -= $mayApply;
                    } else {
                        $mayApply = $gt;
                        $gt = 0;
                    }
                    /*$appliedGC->setData('amount_used', $appliedGC->getData('amount_used') + $mayApply);
                    if (!$invoice->hasData('__teamwork_cegiftcards_gc_link')) {
                        $invoice->setData('__teamwork_cegiftcards_gc_link', $appliedGCs);
                    }
                    $willBeUsed += $mayApply;*/
                    $invoiceLinkObject = Mage::getModel('teamwork_cegiftcards/order_invoice_link');
                    $invoiceLinkObject->setData('gc_link_id', $appliedGC->getId());
                    $invoiceLinkObject->setData('amount_used', $mayApply);
                }
            }

            if ($bgt) {
                //get already used amount
                $alreadyUsed = 0;
                if ($invoiceLinkCollection === false) {
                    $invoiceLinkCollection = Mage::getModel('teamwork_cegiftcards/order_invoice_link')->getCollection()->addGCLinkFilter($appliedGC);
                }
                foreach ($invoiceLinkCollection as $record){
                    $alreadyUsed += $record->getData('base_amount_used');
                }
                $mayApplyBase = $appliedGC->getData('base_amount') - $alreadyUsed;
                if ($mayApplyBase) {
                    if ($mayApplyBase < $bgt) {
                        $bgt -= $mayApplyBase;
                    } else {
                        $mayApplyBase = $bgt;
                        $bgt = 0;
                    }
                    /*$appliedGC->setData('base_amount_used', $appliedGC->getData('base_amount_used') + $mayApplyBase);
                    if (!$invoice->hasData('__teamwork_cegiftcards_gc_link')) {
                        $invoice->setData('__teamwork_cegiftcards_gc_link', $appliedGCs);
                    }
                    $willBeUsedBase += $mayApplyBase;*/
                    if ($invoiceLinkObject === false) {
                        $invoiceLinkObject = Mage::getModel('teamwork_cegiftcards/order_invoice_link');
                        $invoiceLinkObject->setData('gc_link_id', $appliedGC->getId());
                    }
                    $invoiceLinkObject->setData('base_amount_used', $mayApplyBase);
                }
            }
            if ($invoiceLinkObject !== false) {
                if (!$invoice->hasData('__teamwork_cegiftcards_invoice_links')) {
                    $linkedGCs = array();
                } else {
                    $linkedGCs = $invoice->getData('__teamwork_cegiftcards_invoice_links');
                }
                $linkedGCs[] = $invoiceLinkObject;
                $invoice->setData('__teamwork_cegiftcards_invoice_links', $linkedGCs);
            }
        }
        $invoice->setGrandTotal($gt);
        $invoice->setBaseGrandTotal($bgt);

        return $this;
    }

}
