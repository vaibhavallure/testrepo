<?php

class Teamwork_CEGiftcards_QuoteController extends Mage_Core_Controller_Front_Action
{
    public function applygcAction()
    {
        $gcCode = Mage::app()->getRequest()->getParam('gc_code', false);
        if ($gcCode !== false) {
            $gcPin = Mage::app()->getRequest()->getParam('gc_pin', false);
            $helper = Mage::helper('teamwork_cegiftcards');
            $helper->sessionMsgsOut($this->_getSession(), $helper->applyGC2Quote($this->_getQuote(), $gcCode, $gcPin));
        }
        $this->_redirect('checkout/cart');
    }

    public function removegcAction()
    {
        $gcCode = Mage::app()->getRequest()->getParam('gc_code', false);
        if ($gcCode !== false) {
            $helper = Mage::helper('teamwork_cegiftcards');
            $helper->sessionMsgsOut($this->_getSession(), $helper->removeGCFromQuote($this->_getQuote(), $gcCode));
        }
        $this->_redirect('checkout/cart');
    }

    protected function _getQuote()
    {
        return Mage::getModel('checkout/cart')->getQuote();
    }

    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function checkgcajaxAction()
    {
        $gcCode = Mage::app()->getRequest()->getParam('gc_code', false);
        $result = array(
            'error' => false,
        );
        if ($gcCode !== false) {
            $gcPin = Mage::app()->getRequest()->getParam('gc_pin', false);
            $result['code'] = $gcCode;
            $svs = Mage::getModel('teamwork_cegiftcards/svs');
            try {
                $gcData = $svs->getGiftcardData($gcCode, $gcPin);
                if ($gcData['active']) {
                    $result['active'] = true;
                    $result['balance'] = Mage::helper('core')->currency($gcData['giftcard_balance'], true, false);
                } else {
                    $result['active'] = false;
                }
            } catch (Teamwork_CEGiftcards_Model_Exception_Svs_Response $e) {
                $result['active'] = false;
            } catch (Exception $e) {
               $result['error'] = true;
            }
        } else {
            $result['error'] = true;
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

}
