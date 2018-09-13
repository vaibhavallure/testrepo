<?php

class Allure_GoogleConnect_Model_Userinfo
{
    protected $userInfo = null;

    public function __construct() {
        $model = Mage::getSingleton('allure_googleconnect/client');
        $client = $model->getClient();
        $oauth2 = $model->getOauth2();
        
        if(!Mage::getSingleton('customer/session')->isLoggedIn())
            return;
        
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $googleconnectId = $customer->getAllureGoogleconnectId();
        $googleconnectToken = $customer->getAllureGoogleconnectToken();
        if($googleconnectId && $googleconnectToken) {
            $helper = Mage::helper('allure_googleconnect');

            try{
                $client->setAccessToken($googleconnectToken);
                
                $this->userInfo = $oauth2->userinfo->get();

                /* The access token may have been updated automatically due to 
                 * access type 'offline' */
                $customer->setAllureGoogleconnectToken($client->getAccessToken());
                $customer->save();           

            } catch(Google_ServiceException $e) {
                // User revoked our credentials
                $helper->disconnect($customer);
                Mage::getSingleton('core/session')
                    ->addNotice(
                        $helper->__('Permission to access your Google account 
                        has been revoked. You can restore permissions by 
                        connecting your Google account again.')
                    );
            } catch(Google_AuthException $e) {
                /* Token expired (shouldn't happen due to access type 'offline',
                 * google client refreshes token automatically) */
                $helper->disconnect($customer);
                Mage::getSingleton('core/session')->addError($e->getMessage());
            } catch(Exception $e) {
                // General exception
                $helper->disconnect($customer);
                Mage::getSingleton('core/session')->addError($e->getMessage());
            }
            
        }
    }

    public function getUserInfo()
    {
        return $this->userInfo;
    }
}
