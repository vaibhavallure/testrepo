<?php
/**
 * 
 * @author allure
 *
 */


class Allure_BulkProduct_Model_Catalog_Convert_Adapter_Product
extends Mage_Catalog_Model_Convert_Adapter_Product
{
    
    /**
     * Save product (import)
     *
     * @param  array $importData
     * @throws Mage_Core_Exception
     * @return bool
     */
    public function saveRow(array $importData)
    {
        //load category collection
        $categoryArray = array();
        $categories = Mage::getModel('catalog/category')->getCollection()
        ->addAttributeToSelect('*');
        foreach ($categories as $category){
            $categoryArray[strtolower($category->getName())] = $category->getId();
        }
        
        if (isset($importData['category_names'])) {
            $categoryNames = explode(",", $importData['category_names']);
            $categoryIds = array();
            foreach ($categoryNames as $catName){
                $categoryIds[] = $categoryArray[strtolower($catName)];
            }
            $importData['category_ids'] = join(",", $categoryIds);
        }
        
        
        return parent::saveRow($importData);
    }
}
