<?php
class Teamwork_CEGiftcards_Service_Model_Weborder extends Teamwork_Service_Model_Weborder
{

    public function generateWebOrderItemsGroups(&$webOrder)
    {
        parent::generateWebOrderItemsGroups($webOrder);

        $order = Mage::getModel('sales/order')->getCollection()
                    ->addFieldToFilter('increment_id', $webOrder['OrderNo'])
                    ->getFirstItem();
        $gcItems = array();
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() == Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_GIFTCARD) {
                $gcItems[(string)$item->getId()] = array('item' => $item, 'need_save' => false, );
                $options = $item->getProductOptions();
                if (array_key_exists('giftcard_type', $options) 
                    && $options['giftcard_type'] != Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_PHYSICAL) {

                        $gcItems[(string)$item->getId()]['is_ph'] =  false;
                        $codes = (isset($options['giftcard_created_codes']) ?
                                $options['giftcard_created_codes'] : array());
                        $chqSentCodes = (isset($options['giftcard_chq_items_attached_codes']) ?
                                $options['giftcard_chq_items_attached_codes'] : array());
                        $gcItems[(string)$item->getId()]['chq_items_attached_codes'] = $chqSentCodes;
                        $codes = array_diff($codes, array_values($chqSentCodes));
                        $gcItems[(string)$item->getId()]['codes'] = $codes;
                } else {
                    $gcItems[(string)$item->getId()]['is_ph'] =  true;
                }
                

            }
        }

        if (count($gcItems)) {

            $WebOrderItemsGroups = $webOrder->WebOrderItemsGroups[0];
            foreach($WebOrderItemsGroups->children() as $child) {
                if ($child->getName() == 'WebOrderItemsGroup') {

                    //get itemgroup elements
                    $WebOrderItemsGroupId = (string)$child['WebOrderItemsGroupId'];
                    $tItems = $this->getTable('service_weborder_item', array('WebOrderItemsGroupId = ?'), array($WebOrderItemsGroupId));
                    $items = array();
                    foreach($tItems as $tItem) {
                        if (array_key_exists((string)$tItem['InternalId'], $gcItems)) {
                            $items[$tItem['WebOrderItemId']] = $tItem['InternalId'];
                        }
                    }

                    $WebOrderItems = $child->WebOrderItems[0];
                    foreach($WebOrderItems->children() as $WebOrderItemsChild) {
                        if ($WebOrderItemsChild->getName() == 'WebOrderItem') {
                            //is gc?
                            $WebOrderItemId = (string)$WebOrderItemsChild['WebOrderItemId'];
                            if (array_key_exists($WebOrderItemId, $items)) {
                                $code = "";
                                if (!$gcItems[$items[$WebOrderItemId]]['is_ph']) {
//                                    $code = "";
//                                } else {
//                                    $code = false;
                                    //if we already attached gc code to this weborder item (if chq already requested this order)
                                    if (array_key_exists($WebOrderItemId, $gcItems[$items[$WebOrderItemId]]['chq_items_attached_codes'])) {
                                        $code = $gcItems[$items[$WebOrderItemId]]['chq_items_attached_codes'][$WebOrderItemId];
                                    } else {
                                        $code = array_shift($gcItems[$items[$WebOrderItemId]]['codes']);
                                        $gcItems[$items[$WebOrderItemId]]['chq_items_attached_codes'][$WebOrderItemId] = $code;
                                        $gcItems[$items[$WebOrderItemId]]['need_save'] = true;
                                    }
//                                    if ($code !== false) {
//                                        $orderItem = $gcItems[$items[$WebOrderItemId]]['item'];
//                                        $options = $orderItem->getProductOptions();
//                                        $product = $orderItem->getProduct();
//                                        $gcType = $this->_getGCType($options['giftcard_type']);
//                                        $this->createAttrNode($WebOrderItemsChild, 'GiftCardInfo', array(
//                                            'GiftCardNo'    => $code,
//                                            'Name'          => $product->getName(),
//                                            'GiftCardType'  => $gcType
//                                        ), false);
//                                    }
                                }
                                
                                $orderItem = $gcItems[$items[$WebOrderItemId]]['item'];
                                $options = $orderItem->getProductOptions();
                                $product = $orderItem->getProduct();
                                $gcType = $this->_getGCType($options['giftcard_type']);
                                $this->createAttrNode($WebOrderItemsChild, 'GiftCardInfo', array(
                                    'GiftCardNo'    => $code,
                                    'Name'          => $product->getName(),
                                    'GiftCardType'  => $gcType
                                ), false);
                                
                                
                                
                                
                            }
                        }
                    }
                }
            }
            //remember our gc codes to weborder items attaching if we did it the first
            foreach($gcItems as $gcItem) {
                if ($gcItem['need_save']) {
                    $orderItem = $gcItem['item'];
                    $options = $orderItem->getProductOptions();
                    $options['giftcard_chq_items_attached_codes'] = $gcItem['chq_items_attached_codes'];
                    $orderItem->setProductOptions($options);
                    $orderItem->save();
                }
            }

        }
    }

    protected function _getGCType($rawType)
    {
        $arr = array(
            Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_VIRTUAL  => 'Virtual',
            Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_PHYSICAL => 'Physical',
            Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_COMBINED => 'Combined',
        );
        if (array_key_exists($rawType, $arr)) {
            return $arr[$rawType];
        }
        return 'Unknown';
    }
}