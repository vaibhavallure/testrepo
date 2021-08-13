<?php
class Allure_PrivateSale_Block_Privatesale extends Mage_Core_Block_Template
{
    public function getPostActionUrl()
    {
        return $this->getUrl('*/*/save');
    }
    public function getHead()
    {
        if(Mage::helper('privatesale')->getStaticBlock())
        {
            return $this->getLayout()->createBlock('cms/block')->setBlockId(Mage::helper('privatesale')->getStaticBlock())->toHtml();
        }

        return "";
    }

}