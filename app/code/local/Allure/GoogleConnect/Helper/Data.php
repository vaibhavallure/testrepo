<?php

class Allure_GoogleConnect_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLED = 'customer/allure_googleconnect/enabled';
    
    public function disconnect(Mage_Customer_Model_Customer $customer) {
        $model = Mage::getSingleton('allure_googleconnect/client');
        $client = $model->getClient();
        
        $client->setAccessToken($customer->getAllureGoogleconnectToken());
        $client->revokeToken();   
        
        $pictureFilename = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA)
                .DS
                .'allure'
                .DS
                .'googleconnect'
                .DS
                .$customer->getAllureGoogleconnectId();
        
        if(file_exists($pictureFilename)) {
            @unlink($pictureFilename);
        }
        
        $customer->setAllureGoogleconnectId(null)
        ->setAllureGoogleconnectToken(null)
        ->save();   
    }
    
    public function connectByGoogleId(
            Mage_Customer_Model_Customer $customer,
            $googleId,
            $token)
    {
        $customer->setAllureGoogleconnectId($googleId)
                ->setAllureGoogleconnectToken($token)
                ->save();
        
        Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
    }
    
    public function connectByCreatingAccount(
            Mage_Customer_Model_Customer $customer,
            $email,
            $firstName,
            $lastName,
            $googleId,
            $token)
    {
            $customer->setEmail($email)
                    ->setFirstname($firstName)
                    ->setLastname($lastName)
                    ->setAllureGoogleconnectId($googleId)
                    ->setAllureGoogleconnectToken($token);
                    //->setPassword($customer->generatePassword(10))
                    //->setPasswordCreatedAt(time())
                    //->save();
            $password = $customer->generatePassword(10);
           /*  $customer->setData('password', $password);
            $customer->setData('password_hash',($customer->hashPassword($password)));
            $customer->setPasswordConfirmation(null);
             */
            $customer->setGoogleLoginCount(1);
            $customer->setCustomerType(16);
            $customer->setPassword($password);
            $customer->setPasswordConfirmation($password);
            $customer->setPasswordCreatedAt(time());
                    
            $customer->setConfirmation(null);
            $customer->save();

            $customer->sendNewAccountEmail();
            
            Mage::getSingleton('customer/session')->setCustomer($customer);
            Mage::getSingleton('customer/session')->renewSession();
            Mage::getSingleton('core/session')->renewFormKey();
            //Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer); 
            //Mage::getSingleton('customer/session')->loginById($customer->getId());
        
    }
    
    public function loginByCustomer(Mage_Customer_Model_Customer $customer)
    {
        if($customer->getConfirmation()) {
            $customer->setConfirmation(null);
            $customer->save();
        }

        Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);        
    }
    
    public function getCustomersByGoogleId($googleId)
    {
        $customer = Mage::getModel('customer/customer');

        $collection = $customer->getCollection()
            ->addAttributeToFilter('allure_googleconnect_id', $googleId)
            ->setPageSize(1);

        if($customer->getSharingConfig()->isWebsiteScope()) {
            $collection->addAttributeToFilter(
                'website_id',
                Mage::app()->getWebsite()->getId()
            );
        }

        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                $collection->addFieldToFilter(
                    'entity_id',
                    array('neq' => Mage::getSingleton('customer/session')->getCustomerId())
                );
            }
        }

        return $collection;
    }
    
    public function getProperDimensionsPictureUrl($googleId, $pictureUrl)
    {
        $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)
                .'allure'
                .'/'
                .'googleconnect'
                .'/'
                .$googleId;

        $filename = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA)
                .DS
                .'allure'
                .DS
                .'googleconnect'
                .DS
                .$googleId;

        $directory = dirname($filename);

        if (!file_exists($directory) || !is_dir($directory)) {
            if (!@mkdir($directory, 0777, true))
                return null;
        }

        if(!file_exists($filename) || 
                (file_exists($filename) && (time() - filemtime($filename) >= 3600))){
            $client = new Zend_Http_Client($pictureUrl);
            $client->setStream();
            $response = $client->request('GET');
            stream_copy_to_stream($response->getStream(), fopen($filename, 'w'));

            $imageObj = new Varien_Image($filename);
            $imageObj->constrainOnly(true);
            $imageObj->keepAspectRatio(true);
            $imageObj->keepFrame(false);
            $imageObj->resize(150, 150);
            $imageObj->save($filename);
        }
        
        return $url;
    }
    
    public function _isEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_ENABLED, Mage::app()->getStore()->getId());
    }
    
}
