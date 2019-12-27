<?php

require_once 'Mage/Customer/controllers/AccountController.php';

class Ecp_Customer_AccountController extends Mage_Customer_AccountController
{

    public function preDispatch()
    {
        // a brute-force protection here would be nice

        parent::preDispatch();

        if (!$this->getRequest()->isDispatched()) {
            return;
        }

        $action = $this->getRequest()->getActionName();
        $openActions = array(
            'create',
            'login',
            'logoutsuccess',
            'forgotpassword',
            'forgotpasswordpost',
            'resetpassword',
            'resetpasswordpost',
            'changeforgotten',
            'confirm',
            'confirmation'
        );
        $pattern = '/^(' . implode('|', $openActions) . ')/i';

        if (!preg_match($pattern, $action)) {
            if (!$this->_getSession()->authenticate($this)) {
                $this->setFlag('', 'no-dispatch', true);
            }
        } else {
            $this->_getSession()->setNoReferer(true);
        }
    }


    /**
     * Forgot customer password page
     */
    public function forgotPasswordAction()
    {
        $this->loadLayout();

        $this->getLayout()->getBlock('forgotPassword')->setEmailValue(
            $this->_getSession()->getForgottenEmail()
        );
        $this->_getSession()->unsForgottenEmail();

        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

     /**
     * Forgot customer password page
     */
    public function forgotPasswordLoginAction()
    {
        $this->loadLayout();

        $this->getLayout()->getBlock('forgotPassword')->setEmailValue(
            $this->_getSession()->getForgottenEmail()
        );
        $this->_getSession()->unsForgottenEmail();

        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     * Forgot customer password action
     */
    public function forgotPasswordPostAction()
    {
        if('onepage' == $this->getRequest()->getParam('back')){
            $back_url = Mage::getBaseUrl('web').'checkout/onepage/';
        }else{
            $back_url = Mage::getBaseUrl('web').'customer/account/login';
        }
        $email = (string) $this->getRequest()->getPost('email');
        if ($email) {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $this->_getSession()->setForgottenEmail($email);
                $this->_getSession()->addError($this->__('Invalid email address.'));
                $this->_redirect('*/*/forgotpassword');
                return;
            }

            /** @var $customer Mage_Customer_Model_Customer */
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);

            if ($customer->getId()) {
                try {
                    $newResetPasswordLinkToken = Mage::helper('customer')->generateResetPasswordLinkToken();
                    $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                    $customer->sendPasswordResetConfirmationEmail();
					$this->_getSession()
                ->addSuccess(Mage::helper('customer')->__('You will receive an email with a link to reset your password.'));
                } catch (Exception $exception) {
                    $this->_getSession()->addError($exception->getMessage());
                    $this->_redirect('*/*/forgotpassword');
                    return;
                }
            } else {
				$this->_getSession()->addError($this->__('There is no account associated with that email address.'));
				$this->_redirect('*/*/forgotpassword');
				return;
			}
            $this->_redirectUrl($back_url);
            return;
        } else {
            $this->_getSession()->addError($this->__('Please enter your email.'));
            $this->_redirect('*/*/forgotpassword');
            return;
        }
    }

    /**
     * Add welcome message and send new account email.
     * Returns success URL
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param bool $isJustConfirmed
     * @return string
     */
    protected function _welcomeCustomer(Mage_Customer_Model_Customer $customer, $isJustConfirmed = false)
    {
        $this->_getSession()->addSuccess(
            $this->__('<span class="thankyou">Thank you for registering with %s.</span>', Mage::app()->getStore()->getFrontendName())
        );
        if ($this->_isVatValidationEnabled()) {
            // Show corresponding VAT message to customer
            $configAddressType = Mage::helper('customer/address')->getTaxCalculationAddressType();
            $userPrompt = '';
            switch ($configAddressType) {
                case Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING:
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you shipping address for proper VAT calculation', Mage::getUrl('customer/address/edit'));
                    break;
                default:
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you billing address for proper VAT calculation', Mage::getUrl('customer/address/edit'));
            }
            $this->_getSession()->addSuccess($userPrompt);
        }

        $customer->sendNewAccountEmail(
            $isJustConfirmed ? 'confirmed' : 'registered',
            '',
            Mage::app()->getStore()->getId()
        );
        //http://magnimine.magnify.ro/issues/2402
        //$successUrl = Mage::getUrl('*/*/index', array('_secure'=>true));
        $successUrl = Mage::getBaseUrl();

        if ($this->_getSession()->getBeforeAuthUrl()) {
            $successUrl = $this->_getSession()->getBeforeAuthUrl(true);
        }
        return $successUrl;
    }

