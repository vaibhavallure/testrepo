<?php

class Allure_Catalog_Block_Adminhtml_Catalog_Product_Grid_Column_Renderer_Qtys extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{

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
     * Render a grid cell as qtys
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    public function render (Varien_Object $row)
    {
        $helper = $this->getHelper();
        $value = $row->getData($this->getColumn()
            ->getIndex());
        if (is_array($value) && count($value)) {
            $output = '<table cellspacing="0" class="qty-table"><col width="100"/><col width="40"/>';
            $totalQty = 0;
            
            foreach ($value as $stockId => $qty) {
                $output .= '<tr><td>' .
                         $helper->getWebsiteTitleByStockId($stockId) .
                         '</td><td>' .
                         ((! is_null($qty)) ? $qty : $helper->__('N / A')) .
                         '</td></tr>';
                $totalQty += floatval($qty);
            }
            
            $output .= '<tr><td><strong>Total</strong></td><td><strong>' .
                     $totalQty . '</strong></td></tr>';
            $output .= '</table>';
            return $output;
        }
        return '';
    }
}
