<?php

class Ebizmarts_BakerlooLoyalty_Model_Validator extends Mage_SalesRule_Model_Validator {

    /**
     * {@inheritdoc}
     */
    protected function _canProcessRule($rule, $address)
    {
        if ($address->getDiscardPromotions() and is_null($rule->getPointsAction())) {
            return false;
        }

        return parent::_canProcessRule($rule, $address);
    }
}