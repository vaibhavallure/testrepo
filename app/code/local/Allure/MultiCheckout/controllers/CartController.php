<?php
include_once ("Ecp/Shoppingcart/controllers/CartController.php");

// include_once("app/code/local/Webtex/Giftcards/controllers/CartController.php");
class Allure_MultiCheckout_CartController extends Ecp_Shoppingcart_CartController // Webtex_Giftcards_CartController
{

    private function _setShippingInfo (array $info)
    {
        $country = (string) $info['country'];
        $postcode = (string) $info['postal'];
        $city = (string) $info['city'];
        $region = (string) $info['region'];
        $regionId = (string) Mage::getModel('directory/region')->load($region, 'default_name')->getRegionId();

        $this->_getQuote()
            ->getShippingAddress()
            ->setCountryId($country)
            ->setCity($city)
            ->setPostcode($postcode)
            ->setRegionId($regionId)
            ->setRegion($region)
            ->setCollectShippingRates(true);
        $this->_getQuote()->save();

        $this->_getSession()->setCartWasUpdated(true);

        $code = (string) 'flatrate_flatrate';
        if (! empty($code)) {
            $shippingAddress = $this->_getQuote()->getShippingAddress();
            $shippingAddress->setShippingMethod($code)
                ->collectShippingRates()
                ->save();
            $taxes = Mage::getSingleton('sales/quote_address_total_tax',
                array(
                        'store' => $this->_getQuote()->getStore()
                )
			);
            $taxes->collect($shippingAddress);
        }

        $this->_getCart()->saveQuote();

        $this->_getSession()->setCartWasUpdated(true);
    }
    
    /**
     * Get refresh totals html
     * @return string
     */
    protected function _getRefreshTotalsHtml ()
    {
        $block = $this->getLayout()
            ->createBlock('checkout/cart_totals')
            ->setTemplate('checkout/cart/totals.phtml');
        $childBlock = $this->getLayout()
            ->createBlock('checkout/cart_shipping')
            ->setTemplate('checkout/cart/shipping.phtml');
        $block->setChild("shipping", $childBlock);
        $output = $block->toHtml();
        return $output;
    }

    /**
     * Initialize coupon
     */
    public function couponPostAction ()
    {
        $isAjax = $this->getRequest()->getParam('ajax', false);
        $response = array(
                'error' => true,
                'message' => '',
                'disable' => false
        );

        /**
         * No reason continue with empty shopping cart
         */
        if (! $this->_getCart()
            ->getQuote()
            ->getItemsCount()) {
            $response['totals_html'] = $this->_getRefreshTotalsHtml();
            if (! $isAjax)
                $this->_goBack();
            else
                die(json_encode($response));
            return;
        }

        $couponCode = (string) $this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (! strlen($couponCode) && ! strlen($oldCouponCode)) {
            $response['totals_html'] = $this->_getRefreshTotalsHtml();
            if (! $isAjax)
                $this->_goBack();
            else
                die(json_encode($response));
            return;
        }

        try {
            $this->_getQuote()
                ->getShippingAddress()
                ->setCollectShippingRates(true);
            $this->_getQuote()
                ->setTotalsCollectedFlag(false)
                ->setCouponCode(strlen($couponCode) ? $couponCode : '')
                ->collectTotals()
                ->save();

            // coupon code apply to two shipment quote's.
            $_checkoutHelper = Mage::helper('allure_multicheckout');
            if (strtolower($this->_getQuote()->getDeliveryMethod()) == strtolower($_checkoutHelper::TWO_SHIP)) {

                $model = Mage::getModel('checkout/type_onepage');
                $model->getQuoteOrdered()
                    ->getShippingAddress()
                    ->setCollectShippingRates(true);
                $model->getQuoteOrdered()
                    ->setTotalsCollectedFlag(false)
                    ->setCouponCode(strlen($couponCode) ? $couponCode : '')
                    ->collectTotals()
                    ->save();

                $model->getQuoteBackordered()
                    ->getShippingAddress()
                    ->setCollectShippingRates(true);
                $model->getQuoteBackordered()
                    ->setTotalsCollectedFlag(false)
                    ->setCouponCode(strlen($couponCode) ? $couponCode : '')
                    ->collectTotals()
                    ->save();
            }

            if (strlen($couponCode)) {
                if ($couponCode == $this->_getQuote()->getCouponCode()) {
                    if (! $isAjax) {
                        $this->_getSession()->addSuccess(
                                $this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape(
                                        $couponCode)));
                    } else {
                        $response['error'] = false;
                        $response['message'] = $this->__('Coupon code "%s" was applied.',
                                Mage::helper('core')->htmlEscape($couponCode));
                        $response['disable'] = true;
                    }
                } else {
                    if (! $isAjax) {
                        $this->_getSession()->addError(
                                $this->__('Coupon code "%s" is not valid.',
                                        Mage::helper('core')->htmlEscape($couponCode)));
                    } else {
                        $response['error'] = true;
                        $response['message'] = $this->__('Coupon code "%s" is not valid.',
                                Mage::helper('core')->htmlEscape($couponCode));
                    }
                }
            } else {
                if (! $isAjax) {
                    $this->_getSession()->addSuccess($this->__('Coupon code was canceled.'));
                } else {
                    $response['error'] = false;
                    $response['message'] = $this->__('Coupon code was canceled.');
                }
            }
        } catch (Mage_Core_Exception $e) {
            if (! $isAjax) {
                $this->_getSession()->addError($e->getMessage());
            } else {
                $response['error'] = true;
                $response['message'] = $e->getMessage();
            }
        } catch (Exception $e) {
            if (! $isAjax) {
                $this->_getSession()->addError($this->__('Cannot apply the coupon code.'));
            } else {
                $response['error'] = true;
                $response['message'] = $this->__('Cannot apply the coupon code.');
            }
            Mage::logException($e);
        }
        
        $response['totals_html'] = $this->_getRefreshTotalsHtml();

        if (! $isAjax)
            $this->_goBack();
        else
            die(json_encode($response));
    }
}

