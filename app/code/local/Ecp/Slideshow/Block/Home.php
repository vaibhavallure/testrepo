<?php

class Ecp_Slideshow_Block_Home
    extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface {

    public function __construct() {
        parent::__construct();
    }

    public function  _prepareLayout() {
       
	    $head = $this->getLayout()->getBlock('head');       
        $head->addJs('supersized.3.2.7.js');
        $head->addJs('supersized.shutter.js');
        $head->addCss('css/supersized.css');
        $head->addCss('css/supersized.shutter.css');
        
        return parent::_prepareLayout();
    }

    public function _toHtml() {
        $this->setTemplate("ecp/slideshow/slideshow.phtml");
        return parent::_toHtml();
    }
    
    public function getSlideshow()     
     { 
        if (!$this->hasData('slideshow')) {
            $this->setData('slideshow', Mage::registry('slideshow'));
        }
        return $this->getData('slideshow');
    }
    
    public function getJsonImages(){
        $tmpImages = array();
        $collection = Mage::getModel('ecp_slideshow/slideshow')->getCollection()			
			->addFilter('status',1) 
			->setOrder('position', 'asc')
			;
        foreach ($collection as $item) {
            $tmpImages[] = "{image : '".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."slideshow/".$item->getSlideBackground()."', title : '".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."slideshow/".$item->getSlideThumb()."', url : '".$item->getUrl()."', description : '".str_replace("\r\n","",$item->getSlideContent())."', background: '".$item->getBackground()."'}";
        }
        return '['.implode(',',$tmpImages).']';
    }
}
