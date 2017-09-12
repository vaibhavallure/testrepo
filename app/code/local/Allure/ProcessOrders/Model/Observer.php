<?php
class Allure_ProcessOrders_Model_Observer{
 
    public function runProcess(){
        $orderProcessor = new Allure_ProcessOrders_Model_OrderProcessor('https://www.venusbymariatash.com/api/v2_soap?wsdl=1', 'sureshinde', 'sunevenus');
        $orderProcessor->processOrders();
        
    }
}
