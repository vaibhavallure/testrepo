<?php
class FME_Restrictcustomergroup_Block_Cms_Block_Block
    extends Mage_Cms_Block_Block {
    
    /**
     * Prepare Content HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        
        //if (Mage::getConfig()->getModuleConfig('FME_Restrictcustomergroup')->is('active', 'true')) {
        ////if (!Mage::helper('core')->isModuleEnabled('FME_Restrictcustomergroup')) {    
        //    return parent::_toHtml();
        //}
        
        $blockId = $this->getBlockId(); //echo $blockId;
        $blockStatic = Mage::getModel('cms/block')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($blockId);
            
        if ($blockStatic->getId() == NULL) {
            return;
        }
        
        $rules = $this->_isRestricted($blockStatic->getId());  //echo '<pre>';print_r($rules->getSize());exit;
        $html = '';
        // getSize is preferred as it will make a query call
        if ($blockId && !$rules->getSize() > 0)
        {
            $block = Mage::getModel('cms/block')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($blockId);
            if ($block->getIsActive())
            {
                /* @var $helper Mage_Cms_Helper_Data */
                $helper = Mage::helper('cms');
                $processor = $helper->getBlockTemplateProcessor();
                $html = $processor->filter($block->getContent());
            }
        }
        
        return $html;
    }
    
    protected function _isRestricted($id)
    {
        $block = new FME_Restrictcustomergroup_Block_Restrictcustomergroup();
        $_rulesCollection = $block->getRules(Mage::helper('restrictcustomergroup')->getRestrictionType()); // rules collection
        $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        
        $res = Mage::getSingleton('core/resource');
        $_blockTable = $res->getTableName('fme_restrictcustomergroup_blocks');
        $_read = $res->getConnection('core_read');
        $model = Mage::getModel('restrictcustomergroup/restrictcustomergroup');
        
        $_restrictedBlockIds = array();
        
        foreach($_rulesCollection as $r)
        {
            $ids = $model->assosiatedBlockIds($r->getId()); //restricted static block ids
            if (in_array($id, $ids))
            {
                $_restrictedBlockIds[$r->getId()] = $id; // prepare array with rules as key and static block ids
            } 
        }
        //echo '<pre>';print_r($_restrictedBlockIds);
        $_rulesCollection->addFieldToFilter('main_table.rule_id', array('in' => array_keys($_restrictedBlockIds)))
            //->addValidationFilter(Mage::app()->getStore()->getId(), $customerGroupId)
            ->setOrder('main_table.priority', 'ASC');
        $_rulesCollection->getSelect()
            ->limit(1); // echo (string)$_rulesCollection->getSelect();exit;
        return $_rulesCollection;
    }
}
