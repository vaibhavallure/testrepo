<?php
class Allure_PrivateSale_Block_Privatesale extends Mage_Core_Block_Template
{
    public function getPostActionUrl()
    {
        return $this->getUrl('*/*/save');
    }

}