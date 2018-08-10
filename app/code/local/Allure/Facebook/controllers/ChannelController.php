<?php

class Allure_Facebook_ChannelController extends Mage_Core_Controller_Front_Action
{
	
	public function indexAction()
	{
		/**
		 * http://developers.facebook.com/docs/reference/javascript/FB.init/
		 * 
		 * You MUST send valid Expires headers and ensure the channel file is cached by the browser. 
		 * We recommend caching indefinitely.
		 * 
		 */
		
		$expires = 365*24*60*60; //1 year
		$this->getResponse()
				->setHeader('Pragma', '', true)
				->setHeader('Cache-Control', 'maxage='.$expires, true)
				->setHeader('Expires', gmdate('D, d M Y H:i:s', time()+$expires), true)
				->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', time()))
				;
		
		if($this->getRequest()->getHeader('If-Modified-Since')) {
			$this->getResponse()->setHttpResponseCode(304);
		}
		
		$locale = $this->getRequest()->getParam('locale', false);
		
		if($locale && !in_array($locale, array_keys(Mage::getModel('facebook/locale')->getLocales()))) {
			$locale = false;
		}
		
		if(!$locale) {
			$locale = Mage::getSingleton('facebook/config')->getLocale();
		}
		
		$this->getResponse()->setBody(
			$this->getLayout()->createBlock('facebook/channel')
			->setLocale($locale)
			->toHtml()
		);
	}
	
}
