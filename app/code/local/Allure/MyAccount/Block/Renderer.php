<?php

class Allure_MyAccount_Block_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer
{
	public function setItem(Mage_Sales_Model_Order_Item $item)
	{
		$this->_item = $item;
		return $this;
	}
}
