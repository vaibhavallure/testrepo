<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Newsletter
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Newsletter subscribe controller
 *
 * @category    Mage
 * @package     Mage_Newsletter
 * @author      Magento Core Team <core@magentocommerce.com>
 */

include_once("Mage/Newsletter/controllers/SubscriberController.php");

class Ecp_Newsletter_SubscriberController extends Mage_Newsletter_SubscriberController
{
    /**
      * New subscription action
      */
    public function newAction()
    {
        $response = array();
        $response['success'] = false;
        
        $response['thankyou'] = $this->getLayout()->createBlock('cms/block')->setBlockId('newsletter_thank_you')->toHtml();
        
        if ($this->getRequest()->isGet() && $this->getRequest()->getParams('email')) {
            $session            = Mage::getSingleton('core/session');
            $customerSession    = Mage::getSingleton('customer/session');
            $email              = (string) $this->getRequest()->getParam('email');
            $firstname          = (string) $this->getRequest()->getParam('first_name');
            $lastname           = (string) $this->getRequest()->getParam('last_name');
            $country            = (string) $this->getRequest()->getParam('country');

            try {
                
                $response['message'] = array();
                
                if (!Zend_Validate::is($email, 'EmailAddress')) {
//                    Mage::throwException($this->__('Please enter a valid email address.'));
                    $response['message']['email'] = $this->__('Please enter a valid email address.');
                    //die(Mage::helper('core')->jsonEncode($response));
                }
                
                if(empty($firstname) || strcasecmp($firstname,'first name')==0 ){
                    $response['message']['first'] = $this->__('Please enter your first name.');
                    //die(Mage::helper('core')->jsonEncode($response));
                }
                
                if(empty($lastname) || strcasecmp($lastname,'last name')==0){
                    $response['message']['last'] = $this->__('Please enter your last name.');
                    //die(Mage::helper('core')->jsonEncode($response));
                }
                
                if(empty($country) || strcasecmp($country,'country')==0){
                    $response['message']['country'] = $this->__('Please select your country.');
                    //die(Mage::helper('core')->jsonEncode($response));
                }
                
                if(!empty($response['message']))
                    die(Mage::helper('core')->jsonEncode($response));

                if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 && 
                    !$customerSession->isLoggedIn()) {
//                    Mage::throwException($this->__('Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.', Mage::helper('customer')->getRegisterUrl()));
                    $response['message']['general'] = $this->__('Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.', Mage::helper('customer')->getRegisterUrl());
                    die(Mage::helper('core')->jsonEncode($response));
                }

                $ownerId = Mage::getModel('customer/customer')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                        ->loadByEmail($email)
                        ->getId();
                if ($ownerId !== null && $ownerId != $customerSession->getId()) {
//                    Mage::throwException($this->__('This email address is already assigned to another user.'));
                    $response['message']['general'] = $this->__('Thank you for your subscription.');
                    $response['success'] = true;
                    die(Mage::helper('core')->jsonEncode($response));
                }

                $status = Mage::getModel('newsletter/subscriber')->subscribeExtended($email,ucwords($firstname),ucwords($lastname),ucwords($country));
                if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                    $response['message']['general'] = $this->__('Confirmation request has been sent.');
                    $response['success'] = true;
                }
                else {
                    $response['message']['general'] = $this->__('Thank you for your subscription.');
                    $response['success'] = true;
                }
            }
            catch (Mage_Core_Exception $e) {
                $response['message']['error'] = $this->__('There was a problem with the subscription: %s', $e->getMessage());
                //$session->addException($e, $this->__('There was a problem with the subscription: %s', $e->getMessage()));
            }
            catch (Exception $e) {
                $response['message']['error'] = $this->__('There was a problem with the subscription: %s', $e->getMessage());
                //$session->addException($e, $this->__('There was a problem with the subscription.'));
            }
        }
        
        die(Mage::helper('core')->jsonEncode($response));
    }

}