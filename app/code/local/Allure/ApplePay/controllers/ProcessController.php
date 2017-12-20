<?php
/**
 * Apple Pay
 *
 * @category    Allure
 * @package     Allure_ApplePay
 * @copyright   Copyright (c) 2017 Allure Inc
 * @license     http://opensource.org/licenses/Apache-2.0  Apache License, Version 2.0
 */

class Allure_ApplePay_ProcessController extends Mage_Core_Controller_Front_Action
{

    /**
      * default action
      *
      * @access public
      * @return void
      */
    public function indexAction()
    {
    }
    
    public function validateMerchantAction() {
        $validationUrl="https://apple-pay-gateway-cert.apple.com/paymentservices/startSession";
        
        $pemPwd = '';
        $displayName    = "Venus By Maria Tash";
        $domainName     = $_SERVER['HTTP_HOST'];
        $merchantId     = 'merchant.com.mariatash.authorizenet';
        
        $payload = array (
                "merchantIdentifier" => $merchantId,
                "domainName"    => $domainName,
                "displayName"   => $displayName
        );
        
        // JSON Payload
        //$validationPayload = '{"merchantIdentifier": "merchant.com.mariatash.authorizenet","domainName": "www.venusbymariatash.com","displayName":"Venus By Maria Tash"}';
        
        $validationPayload = json_encode($payload);
        
        try{	//setting the curl parameters.
            $ch = curl_init();
            if (FALSE === $ch)
                throw new Exception('failed to initialize');
                curl_setopt($ch, CURLOPT_URL, $validationUrl);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $validationPayload);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
                // The following two curl SSL options are set to "false" for ease of development/debug purposes only.
                // Any code used in production should either remove these lines or set them to the appropriate
                // values to properly use secure connections for PCI-DSS compliance.
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);	//for production, set value to true or 1
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);	//for production, set value to 2
                curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
                curl_setopt($ch, CURLOPT_SSLCERT, dirname(__FILE__).'/../certs/Identity_VenusByMariaTash.pem');
                curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $pemPwd);
                curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
                $content = curl_exec($ch);
                
                if (FALSE === $content)
                {
                    print_r(curl_error($ch));
                    throw new Exception(curl_error($ch), curl_errno($ch));
                }
                curl_close($ch);
                print_r($content);
                // $content is the Apple Response, it should be a merchant session object
                // but may need to do some manipulation here
                
        } catch (Exception $e) {
            trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
        }
    }
    
    public function saveTransactionAction() {
        
        $data = $_POST;
        
        Mage::log(json_encode($data), Zend_Log::DEBUG, 'applepay.log', true);
        
        die('DONE');
    }
}
