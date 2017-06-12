<?php
class NowInStore_CatalogBuilder_CategoriesController extends Mage_Core_Controller_Front_Action
{
    public function addCategories(&$categories, $category_collection) {


    }
    public function indexAction()
    {
        $category_collection = Mage::getModel('catalog/category')
                ->getCollection()
                ->addAttributeToSelect('*')
                ->addIsActiveFilter();
        $categories = array();
        foreach($category_collection as $category) {
            array_push($categories, array(
                "id" => $category->getId(),
                "name" => $category->getName()
            ));
        }
        $jsonData = json_encode($categories);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }
}
