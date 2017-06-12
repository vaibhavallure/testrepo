<?php
class Ecp_Celebrities_Block_Adminhtml_Outfits_Renderer_Products extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
     
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    } 
    
    protected function _getValue(Varien_Object $row)
    {       
        $productNames = "";
        $val = $row->getData($this->getColumn()->getIndex());
       // $val = str_replace("no_selection", "", $val);
        $ids = explode(",",$val);
        foreach($ids as $id){
            $product = Mage::getModel('catalog/product')->load($id);
            $productNames .= $productNames=="" ?  $product->getName() : "</BR>".$product->getName();
        }

        return $productNames;
    }
}