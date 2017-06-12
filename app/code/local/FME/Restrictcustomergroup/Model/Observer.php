<?php

class FME_Restrictcustomergroup_Model_Observer {
    #code

    protected $_helper;
    protected $_idsByType;
    protected $_ruleType;

    public function __construct() {

        if (Mage::app()->getStore()->isAdmin()) {
            return;
        }

        $this->_helper = Mage::helper('restrictcustomergroup');
        $this->_ruleType = $this->_helper->getRestrictionType();
    }

    /**
     * @event: controller_action_layout_generate_blocks_after
     * restricting by redirecting according to current url
     * */
    public function restrictByType(Varien_Event_Observer $observer) {

        if ($this->_ruleType == 'manual') {
            $ruleIds = array();
            $block = new FME_Restrictcustomergroup_Block_Restrictcustomergroup();
            $_rulesCollection = $block->getRules($this->_ruleType); // rules collection

            if ($_rulesCollection->count() > 0) {
                $currentUrl = Mage::helper('core/url')->getCurrentUrl();
                $redirectTo = array();
                $ruleIds = array();

                foreach ($_rulesCollection as $rule) {
                    $serToArr = unserialize($rule->getManualUrlRedirect()); //echo '<pre>';print_r($serToArr);exit;
                    // check current url for each saved condition
                    if (in_array($currentUrl, array_keys($serToArr))) {
                        $ruleIds[] = $rule->getId(); //get rule id when matched
                        $redirectTo[$rule->getId()] = $serToArr[$currentUrl]; // get correspondent url for the match
                    }
                    // add priority if the rule id more than 1
                    if (!empty($ruleIds)) {
                        $_rulesCollection->addFieldToSelect('rule_id'); // select only rule id
                        $_rulesCollection->addFieldToFilter('main_table.rule_id', array('in' => $ruleIds))
                                ->setOrder('main_table.priority', 'ASC');
                        $_rulesCollection->getSelect()->limit(1); //echo '<pre>';print_r($_rulesCollection->getData());exit;
                        $data = $_rulesCollection->getData();
                        $id = $data[0]['rule_id'];
                        Mage::app()
                                ->getFrontController()
                                ->getResponse()
                                ->setRedirect($redirectTo[$id]); // redirect
                    }
                }
            }
        }
    }

    /**
     * @event: catalog_product_collection_load_after
     * filtering catalog product list anywhere
     */
    public function filterByRule(Varien_Event_Observer $observer) {

        if (Mage::app()->getStore()->isAdmin()) {
            return;
        }

        if ($this->_ruleType == 'manual') {
            return false;
        }

        $_currentCategory = null;
        if (Mage::registry('current_category')) {
            $_currentCategory = Mage::registry('current_category');
        }

        $pageNum = Mage::getBlockSingleton('catalog/product_list_toolbar')->getCurrentPage(); // echo $pageNum;
        $currentMode = Mage::getBlockSingleton('catalog/product_list_toolbar')->getCurrentMode();
        $pageSize = Mage::getBlockSingleton('catalog/product_list_toolbar')->getLimit(); // echo $pageSize;
        /**
          request value for limit Mage::app()->getRequest()->getParam('limit');
          using pager html limit. won't work for catalog
          $pageSize = Mage::getBlockSingleton('page/html_pager')->getLimit(); echo $pageSize;
          requesting current mode
          if($this->getMode()!= 'grid') {
          $limit = Mage::getStoreConfig('catalog/frontend/list_per_page');
          }
          else {
          $limit = Mage::getStoreConfig('catalog/frontend/grid_per_page');
          }
         */
//        $_productCollection = $observer->getCollection()
//                ->addStoreFilter()
//                ->setPage($pageNum, $pageSize); // echo '<pre>';print_r($_productCollection->getData());exit;
//
//        if ($_currentCategory != null) {
//            $_currentCategory = Mage::getModel('catalog/category')
//                    ->load(Mage::registry('current_category')->getId());
//            $_productCollection = $observer->getCollection()
//                    ->addStoreFilter()
//                    ->addCategoryFilter($_currentCategory)
//                    ->setPage($pageNum, $pageSize);
//        }
//
//        //echo $_productCollection->count();exit;
//        //current product collection
//
//        if (!empty($_excludeProducts)) {
//            /* filtering collection */
//            //$_productCollection = $observer->getEvent()->getCollection();
//            $observer->getCollection()
//                    ->addFieldToFilter('entity_id', array('nin' => array_keys($_excludeProducts)))
//                    ->clear()
//                    ->load(); //->setPage($pageNum, $pageSize);
//        }

        $productData = $observer->getEvent()
                ->getCollection()
                ->getData(); // $_productCollection->getData();
        //>setPage($currentPage, 6); //echo $_productCollection->count();
        //current product collection
        $_excludeProducts = $this->_getExcludedProductIds($productData);
//        echo '<pre>';
//        print_r($_excludeProducts);
//        exit;
        if (empty($_excludeProducts)) {
            return;
        }
        /* filtering collection */ //echo '<pre>';print_r(array_values($observer->getEvent()->getData()));exit;
        $observer->getEvent()
                ->getCollection()
                ->addIdFilter(array_unique($_excludeProducts), true)
                //->addFieldToFilter('entity_id', array('nin' => array_unique($_excludeProducts)))
                ->setPageSize($pageSize - count($_excludeProducts))
                ->setCurPage($pageNum);
    }

