<?php
class Ecp_Alertstock_Model_System_Config_Source_Order_Status
{

    public function toOptionArray()
    {
        $statuses[] = array('value' => 'no_change', 'label' => Mage::helper('adminhtml')->__('-- No change --'));
		# Support for Custom Order Status introduced in Magento version 1.5
		$orderStatus = Mage::getModel('sales/order_config')->getStatuses();
		foreach ($orderStatus as $status => $label) {
			$statuses[] = array('value' => $status, 'label' => Mage::helper('adminhtml')->__((string)$label));
		}
        return $statuses;
    }

    // Function to just put all order status "codes" into an array.
    public function toArray()
    {
        $statuses = $this->toOptionArray();
        $statusArray = array();
        foreach ($statuses as $status) {
            $statusArray[$status['value']];
        }
        return $statusArray;
    }
}
