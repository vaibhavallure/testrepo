<?php

class Ecp_DiscoverNavigation_Model_Observer {

    public function checkMarkedCategories(Varien_Event_Observer $observer) {
        
        $event = $observer->getEvent();
//        Mage::log($event->getData(),null,'milog.log');
        $showInMenu = $event->getData('data_object')->getData('discover_mt_navigation');
        $category_id = $event->getData('data_object')->getData('entity_id');
        $category_name = $event->getData('data_object')->getData('name');
        $url_path = $event->getData('data_object')->getData('url_path');
        if(empty($url_path)){
            $category = Mage::getModel('catalog/category')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($category_id);
            $url_path = $category->getUrlpath();             
        }
        Mage::log($event->getData('data_object'),null,'milog11.log');

        $model = Mage::getModel('ecp_discovernavigation/discovernavigation')->loadByCategoryId($category_id);
        if ($showInMenu != 0) {
            $model->setData('category_name', $category_name);
            $model->setData('category_id', $category_id);
            $model->setData('url', $url_path);
            $model->setData('type', $showInMenu);
            $model->save();
        } else {
            if ($showInMenu == 0 && $model) {
                $relContact = Mage::getModel('ecp_discovernavigation/discovernavigation');
                $relContact->setId($model->getId());
                $relContact->delete();
            }
        }
    }
    
    public function onCategoryDeleteAfter(Varien_Event_Observer $observer){
        $event = $observer->getEvent();
//        Mage::log($event->getData(),null,'milog.log');
        $category_id = $event->getData('data_object')->getData('entity_id');
//        Mage::log($category_id,null,'milog.log');
        $model = Mage::getModel('ecp_discovernavigation/discovernavigation')->loadByCategoryId($category_id);
        $data = $model->getData();
        if (!empty($data)) {
            $modelToDelete = Mage::getModel('ecp_discovernavigation/discovernavigation');
            $modelToDelete->load($model->getId());
            $modelToDelete->delete();
        }
    }

    public function checkCategory(Varien_Event_Observer $observer) {
        $categoryId = (int) $observer->getControllerAction()->getRequest()->getParam('id', false);
        if (!$categoryId) {
            return false;
        }
        $category = Mage::getModel('catalog/category')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($categoryId);

        if ($category->getDiscoverMtNavigation() == Ecp_DiscoverNavigation_Model_Entity_Attribute_Source_Options::HOME) {           
            $observer->getControllerAction()->getRequest()->setParam('id',Mage::getStoreConfig('ecp_menu/menu/value'));
        }
    }

}