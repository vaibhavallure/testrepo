<?php

class Allure_Inventory_Block_Adminhtml_Inventory_Renderer_Qtys extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
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
        $absValue = abs($value);
        $output = '<label>' . round($value) . '</label>';
        $output .= '<input style="width:50px;height:25px;margin-left:10px" value="' .
                 $absValue . '"/>';
        return $output;
    }
}