    protected function _getExcludedProductIds($products) {

        $exclude = array();
        foreach ($products as $item) {

            $pid = $item['entity_id'];
            $rules = $this->_fetchRulesByProduct($pid); //echo '<pre>';print_r($rules);//exit;

            if (count($rules->getData()) > 0) {

                $_r = $rules->getData();
                $exclude[$pid] = $_r[0]['rule_id'];
            }
        }

        return array_keys($exclude);
    }

    protected function _fetchRulesByProduct($productId) {

        if ($this->_ruleType == 'manual') {
            return false;
        }

        $ruleIds = array();
        $block = new FME_Restrictcustomergroup_Block_Restrictcustomergroup();
        $_rulesCollection = $block->getRules($this->_ruleType); // rules collection

        if ($_rulesCollection->getSize() > 0) {

            foreach ($_rulesCollection as $rule) {

                $model = Mage::getModel('restrictcustomergroup/restrictcustomergroup_product_rulecss');
                $model->setWebsiteIds(Mage::app()->getStore()->getWebsite()->getId());
                /* in case if afterload didn't objectify the rules */
                if ($rule["condition_serialized"] != '') {

                    if (!$rule['condition_serialized'] instanceof Varien_Object) {

                        $str = $rule['condition_serialized'];
                        $rule['condition_serialized'] = unserialize($str);
                        $rule['condition_serialized'] = new Varien_Object($rule['condition_serialized']);
                        //echo '<pre>';print_r($rule['condition_serialized']);
                    }

                    $conditions = $rule["condition_serialized"]->getConditions(); //echo '<pre>';print_r($conditions);

                    if (isset($conditions['css'])) {
                        $match = array();
                        $model->getConditions()
                                ->loadArray($conditions, 'css');
                        $match = $model->getMatchingProductIds();

                        if (in_array($productId, $match)) {
                            $ruleIds[] = $rule["rule_id"];
                        }
                    } else {

                        $match = $model->setReturnMode(2) //set return mode to apply on all
                                ->getMatchingProductIds();  

                        if (in_array($productId, $match)) {

                            $ruleIds[] = $rule["rule_id"];
                        }
                    }
                }
            }// end foreach
        }

        $_rulesCollection->addFieldToFilter('main_table.rule_id', array('in' => $ruleIds))
                ->setOrder('main_table.priority', 'ASC');
        $_rulesCollection->getSelect()
                ->limit(1);

        return $_rulesCollection;
    }

