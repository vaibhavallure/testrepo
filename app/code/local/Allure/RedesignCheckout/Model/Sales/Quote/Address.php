<?php
/**
 * 
 * @author allure
 *
 */
class Allure_RedesignCheckout_Model_Sales_Quote_Address extends Teamwork_Universalcustomers_Model_Sales_Quote_Address
{
    /**
     * Add item to address
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @param   int $qty
     * @return  Mage_Sales_Model_Quote_Address
     */
    public function addItem(Mage_Sales_Model_Quote_Item_Abstract $item, $qty = null)
    {
        if ($item instanceof Mage_Sales_Model_Quote_Item) {
            if ($item->getParentItemId()) {
                return $this;
            }
            $addressItem = Mage::getModel('sales/quote_address_item')
                ->setAddress($this)
                ->importQuoteItem($item);
            //set item is gift item or not
            $addressItem->setIsGiftItem($item->getIsGiftItem());
            $addressItem->setGiftItemQty($item->getGiftItemQty());
            $addressItem->setIsGiftWrap($item->getIsGiftWrap());
            
            $this->getItemsCollection()->addItem($addressItem);
            
            if ($item->getHasChildren()) {
                foreach ($item->getChildren() as $child) {
                    $addressChildItem = Mage::getModel('sales/quote_address_item')
                        ->setAddress($this)
                        ->importQuoteItem($child)
                        ->setParentItem($addressItem);
                    //set item is gift item or not
                    $addressChildItem->setIsGiftItem($item->getIsGiftItem());
                    $addressChildItem->setGiftItemQty($item->getGiftItemQty());
                    $addressChildItem->setIsGiftWrap($item->getIsGiftWrap());
                    
                    $this->getItemsCollection()->addItem($addressChildItem);
                }
            }
        } else {
            $addressItem = $item;
            $addressItem->setAddress($this);
            if (!$addressItem->getId()) {
                //set item is gift item or not
                $addressItem->setIsGiftItem($item->getIsGiftItem());
                $addressItem->setGiftItemQty($item->getGiftItemQty());
                $addressItem->setIsGiftWrap($item->getIsGiftWrap());
                
                $this->getItemsCollection()->addItem($addressItem);
            }
        }
        
        if ($qty) {
            $addressItem->setQty($qty);
        }
        return $this;
    }
}
