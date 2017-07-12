<?php
/**
 * Paradox Labs, Inc.
 * http://www.paradoxlabs.com
 * 717-431-3330
 * 
 * Need help? Open a ticket in our support system:
 *  http://support.paradoxlabs.com
 * 
 * Want to customize or need help with your store?
 *  Phone: 717-431-3330
 *  Email: sales@paradoxlabs.com
 *
 * @category	ParadoxLabs
 * @package		TokenBase
 * @author		Ryan Hoerr <magento@paradoxlabs.com>
 * @license		http://store.paradoxlabs.com/license.html
 */

include_once("ParadoxLabs/TokenBase/controllers/Customer/PaymentinfoController.php");
class Allure_TokenBase_Customer_PaymentinfoController extends ParadoxLabs_TokenBase_Customer_PaymentinfoController
{
	
	/**
	 * Create or update a card on save
	 */
	public function saveAction()
	{
		$id			= intval( $this->getRequest()->getPost('id') );
		$method		= $this->getRequest()->getParam('method');
		$type 		= $this->getRequest()->getParam('mode_type'); //allure code
		
		if( $this->_formKeyIsValid() === true && $this->_methodIsValid() === true ) {
			/**
			 * Convert inputs into an address and payment object for storage.
			 */
			try {
				/**
				 * Load the card and verify we are actually the cardholder before doing anything.
				 */
				$card		= Mage::getModel( $method . '/card' )->load( $id );
				$customer	= Mage::helper('tokenbase')->getCurrentCustomer();
				
				if( $card && ( $id == 0 || ( $card->getId() == $id && $card->hasOwner( $customer->getId() ) ) ) ) {
					/**
					 * Process address data
					 */
					$newAddrId	= intval( Mage::app()->getRequest()->getParam('shipping_address_id') );
					
					// Existing address
					if( $newAddrId > 0 ) {
						$newAddr = Mage::getModel('customer/address')->load( $newAddrId );
						
						if( $newAddr->getCustomerId() != $customer->getId() ) {
							Mage::throwException( $this->__('An error occurred. Please try again.') );
						}
					}
					// New address
					else {
						$newAddr = Mage::getModel('customer/address');
						$newAddr->setCustomerId( $customer->getId() );
						
						$data = Mage::app()->getRequest()->getPost( 'billing', array() );
						
						$addressForm    = Mage::getModel('customer/form');
						$addressForm->setFormCode('customer_address_edit');
						$addressForm->setEntity( $newAddr );
						
						$addressData    = $addressForm->extractData( $addressForm->prepareRequest( $data ) );
						$addressErrors  = $addressForm->validateData( $addressData );
						
						if( $addressErrors !== true ) {
							Mage::throwException( implode( ' ', $addressErrors ) );
						}
						
						$addressForm->compactData( $addressData );
						$addressErrors  = $newAddr->validate();
						
						$newAddr->setSaveInAddressBook( false );
						$newAddr->implodeStreetAddress();
					}
					
					/**
					 * Process payment data
					 */
					$cardData = Mage::app()->getRequest()->getParam('payment');
					$cardData['method']		= $method;
					$cardData['card_id']	= $card->getId();
					
					$nameOnCard = $cardData['name_on_card']; //allure
					
					if( isset( $cardData['cc_number'] ) ) {
						$cardData['cc_last4']	= substr( $cardData['cc_number'], -4 );
					}
					
					
					Mage::log(Mage::getSingleton('checkout/session')->getQuote()->getId(),Zend_log::DEBUG,'abc',true);
					//die;
					$newPayment = Mage::getModel('sales/quote_payment');
					$newPayment->setQuote( Mage::getSingleton('checkout/session')->getQuote() );
					$newPayment->getQuote()->getBillingAddress()->setCountryId( $newAddr->getCountryId() );
					
					if(!empty($type)){
						$cardData= new Varien_Object($cardData);
						
						Mage::dispatchEvent(
								'sales_quote_payment'. '_import_data_before',
								array(
										'payment'=>$newPayment,
										'input'=>$cardData,
								)
								);
						$newPayment->setMethod($cardData->getMethod());
						$newPayment1= $newPayment->getMethodInstance();
						Mage::getSingleton('checkout/session')->getQuote()->collectTotals();
						
						$newPayment1->assignData($cardData);
						/*
						 * validating the payment data
						 */
						$newPayment1->validate();
					}else{
						$newPayment->importData( $cardData );
					}
					/**
					 * Save payment data
					 */
					$card->setMethod( $method );
					$card->setActive( 1 );
					$card->setCustomer( $customer );
					$card->setAddress( $newAddr );
					$card->importPaymentInfo( $newPayment );
					if(!empty($nameOnCard))
						$card->setNameOnCard($nameOnCard);
					$card->save();
					
					Mage::getSingleton('customer/session')->unsTokenbaseFormData();
				}
				else {
					Mage::getSingleton('core/session')->addError( $this->__('Invalid Request.') );
					return $this->_redirectReferer();
				}
			}
			catch( Exception $e ) {
				Mage::getSingleton('customer/session')->setTokenbaseFormData( Mage::app()->getRequest()->getPost() );
				
				Mage::helper('tokenbase')->log( $method, (string)$e );
				Mage::getSingleton('core/session')->addError( $e->getMessage() );
				
				return $this->_redirectReferer();
			}
		}
		else {
			Mage::getSingleton('core/session')->addError( $this->__('Invalid Request.') );
			return $this->_redirectReferer();
		}
		
		Mage::getSingleton('core/session')->addSuccess( $this->__('Payment data saved successfully.') );
		
		$this->_redirect( '*/*', array( 'method' => $method ) );
	}
	
	
}
