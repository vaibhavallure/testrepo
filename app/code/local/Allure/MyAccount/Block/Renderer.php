<?php

class Allure_MyAccount_Block_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer
{
	public function setItem(Mage_Sales_Model_Order_Item $item)
	{
		$this->_item = $item;
		return $this;
	}
	
	public function getItem(){
		return $this->_item;
	}
	
	public function getProductOptions()
	{
		/* @var $helper Mage_Catalog_Helper_Product_Configuration */
		//$helper = Mage::helper('catalog/product_configuration');
		return $this->getCustomOptions($this->getItem());
	}
	
	
	public function getCustomOptions( $item)
	{
		$product = $item->getProduct();
		$options = array();
		$optionIds = $item->getOptionByCode('option_ids');
		if ($optionIds) {
			$options = array();
			foreach (explode(',', $optionIds->getValue()) as $optionId) {
				$option = $product->getOptionById($optionId);
				if ($option) {
					$itemOption = $item->getOptionByCode('option_' . $option->getId());
					$group = $option->groupFactory($option->getType())
					->setOption($option)
					->setConfigurationItem($item)
					->setConfigurationItemOption($itemOption);
					
					if ('file' == $option->getType()) {
						$downloadParams = $item->getFileDownloadParams();
						if ($downloadParams) {
							$url = $downloadParams->getUrl();
							if ($url) {
								$group->setCustomOptionDownloadUrl($url);
							}
							$urlParams = $downloadParams->getUrlParams();
							if ($urlParams) {
								$group->setCustomOptionUrlParams($urlParams);
							}
						}
					}
					
					$options[] = array(
							'label' => $option->getTitle(),
							'value' => $group->getFormattedOptionValue($itemOption->getValue()),
							'print_value' => $group->getPrintableOptionValue($itemOption->getValue()),
							'option_id' => $option->getId(),
							'option_type' => $option->getType(),
							'custom_view' => $group->isCustomizedView()
					);
				}
			}
		}
		
		$addOptions = $item->getOptionByCode('additional_options');
		if ($addOptions) {
			$options = array_merge($options, unserialize($addOptions->getValue()));
		}
		
		return $options;
	}
	
}
