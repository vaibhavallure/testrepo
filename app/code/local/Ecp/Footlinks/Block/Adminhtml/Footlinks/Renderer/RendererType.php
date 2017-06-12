<?php

class Ecp_Footlinks_Block_Adminhtml_Footlinks_Renderer_RendererType extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $row = $row->getData($this->getColumn()->getIndex());
        
        if($row==1)
            return 'Block';
        elseif($row==2)
            return 'Url';
        elseif($row==0)
            return 'Seo text container';
        
    }
    
}
