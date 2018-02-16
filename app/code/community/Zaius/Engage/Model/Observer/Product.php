<?php
class Zaius_Engage_Model_Observer_Product extends Zaius_Engage_Model_Observer {

  public function entity($observer) {
    $helper = Mage::helper('zaius_engage');
    if ($helper->isEnabled()) {
      $product = $observer->getProduct();
      $entity = array();
      if ($helper->isCollectAllProductAttributes()) {
        $entity = Zaius_Engage_Model_ProductAttribute::getAttributes($product);
      }
      $entity['product_id']  = $helper->getProductID($product->getId());
      $entity['name']        = $product->getName();
      $entity['sku']         = $product->getSku();
      $entity['description'] = $product->getShortDescription();
      $entity['category']    = $this->getDeepestCategoryPath($product);
      if ($product->getManufacturer()) {
        $entity['brand'] = $product->getAttributeText('manufacturer');
      }
      if ($product->getPrice()) {
        $entity['price'] = $product->getPrice();
      }
      if ($product->getSpecialPrice()) {
        $entity['special_price'] = $product->getSpecialPrice();
        if ($product->getSpecialFromDate()) {
          $entity['special_price_from_date'] = strtotime($product->getSpecialFromDate());
        }
        if ($product->getSpecialToDate()) {
          $entity['special_price_to_date'] = strtotime($product->getSpecialToDate());
        }
      }
      try {
        $entity['image_url'] = Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage());
      } catch (Exception $e) {
        Mage::log('ZAIUS: Unable to retrieve product image_url - ' . $e->getMessage());
      }
      $this->postEntity('product', $entity);
    }
  }

  public function listing($observer) {
    $helper = Mage::helper('zaius_engage');
    if ($helper->isEnabled() && $helper->isTrackProductListings()) {
      $layout = Mage::getSingleton('core/layout');
      $productListBlock = $layout->getBlock('product_list');
      if ($productListBlock) {
        foreach ($productListBlock->getLoadedProductCollection() as $product) {
          $eventData               = array();
          $eventData['action']     = 'listing';
          $eventData['product_id'] = $helper->getProductID($product->getId());
          $eventData['category']   = $this->getCurrentOrDeepestCategoryPath($product);
          Zaius_Engage_Model_BlockManager::getInstance()->addEvent('product', $eventData);
        }
      }
    }
  }

  public function detail($observer) {
    $helper = Mage::helper('zaius_engage');
    if ($helper->isEnabled()) {
      $params = $this->getParams($observer);
      $action = $this->getFullActionName($observer);
      if ($action === 'catalog_product_view') {
        $product = Mage::getModel('catalog/product')->load($params['id']);
        if ($product) {
          $eventData               = array();
          $eventData['action']     = 'detail';
          $eventData['product_id'] = $helper->getProductID($product->getId());
          $eventData['category']   = $this->getCurrentOrDeepestCategoryPath($product);
          Zaius_Engage_Model_BlockManager::getInstance()->addEvent('product', $eventData);
        }
      }
    }
  }

  public function addToCart($observer) {
    $helper = Mage::helper('zaius_engage');
    if ($helper->isEnabled()) {
      $product                 = $observer->getProduct();
      $eventData               = array();
      $eventData['action']     = 'add_to_cart';
      $eventData['product_id'] = $helper->getProductID($product->getId());
      $eventData['category']   = $this->getCurrentOrDeepestCategoryPath($product);
      $quote                   = Mage::getSingleton('checkout/session')->getQuote();
      $quoteHash               = Mage::helper('zaius_engage')->computeQuoteHashV3($quote);
      if ($quoteHash != null) {
        $eventData['cart_id']   = $quote->getId();
        $eventData['cart_hash'] = $quoteHash;
      }
      Zaius_Engage_Model_BlockManager::getInstance()->addEvent('product', $eventData);
    }
  }

  public function removeFromCart($observer) {
    $helper = Mage::helper('zaius_engage');
    if ($helper->isEnabled()) {
      $product                 = $observer->getQuoteItem()->getProduct();
      $eventData               = array();
      $eventData['action']     = 'remove_from_cart';
      $eventData['product_id'] = $helper->getProductID($product->getId());
      $eventData['category']   = $this->getCurrentOrDeepestCategoryPath($product);
      Zaius_Engage_Model_BlockManager::getInstance()->addEvent('product', $eventData);
    }
  }

  public function wishlist($observer) {
    $helper = Mage::helper('zaius_engage');
    if ($helper->isEnabled()) {
      $product                 = $observer->getProduct();
      $eventData               = array();
      $eventData['action']     = 'add_to_wishlist';
      $eventData['product_id'] = $helper->getProductID($product->getId());
      $eventData['category']   = $this->getCurrentOrDeepestCategoryPath($product);
      Zaius_Engage_Model_BlockManager::getInstance()->addEvent('product', $eventData);
    }
  }

  private function getCurrentOrDeepestCategoryPath($product) {
    $category = Mage::registry('current_category');
    if (!$category) {
      $category = $this->getDeepestCategory($product);
    }
    if ($category) {
      return Mage::helper('zaius_engage')->buildCategoryPath($category->getId());
    }
    return null;
  }

  private function getDeepestCategory($product) {
    $maxDepth = -1;
    $deepestCategory = null;
    $categoryIds = $product->getCategoryIds();
    if ($categoryIds) {
      foreach ($categoryIds as $categoryId) {
        $category = Mage::getModel('catalog/category')->load($categoryId);
        $depth = count(explode('/', $category->getPath()));
        if ($depth > $maxDepth) {
          $maxDepth = $depth;
          $deepestCategory = $category;
        }
      }
    }
    return $deepestCategory;
  }

  private function getDeepestCategoryPath($product) {
    $category = $this->getDeepestCategory($product);
    if ($category) {
      return Mage::helper('zaius_engage')->buildCategoryPath($category->getId());
    }
    return null;
  }

}
