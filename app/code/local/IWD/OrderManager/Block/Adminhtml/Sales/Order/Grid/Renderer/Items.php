<?php

class IWD_OrderManager_Block_Adminhtml_Sales_Order_Grid_Renderer_Items extends IWD_OrderManager_Block_Adminhtml_Sales_Order_Grid_Renderer_Abstract
{

	protected function loadItems ()
	{
		$order_id = $this->getOrderId();
		$order = Mage::getModel('sales/order')->load($order_id);
		$order_item_collection = $order->getAllVisibleItems();
		$items = array();
		
		foreach ($order_item_collection as $item) {
			$items[] = $item->getName() . ' (' . number_format($item->getQtyOrdered(), 0) . ')';
		}
		
		return $items;
	}

	protected function Grid ()
	{
		$items = $this->loadItems();
		return $this->formatBigData($items);
	}

	protected function Export ()
	{
		$items = $this->loadItems();
		return implode(',', $items);
	}
}
