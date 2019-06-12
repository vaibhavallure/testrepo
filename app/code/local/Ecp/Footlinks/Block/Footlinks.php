<?php
class Ecp_Footlinks_Block_Footlinks extends Mage_Core_Block_Template { 
    
    public function _construct()
    {
        $this->setTemplate('footlinks/footlinks.phtml'); 
        
        $this->addData(array(
            'cache_lifetime'=> 900,
            'cache_tags'    => array(Mage_Core_Model_Store::CACHE_TAG, Mage_Cms_Model_Block::CACHE_TAG)
        ));        

        /*to resolve footer cache issue */
        Mage::app()->getCacheInstance()->cleanType(block_html);

    }

    public function getCacheKeyInfo()
    {
        $num = rand(0, 5000); // TODO: will implement cache later. Issue MT-1599
        return array(
            'BLOCK_FOOTER_LINKS',
            $num,
            Mage::app()->getStore()->getId(),
            (int)Mage::app()->getStore()->isCurrentlySecure(),
            Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template'),
            Mage::getSingleton('customer/session')->isLoggedIn()
        );
    }  

    public function getAllCustomLinks() {
        return $collection = Mage::getModel("ecp_footlinks/footlinks")->getCollection()
                ->addFieldToFilter('status', 1)
                ->addOrder('sort_order', 'asc');

    }

    public function getBlock($id) {
        $block = Mage::getModel('cms/block')->load($id);
        return $this->getLayout()->createBlock('cms/block')->setBlockId($block->getIdentifier())->toHtml();
    }

    public function validURL($url) {
        return (preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url)) ? $url : 'http://' . $url;
    }
}