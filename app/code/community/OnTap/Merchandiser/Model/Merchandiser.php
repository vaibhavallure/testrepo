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
class OnTap_Merchandiser_Model_Merchandiser
{
    /**
     * _data
     * 
     * (default value: array())
     * 
     * @var array
     */
    protected $_data = array();
    
    /**
     * moveInStockToTheTop
     * 
     * @param mixed $params
     * @return void
     */
    public function moveInStockToTheTop($params)
    {
        $catId = $params['catId'];
        $merchandiserResourceModel = Mage::getResourceModel('merchandiser/merchandiser');
        $outStockProducts = $merchandiserResourceModel->getOutofStockProducts($catId);
        
        $maxPosition = $merchandiserResourceModel->getMaxInstockPositionFromCategory($catId);
        
        if (count($outStockProducts)) {
            foreach ($outStockProducts as $outStockProduct) {
                $outStockProductId = $outStockProduct['product_id'];
                $merchandiserResourceModel->updateProductPosition($catId, $outStockProductId, ++$maxPosition);
            }
        }
    }
   
    /**
     * moveSaleAtTop
     * 
     * @param mixed $params
     * @return void
     */
    public function moveSaleAtTop($params)
    {
        $catId = $params['catId'];
        $merchandiserResourceModel = Mage::getResourceModel('merchandiser/merchandiser');
        $readResult = $merchandiserResourceModel->getSaleCategoryProducts($catId, "DESC");
        $position = 1;
        foreach ($readResult as $row) {
            $merchandiserResourceModel->updateProductPosition($catId, $row['product_id'], $position);
            $position++;
        }
    }
    
    /**
     * moveSaleAtBottom
     * 
     * @param mixed $params
     * @return void
     */
    public function moveSaleAtBottom($params)
    {
        $catId = $params['catId'];
        $merchandiserResourceModel = Mage::getResourceModel('merchandiser/merchandiser');
        $readResult = $merchandiserResourceModel->getSaleCategoryProducts($catId, "ASC");
        $position = 1;
        foreach ($readResult as $row) {
            $merchandiserResourceModel->updateProductPosition($catId, $row['product_id'], $position);
            $position++;
        }
    } 
    
    /**
     * affectCategoryBySmartRule
     * 
     * @param mixed $categoryId
     * @return void
     */
    public function affectCategoryBySmartRule($categoryId)
    {
        $merchandiserResourceModel = Mage::getResourceModel('merchandiser/merchandiser');
        $insertData = array();
        $allocatedProducts = array();
        $iCounter = 1;
        
        $categoryValues = $merchandiserResourceModel->getCategoryValues($categoryId);
        if ($categoryValues['smart_attributes'] == "") {
            $categoryValues['ruled_only'] = 0;
        }
        
        $categoryProductsResult = $merchandiserResourceModel->getCategoryProduct($categoryId);
        $positionsArray = array();
        foreach ($categoryProductsResult as $categoryProductPostions) {
            $positionsArray[$categoryProductPostions['product_id']] = $categoryProductPostions['position'];
        }
        
        asort($positionsArray);
        $productPositions = $positionsArray;
        $productPositions = array_keys($productPositions);
        
        $categoryProducts = array_map(
            array($this, 'categoryProductsMap'),
            $categoryProductsResult
        );
        
        $heroProducts = $categoryValues['heroproducts'];
        $productObject = Mage::getModel('catalog/product');
                
        foreach (explode(",", $heroProducts) as $heroSKU) {
            if ($heroSKU != '' && $productId = $productObject->getIdBySku(trim($heroSKU))) {
                if ($productId > 0) {
                    if (!in_array($productId, $allocatedProducts)) {
                        $allocatedProducts[] = $productId;
                        unset($positionsArray[$productId]);
                        $insertData[] = array(
                            'category_id' => $categoryId,
                            'product_id' => $productId,
                            'position' => $iCounter
                        );
                        $iCounter++;
                    }
                }
            }
        }
        
        $addTo = Mage::helper('merchandiser')->newProductsHandler(); // 1= TOP , 2 = BOTTOM
        $addTo = $addTo < 1 ? 1 : $addTo;
        
        $categoryProducts = array_diff($categoryProducts, $allocatedProducts);
        $ruledProductIds = Mage::helper('merchandiser')->smartFilter($categoryId, $categoryValues['smart_attributes']);
        $ruledProductCount = $iCounter;
        
        if (sizeof($ruledProductIds) > 0) {
            $normalProductCount = sizeof($positionsArray) > 0 ? max($positionsArray) : 0;
            $differenceFactor = $iCounter - $normalProductCount;
            if ($differenceFactor <= 0) {
                $differenceFactor = 1;
            }
            if ($addTo == 2 && $categoryValues['ruled_only'] == 0) {
                 $ruledProductCount = $differenceFactor + $normalProductCount;
            }
            foreach ($ruledProductIds as $productId) {
                if (!in_array($productId, $allocatedProducts)) {
                    $allocatedProducts[] = $productId;
                    if ($addTo == 2) {
                        unset($positionsArray[$productId]);
                    }
                    $insertData[] = array(
                        'category_id' => $categoryId,
                        'product_id' => $productId,
                        'position' => $ruledProductCount
                    );
                    $ruledProductCount++;
                }
            }
        }
        
        if ($addTo == 1) {
            $iCounter = $ruledProductCount;
        }
        
        if ($categoryValues['ruled_only'] == 0) {
            if (sizeof($categoryProducts) > 0) {
                $incrementFactor = $iCounter - min($positionsArray);
                if ($incrementFactor < 0) {
                    $incrementFactor = 0;
                }
                foreach ($categoryProducts as $productId ) {
                    if (!in_array($productId, $allocatedProducts)) {
                        $allocatedProducts[] = $productId;
                        $currentPosition = ($positionsArray[$productId] > 0) ? $positionsArray[$productId] : 0;
                        $currentPosition += $incrementFactor;
                        $insertData[] = array(
                            'category_id' => $categoryId,
                            'product_id' => $productId,
                            'position' => $currentPosition
                        );
                    }
                }
            }
        }
        
        if (sizeof($insertData)>0) {
            $this->manipulateCategoryProducts($insertData, $categoryId);
        }
    }
    
