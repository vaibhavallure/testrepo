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
 * @category    OnTap
 * @package     OnTap_Merchandiser
 * @copyright   Copyright (c) 2014 On Tap Networks Ltd. (http://www.ontapgroup.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OnTap_Merchandiser_Block_Category_List extends Mage_Core_Block_Template
{
    const MIN_HEIGHT = 300;
    const MIN_HEIGHT_PER_ITEM = 15;

    /**
     * getHeroBoxHtml
     * 
     * @return string or false
     */
    public function getHeroBoxHtml()
    {
        $model = Mage::getModel('merchandiser/merchandiser');
        $heroProducts = $model->getCategoryValues($this->getCategoryId(), 'heroproducts');
       
     
        if (trim($heroProducts) == "") {
            return false;
        }
        
        $productObject = Mage::getModel('catalog/product');
        $iCounter = 0;
        $html = array();
        foreach (explode(",", $heroProducts) as $heroSku) {
            $iCounter++;
            if ($productId = $productObject->getIdBySku(trim($heroSku))) {
                $productBox =  $this->getLayout()
                    ->createBlock('merchandiser/adminhtml_catalog_product_list')
                    ->setTemplate('merchandiser/new/category/heroproductbox.phtml');
                    
                $productBox->setPid($productId);
                $productBox->setCurrentPosition($iCounter);
                
                $html[] = $productBox->toHtml();
            }
        }
        echo $html;
        return count($html) > 0 ? $html : false;
    }

    /**
     * getProductBoxHeight
     * 
     * @return int
     */
    public function getProductBoxHeight()
    {
        $attrCodeCount = Mage::helper('merchandiser')->getAttributeCodesCount();
        return self::MIN_HEIGHT + ($attrCodeCount * self::MIN_HEIGHT_PER_ITEM);
    }

    /**
     * getAjaxHtml
     * 
     * @return string
     */
    public function getAjaxHtml()
    {
        $currentPage = $this->getRequest()->getParam('current_page');
        $pageLimit = $this->getRequest()->getParam('extra_products');
        
        $productCollection = $this->getProductCollection();
        $clonedCollection = clone $productCollection;
        $collectionSize = sizeof($clonedCollection);
        $productCollection->setPage($currentPage, $pageLimit);
        
        $currentPosition = ((int)$currentPage-1) * (int)$pageLimit + 1;
        
        $html = "";
                
        if (0 < $productCollection->count()) {
        	if((($currentPage-1)*$pageLimit) >= $collectionSize){
        		return $html;
        	}
            foreach ($productCollection as $_product) {
                $productBox =  $this->getLayout()
                    ->createBlock('merchandiser/adminhtml_catalog_product_list')
                    ->setTemplate('merchandiser/new/category/productbox.phtml');
                    
                $productBox->setPid($_product->getId());
                $productBox->setCurrentPosition($currentPosition);
                $html .= $productBox->toHtml();
            }
        } else {
            $html = Mage::helper('merchandiser')->__('false');
        }
        
        return $html;
    }

    /**
     * getCategoryProductCollection
     * 
     * @param int $catId
     * @param int $storeId (default: null)
     * @return Varien_Data_Collection
     */
    public function getCategoryProductCollection($catId, $storeId=null)
    {
        if (is_numeric($catId)) {
            $collection = Mage::getSingleton('merchandiser/search')
                ->addCategoryFilter($catId)
                ->getProductCollection()
                ->setStoreId($storeId);
               
            
             //Added by Allure #MT-247
           // $collection->addAttributeToFilter('type_id', array('neq' => 'simple'));
          
        } else {
            $collection = $this->_getProductCollection();
        }
        return $collection;
    }

    /**
     * getCategory
     * 
     * @return array
     */
    public function getCategory()
    {
        if (!$this->getData('category')) {
            if ($this->getCategoryId()) {
                if ($category = Mage::getModel('catalog/category')->load($this->getCategoryId())) {
                    $this->setData('category', $category);
                }
            }
        }
        return $this->getData('category');
    }
    
    /**
     * getCategoryId
     * 
     * @return int or null
     */
    public function getCategoryId()
    {
        $categoryId = $this->getRequest()->getParam('category_id');
        return is_numeric($categoryId) ? (int)$categoryId : null;
    }
    
    /**
     * getProductCollection
     * 
     * @return Varien_Data_Collection
     */
    public function getProductCollection()
    {
        $products = Mage::getModel('catalog/category')->load($this->getCategoryId())
            ->getProductCollection()
            ->addAttributeToSelect('*');
        
        //Added by Allure #MT-247 ,MT-548
        
        $catIds=Mage::getStoreConfig("merchandiser/options/hide_simple_products");
        if(isset($catIds))
            $catIds=explode(',', $catIds);
            
        //$catIds=array(64,4,13,14,15,16,17,18,19);
        
        if (in_array($this->getCategoryId(), $catIds))
            $products->addAttributeToFilter('type_id', array('neq' => 'simple'));
        
        $heroProducts = Mage::getModel('merchandiser/merchandiser')
            ->getCategoryValues($this->getCategory()->getId(), 'heroproducts');
        if ($heroProducts != '') {
            $products->addFieldToFilter('sku', array('nin' => array_map('trim', explode(",", $heroProducts))));
        }
        $products->getSelect()->order('cat_pro.position ASC');
        
        
       	if(Mage::helper('merchandiser')->isHideNoStockProducts() && Mage::helper('merchandiser')->isHideInvisibleProducts()){
       		$products->addAttributeToFilter('visibility', array('or' => array(4,2)));
       		Mage::getResourceModel('merchandiser/merchandiser')->addZeroStockFilter($products);
        }elseif (Mage::helper('merchandiser')->isHideInvisibleProducts()) {
            $products->addAttributeToFilter('visibility', array('or' => array(4,2)));
        }
        
        if (Mage::helper('merchandiser')->isHideDisabledProducts()) {
            $products->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        }
        return $products;
    }
}
