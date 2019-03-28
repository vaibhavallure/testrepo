<?php

class Teamwork_CEGiftcards_Model_Giftcard_Link extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resources
     */
    protected function _construct()
    {
        $this->_init('teamwork_cegiftcards/giftcard_link');
    }

    public function getBalance()
    {
        if ($this->hasData('requested_at')) {
            $requestedAt = $this->hasData('requested_at');
        }
        return $this->getData('balance');
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        if ($this->getData('load_transactions')) {
            $transactions = Mage::getResourceCollection('teamwork_cegiftcards/giftcard_transaction_collection')->addGCLinkFilter($this);
            $this->setData('transactions', $transactions);
        }
    }

    protected function _afterSave()
    {
        parent::_afterSave();
        if ($this->hasData('transactions')) {
            $transactions = $this->getData('transactions');
            if (is_array($transactions)) {
                foreach($transactions as $transactionId => $amount) {
                    $object = Mage::getModel('teamwork_cegiftcards/giftcard_transaction');
                    $object->setData('gc_link_id', $this->getId());
                    $object->setData('transaction_id', $transactionId);
                    $object->setData('amount', $amount);
                    $object->save();
                }
            }
        }
    }
}
