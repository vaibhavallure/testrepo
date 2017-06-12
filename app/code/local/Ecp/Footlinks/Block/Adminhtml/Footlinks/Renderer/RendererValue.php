<?php

class Ecp_Footlinks_Block_Adminhtml_Footlinks_Renderer_RendererValue extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $blocks = Mage::getModel('cms/block')->getCollection()->toOptionArray();         
        $idx = $this->multiarray_search($blocks,'value',$row->getData($this->getColumn()->getIndex()));
                
        if($idx != -1){ 
            return $blocks[$idx]['label'];
        }else{ 
            return '';
        };
    }
    
    public function multiarray_search($arrayVet, $campo, $valor){

        while(isset($arrayVet[key($arrayVet)])){

            $searchin = explode("|",$arrayVet[key($arrayVet)][$campo]);

            foreach($searchin as $s){

                if($s === $valor){

                    return key($arrayVet);
                }            
            }
            next($arrayVet);
        }
        return -1;

    }
}