   /*
    * This is replaced by the custom login redirect module. in order to use this that should be disabled.
    */
    protected function _loginPostRedirect()
    {
        $session = $this->_getSession();

        if (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::getBaseUrl()) {

            // Redirect customer to the last page visited after logging in
            if ($session->isLoggedIn()) {
                if (!Mage::getStoreConfigFlag(
                    Mage_Customer_Helper_Data::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD
                )) {
                    $referer = $this->getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME);
                    if ($referer) {
                        // Rebuild referer URL to handle the case when SID was changed
                        $referer = Mage::getModel('core/url')
                            ->getRebuiltUrl(Mage::helper('core')->urlDecode($referer));
                        if ($this->_isUrlInternal($referer)) {
                            $session->setBeforeAuthUrl($referer);
                        }
                    }
                } else if ($session->getAfterAuthUrl()) {
                    $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
                }

            } else {
                $session->setBeforeAuthUrl(Mage::helper('customer')->getLoginUrl());
            }
        } else if ($session->getBeforeAuthUrl() == Mage::helper('customer')->getLogoutUrl()) {
            $session->setBeforeAuthUrl(Mage::helper('customer')->getDashboardUrl());
        } else {
            if (!$session->getAfterAuthUrl()) {
                $session->setAfterAuthUrl($session->getBeforeAuthUrl());
            }
            if ($session->isLoggedIn()) {
                $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
            }
        }

        //to home
        if($session->getBeforeAuthUrl()=='' || $session->getBeforeAuthUrl() == 'NULL'){
        	$session->setBeforeAuthUrl(Mage::getBaseUrl());
        }
      	//

