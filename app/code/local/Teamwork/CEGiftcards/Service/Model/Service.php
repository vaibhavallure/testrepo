<?php

class Teamwork_CEGiftcards_Service_Model_Service extends Teamwork_Service_Model_Service
{
    protected function getItems($style_id, $items, $attribute_set_id)
    {
        parent::getItems($style_id, $items, $attribute_set_id);
        $table = 'service_items';
        $channel = $this->_parser->getElementVal($this->_xml->Channel);
        foreach($items->Item as $item)
        {
            $xml_item_id = $this->_parser->getElementVal($item, false, 'ItemId');

            $IsChargeItem =  $this->_parser->getElementVal($item->IsChargeItem);
            $IsChargeItem = ($IsChargeItem == "true" ? 1 : 0); 
            $EligibleForDiscount =  $this->_parser->getElementVal($item->EligibleForDiscount);
            $EligibleForDiscount = ($EligibleForDiscount == "true" ? 1 : 0);
            $NeverChargeShipping =  $this->_parser->getElementVal($item->NeverChargeShipping);
            $NeverChargeShipping = ($NeverChargeShipping == "true" ? 1 : 0);

            
            $data = array(
                'IsChargeItem'        => $IsChargeItem,
                'ChargeItemType'      => $this->_parser->getElementVal($item->ChargeItemType),
                'EligibleForDiscount' => $EligibleForDiscount,
                'NeverChargeShipping' => $NeverChargeShipping,
            );
            
            $this->_db->update($table, $data, array('item_id' => $xml_item_id, 'channel_id' => $channel));
        }
    }
}