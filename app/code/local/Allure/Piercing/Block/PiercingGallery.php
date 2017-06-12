<?php

class Allure_Piercing_Block_PiercingGallery extends Mage_Core_Block_Template
{

    private $_piercingCategories = array();
    private $_currentLevel = 0;
    
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function getPiercingGallery()     
    { 
        if (!$this->hasData('piercinggallery')) {
            $this->setData('piercinggallery', Mage::registry('piercinggallery'));
        }
        return $this->getData('piercinggallery');
        
    }
    
    public function _init(){
        $this->_currentLevel = 0;
        
        $piercingCat = Mage::getModel('catalog/category')->getCollection()->addAttributeToFilter('name','PIERCING')->getData();

        $piercingSubCat = Mage::getModel('catalog/category')->getCollection()
                ->addAttributeToSelect('name')->addFieldToFilter('parent_id',$piercingCat[0]['entity_id'])
                ->addFieldToFilter('is_active', array('eq'=>'0'))
                ->addAttributeToSort('position', 'ASC')
                ->getData();

        $contador = $contador2 = 0;
        foreach($piercingSubCat as $subcat){
            $subcategory = Mage::getModel('catalog/category')->load($subcat['entity_id'])->getData();
            $this->_piercingCategories[$subcategory['name']] = array();
            $this->_piercingCategories[$subcategory['name']]['name'] = $subcategory['name'];

            $locations = Mage::getModel('catalog/category')->getCollection()
                ->addAttributeToSelect('*')->addFieldToFilter('parent_id',$subcategory['entity_id'])->addAttributeToSort('position', 'ASC');
            
            foreach($locations as $location){

                $current = Mage::registry('current_piercing_location');
                if($contador == 0 && empty($current)){
                    Mage::register('current_piercing_location',$location->getId());
                    $this->_currentLevel = $contador2;
                }
                
                $contador++;
                
                if(Mage::registry('current_piercing_location')==$location->getId()){
                    $this->_currentLevel = $contador2;
                }
                    
                $this->_piercingCategories[$subcategory['name']]['locationtype'][$location->getId()] = array();
                $this->_piercingCategories[$subcategory['name']]['locationtype'][$location->getId()]['name'] = $location->getName();
                $this->_piercingCategories[$subcategory['name']]['locationtype'][$location->getId()]['id'] = $location->getEntityId();
                                                
            }
            
            $contador2++;
        }
    }
    
