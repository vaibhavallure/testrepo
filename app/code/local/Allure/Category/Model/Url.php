<?php
class Allure_Category_Model_Url extends Dnd_Patchindexurl_Model_Url
{
    /**
     * Get unique category request path
     *
     * @param Varien_Object $category
     * @param string $parentPath
     * @return string
     */
    public function getCategoryRequestPath($category, $parentPath)
    {
        $storeId = $category->getStoreId();
        $idPath  = $this->generatePath('id', null, $category);
        
        if (isset($this->_rewrites[$idPath])) {
            $this->_rewrite = $this->_rewrites[$idPath];
            $existingRequestPath = $this->_rewrites[$idPath]->getRequestPath();
        }
        
        if ($category->getUrlKey() == '') {
            $urlKey = $this->getCategoryModel()->formatUrlKey($category->getName());
        }
        else {
            $urlKey = $this->getCategoryModel()->formatUrlKey($category->getUrlKey());
        }
        
        $categoryUrlSuffix = $this->getCategoryUrlSuffix($storeId);
        if (null === $parentPath) {
            $parentPath = $this->getResource()->getCategoryParentPath($category);
        }
        elseif ($parentPath == '/') {
            $parentPath = '';
        }
        $parentPath = Mage::helper('catalog/category')->getCategoryUrlPath($parentPath, true, $storeId);
        
        
        $oldParentPath = $parentPath;
        if($parentPath){
            $helper = Mage::helper("allure_category");
            if($helper->isAllowCategoryForCustomUrlChanges($category->getParentId())){
                $categories = explode("/", $parentPath);
                $isAllowParent = $helper->isAllowParentCategoryInUrl();
                $isAllowSubParent = $helper->isAllowSubParentCategoryInUrl();
                if(count($categories) == 1){
                    $parentPath = "";
                    if($isAllowParent || $isAllowSubParent){
                        $parentPath = $categories[0]."/";
                    }
                }else{
                    if($isAllowParent){
                        $parentPath = $categories[0]."/";
                    }
                    
                    if(!$isAllowSubParent){
                        $parentPath .= "";
                    }
                }
            }
        }
        
        $requestPath = $parentPath . $urlKey;
        $regexp = '/^' . preg_quote($requestPath, '/') . '(\-[0-9]+)?' . preg_quote($categoryUrlSuffix, '/') . '$/i';
        if (isset($existingRequestPath) && preg_match($regexp, $existingRequestPath)) {
            return $existingRequestPath;
        }
        
        $oldFullPath = $oldParentPath . $urlKey . $categoryUrlSuffix;
        
        $fullPath = $requestPath . $categoryUrlSuffix;
        
        if($oldFullPath != $fullPath){
            $this->_deleteOldTargetPath($fullPath, $idPath, $storeId);
        }else{
            $this->_deleteOldTargetPath($oldFullPath, $idPath, $storeId);
        }
        
        if ($this->_deleteOldTargetPath($fullPath, $idPath, $storeId)) {
            return $requestPath;
        }
        
        return $this->getUnusedPathByUrlKey($storeId, $fullPath, $this->generatePath('id', null, $category), $urlKey);
    }
}