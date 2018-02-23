<?php

class Teamwork_CEGiftcards_Model_Order_Creditmemo_Total extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $appliedGCs = Mage::getModel('teamwork_cegiftcards/giftcard_link')->getCollection()->addOrderFilter($order);
        $gt = $creditmemo->getGrandTotal();
        $bgt = $creditmemo->getBaseGrandTotal();

        $gcAmountWillBeUsed = 0;
        $gcBaseAmountWillBeUsed = 0;

        foreach($appliedGCs as $appliedGC) {
            $invoiceLinkCollection = false;
            $creditmemoriedLinkCollection = false;
            $creditmemoLinkObject = false;
            if ($gt) {

                $invoiced = 0;
                $invoiceLinkCollection = Mage::getModel('teamwork_cegiftcards/order_invoice_link')->getCollection()->addGCLinkFilter($appliedGC);
                foreach ($invoiceLinkCollection as $record){
                    $invoiced += $record->getData('amount_used');
                }

                $returned = 0;
                $creditmemoriedLinkCollection = Mage::getModel('teamwork_cegiftcards/order_creditmemo_link')->getCollection()->addGCLinkFilter($appliedGC);
                foreach ($creditmemoriedLinkCollection as $record){
                    $returned += $record->getData('amount_used');
                }

                $mayReturnToGC = $invoiced - $returned;

                if ($mayReturnToGC) {
                    if ($mayReturnToGC < $gt) {
                        $gt -= $mayReturnToGC;
                    } else {
                        $mayReturnToGC = $gt;
                        $gt = 0;
                    }

                    $creditmemoLinkObject = Mage::getModel('teamwork_cegiftcards/order_creditmemo_link');
                    $creditmemoLinkObject->setData('gc_link_id', $appliedGC->getId());
                    $creditmemoLinkObject->setData('amount_used', $mayReturnToGC);

                    $gcAmountWillBeUsed += $mayReturnToGC;
                }
            }

            if ($bgt) {

                $invoiced = 0;
                if ($invoiceLinkCollection === false) {
                    $invoiceLinkCollection = Mage::getModel('teamwork_cegiftcards/order_invoice_link')->getCollection()->addGCLinkFilter($appliedGC);
                }
                foreach ($invoiceLinkCollection as $record){
                    $invoiced += $record->getData('base_amount_used');
                }

                $returned = 0;
                if ($creditmemoriedLinkCollection === false) {
                    $creditmemoriedLinkCollection = Mage::getModel('teamwork_cegiftcards/order_creditmemo_link')->getCollection()->addGCLinkFilter($appliedGC);
                }
                foreach ($creditmemoriedLinkCollection as $record){
                    $returned += $record->getData('base_amount_used');
                }

                $mayReturnToGCBase = $invoiced - $returned;

                if ($mayReturnToGCBase) {
                    if ($mayReturnToGCBase < $bgt) {
                        $bgt -= $mayReturnToGCBase;
                    } else {
                        $mayReturnToGCBase = $bgt;
                        $bgt = 0;
                    }

                    if ($creditmemoLinkObject === false) {
                        $creditmemoLinkObject = Mage::getModel('teamwork_cegiftcards/order_creditmemo_link');
                        $creditmemoLinkObject->setData('gc_link_id', $appliedGC->getId());
                    }
                    $creditmemoLinkObject->setData('base_amount_used', $mayReturnToGCBase);

                    $gcBaseAmountWillBeUsed += $mayReturnToGCBase;
                }
            }
            if ($creditmemoLinkObject !== false) {
                if (!$creditmemo->hasData('__teamwork_cegiftcards_creditmemo_links')) {
                    $linkedGCs = array();
                } else {
                    $linkedGCs = $creditmemo->getData('__teamwork_cegiftcards_creditmemo_links');
                }
                $linkedGCs[] = $creditmemoLinkObject;
                $creditmemo->setData('__teamwork_cegiftcards_creditmemo_links', $linkedGCs);
            }
        }
        $creditmemo->setGrandTotal($gt);
        $creditmemo->setBaseGrandTotal($bgt);

        if ((!$gt && $gcAmountWillBeUsed) || (!$bgt && $gcBaseAmountWillBeUsed)) {
            $creditmemo->setAllowZeroGrandTotal(true);
        }

        return $this;
    }

}
