<?php
/**
 * @author allure
 */
class Allure_Category_Model_Observer{	
    
    public function setProductToCategory($observer){
	    $category = $observer->getEvent()->getDataObject();
	    $data = $category->getData();
	    $productData = $data['posted_products'];
	    $affectedProduct = $data['affected_product_ids'];
	    $helper = Mage::helper("inventory");
	    $parentCategoryId = $helper->getParentCategoryId();
	    $childCategoryId=$helper->getChildCategoryId();
	    try{
	        if($data['entity_id'] == $parentCategoryId){
	            $collection = Mage::getResourceModel('catalog/product_collection')
	               ->addAttributeToFilter('type_id', array('eq' => 'simple'));
	            
	            $collection->getSelect()->joinLeft(array('link_table' => $collection->getTable('catalog/product_super_link')),
	                'link_table.product_id = e.entity_id',
	                array('parent_id')
	                );
	            
	            $collection->getSelect()->join(
	                array('category_product' => 'catalog_category_product'),
	                'category_product.product_id = link_table.parent_id',
	                array('category_id')
	                );
	            $collection->getSelect()->where('category_product.category_id ='.$parentCategoryId);
	            
	            
	            $collection2 = Mage::getResourceModel('catalog/product_collection')
	            ->addAttributeToFilter('type_id', array('eq' => 'simple'));
	            
	            $collection2->getSelect()->join(
	                array('category_product' => 'catalog_category_product'),
	                'category_product.product_id = e.entity_id',
	                array('category_id')
	                );
	            $collection2->getSelect()->where('category_product.category_id ='.$parentCategoryId);
	            
	            
	            $productArr = array();
	            $arr1 = $collection->getAllIds();
	            $arr2 = $collection2->getAllIds();
	            $arr1 = array_merge($arr1,$arr2);
	            foreach ($arr1 as $id){
	                $productArr[$id] = 1;
	            } 
	            
	            if(count($arr1) > 0){
	                $categoryT = Mage::getModel('catalog/category')
	                   ->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
	                   ->load($childCategoryId);
	                $categoryT->setPostedProducts($productArr)->save();
	            }
	        }
	    }catch (Exception $e){
	    }
	    
	}
	public function setProductToChildCategory(){
	    $helper = Mage::helper("inventory");
	    $parentCategoryId = $helper->getParentCategoryId();
	    $childCategoryId=$helper->getChildCategoryId();
	    try{
	            $collection = Mage::getResourceModel('catalog/product_collection')
	            ->addAttributeToFilter('type_id', array('eq' => 'simple'));
	            
	            $collection->getSelect()->joinLeft(array('link_table' => $collection->getTable('catalog/product_super_link')),
	                'link_table.product_id = e.entity_id',
	                array('parent_id')
	                );
	            
	            $collection->getSelect()->join(
	                array('category_product' => 'catalog_category_product'),
	                'category_product.product_id = link_table.parent_id',
	                array('category_id')
	                );
	            $collection->getSelect()->where('category_product.category_id ='.$parentCategoryId);
	            
	            
	            $collection2 = Mage::getResourceModel('catalog/product_collection')
	            ->addAttributeToFilter('type_id', array('eq' => 'simple'));
	            
	            $collection2->getSelect()->join(
	                array('category_product' => 'catalog_category_product'),
	                'category_product.product_id = e.entity_id',
	                array('category_id')
	                );
	            $collection2->getSelect()->where('category_product.category_id = '.$parentCategoryId);
	            
	            
	            $productArr = array();
	            $arr1 = $collection->getAllIds();
	            $arr2 = $collection2->getAllIds();
	            $arr1 = array_merge($arr1,$arr2);
	            foreach ($arr1 as $id){
	                $productArr[$id] = 1;
	            }
	            
	            if(count($arr1) > 0){
	                $categoryT = Mage::getModel('catalog/category')
	                ->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
	                ->load($childCategoryId);
	                $categoryT->setPostedProducts($productArr)->save();
	            }
	     
	    }catch (Exception $e){
	    }
	    
	}
	    
}
