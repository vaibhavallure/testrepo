<?php

class OpsWay_CheckoutTiming_Model_Observer
{

    protected $isEnabled;
    protected $loggingModel;
    public $events = array(
        'controller_action_predispatch_checkout_onepage_index',
        'controller_action_predispatch_checkout_onepage_saveMethod',
        'controller_action_predispatch_checkout_onepage_saveBilling',
        'controller_action_predispatch_checkout_onepage_saveShipping',
        'controller_action_predispatch_checkout_onepage_saveShippingMethod',
        'controller_action_predispatch_checkout_onepage_savePayment',
        'controller_action_predispatch_checkout_onepage_saveOrder'
    );


    public function __construct()
    {
        $this->isEnabled = (bool)Mage::getStoreConfig('opsway_checkout_timing/settings/enabled');
        if (!$this->isEnabled) {
            return;
        }

        $this->loggingModel = Mage::getModel('OpsWay_CheckoutTiming_Model_Logging');
        $this->loggingModel->prepareTiming($this->events);
    }


    public function onCheckoutEvent(Varien_Event_Observer $observer)
    {
        if (!$this->isEnabled) {
            return;
        }
        $session = Mage::getSingleton('core/session');
        $step = array_search($observer->event->name, $this->events);
        if ($step === false) {
            return;
        }

        $timing = $session->getCheckoutTiming();
        if ($step == 0) {
            $timing = array();
        }
        $timing[$step] = array(
            'event' => $observer->event->name,
            'time' => microtime(true)
        );
        $session->setCheckoutTiming($timing);
        if ($step == count($this->events) - 1) {
            $this->loggingModel->log($timing);
        }
    }

}