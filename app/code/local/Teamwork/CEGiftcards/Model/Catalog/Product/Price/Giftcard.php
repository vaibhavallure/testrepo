<?php

class Teamwork_CEGiftcards_Model_Catalog_Product_Price_Giftcard extends Mage_Catalog_Model_Product_Type_Price
{
    /**
     * Return price of the specified product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getPrice($product)
    {
        if ($product->hasCustomOptions()) {
            $customOption = $product->getCustomOption('giftcard_amount');
            if ($customOption) {
                if ($customOption->getValue() == 'custom') {
                    $customOption = $product->getCustomOption('giftcard_amount_custom');
                    if ($customOption) {
                        return $customOption->getValue();
                    }
                } else {
                    return $customOption->getValue();
                }
            }
        }

        $amounts = $product->getTypeInstance(true)->getLoadAmounts($product);
        $result = 0;
        if (count($amounts)) {
            $result = min($amounts);
        }
        if ($product->getGiftcardOpenAmount() && $product->getGiftcardAmountMin() > 0) {
            $result = $result > 0 ? min(array($result, $product->getGiftcardAmountMin())) : $product->getGiftcardAmountMin();
        }
        return $result;
    }

    /**
     * Retrieve product final price
     *
     * @param integer $qty
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getFinalPrice($qty=null, $product)
    {
        $finalPrice = $product->getPrice();
        $product->setData('final_price', $finalPrice);
        return max(0, $product->getData('final_price'));
    }
}