    public function getLooksByLocation($locationId){
//        $looks = Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect('*')->addFieldToFilter('parent_id',$location->getId());
       $looks = Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect('*')->addFieldToFilter('parent_id',$locationId)
           ->addFieldToFilter('is_active',1)
           ->addFieldToFilter('include_in_menu',1)
           ->addAttributeToSort('position','asc');
       $array = array();
       
       foreach($looks as $look){
            $productsLooks = array();
            
            $productsLooks['name'] = $look->getName();
            $productsLooks['img'] = $look->getImageUrl();
            $productsLooks['id'] = $look->getId();
//            $this->_piercingCategories[$subcategory['name']]['locationtype'][$location->getName()]['looks'][$look->getName()]['name'] = $look->getName();
//            $this->_piercingCategories[$subcategory['name']]['locationtype'][$location->getName()]['looks'][$look->getName()]['img'] = $look->getImageUrl();

            $products = $look->getProductCollection()
                    ->addAttributeToFilter('visibility' , array('neq'=>Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
            $arrayProducts = array();
            
            foreach ($products as $product) {                 
                 $arrayProducts[] = $product;
            }
            
            $productsLooks['products'] = $arrayProducts;
            $array[] = $productsLooks;
//            $this->_piercingCategories[$subcategory['name']]['locationtype'][$location->getName()]['looks'][$look->getName()]['products'] = $productsLooks;
        }
        
        return $array;
    }
    
    public function getCategoriesArray(){
        if(empty($this->_piercingCategories))
            $this->_init();
        
        return $this->_piercingCategories;
    }

    public function getProductPriceBlock($product) {
        Mage::unregister('product');
        Mage::unregister('current_product');
        Mage::register('product',$product);
        Mage::register('current_product',$product);
        return $this->getLayout()->createBlock(
                'Mage_Catalog_Block_Product_Price', 
                'price', 
                array('template' => 'catalog/product/price.phtml')
        );
    }
    
    public function getJsonConfig($product){
        $productView = $this->getLayout()->createBlock(
                'Mage_Catalog_Block_Product_View', 
                'product_view', 
                array('template' => '')
        )->setProduct($product);
        
        return $productView->getJsonConfig();
    }
    
    public function getAttributesBlocks($product) {
        $options = $this->getLayout()->createBlock(
                'Mage_Catalog_Block_Product_View_Type_Configurable', 
                'options_configurable', 
                array('template' => 'catalog/product/view/type/options/configurable.phtml')
        );
        
        $options->setProduct($product);
        return $options;
    }
    
    public function getAddToCartBlock($product){
        $addToCart = $this->getLayout()->createBlock(
                'Mage_Catalog_Block_Product_View', 
                'options_configurable', 
                array('template' => 'catalog/product/view/addtocart.phtml')
        );
        return $addToCart;
    }
    
    public function getAddToBlock($product){
        $addToCart = $this->getLayout()->createBlock(
                'Mage_Catalog_Block_Product_View', 
                'options_configurable', 
                array('template' => 'catalog/product/view/addto.phtml')
        );
        return $addToCart;
    }
    
    public function getCurrentLevel(){
        return $this->_currentLevel;
    }
    
    public function getBreadcrumbs(){
        $tmp = $this->getLayout()->getBlock('breadcrumbs');
        $piercing = Mage::getModel('catalog/category')->load(Mage::getStoreConfig('ecp_piercinggallery/piercinggallery/piercing_category_id'));
        $locationtype = $this->getRequest()->getParam('locationtype',null);
		$piercing_galary = Mage::getModel('catalog/category')->load(Mage::getStoreConfig('ecp_piercinggallery/piercinggallery/piercing_gallery_category_id'));
        $tmp->addCrumb('home', array('label'=>Mage::helper('cms')->__('Home'), 'title'=>Mage::helper('cms')->__('Home Page'), 'link'=>Mage::getBaseUrl()));
        $tmp->addCrumb('piercing', array('label'=>$piercing->getName(), 'title'=>$piercing->getName(), 'link'=>$piercing->getUrl()));
        if( $locationtype) {
            $locationtype_category = $piercing = Mage::getModel('catalog/category')->load( $locationtype);
            $tmp->addCrumb('piercinggal', array('label'=>$piercing_galary->getName(), 'title'=>$piercing_galary->getName(), 'link'=>$piercing_galary->getUrl()));
            $tmp->addCrumb('locationtype', array('label'=>$locationtype_category->getName(), 'title'=>$locationtype_category->getName()));
        } else {
            $tmp->addCrumb('piercinggal', array('label'=>$piercing_galary->getName(), 'title'=>$piercing_galary->getName()));
        }
        return $this->getLayout()->getBlock('breadcrumbs')->toHtml();
    }
    
    public function getGalleryList(){
    	$helper = Mage::helper('allure_piercing');
    	$collection = $helper->getShopCollection();
    	$categories = $this->getCategoriesArray();
    	$main = array();
    	$_cat = array();
    	/* foreach ($categories as $cat){
    		$name = "#".$cat['name'];
    		$temp = array();
    		$tempCat = array();
    		foreach ($cat['locationtype'] as $location){
    			$subName = "#".$location['name']."";
    			foreach ($collection as $feed){
    				$text = $feed->getCaption();
    				if($helper->checkSubstring($text,$subName)){
    					if(!key_exists($location['name'], $temp)){
    						//$main[$cat['name']]=$cat['name'];
    						$tempCat[] = $location['name'];
    						//$temp[] = array($location['name']=>$helper->getProducts($subName));
    						$temp[$location['name']] = $helper->getProducts($subName);
    					}
    				}
    			}
    		}
    		if(!empty($temp)){
    			$main[]=array($cat['name']=>$temp);
    			$_cat[]=array($cat['name']=>$tempCat);
    		}
    	} */
    	
    	
    	$catt = array();
    	$temp1 = array();
    	foreach ($collection as $_feed){
    		$temp = array();
    		$tempCat = array();
    		$text = $_feed->getCaption();
    		$str = $helper->getShopCat($text);
    		
    		$arr = array();
    		if(!empty($str)){
    			$str = str_replace('#', '', $str);
    			$arr = explode(" ", $str);
    			if(count($arr)>=2){
		    		$name = $arr[0];
		    		$subName = $arr[1];
		    		foreach ($collection as $feed){
		    			$text = $feed->getCaption();
		    			$str1 = $helper->getShopCat($text);
		    			
		    			$arr1 = array();
		    			if(!empty($str1)){
			    			$str1 = str_replace('#', '', $str1);
			    			$arr1= explode(" ", $str1);
			    			if(count($arr1)>=2){
			    				$name1 = $arr1[0];
			    				$subName1 = $arr1[1];
			    			
				    			if($name==$name1){
					    			if($helper->checkSubstring($text,"#".$name1." #".$subName1)){
					    				if(!key_exists($subName1, $temp)){
					    					$tempCat[] = $name;
					    					$temp[$subName1] = $helper->getProducts("#".$name1. " #".$subName1);
					    				}
				    				}	
			    				}
			    			}
		    			}
		
			    		/* if(!empty($temp)){
			    			if(!key_exists($name, $main))
			    				$main[]=array($name=>$temp);
			    			$_cat[]=array($name=>$tempCat);
			    		} */
	    			}
	    			
	    			if(!empty($temp)){
	    				if(!key_exists($name, $main)){
	    					$main[$name]=array($name=>$temp);
	    				}
	    				$_cat[]=array($name=>$tempCat);
	    			}
	    			
    			}
    		}
    	}
    	/* 
    	$main1 = array(); 
    	$t = array();
    	foreach ($main as $key=>$sub){
    		foreach ($sub as $key1=>$sub1){
    			foreach ($sub as $key2=>$sub2){
    				if($key1==$key2){	
    					$t[] = $sub1;
    				}
    			}
    			if(!key_exists($key1, $main1)){
    				$main1[$key1] = $t;
    			}
    		}
    	}
    	echo  json_encode($main1);
    	echo  json_encode($main);die; */
    	return $main;
    }
    
}