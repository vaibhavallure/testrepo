<?php

/**
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future. If you wish to customize for your
 * needs please refer to Entrepids Event-Observer for more information.
 * 
 * @category    Ecp
 * @package     Ecp_Seo
 */

/**
 * Description of Seo
 *
 * @category    Ecp
 * @package     Ecp_Seo
 */
class Ecp_Seo_Block_Seo extends Mage_Core_Block_Template
{
    protected $_content;
    
    public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
    public function setContent($content){
        $this->content = $content;        
    }
    
    public function getContent()
    {
        return $this->_content;
    }
    
     public function getSeo()     
     { 
        if (!$this->hasData('seo')) {
            $this->setData('seo', Mage::registry('seo'));
        }
        return $this->getData('seo');
        
    }
    
    public function getSeoCategory($categoryId){
        $category = Mage::getModel('catalog/category')->load($categoryId);
        return $category->getSeo();        
    }
    
    protected function _toHtml(){   
        $this->setTemplate('ecp/seo/seo.phtml');
        return parent::_toHtml();
    }
}