    /**
     * @event: cms_page_render
     * restricting cms pages
     */
    public function restrictCmsPage(Varien_Event_Observer $observer) {

        if (Mage::app()->getStore()->isAdmin()) {
            return;
        }

        if ($this->_ruleType == 'manual') {
            return false;
        }

        $event = $observer->getEvent();
        $page = $event->getPage(); //echo '<pre>';print_r($page->getData());exit;
        $pageName = $page->getIdentifier();
        $block = new FME_Restrictcustomergroup_Block_Restrictcustomergroup();
        $_rulesCollection = $block->getRules($this->_ruleType); // rules collection
        $ruleToApply = array();

        foreach ($_rulesCollection as $rule) {
            if (strpos($rule->getCmsPages(), $pageName) !== false) {
                $ruleToApply[] = $rule->getId();
            }
        }

        if (!empty($ruleToApply)) {
            $_rulesCollection->addFieldToFilter('main_table.rule_id', array('in' => $ruleToApply))
                    ->setOrder('main_table.priority', 'ASC');
            $_rulesCollection->getSelect()
                    ->limit(1);  //echo '<pre>';print_r($_rulesCollection->getData());exit;

            echo Mage::app()->getLayout()
                    ->createBlock('restrictcustomergroup/restrictcustomergroup')
                    ->setRulesCollection($_rulesCollection)
                    ->setTemplate('restrictcustomergroup/restrictcustomergroup.phtml')
                    ->toHtml();
            exit;
        }
    }

    /**
     * @event: controller_action_predispatch
     * restricting other pages
     * */
    public function restrictOtherPages(Varien_Event_Observer $observer) {

        if (Mage::app()->getStore()->isAdmin()) {
            return;
        }

        if ($this->_ruleType == 'manual') {
            return false;
        }

        $currentModule = Mage::app()
                ->getFrontController()
                ->getRequest()
                ->getModuleName();

        $event = $observer->getEvent();
        $block = new FME_Restrictcustomergroup_Block_Restrictcustomergroup();
        $_rulesCollection = $block->getRules($this->_ruleType); // rules collection
        $ruleToApply = array();

        foreach ($_rulesCollection as $rule) {
            if (strpos($rule->getOtherPages(), $currentModule) !== false) {
                $ruleToApply[] = $rule->getId();
            }
        }// echo '<pre>';print_r($ruleToApply);exit;

        if (!empty($ruleToApply)) {
            $_rulesCollection->addFieldToFilter('main_table.rule_id', array('in' => $ruleToApply))
                    ->setOrder('main_table.priority', 'ASC');
            $_rulesCollection->getSelect()
                    ->limit(1); // echo '<pre>';print_r($_rulesCollection->getData());exit;

            echo Mage::app()->getLayout()
                    ->createBlock('restrictcustomergroup/restrictcustomergroup')
                    ->setRulesCollection($_rulesCollection)
                    ->setTemplate('restrictcustomergroup/restrictcustomergroup.phtml')
                    ->toHtml();
            exit;
        }
    }

    /**
     * @event: catalog_product_load_after
     * restrict product view
     * */
    public function restrictProductView(Varien_Event_Observer $observer) {

        if (Mage::app()->getStore()->isAdmin()) {
            return;
        }

        $event = $observer->getEvent();
        $product = $event->getProduct();
        $_rulesCollection = $this->_fetchRulesByProduct($product->getId());

        if (count($_rulesCollection->getData()) > 0) {
            echo Mage::app()->getLayout()
                    ->createBlock('restrictcustomergroup/restrictcustomergroup')
                    ->setRulesCollection($_rulesCollection)
                    ->setTemplate('restrictcustomergroup/restrictcustomergroup.phtml')
                    ->toHtml();
            exit;
        }
    }

    public function filterProductList(Varien_Event_Observer $observer) {
        $this->filterByRule($observer);
    }

}
