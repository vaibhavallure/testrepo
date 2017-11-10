<?php
class Allure_CurrencyServices_Model_CurrencyLayer extends Mage_Directory_Model_Currency_Import_Abstract
{
    protected $_url = "http://www.apilayer.net/api/live?access_key=363399e90228285463170d369a9f6836&source={{CURRENCY_FROM}}&currencies={{CURRENCY_TO}}";
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
            
            $exchangeData = $exchangeDataObject['quotes'];
            
            $exchange_rate = $exchangeData[$currencyFrom.$currencyTo];

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