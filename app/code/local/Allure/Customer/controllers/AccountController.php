<?php

class Allure_Customer_AccountController extends Mage_Core_Controller_Front_Action
{
	public function forgotPasswordPostAction()
	{
		$result = [
			'success' 	=> false,
			'msg'		=> 'Unknown'
		];

		Mage::log('forgotPassword:: '.$this->getRequest()->getParam('email'), Zend_log::DEBUG, 'univeral.log', true);

		if ($this->getRequest()->getParam('email') && $this->_validateFormKey()) {

			$email = $this->getRequest()->getParam('email');

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

		Mage::log('ajaxLogin:: '.$this->getRequest()->getParam('usrname'), Zend_log::DEBUG, 'univeral.log', true);

		$session = Mage::getSingleton('customer/session');

		if ($session->isLoggedIn()) {
			$result['msg'] = 'Already Logged In.';
		} else if ($this->getRequest()->getParam('usrname') && $this->_validateFormKey()) {
			$request = $this->getRequest()->getParams();

			$username = $this->getRequest()->getParam('usrname');
			$password = $this->getRequest()->getParam('passwd');

			if (empty($username) || empty($password)) {
				$result['error'] = Mage::helper('core')->__('Login and password are required.');
			} else {
				try
				{
					$session->login($username, $password);
					$result['success'] = true;
					$result['msg'] = Mage::helper('core')->__('Login Successfull');

                    $result['redirect'] = true;
                    $result['url'] = $this->_redirectReferer();

				} catch (Mage_Core_Exception $e) {
					switch ($e->getCode()) {
						case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
							$message = Mage::helper('core')->__('Email is not confirmed. <a href="%s">Resend confirmation email.</a>', Mage::helper('customer')->getEmailConfirmationUrl($username));
							break;
						default:
							$message = $e->getMessage();
					}

					$result['error'] = $message;
					//$session->setUsername($username);
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

		Mage::log('createCustomer:: '.$this->getRequest()->getParam('email'), Zend_log::DEBUG, 'univeral.log', true);

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

		Mage::log('deletemyaccount:: '.$this->getRequest()->getParam('email'), Zend_log::DEBUG, 'univeral.log', true);

		if ($this->getRequest()->getParam('email') && $this->_validateFormKey()) {

	        $customerId = $this->getRequest()->getParam('id');
			$email = $this->getRequest()->getParam('email');

	        if (!empty($email) && !empty($customerId)) {
	            $customer = Mage::getModel('customer/customer');
	            $customer->loadByEmail($email);

	            if ($customer->getId()) {
	                $customer->setEmail('del-'.$email);
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
