<?php
class FME_Restrictcustomergroup_Block_Restrictcustomergroup extends Mage_Core_Block_Template {


	/**
	 * Cache tag constant for feed notify stock
	 *
	 * @var string
	 */
	const CACHE_TAG = 'block_restrictcustomergroup';
	
	/**
	 * Constructor
	 *
	 * @return null
	 */
	protected function _construct()
	{
		$this->setCacheTags(array(self::CACHE_TAG));
		/*
		 * setting cache to save the rss for 10 minutes
		 */
		$this->setCacheKey('restrictcustomergroup');
		$this->setCacheLifetime(null);
		parent::_construct();
	}
	
	public function _prepareLayout() {
		
		return parent::_prepareLayout();
	
    }
    
     public function getRestrictcustomergroup() {
		
        if (!$this->hasData('restrictcustomergroup')) {
			
            $this->setData('restrictcustomergroup', Mage::registry('restrictcustomergroup'));
			
        }
		
        return $this->getData('restrictcustomergroup');
        
    }
	
	public function getRules($type) {
		
		
		$customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
		$_rulesCollection = Mage::getModel('restrictcustomergroup/restrictcustomergroup')
			->getCollection()
			->distinct(true)
			//->addStoreFilter(Mage::app()->getStore()->getId())
			->addFieldToFilter('main_table.form_type', $type)
			->addValidationFilter(Mage::app()->getStore()->getId(), $customerGroupId);
		//echo (string) $_rulesCollection->getSelect();exit;
		
		return $_rulesCollection;
	}
	
	public function getRulesToApply($id = null) {
		
		$out = '';
		$ruleIds = array();
		
		//Get Current Product ID
		$productId = $this->getRequest()->getParam('id'); //echo $productId;

		if ($id != null) {
			
			$productId = $id;
		}
		//Get the Matching Rule ids for current product
		foreach ($this->getRules() as $rule) {
			
			$model = Mage::getModel('restrictcustomergroup/restrictcustomergroup_product_rulecss');
			$model->setWebsiteIds(Mage::app()->getStore()->getWebsite()->getId());
			
			if ($rule["condition_serialized"] != '') {
				
				$conditions = $rule["condition_serialized"]->getConditions(); //echo '<pre>';print_r($conditions);
				
				if (isset($conditions['css'])) {
					
					$model->getConditions()->loadArray($conditions, 'css');
					$match = $model->getMatchingProductIds();
					
					if (in_array($productId, $match)) {
						
						$ruleIds[] = $rule["rule_id"];
						
					}
					
				}
			}
		}
		//Rebuild collection to get the rule that has to be applied on current product
		$collection = Mage::getModel('backgroundimages/backgroundimages')->getCollection();
		$collection->addStoreFilter()
				->addStatusFilter()
				->addDateFilter()
				->addIdsFilter($ruleIds); //echo (string) $collection->getSelect();exit;
				
		if ($this->_priority) {
			
			$collection->setPriorityOrder()
			->setPageSize(1);
			
		}
		
		$collection->getData();

		return $collection;
    }
}