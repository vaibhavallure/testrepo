<?php

class Allure_Customer_AccountController extends Mage_Core_Controller_Front_Action
{
    public function forgotPasswordPostAction()
    {
        $result = [
            'success' => false,
            'msg' => 'Unknown'
        ];

        Mage::log('forgotPassword:: ' . $this->getRequest()->getParam('email'), Zend_log::DEBUG, 'univeral.log', true);

        if ($this->getRequest()->getParam('email') && $this->_validateFormKey()) {

            $email = $this->getRequest()->getParam('email');

            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);

            if ($customer->getId()) {
                try {

                    $tw_uc_guid = $customer->getTwUcGuid();
                    $tw_customer_id = $customer->getTeamworkCustomerId();
                    if (empty($tw_uc_guid) && !empty($tw_customer_id)) {
                        $customer->setTwUcGuid($tw_customer_id);
                        $customer->save();
                    }

                    $newResetPasswordLinkToken = Mage::helper('customer')->generateResetPasswordLinkToken();
                    $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                    $customer->sendPasswordResetConfirmationEmail();
                    $result['success'] = true;
                    $result['msg'] = Mage::helper('core')->__('Reset link sent on your email');
                } catch (Exception $exception) {
                    Mage::log($exception);
                    $result['success'] = flase;
                    $result['msg'] = Mage::helper('core')->__($exception->getMessage());
                }
            } else {
                $result['success'] = flase;
                $result['msg'] = Mage::helper('core')->__('Invalid Email Address');

            }
        } else {
            $result['success'] = flase;
            $result['msg'] = Mage::helper('core')->__('Please enter your email.');
        }

        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     *
     */
    public function ajaxLoginAction()
    {
        $result = [
            'success' => false,
            'msg' => 'Unknown'
        ];

        $login = $this->getRequest()->getParam('login');

        if (isset($login)) {
            extract($login);
        }

        Mage::log('ajaxLogin:: ' . $username, Zend_log::DEBUG, 'univeral.log', true);

        $session = Mage::getSingleton('customer/session');

        if ($session->isLoggedIn()) {
            $result['msg'] = 'Already Logged In.';
        } else if (isset($username) && $this->_validateFormKey()) {
            $request = $this->getRequest()->getParams();

            if (empty($username) || empty($password)) {
                $result['error'] = Mage::helper('core')->__('Login and password are required.');
            } else {
                try {

                    $customerOb = Mage::getModel('customer/customer')->loadByEmail($username);

                    if (!empty($customerOb)) {
                        $tw_uc_guid = $customerOb->getTwUcGuid();
                        $tw_customer_id = $customerOb->getTeamworkCustomerId();
                        if (empty($tw_uc_guid) && !empty($tw_customer_id)) {
                            $customerOb->setTwUcGuid($tw_customer_id);
                            $customerOb->save();
                        }
                    }

                    $session->login($username, $password);
                    $result['success'] = true;
                    $result['msg'] = Mage::helper('core')->__('Login Successfull');

                    $result['redirect'] = true;
                    $refererUrl = $this->_getRefererUrl();

                    if (empty($refererUrl)) {
                        $refererUrl = empty($defaultUrl) ? Mage::getBaseUrl() : 'customer/account';
                    }

                    $result['url'] = $refererUrl;

                    /*code to redirect checkout after login*/
                    if(!empty($this->getRequest()->getParam('redirectUrl')))
                    {
                        $result['redirectUrl']=$this->getRequest()->getParam('redirectUrl');
                    }

                } catch (Mage_Core_Exception $e) {

                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $message = Mage::helper('core')->__('Email is not confirmed. <a href="%s">Resend confirmation email.</a>', Mage::helper('customer')->getEmailConfirmationUrl($username));
                            break;

                      /*Allure new changes for wholesale portal*/
                        case Mage_Customer_Model_Customer::EXCEPTION_WHOLESALE_LOGIN_FOR_RETAIL:
                            $message=""; /*Invalid User Account For Retail Site*/
                            $result['error_code']=Mage_Customer_Model_Customer::EXCEPTION_WHOLESALE_LOGIN_FOR_RETAIL;
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_RETAIL_LOGIN_FOR_WHOLESALE:
                            $message=""; /*Invalid User Account For Wholesale Site*/
                            $result['error_code']=Mage_Customer_Model_Customer::EXCEPTION_RETAIL_LOGIN_FOR_WHOLESALE;
                            break;
                        /*changes end*/

                        default:
                            $message = $e->getMessage();
                    }

                    $result['error'] = $message;
                    //$session->setUsername($username);
                }
            }
        }

