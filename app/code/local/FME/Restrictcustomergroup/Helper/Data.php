<?php

class FME_Restrictcustomergroup_Helper_Data extends Mage_Core_Helper_Abstract
{
    const RESTRICTION_TYPE = 'restrictcustomergroup/basic/restriction_type';
    
    public function getRestrictionType($storeId = null) {
        
        if ($storeId == null)
        {
            $storeId = Mage::app()->getStore()->getId();
        }
        
        $type = Mage::getStoreConfig(self::RESTRICTION_TYPE, $storeId);
        
        return $type;
    }
    
    
    public function convertFlatToRecursive(array $rule, $keys)
	{
        $arr = array();
        foreach ($rule as $key => $value)
        {
            if (in_array($key, $keys) && is_array($value))
            {
                foreach ($value as $id => $data)
                {
                    $path = explode('--', $id);
                    $node = & $arr;
                    for ($i = 0, $l = sizeof($path); $i < $l; $i++)
                    {
                        if (!isset($node[$key][$path[$i]]))
                        {
                            $node[$key][$path[$i]] = array();
                        }
                        $node = & $node[$key][$path[$i]];
                    }
                    foreach ($data as $k => $v)
                    {
                        $node[$k] = $v;
                    }
                }
            }
			else
            {
                if (in_array($key, array('from_date', 'to_date')) && $value)
                {
                    $value = Mage::app()->getLocale()->date(
                            $value, Varien_Date::DATE_INTERNAL_FORMAT, null, false
                    );
                }
            }
        }

        return $arr;
    }
    
    public function updateChild($array, $from, $to)
	{
        foreach ($array as $k => $rule)
        {
            foreach ($rule as $name => $param)
            {
                if ($name == 'type' && $param == $from)
                    $array[$k][$name] = $to;
            }
        }
        
        return $array;
    }

	public function checkVersion($version, $operator = '>=')
	{
        return version_compare(Mage::getVersion(), $version, $operator);
    }
    
    public function getAllCmsPages() {
        
        $_helper = Mage::helper('restrictcustomergroup');
        $cmsCollection = Mage::getModel('cms/page')->getCollection();
        $options = array();
        foreach ($cmsCollection as $c)
        {
            $options[] = array(
                'label' => $_helper->__($c->getTitle()),
                'value' => $_helper->__($c->getIdentifier())
            );
        }
        
        return $options;
    }
    
    public function getOtherPages() {
        $_helper = Mage::helper('restrictcustomergroup');
        //$config = Mage::getConfig();
        //$modules = $config->loadModules(); echo '<pre>';print_r($modules);exit;
        
        $modules = array(
            array(
              'label' => $_helper->__('Customer'),
              'value' => $_helper->__('customer')
            ),array(
              'label' => $_helper->__('Checkout'),
              'value' => $_helper->__('checkout')
            ),array(
              'label' => $_helper->__('Wishlist'),
              'value' => $_helper->__('wishlist')
            )
        );
        
        return $modules;
    }
    
    public function filterWhiswigContents($data) {
    
    	$helper = Mage::helper('cms');
        $processor = $helper->getPageTemplateProcessor();
        
        return $processor->filter($data);
    }
	
	public function getCustomerGroupOptions($filterIds = array()) {
		$customerGroups = Mage::getModel('customer/group')->getCollection();
		if (!empty($filterIds)) {
			$customerGroups->addFieldToFilter('main_table.customer_group_id', array('in' => $filterIds));
		}
		//echo '<pre>';print_r($customerGroups->toOptionHash());exit;
		return $customerGroups->toOptionHash();
	}
	
	public function getCustomerGroupLabels($filterIds = array()) {
		$data = $this->getCustomerGroupOptions($filterIds);
		$labels = '';
		if (!empty($data)) {
			foreach ($data as $i) {
				$labels .= "{$i} <br/>";
			}
		}
		return $labels;
	}
}