<?php

class Allure_GoogleConnect_IndexController extends Mage_Core_Controller_Front_Action
{
    protected $referer = null;

    public function connectAction()
    {
        if(!($this->referer = $this->getRequest()->getParam('state')))
            $this->referer = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

        try {
            $this->_connectCallback();
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }

        $this->_redirectUrl(urldecode($this->referer));
    }

    public function disconnectAction()
    {
        $this->referer = Mage::getUrl('googleconnect/account');

        $customer = Mage::getSingleton('customer/session')->getCustomer();

        try {
            $this->_disconnectCallback($customer);
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }

        $this->_redirectUrl($this->referer);
    }

    protected function _disconnectCallback(Mage_Customer_Model_Customer $customer) {
        Mage::helper('allure_googleconnect')->disconnect($customer);

        Mage::getSingleton('core/session')
            ->addSuccess(
                $this->__('You have successfully disconnected your Google account
                    from our store account.')
            );
    }

    protected function _connectCallback() {
        if(!($errorCode = $this->getRequest()->getParam('error')) &&
            !($code = $this->getRequest()->getParam('code'))) {
            // Direct route access - deny
            return;
        }

        if($errorCode) {
            // Google API read light - abort
            if($errorCode === 'access_denied') {
                Mage::getSingleton('core/session')
                    ->addNotice(
                        $this->__('Google Connect process aborted.')
                    );

                return;
            }

            throw new Exception(
                sprintf(
                    $this->__('Sorry, "%s" error occured. Please try again.'),
                    $errorCode
                )
            );

            return;
        }

        if ($code) {
            // Google API green light - proceed
            $model = Mage::getSingleton('allure_googleconnect/client');
            $client = $model->getClient();
            $oauth2 = $model->getOauth2();

            $client->authenticate();
            $token = $client->getAccessToken();
            $userInfo = $oauth2->userinfo->get();

            $customersByGoogleId = Mage::helper('allure_googleconnect')
                ->getCustomersByGoogleId($userInfo['id']);

            if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                // Logged in user
                if($customersByGoogleId->count()) {
                    // Google account already connected to other account - deny
                    Mage::getSingleton('core/session')
                        ->addNotice(
                            $this->__('Your Google account is already connected
                                to one of our store accounts.')
                        );

                    return;
                }

                // Connect from account dashboard - attach
                $customer = Mage::getSingleton('customer/session')->getCustomer();

                Mage::helper('allure_googleconnect')->connectByGoogleId(
                    $customer,
                    $userInfo['id'],
                    $token
                );

                Mage::getSingleton('core/session')->addSuccess(
                    $this->__('Your Google account is now connected to your
                        store accout. You can now login using our Google Connect
                        button or using store account credentials you will
                        receive to your email address.')
                );

                return;
            }

            if($customersByGoogleId->count()) {
                // Existing connected user - login
                $customer = $customersByGoogleId->getFirstItem();

                Mage::helper('allure_googleconnect')->loginByCustomer($customer);

               try {
                   $cust=Mage::getModel('customer/customer')->load($customer->getId());
                   $cust->setGoogleLoginCount($cust->getGoogleLoginCount()+1);
                   $cust->save();
               } catch (Exception $e) {
               }
                
                Mage::getSingleton('core/session')
                    ->addSuccess(
                        $this->__('You have successfully logged in using your
                            Google account.')
                    );

                return;
            }

            $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId(Mage::app()->getWebsite()->getId())
                    ->loadByEmail($userInfo['email']);

            if($customer->getId())  {
                try {
                    $cust=Mage::getModel('customer/customer')->load($customer->getId());
                    $cust->setGoogleLoginCount($cust->getGoogleLoginCount()+1);
                    $cust->save();
                } catch (Exception $e) {
                }
                
                // Email account already exists - attach, login
                Mage::helper('allure_googleconnect')->connectByGoogleId(
                    $customer,
                    $userInfo['id'],
                    $token
                );

                Mage::getSingleton('core/session')->addSuccess(
                    $this->__('We have discovered you already have an account at
                        our store. Your Google account is now connected to your
                        store account.')
                );

                return;
            }

            // New connection - create, attach, login
            if(empty($userInfo['given_name'])) {
                throw new Exception(
                    $this->__('Sorry, could not retrieve your Google first name.
                        Please try again.')
                );
            }

            if(empty($userInfo['family_name'])) {
                throw new Exception(
                    $this->__('Sorry, could not retrieve your Google last name.
                        Please try again.')
                );
            }

            Mage::helper('allure_googleconnect')->connectByCreatingAccount(
                $customer,
                $userInfo['email'],
                $userInfo['given_name'],
                $userInfo['family_name'],
                $userInfo['id'],
                $token
            );

            Mage::getSingleton('core/session')->addSuccess(
                $this->__('Your Google account is now connected to your new user
                    accout at our store. Now you can login using our Google Connect
                    button or using store account credentials you will receive to
                    your email address.')
            );
        }
    }

}
