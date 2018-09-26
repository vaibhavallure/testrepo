<?php
/**
 * Tax totals calculation model
 */
class Allure_GeoTax_Model_Sales_Total_Quote_Tax extends Mage_Tax_Model_Sales_Total_Quote_Tax
{ 
    
    /**
     * Calculate address total tax based on address subtotal
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @param   Varien_Object $taxRateRequest
     * @return  Mage_Tax_Model_Sales_Total_Quote
     */
    protected function _totalBaseCalculation(Mage_Sales_Model_Quote_Address $address, $taxRateRequest)
    {
        $items = $this->_getAddressItems($address);
        $store = $address->getQuote()->getStore();
        $taxGroups = array();
        $itemTaxGroups = array();
        $catalogPriceInclTax = $this->_config->priceIncludesTax($store);
        
        $rateInfo = Mage::getSingleton('tax/calculation')->getTaxRateObject($taxRateRequest);
        $isMinTax = false;
        $minTaxAmt = 0;
        if($rateInfo != null){
            if($rateInfo["is_min_tax_amount"]){
                $minTaxAmt  = $rateInfo["min_tax_amount"];
                $isMinTax   = true;
            }
        }


        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            
            $qty    = $item->getQty();
            $price  = $item->getPrice();
            $itemPrice = $price * $qty;
            if($itemPrice <= $minTaxAmt){
                continue;
            }
            
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $this->_totalBaseProcessItemTax(
                        $child, $taxRateRequest, $taxGroups, $itemTaxGroups, $catalogPriceInclTax);
                }
                $this->_recalculateParent($item);
            } else {
                $this->_totalBaseProcessItemTax(
                    $item, $taxRateRequest, $taxGroups, $itemTaxGroups, $catalogPriceInclTax);
            }
        }
        
        if ($address->getQuote()->getTaxesForItems()) {
            $itemTaxGroups += $address->getQuote()->getTaxesForItems();
        }
        $address->getQuote()->setTaxesForItems($itemTaxGroups);
        
        foreach ($taxGroups as $taxId => $data) {
            if ($catalogPriceInclTax) {
                $rate = (float)$taxId;
            } else {
                $rate = $data['applied_rates'][0]['percent'];
            }
            
            $inclTax = $data['incl_tax'];
            
            $totalTax = array_sum($data['tax']);
            $baseTotalTax = array_sum($data['base_tax']);
            $this->_addAmount($totalTax);
            $this->_addBaseAmount($baseTotalTax);
            $totalTaxRounded = $this->_calculator->round($totalTax);
            $baseTotalTaxRounded = $this->_calculator->round($totalTaxRounded);
            $this->_saveAppliedTaxes($address, $data['applied_rates'], $totalTaxRounded, $baseTotalTaxRounded, $rate);
        }
        return $this;
    }
    
}
