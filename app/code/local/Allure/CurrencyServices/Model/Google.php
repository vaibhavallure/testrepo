<?php
class Allure_CurrencyServices_Model_Google extends Mage_Directory_Model_Currency_Import_Abstract
{
    protected $_url = "http://www.google.com/finance/converter?a=1&from={{CURRENCY_FROM}}&to={{CURRENCY_TO}}";
    protected $_new_url = "https://www.google.com/finance/info?q=CURRENCY:{{CURRENCY_FROM}}{{CURRENCY_TO}}";
    protected $_messages = array();

    protected function _newConvert($currencyFrom, $currencyTo, $retry=0) {
        $url = str_replace('{{CURRENCY_FROM}}', $currencyFrom, $this->_url);
        $url = str_replace('{{CURRENCY_TO}}', $currencyTo, $url);

        try {
            sleep(1);
            $handle = fopen($url, "r");
            $exchange_data = fread($handle, 2000);
            fclose($handle);
            
            $exchange_data = str_replace('// ','', $exchange_data);
            
            $exchangeDataObject = json_decode($exchange_data,true);
            
            $exchangeData = $exchangeDataObject[0];
            
            $exchange_rate = $exchangeData['l'];

            if (!$exchange_rate) {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s', $url);
                return null;
            }

            return (float) $exchange_rate * 1.0;
        } catch (Exception $e) {
            if ($retry == 0) {
                $this->_convert($currencyFrom, $currencyTo, 1);
            } else {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s', $url);
            }
        }
    }
    
    protected function _convert($currencyFrom, $currencyTo, $retry=0) {
        $url = str_replace('{{CURRENCY_FROM}}', $currencyFrom, $this->_old_url);
        $url = str_replace('{{CURRENCY_TO}}', $currencyTo, $url);
    
        try {
            $ch = curl_init();
            $timeout = 5;
    
            curl_setopt ($ch, CURLOPT_URL, $url);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
            curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    
            $rawdata = curl_exec($ch);
            curl_close($ch);
            $data = explode('bld>', $rawdata);
            $data = explode($currencyTo, $data[1]);
    
            $exchange_rate = $data[0];
    
            if (!$exchange_rate) {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s', $url);
                return null;
            }
    
            return (float) $exchange_rate * 1.0;
        } catch (Exception $e) {
            if ($retry == 0) {
                $this->_convert($currencyFrom, $currencyTo, 1);
            } else {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s', $url);
            }
        }
    }
}