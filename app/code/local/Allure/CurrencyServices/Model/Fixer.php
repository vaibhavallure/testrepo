<?php
class Allure_CurrencyServices_Model_Fixer extends Mage_Directory_Model_Currency_Import_Abstract
{
    protected $_url = "https://api.fixer.io/latest?base={{CURRENCY_FROM}}&symbols={{CURRENCY_TO}}";
    protected $_messages = array();

    protected function _convert($currencyFrom, $currencyTo, $retry = 0) {
        $url = str_replace('{{CURRENCY_FROM}}', $currencyFrom, $this->_url);
        $url = str_replace('{{CURRENCY_TO}}', $currencyTo, $url);

        try {
            sleep(1);
            $handle = fopen($url, "r");
            $exchange_data = fread($handle, 2000);
            fclose($handle);
            
            $exchangeDataObject = json_decode($exchange_data,true);
            
            $exchangeData = $exchangeDataObject['rates'];
            
            $exchange_rate = $exchangeData[$currencyTo];

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