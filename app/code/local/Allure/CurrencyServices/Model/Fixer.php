<?php
class Allure_CurrencyServices_Model_Fixer extends Mage_Directory_Model_Currency_Import_Abstract
{
    protected $_url = "https://api.fixer.io/latest?base={{CURRENCY_FROM}}&symbols={{CURRENCY_TO}}";
    protected $_messages = array();

    protected function _convert($currencyFrom, $currencyTo, $retry = 0) {
        $url = str_replace('{{CURRENCY_FROM}}', $currencyFrom, $this->_url);
        $url = str_replace('{{CURRENCY_TO}}', $currencyTo, $url);

        try {
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $exchange_data = curl_exec($ch);
            curl_close($ch);
            
            $exchangeDataObject = json_decode($exchange_data,true);
            
            $exchangeData = $exchangeDataObject['rates'];
            
            if (count($exchangeData)) {
                $exchange_rate = $exchangeData[$currencyFrom.$currencyTo];
            }

            if (!$exchange_rate) {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s', $url);
                return null;
            }

            return (float) $exchange_rate * 1.0;
        } catch (Exception $e) {
            if ($retry == 0) {
                return (float) $this->_convert($currencyFrom, $currencyTo, 1);
            } else {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s', $url);
            }
        }
    }
}