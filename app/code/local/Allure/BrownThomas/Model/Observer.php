<?php
/**
 * Created by allure.
 * User: adityagatare
 * Date: 13/11/18
 * Time: 11:17 PM
 */

class Allure_BrownThomas_Model_Observer {
    public function checkPrice($observer) {
        $product = $observer->getEvent()->getProduct();

    }
}