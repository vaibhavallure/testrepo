<?php

class Allure_Customer_AccountController extends Mage_Core_Controller_Front_Action
{
	public function forgotPasswordPostAction()
	{
		$result = [
			'success' 	=> false,
			'msg'		=> 'Unknown'
		];

		if (!$this->getRequest()->getParam('form_key') && $this->getRequest()->getParam('request')) {
		    $this->getRequest()->setParam('form_key', $this->getRequest()->getParam('request')['form_key']);
  		}

		if ($this->getRequest()->getParam('request') && $this->_validateFormKey()) {

			$request = $this->getRequest()->getParam('request');
			$email = $request['email'];

            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);
            if ($customer->getId()) {
                try {
                    $newResetPasswordLinkToken =  Mage::helper('customer')->generateResetPasswordLinkToken();
                    $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                    $customer->sendPasswordResetConfirmationEmail();
                    $result['success'] = true;
                    $result['msg'] = Mage::helper('core')->__('Reset link sent on your email');
                } catch (Exception $exception) {
                    Mage::log($exception);
                    $result['success'] = flase;
                    $result['msg'] = Mage::helper('core')->__($exception->getMessage());
                }
            }else{
                $result['success'] = flase;
                $result['msg'] = Mage::helper('core')->__('Invalid Email Address');

            }
		} else {

            $result['success'] = flase;
            $result['msg'] = Mage::helper('core')->__('Please enter your email.');
		}

		$this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}

	public function ajaxLoginAction()
	{
		$result = [
			'success' 	=> false,
			'msg'		=> 'Unknown'
		];

		$session = Mage::getSingleton('customer/session');

		if ($session->isLoggedIn()) {
			// is already login redirect to account page
			return;
		}

		if (!$this->getRequest()->getParam('form_key') && $this->getRequest()->getParam('request')) {
			$this->getRequest()->setParam('form_key', $this->getRequest()->getParam('request')['form_key']);
		}

		if ($this->getRequest()->getParam('request') && $this->_validateFormKey()) {
			$request = $this->getRequest()->getParam('request');

			Mage::log(($request),Zend_log::DEBUG,'notifications',true);
			if (empty($request['usrname']) || empty($request['passwd'])) {
				$result['error'] = Mage::helper('core')->__('Login and password are required.');
			} else {
				try
				{
					$session->login($request['usrname'], $request['passwd']);
					$result['success'] = true;
					$result['msg'] = Mage::helper('core')->__('Login Successfull');
                    if(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)==$_SERVER['HTTP_REFERER']){
                        Mage::log(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB),Zend_Log::DEBUG,'demo.log',true);
                        $result['redirect'] = true;
                        $result['url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'customer/account';
                    }

				} catch (Mage_Core_Exception $e) {
					switch ($e->getCode()) {
						case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
							$message = Mage::helper('core')->__('Email is not confirmed. <a href="%s">Resend confirmation email.</a>', Mage::helper('customer')->getEmailConfirmationUrl($request['usrname']));
							break;
						default:
							$message = $e->getMessage();
					}
					$result['error'] = $message;
					$session->setUsername($request['usrname']);
				}
			}
		}

		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}

	public function createCustomerAction()
	{
		$result = [
			'success' 	=> false,
			'msg'		=> 'Unknown'
		];

		if (!$this->getRequest()->getParam('form_key') && $this->getRequest()->getParam('request')) {
		    $this->getRequest()->setParam('form_key', $this->getRequest()->getParam('request')['form_key']);
  		}

		if ($this->getRequest()->getParam('request') && $this->_validateFormKey()) {
			$websiteId = Mage::app()->getWebsite()->getId();
			$store = Mage::app()->getStore();

			$request = $this->getRequest()->getParam('request');
			//$request=json_decode($request);
			//$request=explode('&', $request);
			Mage::log($request,Zend_log::DEBUG,'notifications',true);
			$customer_email = $request['email'];
			$customer_fname = $request['firstname'];
			$customer_lname = $request['lastname'];

			//$passwordLength = 10; // the lenght of autogenerated password
			$password = $request['password'];

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
				$isSubscribe=$request['is_subscribed'];
				//$customer->setPassword($customer->generatePassword($passwordLength));
				$customer->setPassword($password);

				if ($isSubscribe=='true')
					$customer->setIsSubscribed(1);

				try {
					//the save the data and send the new account email.
					$customer->save();
					$customer->setPasswordCreatedAt(time());

					$customer->setConfirmation(null);
					$customer->save();
					$customer->sendNewAccountEmail();
					Mage::getSingleton('customer/session')->loginById($customer->getId());
					$result['success'] = true;
					$result['msg'] = Mage::helper('core')->__('Account created Successfully');
				} catch(Exception $ex) {
					Mage::log(" Customer create when new customer book appointment :".$ex,Zend_Log::DEBUG,'appointments',true);
					//Mage::log($e->getMessage());
					//print_r($e->getMessage());
					$result['error'] = $ex;
				}
			} else {
				$result['success'] = false;
				$result['error'] = Mage::helper('core')->__('Cutomer with this email already exits.');

			}
		}

		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}

	public function deletemyaccountAction()
	{
		$result = [
			'success' 	=> false,
			'msg'		=> 'Unknown'
		];

		if (!$this->getRequest()->getParam('form_key') && $this->getRequest()->getParam('request')) {
		    $this->getRequest()->setParam('form_key', $this->getRequest()->getParam('request')['form_key']);
  		}

		if ($this->getRequest()->getParam('request') && $this->_validateFormKey()) {

	        $request = $this->getRequest()->getParam('request');

	        if (!empty($request['email']) && !empty($request['id'])) {
	            $customer = Mage::getModel('customer/customer');
	            $customer->loadByEmail($request['email']);

	            if ($customer->getId()) {
	                $customer->setEmail('del-'.$request['email']);
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

		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
}