		if (Mage::getModel('core/cookie')->get('from_checkout_page') == 1) {
			Mage::getModel('core/cookie')->delete('from_checkout_page');
			$this->_redirect('checkout/onepage');
        } elseif(Mage::getModel('core/cookie')->get('from_multi_checkout_page') == 1) {
			Mage::getModel('core/cookie')->delete('from_multi_checkout_page');
			$this->_redirect('checkout/multishipping');
        } else {
			$this->_redirectUrl($session->getBeforeAuthUrl(true));
		}

    }

    /**
     * Login post action
     */
    public function loginPostAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $session = $this->_getSession();
        if ((count(explode('/wishlist/', $session->getBeforeAuthUrl())) < 2) && (count(explode('/multishipping/', $session->getBeforeAuthUrl())) < 2)) {
            if ('onepage' == $this->getRequest()->getParam('back')) {
                $back_url = Mage::getBaseUrl('web') . 'checkout/onepage/';
                $session->setBeforeAuthUrl($back_url);
            } else if ('login' == $this->getRequest()->getParam('back')) {
                $back_url = Mage::getBaseUrl('web') . 'customer/account/login';
                $session->setBeforeAuthUrl($back_url);
                $session->setAfterAuthUrl($session->getBeforeAuthUrl());
            }
        }

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                $message="";
                try {
                    $session->login($login['username'], $login['password']);
                    if ($session->getCustomer()->getIsJustConfirmed()) {
                        $this->_welcomeCustomer($session->getCustomer(), true);
                    }
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    $session->addError($message);
                    $session->setUsername($login['username']);
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }

                /* customer login monitoring code added by allure
                 * */
                if($message)
                {
                    $result['success']=false;
                    $result['error']=$message;
                }
                else{
                    $result['success']=true;
                }

                Mage::helper('customerloginmonitor')->addLoginInfo($result);

                /*allure code ended---------------------------------------------------
                * */

            } else {
                $session->addError($this->__('Login and password are required.'));
            }
        }

        $this->_loginPostRedirect();
    }

    /**
     * Change customer password action
     */
    public function editPostAjaxAction()
    {
    	if (!$this->_validateFormKey()) {
    		return ;//$this->_redirect('*/*/edit');
    	}

    	if ($this->getRequest()->isPost()) {
    		/** @var $customer Mage_Customer_Model_Customer */
    		$customer = $this->_getSession()->getCustomer();
    		$customer->setOldEmail($customer->getEmail());
    		/** @var $customerForm Mage_Customer_Model_Form */
    		$customerForm = $this->_getModel('customer/form');
    		$customerForm->setFormCode('customer_account_edit')
    		->setEntity($customer);

    		$customerData = $customerForm->extractData($this->getRequest());

    		$errors = array();
    		$customerErrors = $customerForm->validateData($customerData);
    		if ($customerErrors !== true) {
    			$errors = array_merge($customerErrors, $errors);
    		} else {
    			$customerForm->compactData($customerData);
    			$errors = array();

    			if($this->getRequest()->getPost('is_change') == 1){ //allure code
    			    if (!$customer->validatePassword($this->getRequest()->getPost('current_password'))) {
    			        $errors[] = $this->__('Invalid current password');
    			    }
    			}

    			// If email change was requested then set flag
    			$isChangeEmail = ($customer->getOldEmail() != $customer->getEmail()) ? true : false;
    			$customer->setIsChangeEmail($isChangeEmail);

    			// If password change was requested then add it to common validation scheme
    			$customer->setIsChangePassword($this->getRequest()->getParam('change_password'));

    			if ($customer->getIsChangePassword()) {
    				$newPass    = $this->getRequest()->getPost('password');
    				$confPass   = $this->getRequest()->getPost('confirmation');

    				if (strlen($newPass)) {
    					/**
    					 * Set entered password and its confirmation - they
    					 * will be validated later to match each other and be of right length
    					 */
    					$customer->setPassword($newPass);
    					$customer->setPasswordConfirmation($confPass);

    				} else {
    					$errors[] = $this->__('New password field cannot be empty.');
    				}
    			}

    			// Validate account and compose list of errors if any
    			$customerErrors = $customer->validate();
    			if (is_array($customerErrors)) {
    				$errors = array_merge($errors, $customerErrors);
    			}
    		}

    		if (!empty($errors)) {
    		 	$result['success'] = 0;
    		 	$result['error'] = $errors;
    		} else{
	    		try {
	    			$customer->cleanPasswordsValidationData();

	    			// Reset all password reset tokens if all data was sufficient and correct on email change
	    			if ($customer->getIsChangeEmail()) {
	    				$customer->setRpToken(null);
	    				$customer->setRpTokenCreatedAt(null);
	    			}

	    			$customer->save();
                    if(!empty($customer->getTwUcGuid())):
                        $customerData = array
                        (
                            'customer_id'   => $customer->getTwUcGuid(),
                            'password'      => $newPass,
                        );

                        Mage::getModel('teamwork_universalcustomers/svs')->updateCustomer($customerData,true);
                    endif;
	    			$result['success'] = 1;
	    			$result['message'] =  $this->__('The account information has been saved.');

	    			$this->loadLayout('myaccount_customer_form_edit');
	    			$html = $this->getLayout()->getBlock('customer_edit')->toHtml();
	    			$result['html']  = $html;

	    			if ($customer->getIsChangeEmail() || $customer->getIsChangePassword()) {
	    				$customer->sendChangedPasswordOrEmail();
	    			}

	    		} catch (Mage_Core_Exception $e) {
	    			$result['success'] = 0;
	    			$result['message'] =  $e->getMessage();
	    			$result['error'] = $errors;
	    		} catch (Exception $e) {
	    			$result['success'] = 0;
	    			$result['message'] =  $this->__('Cannot save the customer.');
	    			$result['error'] = $errors;
	    		}
	    	}
    	}

    	$this->getResponse()->setHeader('Content-type', 'application/json');
    	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

    }

}
