<?php

class Allure_Facebook_Block_Channel extends Allure_Facebook_Block_Template
{
    protected function _toHtml()
    {
		return '<script src="'.($this->isSecure() ? 'https://' : 'http://').'connect.facebook.net/'.$this->escapeUrl($this->getData('locale') ?  $this->getData('locale') : $this->getLocale()).'/all.js"></script>';
    }
}
