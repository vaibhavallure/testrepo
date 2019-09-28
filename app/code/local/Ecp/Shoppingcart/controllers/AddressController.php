<?php
//@GC Cart
include_once("Mage/Customer/controllers/AddressController.php");

class Ecp_Shoppingcart_AddressController extends Mage_Customer_AddressController
{
    public function formPostAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/');
        }
        // Save data
        if ($this->getRequest()->isPost()) {
            $customer = $this->_getSession()->getCustomer();
            /* @var $address Mage_Customer_Model_Address */
            $address  = Mage::getModel('customer/address');
            $addressId = $this->getRequest()->getParam('id');
            if ($addressId) {
                $existsAddress = $customer->getAddressById($addressId);
                if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
                    $address->setId($existsAddress->getId());
                }
            }

            $errors = array();

            /* @var $addressForm Mage_Customer_Model_Form */
            $addressForm = Mage::getModel('customer/form');
            $addressForm->setFormCode('customer_address_edit')
                ->setEntity($address);
            $addressData    = $addressForm->extractData($this->getRequest());
            $addressErrors  = $addressForm->validateData($addressData);
            if ($addressErrors !== true) {
                $errors = $addressErrors;
            }

            try {
                $addressForm->compactData($addressData);
                $address->setCustomerId($customer->getId())
                    ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                    ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));

                $addressErrors = $address->validate();
                if ($addressErrors !== true) {
                    $errors = array_merge($errors, $addressErrors);
                }

                if (count($errors) === 0) {
                    $address->save();
                    if($this->getRequest()->getParam('default_billing', false)){
                        Mage::getModel('checkout/type_multishipping')
                            ->setQuoteCustomerBillingAddress($addressId);
                    }
                    $this->_getSession()->addSuccess($this->__('The address has been saved.'));
                    $this->_redirectSuccess(Mage::getUrl('*/*/index', array('_secure'=>true)));
                    return;
                } else {
                    $this->_getSession()->setAddressFormData($this->getRequest()->getPost());
                    foreach ($errors as $errorMessage) {
                        $this->_getSession()->addError($errorMessage);
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->setAddressFormData($this->getRequest()->getPost())
                    ->addException($e, $e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->setAddressFormData($this->getRequest()->getPost())
                    ->addException($e, $this->__('Cannot save address.'));
            }
        }
        return $this->_redirectError(Mage::getUrl('*/*/edit', array('id' => $address->getId())));
    }
    
    public function setDefaultAddressAjaxAction(){
    	if (!$this->_validateFormKey()) {
    		return $this->_redirect('*/*/');
    	}
    	
    	if ($this->getRequest()->isPost()) {
    		$customer = $this->_getSession()->getCustomer();
    		/* @var $address Mage_Customer_Model_Address */
    		$address  = Mage::getModel('customer/address');
    		$addressId = $this->getRequest()->getParam('id');
    		if ($addressId) {
    			$existsAddress = $customer->getAddressById($addressId);
    			if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
    				$address->setId($existsAddress->getId());
    			}
    		}
    		
    		try {
    			$type = $this->getRequest()->getParam('type', false);
    			
    			if($type=='default_billing'){
    				$address->setCustomerId($customer->getId())
    					->setIsDefaultBilling(1);
    			}
    			
    			if($type=='default_shipping'){
    				$address->setCustomerId($customer->getId())
    				->setIsDefaultShipping(1);
    			}
    			
    				$address->save();
    				if($type=='default_billing'){
    					Mage::getModel('checkout/type_multishipping')
    					->setQuoteCustomerBillingAddress($addressId);
    				}
    				$result['message'] = $this->__('The address has been saved.');
    				$result['success'] = 1;
    				
    				/* $this->loadLayout('customer_address_index');
    				$html = $this->getLayout()->getBlock('address_book')->toHtml();
    				$result['html']  = $html; */
    				
    		} catch (Mage_Core_Exception $e) {
    			$result['success'] = 0;
    			$result['message'] = $this->__('Cannot save address.');
    			$result['error'] = $e->getMessage();
    		} catch (Exception $e) {
    			$result['success'] = 0;
    			$result['message'] = $this->__('Cannot save address.');
    			$result['error'] = $e->getMessage();
    		}
    		$this->getResponse()->setHeader('Content-type', 'application/json');
    		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    	}
    }
    
    public function editAjaxAction()
    {
    	$this->loadLayout('myaccount_customer_address_edit');
    	$html = $this->getLayout()->getBlock('customer_address_edit')->toHtml();
    	$result['html']  = $html;
    	
    	$this->getResponse()->setHeader('Content-type', 'application/json');
    	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    	 
    }
    
    
    public function newAjaxAction()
    {
    	$this->loadLayout('myaccount_customer_address_new');
    	$html = $this->getLayout()->getBlock('customer_address_new')->toHtml();
    	$result['html']  = $html;
    	
    	$this->getResponse()->setHeader('Content-type', 'application/json');
    	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    	
    }
    
    
    public function deleteAjaxAction()
    {
    	if (!$this->_validateFormKey()) {
    		return ;
    	}
    	$addressId = $this->getRequest()->getParam('id', false);
    	
    	if ($addressId) {
    		$address = Mage::getModel('customer/address')->load($addressId);
    		
    		if ($address->getCustomerId() != $this->_getSession()->getCustomerId()) {
    			$result['success'] = 0;
    			$result['message'] = $this->__('The address does not belong to this customer.');
    			
    		}else{
	    		try {
	    			$address->delete();
	    			$this->loadLayout('myaccount_customer_address_data'); 
	    			$result['message'] = $this->__('The address has been deleted.');
	    			$result['success'] = 1;
	    			$html =  $this->getLayout()->getBlock('customer_address_edit')->toHtml();
	    			$result['html']  = $html;
	    		} catch (Exception $e){
	    			$result['success'] = 0;
	    			$result['message'] = $this->__('An error occurred while deleting the address.');
	    		}
    		}
    	}
    	
    	$this->getResponse()->setHeader('Content-type', 'application/json');
    	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    	
    }
    
    
    public function saveAjaxAction()
    {
    	if (!$this->_validateFormKey()) {
    		return ;//$this->_redirect('*/*/');
    	}
    	// Save data
    	if ($this->getRequest()->isPost()) {
    		$customer = $this->_getSession()->getCustomer();
    		/* @var $address Mage_Customer_Model_Address */
    		$address  = Mage::getModel('customer/address');
    		$addressId = $this->getRequest()->getParam('address_id');
    		if ($addressId) {
    			$existsAddress = $customer->getAddressById($addressId);
    			if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
    				$address->setId($existsAddress->getId());
    			}
    		}
    		
    		$errors = array();
    		
    		/* @var $addressForm Mage_Customer_Model_Form */
    		$addressForm = Mage::getModel('customer/form');
    		$addressForm->setFormCode('customer_address_edit')
    		->setEntity($address);
    		$addressData    = $addressForm->extractData($this->getRequest());
    		$addressErrors  = $addressForm->validateData($addressData);
    		if ($addressErrors !== true) {
    			$errors = $addressErrors;
    		}
    		
    		try {
    			$addressForm->compactData($addressData);
    			$address->setCustomerId($customer->getId())
    			->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
    			->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
    			
    			$addressErrors = $address->validate();
    			if ($addressErrors !== true) {
    				$errors = array_merge($errors, $addressErrors);
    			}
    			
    			if (count($errors) === 0) {
    				$address->save();
    				if($this->getRequest()->getParam('default_billing', false)){
    					Mage::getModel('checkout/type_multishipping')
    					->setQuoteCustomerBillingAddress($addressId);
    				}
    				$this->loadLayout('myaccount_customer_address_data');
    				$html =  $this->getLayout()->getBlock('customer_address_edit')->toHtml();
    				$result['html']  = $html;
    				
    				$result['success'] = 1;
    				$result['message'] = $this->__('The address has been saved.');
    			} else {
    				$result['success'] = 0;
    				$result['error'] = $errors;
    			}
    		} catch (Mage_Core_Exception $e) {
    			$result['success'] = 0;
    			$result['error']   = array($e->getMessage());
    		} catch (Exception $e) {
    			$result['success'] = 0;
    			$result['error']   = array($this->__('Cannot save address.'));
    		}
    	}
    	
    	$this->getResponse()->setHeader('Content-type', 'application/json');
    	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    	
    	
    }
    
    public function setDefaultShippingAjaxAction(){
    	
    }
    
    /**
     * Save customer address form for multi address checkout.
     */
    public function saveCustomerAddressFormAction(){
        if (!$this->_validateFormKey()) {
            return $this->_redirect('checkout/multishipping/addresses');
        }
        // Save data
        if ($this->getRequest()->isPost()) {
            $customer = $this->_getSession()->getCustomer();
            /* @var $address Mage_Customer_Model_Address */
            $address  = Mage::getModel('customer/address');
            $addressId = $this->getRequest()->getParam('id');
            if ($addressId) {
                $existsAddress = $customer->getAddressById($addressId);
                if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
                    $address->setId($existsAddress->getId());
                }
            }
            
            $errors = array();
            
            /* @var $addressForm Mage_Customer_Model_Form */
            $addressForm = Mage::getModel('customer/form');
            $addressForm->setFormCode('customer_address_edit')
            ->setEntity($address);
            $addressData    = $addressForm->extractData($this->getRequest());
            $addressErrors  = $addressForm->validateData($addressData);
            if ($addressErrors !== true) {
                $errors = $addressErrors;
            }
            
            try {
                $addressForm->compactData($addressData);
                $address->setCustomerId($customer->getId())
                ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
                
                $addressErrors = $address->validate();
                if ($addressErrors !== true) {
                    $errors = array_merge($errors, $addressErrors);
                }
                
                if (count($errors) === 0) {
                    $address->save();
                    if($this->getRequest()->getParam('default_billing', false)){
                        Mage::getModel('checkout/type_multishipping')
                        ->setQuoteCustomerBillingAddress($addressId);
                    }
                    $this->_getSession()->addSuccess($this->__('The address has been saved.'));
                    $this->_redirectSuccess(Mage::getUrl('checkout/multishipping/addresses', array('_secure'=>true)));
                    return;
                } else {
                    $this->_getSession()->setAddressFormData($this->getRequest()->getPost());
                    foreach ($errors as $errorMessage) {
                        $this->_getSession()->addError($errorMessage);
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->setAddressFormData($this->getRequest()->getPost())
                ->addException($e, $e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->setAddressFormData($this->getRequest()->getPost())
                ->addException($e, $this->__('Cannot save address.'));
            }
        }
        return $this->_redirectError(Mage::getUrl('checkout/multishipping/addresses'));
    }
}
