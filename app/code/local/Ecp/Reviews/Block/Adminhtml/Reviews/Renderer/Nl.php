<?php
class Ecp_Reviews_Block_Adminhtml_Reviews_Renderer_Nl extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
public function render(Varien_Object $row)
{
$value =  $row->getData($this->getColumn()->getIndex());
return nl2br($value);
}
}
?>