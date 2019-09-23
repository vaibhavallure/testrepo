<?php
/**
 * 
 * @author allure
 *
 */
require_once ('app/code/core/Mage/Checkout/Block/Multishipping/Overview.php');
class Allure_RedesignCheckout_Block_Checkout_Multishipping_Overview 
extends Mage_Checkout_Block_Multishipping_Overview
{
    public function addGiftItemRender($type, $block, $template)
    {
        parent::addItemRender($type, $block, $template);
        return $this;
    }
    
    /**
     * Return row-level item html
     *
     * @param Varien_Object $item
     * @return string
     */
    public function getGiftItemHtml(Varien_Object $item)
    {
        $type = "gift";
        $block = $this->getGiftItemRenderer($type);
        $block->setItem($item);
        $this->_prepareItem($block);
        return $block->toHtml();
    }
    
       
    public function getGiftItemRenderer($type)
    {
        if (!isset($this->_itemRenders[$type])) {
            $type = 'gift';
        }

        if (is_null($this->_itemRenders[$type]['renderer'])) {
            $this->_itemRenders[$type]['renderer'] = $this->getLayout()
            ->createBlock($this->_itemRenders[$type]['block'])
            ->setTemplate($this->_itemRenders[$type]['template'])
            ->setRenderedBlock($this);
        }
        return $this->_itemRenders[$type]['renderer'];
    }
}

