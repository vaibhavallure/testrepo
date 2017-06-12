<?php

class Magestore_Webpos_Block_Product_Configurable extends Magestore_Webpos_Block_Product_View
{
	public function getStartFormHtml(){
		if ($bundleBlock = $this->getLayout()->getBlock('product.info.configurable')){
			$html  = '<div style="display:none">';
			$html .= $bundleBlock->toHtml();
			$html .= '</div>';
			return $html;
		}
		return parent::getStartFormHtml();
	}
}