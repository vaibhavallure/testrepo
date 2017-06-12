<?php

//@GCCart
class Ecp_Shoppingcart_Block_Topcart 
    extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface {

    protected $cart = null;
    protected $items = array();
    protected $totals = null;
    protected $isEmpty = true;
    protected $grandTotal = 0;
    protected $total = 0;

    public function __construct() {
        $this->cart = Mage::getModel('checkout/cart');
        $tmp = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
        foreach ($tmp as $item) {
            if ($item->getProduct()->getTypeId() == 'simple' || $item->getProduct()->getTypeId() == 'customproduct' || $item->getProduct()->getTypeId() == 'giftcards') {
                $this->items[] = $item;
            }
        }
        $this->items = array_reverse($this->items);

        $this->isEmpty = (count($this->items) > 0) ? false : true;
        $totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals();
        foreach ($totals as $key => $total)
            $this->totals[$key] = Mage::helper('core')->currency((int) $total->getValue() * 1.16, true, false);
        parent::__construct();
    }

    protected function _toHtml() {
        $this->setTemplate('topcart/topcart.phtml');
        return parent::_toHtml();
    }

    public function getTitle() {
        return 'Mi Carrito'; //@todo Move to Top Cart config panel
    }

    public function getAllItems() {
        return $this->items;
    }

    public function getQtyItems() {
        $total = 0;
        $configurable_product = Mage::getModel('catalog/product_type_configurable');
        $parentId = array();
        $tmp = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
        $incl_tax = Mage::helper('tax')->priceIncludesTax();

        foreach ($this->items as $item) {
            if ($item->getProduct()->getTypeId() == 'simple' || $item->getProduct()->getTypeId() == 'customproduct' || $item->getProduct()->getTypeId() == 'giftcards') {
                $parentId = $item->getParentItemId();
                $rate_id = null;
                $porcentaje_iva = 0;
                $iva = 0;
                $ivaThisProduct = 0;
                $totalIvaThisProduct = 0;
                $product = Mage::getModel('catalog/product')->load($item->getProduct()->getId());

                if ($parentId != null) {
                    foreach ($tmp as $configurable) {
                        if ($configurable->getItemId() == $parentId && $item->getSku() == $configurable->getSku()) {
                            $rate_id = null;
                            $tax_class = Mage::getModel('tax/calculation')->getCollection()
                                            ->addFieldToFilter('product_tax_class_id', $configurable->getProduct()->getTaxClassId());
                            foreach ($tax_class as $items) {
                                $rate_id = $items->getTaxCalculationRateId();
                            }

                            if ($rate_id != null) {
                                $rate = Mage::getModel('tax/calculation_rate')->load($rate_id);
                                $porcentaje_iva = $rate->getRate() / 100;
                            }

                            if (!$incl_tax) {
                                $ivaThisProduct = $configurable->getProduct()->getFinalPrice() * $porcentaje_iva;

                                $this->totalIva += $ivaThisProduct * $configurable->getQty();
                                $this->grandTotal += $configurable->getQty() * $configurable->getProduct()->getFinalPrice();
                                $this->totalMasIva += ( $ivaThisProduct + $configurable->getProduct()->getFinalPrice()) * $configurable->getQty();
                            } else {
                                $precioMenosIva = $configurable->getProduct()->getFinalPrice() / (1 + $porcentaje_iva);
                                $ivaThisProduct = $configurable->getProduct()->getFinalPrice() - $precioMenosIva;
                                $totalIvaThisProduct = $ivaThisProduct * $configurable->getQty();

                                $this->totalIva += $totalIvaThisProduct;
                                $this->grandTotal += $configurable->getQty() * $precioMenosIva;
                                $this->totalMasIva += $configurable->getProduct()->getFinalPrice() * $configurable->getQty();
                            }
                            $total += $configurable->getQty();
                        }
                    }
                } else {
                    $rate_id = null;
                    $tax_class = Mage::getModel('tax/calculation')->getCollection()
                                    ->addFieldToFilter('product_tax_class_id', $item->getProduct()->getTaxClassId());
                    foreach ($tax_class as $items) {
                        $rate_id = $items->getTaxCalculationRateId();
                    }

                    if ($rate_id != null) {
                        $rate = Mage::getModel('tax/calculation_rate')->load($rate_id);
                        $porcentaje_iva = $rate->getRate() / 100;
                    }

                    if (!$incl_tax) {
                        $iva = $product->getFinalPrice() * $porcentaje_iva;
                        $this->totalIva += $iva * $item->getQty();
                        $this->grandTotal += $product->getFinalPrice() * $item->getQty();
                        $this->totalMasIva += $item->getQty() * ($product->getFinalPrice() + $iva);
                    } else {
                        $precioMenosIva = $product->getFinalPrice() / (1 + $porcentaje_iva);
                        $ivaThisProduct = $product->getFinalPrice() - $precioMenosIva;
                        $totalIvaThisProduct = $ivaThisProduct * $item->getQty();

                        $this->totalIva += $totalIvaThisProduct;
                        $this->grandTotal += $item->getQty() * $precioMenosIva;
                        $this->totalMasIva += $product->getFinalPrice() * $item->getQty();
                    }
                    $total += $item->getQty();
                }
            }
        }
        return ($total == 0) ? '0' : $total;
    }

    public function getTotalCart() {
        return $this->totals['grand_total'];
    }

    public function getCartItemsCount() {
        return count($this->items);
    }

    public function getProductAndAttributes($id){

        $colours = Mage::getStoreconfig('ecp_color/color_attr');
        $colours = explode(',', $colours);
        $_collection = Mage::getModel('catalog/product')->getCollection()
            ->addFieldToFilter('entity_id', $id)
            ->addAttributeToSelect('size');
        foreach($colours as $colour) {
            $_collection->addAttributeToSelect($colour);
        }
        return $_collection->getFirstItem();
    }

    public function itemQty($item){
        if ($item->getProduct()->getTypeId() == 'simple' || $item->getProduct()->getTypeId() == 'customproduct' || $item->getProduct()->getTypeId() == 'giftcards') {
             return ($item->getParentItem())
                ? $item->getParentItem()->getQty()
                : $item->getQty();
        }else return '--';
    }

    public function addToTotal($item){
        return $this->total += $item->getQty() * $item->getProduct()->getFinalPrice();
    }

    public function getSubtotal(){
        return Mage::helper('core')->currency($this->total, true, false);
    }
}