        /*
         * code added to log customer login
         * */
        Mage::helper('customerloginmonitor')->addLoginInfo($result);
        /*end---------------------------------*/


        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function createCustomerAction()
    {
        $result = [
            'success' => false,
            'msg' => 'Unknown'
        ];

        Mage::log('createCustomer:: ' . json_encode($this->getRequest()->getParams()), Zend_log::DEBUG, 'universal.log', true);

        if ($this->getRequest()->getParam('email') && $this->_validateFormKey()) {
            $websiteId = Mage::app()->getWebsite()->getId();
            $store = Mage::app()->getStore();

            $customer_email = $this->getRequest()->getParam('email');
            $customer_fname = $this->getRequest()->getParam('firstname');
            $customer_lname = $this->getRequest()->getParam('lastname');
            $isSubscribed = $this->getRequest()->getParam('is_subscribed');

            //$passwordLength = 10; // the lenght of autogenerated password
            $password = $this->getRequest()->getParam('password');

            $customer = Mage::getModel('customer/customer');
            $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
            $customer->loadByEmail($customer_email);

            //Check if the email exist on the system.If YES,  it will not create a user account.
            if (!$customer->getId()) {

                //setting data such as email, firstname, lastname, and password
                $customer->setEmail($customer_email);
                $customer->setWebsiteId($websiteId);
                $customer->setStore($store);
                $customer->setFirstname($customer_fname);
                $customer->setLastname($customer_lname);
                //$customer->setPassword($customer->generatePassword($passwordLength));
                $customer->setPassword($password);

                if ($isSubscribed == 'true') {
                    $customer->setIsSubscribed(1);
                }

                try {
                    //the save the data and send the new account email.
                    $customer->save();
                    $customer->setPasswordCreatedAt(time());

                    $customer->setConfirmation(null);
                    $customer->save();
                    $customer->sendNewAccountEmail();
                    $customerId = $customer->getId();
                    Mage::getSingleton('customer/session')->loginById($customerId);
                    Mage::getSingleton('customer/session')->logout();
                    Mage::getSingleton('customer/session')->loginById($customerId);
                    $result['success'] = true;
                    $result['msg'] = Mage::helper('core')->__('Account created Successfully');
                } catch (Exception $e) {
                    $result['msg'] = $e->getMessage();
                }
            } else {
                $result['success'] = false;
                $result['msg'] = Mage::helper('core')->__('Cutomer with this email already exits.');
            }
        }

        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function deletemyaccountAction()
    {
        $result = [
            'success' => false,
            'msg' => 'Unknown'
        ];

        Mage::log('deletemyaccount:: ' . $this->getRequest()->getParam('email'), Zend_log::DEBUG, 'univeral.log', true);

        if ($this->getRequest()->getParam('email') && $this->_validateFormKey()) {

            $customerId = $this->getRequest()->getParam('id');
            $email = $this->getRequest()->getParam('email');

            if (!empty($email) && !empty($customerId)) {
                $customer = Mage::getModel('customer/customer');
                $customer->loadByEmail($email);

                if ($customer->getId()) {
                    $customer->setEmail('del-' . $email);
                    $customer->save();
                    Mage::getSingleton('customer/session')->logout();
                    $result['success'] = true;
                    $result['msg'] = Mage::helper('core')->__('Account deleted Sucessfully');
                    Mage::getSingleton("core/session")->addSuccess("Account deleted Sucessfully");
                } else {
                    $result['success'] = false;
                    $result['msg'] = Mage::helper('core')->__('Unable to delete account');
                    Mage::getSingleton("core/session")->addError("Unable to delete account");
                }
            }
        }

        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}