    public function manipulateCategoryProducts($insertData, $categoryId){
    	$merchandiserResourceModel = Mage::getResourceModel('merchandiser/merchandiser');
    	$addedproducts = array();
    	foreach ($insertData as $data){
    		$addedproducts[] = $data['product_id'];
    		$merchandiserResourceModel->manipulateRow($data);
    	}
    	$merchandiserResourceModel->removeRestProducts($addedproducts, $categoryId);
    }

    
    /**
     * categoryProductsMap
     * 
     * @param mixed $value
     * @return string
     */
    public function categoryProductsMap($value)
    {
        if (is_array($value) && isset($value['product_id'])) {
            return $value['product_id'];
        }
    }
    
    /**
     * getCategoryValues
     * 
     * @param mixed $categoryId
     * @param mixed $field (default: null)
     * @return string
     */
    public function getCategoryValues($categoryId, $field = null)
    {
        return Mage::getResourceModel('merchandiser/merchandiser')->getCategoryValues($categoryId, $field);
    }
    
    /**
     * clearEntityCache
     * 
     * @param Mage_Core_Model_Abstract $entity
     * @param array $ids
     * @return void
     */
    public function clearEntityCache(Mage_Core_Model_Abstract $entity, array $ids)
    {
        $cacheTags = array();
        foreach ($ids as $entityId) {
            $entity->setId($entityId);
            $cacheTags = array_merge($cacheTags, $entity->getCacheIdTags());
        }
        if (!empty($cacheTags)) {
            Enterprise_PageCache_Model_Cache::getCacheInstance()->clean($cacheTags);
        }
    }
    
    /**
     * manipulateCategoryProductsAfterCategorySave
     * 
     * @param int $categoryId
     * @param array $insertData
     * @return void
     */
    
    public function manipulateCategoryProductsAfterCategorySave($insertData, $categoryId){
    	$merchandiserResourceModel = Mage::getResourceModel('merchandiser/merchandiser');
    	$addedproducts = array();
    	foreach ($insertData as $data){
    		$addedproducts[] = $data['product_id'];
    		$merchandiserResourceModel->manipulateRowCategorySave($data);
    	}
    	$merchandiserResourceModel->removeRestProducts($addedproducts, $categoryId);
    }
}