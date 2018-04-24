<?php

class Allure_Catalog_Block_Adminhtml_Catalog_Product_Grid_Column_Renderer_Batchprices extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{

    protected $storeIds;

    /**
     * Get warehouse helper
     *
     * @return Allure_Catalog_Helper_Data
     */
    public function getHelper ()
    {
        return Mage::helper('allure_catalog');
    }

    /**
     * Get currency code
     *
     * @param array $row
     *
     * @return string
     */
    protected function _getCurrencyCode ($row)
    {
        $code = $this->getColumn()->getCurrencyCode();
        if ($code) {
            return $code;
        }
        $code = $row->getData($this->getColumn()
            ->getCurrency());
        if ($code) {
            return $code;
        }
        return false;
    }

    /**
     * Get rate
     *
     * @param array $row
     *
     * @return float
     */
    protected function _getRate ($row)
    {
        $rate = $this->getColumn()->getRate();
        if ($rate) {
            return (float) $rate;
        }
        $rate = $row->getData($this->getColumn()
            ->getRateField());
        if ($rate) {
            return (float) $rate;
        }
        return 1;
    }

    protected function getStoreIds ()
    {
        $helper = $this->getHelper();
        if (is_null($this->storeIds)) {
            $this->storeIds = $helper->getStoreIdsByUsingStockIds();
        }
        return $this->storeIds;
    }

    /**
     * Render a grid cell as qtys
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    public function render (Varien_Object $row)
    {
        $helper = $this->getHelper();
        $storeIds = $this->getStoreIds();
        $value = $row->getData($this->getColumn()
            ->getIndex());
        if (is_array($value) && count($value)) {
            $currencyCode = $this->_getCurrencyCode($row);
            $rate = $this->_getRate($row);
            
            $output = '<table cellspacing="0" class="batch-prices-table"><col width="100"/><col width="40"/>';
            foreach ($value as $stockId => $price) {
                $store = Mage::getModel('core/store')->load($storeIds[$stockId]);
                $currencyCode = $store->getCurrentCurrencyCode();
                if ($currencyCode) {
                    $price = sprintf("%f", ((float) $price) * $rate);
                    // Mage::log($price,Zend_Log::DEBUG,'abc',true);
                    $price = Mage::app()->getLocale()
                        ->currency($currencyCode)
                        ->toCurrency($price);
                }
                $output .= '<tr><td>' .
                         $helper->getWebsiteTitleByStockId($stockId) .
                         '</td><td>' . $price . '</td></tr>';
            }
            $output .= '</table>';
            return $output;
        }
        return '';
    }
}
