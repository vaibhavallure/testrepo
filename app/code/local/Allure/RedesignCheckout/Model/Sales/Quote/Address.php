<?php
/**
 * 
 * @author allure
 *
 */
class Allure_RedesignCheckout_Model_Sales_Quote_Address extends Teamwork_Universalcustomers_Model_Sales_Quote_Address
{
    /**
     * Retrieve all grouped shipping rates
     *
     * @return array
     */
    /* public function getGroupedAllShippingRates()
    {
        $rates = array();
        foreach ($this->getShippingRatesCollection() as $rate) {
            if (!$rate->isDeleted() && $rate->getCarrierInstance()) {
                if (!isset($rates[$rate->getCarrier()])) {
                    $rates[$rate->getCarrier()] = array();
                }
                
                if($rate->getCarrier() == "matrixrate"){
                    $methodArr = explode("#",$rate->getMethod());
                    if(count($methodArr) == 2){
                        if($methodArr[1] == 0){
                            continue;
                        }
                    }
                }
                
                $rates[$rate->getCarrier()][] = $rate;
                $rates[$rate->getCarrier()][0]->carrier_sort_order = $rate->getCarrierInstance()->getSortOrder();
            }
        }
        uasort($rates, array($this, '_sortRates'));
        return $rates;
    } */
    
    
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
                
                $this->getItemsCollection()->addItem($addressItem);
            }
        }
        
        if ($qty) {
            $addressItem->setQty($qty);
        }
        return $this;
    }
}
