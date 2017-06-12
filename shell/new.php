<?php

require_once 'abstract.php';

class newProducts extends Mage_Shell_Abstract {
    protected  $catId = 64;
    
    public function run(){       
        $todayStartOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('00:00:00')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        
        $todayEndOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('23:59:59')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $collection = Mage::getModel('catalog/product')->getCollection();
        $products = $collection->addAttributeToSelect('*')
            ->addAttributeToFilter('news_from_date', array('or'=> array(
                0 => array('date' => true, 'to' => $todayEndOfDayDate),
                1 => array('is' => new Zend_Db_Expr('null')))
            ), 'left')
            ->addAttributeToFilter('news_to_date', array('or'=> array(
                0 => array('date' => true, 'from' => $todayStartOfDayDate),
                1 => array('is' => new Zend_Db_Expr('null')))
            ), 'left')
            ->addAttributeToFilter(
                array(
                    array('attribute' => 'news_from_date', 'is'=>new Zend_Db_Expr('not null')),
                    array('attribute' => 'news_to_date', 'is'=>new Zend_Db_Expr('not null'))
                    )
              )
            ->addAttributeToSort('news_from_date', 'desc');   
        $prodIds = $products->getAllIds();        
        $this->cleanCateogy($prodIds);
        
        
        foreach($prodIds as $pid){
            $product = Mage::getModel('catalog/product')->load($pid);            
            $typeId = $product->getTypeId();  
            Mage::Log($product->getEntityId(),null,'test.log');
            $this->addProductToCategory($product,$this->catId);  
            /*
            if($typeId == 'configurable'){
                    $childProducts = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($pid);                      
                    if(count($childProducts[0])){     
                        foreach($childProducts[0] as $id){
                            $simpleProduct = Mage::getModel('catalog/product')->load($id);
                            $this->addProductToCategory($simpleProduct,$this->catId);
                        }
                    }
           }
           * 
           */
                        
        }
        
    }
    
   public function addProductToCategory($product,$catId){
        $cats = $product->getCategoryIds();
        if(!in_array($catId, $cats)){
            $cats[] = $catId;
            $product->setCategoryIds($cats)->save();
            Mage::Log($product->getEntityId(),null,'test.log');
       
        }
   }
   
   public function removeProductToCategory($product,$catId){
        $cats = $product->getCategoryIds();
        if(in_array($catId, $cats)){
            foreach($cats as $key => $id){
                if($id == $catId) {
                    unset($cats[$key]);
                    break;
                }
            }
            $product->setCategoryIds($cats)->save();
        }
   }
   
   
   public function cleanCateogy($prodIds){
       $category = Mage::getModel('catalog/category')->load($this->catId);
       $category_pids = $category->getProductCollection()->getAllIds();       
       $remove = array_diff($category_pids, $prodIds);      
       if(count($remove)){
           foreach($remove as $id){
               $product = Mage::getModel('catalog/product')->load($id);
               $this->removeProductToCategory($product,$this->catId);
           }
       }
   }
   public function test(){
       Ecp_Category_Model_Observer::newProducts();
   }
   
}
$update = new newProducts();
$update->run();
//$update->test();
?>