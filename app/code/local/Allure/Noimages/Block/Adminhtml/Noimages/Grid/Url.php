<?php
class Allure_Noimages_Block_Adminhtml_Noimages_Grid_Url extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        try
        {
        	$html  = '';
        	$value = $this->_getValue($row);
        	if (!empty($value)){
        		$html .= '<a popup="1" href="'.$this->getBaseUrl().$value.'.html" onclick="popWin(this.href,\'_blank\',\'width=800,height=700,resizable=1,scrollbars=1\');return false;">View Front';
        		$html .= '</a>';
        	}
        	return $html;
        } catch (Exception $e) {  }
        return '';
    }
}