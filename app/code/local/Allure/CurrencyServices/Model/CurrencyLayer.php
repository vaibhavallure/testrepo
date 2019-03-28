<?php
class Allure_CurrencyServices_Model_CurrencyLayer extends Mage_Directory_Model_Currency_Import_Abstract
{
    protected $_url = "http://www.apilayer.net/api/live?access_key=363399e90228285463170d369a9f6836";
    protected $_messages = array();

    protected function _convert($currencyFrom, $currencyTo, $retry = 0) {
        $url = $this->_url.'&source='.$currencyFrom;
        $url = $url.'&currencies='.$currencyTo;

        try {
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $exchange_data = curl_exec($ch);
            curl_close($ch);
            
            $exchangeDataObject = json_decode($exchange_data,true);
            
            $exchangeData = $exchangeDataObject['quotes'];
            
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