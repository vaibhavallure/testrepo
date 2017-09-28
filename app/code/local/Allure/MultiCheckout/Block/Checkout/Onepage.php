<?php

class Allure_MultiCheckout_Block_Checkout_Onepage extends Mage_Checkout_Block_Onepage
{

    public function getSteps ()
    {
        $steps = array();
        if (! $this->isCustomerLoggedIn()) {
            $steps['login'] = $this->getCheckout()->getStepData('login');
        }
        // New Code Adding step deliveryinstructions here
        $stepCodes = array(
                'login',
                'billing',
                'shipping',
                'shipping_method',
                'deliveryinstructions',
                'payment',
                'review'
        );
        foreach ($stepCodes as $step) {
            $steps[$step] = $this->getCheckout()->getStepData($step);
        }
        return $steps;
    }
}
