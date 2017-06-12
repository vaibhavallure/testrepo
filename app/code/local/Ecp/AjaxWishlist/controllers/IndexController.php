<?php
class Ecp_AjaxWishlist_IndexController extends Mage_Core_Controller_Front_Action
{

    public function compareAction()
    {
        $response = array();

        if ($productId = (int)$this->getRequest()->getParam('product')) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);

            if ($product->getId() /* && !$product->isSuper()*/) {
                Mage::getSingleton('catalog/product_compare_list')->addProduct($product);
                $response['status'] = 'SUCCESS';
                $response['message'] = $this->__('The product %s has been added to comparison list.', Mage::helper('core')->escapeHtml($product->getName()));
                Mage::register('referrer_url', $this->_getRefererUrl());
                Mage::helper('catalog/product_compare')->calculate();
                Mage::dispatchEvent('catalog_product_compare_add_product', array('product' => $product));
                $this->loadLayout();
                $sidebar_block = $this->getLayout()->getBlock('catalog.compare.sidebar');
                $sidebar_block->setTemplate('ajaxwishlist/catalog/product/compare/sidebar.phtml');
                $sidebar = $sidebar_block->toHtml();
                $response['sidebar'] = $sidebar;
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }


    protected function _getWishlist()
    {
        $wishlist = Mage::registry('wishlist');
        if ($wishlist) {
            return $wishlist;
        }

        try {
            $wishlist = Mage::getModel('wishlist/wishlist')
                ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer(), true);
            Mage::register('wishlist', $wishlist);
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('wishlist/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('wishlist/session')->addException($e,
                Mage::helper('wishlist')->__('Cannot create wishlist.')
            );
            return false;
        }

        return $wishlist;
    }

    public function addAction()
    {
        $response = array();
        if (!Mage::getStoreConfigFlag('wishlist/general/active')) {
            $response['status'] = 'ERROR';
            $response['message'] = $this->__('Wishlist Has Been Disabled By Admin');
        }
        if (!(Mage::getSingleton('customer/session')->isLoggedIn()) ) {
            $productId = (int)$this->getRequest()->getParam('product');
            $cpid = (int)$this->getRequest()->getParam('cpid');
            $product = Mage::getModel('catalog/product')->load($productId);

            Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::helper('wishlist')->getAddUrl($product) .'product/'.$productId.'/cpid/'.$cpid. '/data/' . base64_encode(serialize($this->getRequest()->getParams())));
            $response['status'] = 'ERROR';
            $response['message'] = 'login';
        }

        if (empty($response)) {
            $session = Mage::getSingleton('customer/session');
            $wishlist = $this->_getWishlist();
            if (!$wishlist) {
                $response['status'] = 'ERROR';
                $response['message'] = $this->__('Unable to Create Wishlist');
            } else {

                $productId = (int)$this->getRequest()->getParam('product');
                if (!$productId) {
                    $response['status'] = 'ERROR';
                    $response['message'] = $this->__('Product Not Found');
                } else {
                    ?>
                    <?php
                    $product = Mage::getModel('catalog/product')->load($productId);
                    if (!$product->getId()) {
                        $response['status'] = 'ERROR';
                        $response['message'] = $this->__('Cannot specify product.');
                    } else {
                        try {
                            $data = $this->getRequest()->getParam('data');
                            if(!empty($data)){
                                $requestParams = unserialize(base64_decode($this->getRequest()->getParam('data')));
                            } else {
                                $requestParams = $this->getRequest()->getParams();
                            }

                            $buyRequest = new Varien_Object($requestParams);
                            Mage::log("Query product: " . print_r($buyRequest, true), null, 'test.log');
                            $result = $wishlist->addNewItem($product, $buyRequest);
                            if (is_string($result)) {
                                Mage::throwException($result);
                            }
                            $wishlist->save();

                            Mage::dispatchEvent(
                                'wishlist_add_product',
                                array(
                                    'wishlist' => $wishlist,
                                    'product' => $product,
                                    'item' => $result
                                )
                            );

                            Mage::helper('wishlist')->calculate();
                            $cpid = (int)$this->getRequest()->getParam('cpid');
                            if($cpid != $productId) {
                                    $cp_product = Mage::getModel('catalog/product')->load($cpid);
                                    $pname = $cp_product->getName();
                            } else {
                                    $pname = $product->getName();
                            }
                            $message = $this->__('Added to Wishlist');
                            $response['status'] = 'SUCCESS';
                            $response['message'] = $message;

                            Mage::unregister('wishlist');
                            Mage::log("Query product: " . print_r($response, true), null, 'test.log');

                        } catch (Mage_Core_Exception $e) {
                            $response['status'] = 'ERROR';
                            $response['message'] = $this->__('An error occurred while adding item to wishlist: %s', $e->getMessage());
                        }
                        catch (Exception $e) {
                            mage::log($e->getMessage());
                            $response['status'] = 'ERROR';
                            $response['message'] = $this->__('An error occurred while adding item to wishlist.');
                        }
                    }
                }
            }

        }
        Mage::log("Query product: " . print_r($response, true), null, 'test.log');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }
}