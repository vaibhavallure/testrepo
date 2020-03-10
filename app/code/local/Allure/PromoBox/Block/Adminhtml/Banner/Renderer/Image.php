<?php
class Ecp_Celebrities_Block_Adminhtml_Celebrities_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
     
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    } 
    
    protected function _getValue(Varien_Object $row)
    {       
        $val = $row->getData($this->getColumn()->getIndex());
       // $val = str_replace("no_selection", "", $val);
        $url = Mage::getBaseUrl('media') . 'celebrities/'.$val; 
        $out = "<img src=". $url ." width='60px'/>"; 
        return $out;
    }
}