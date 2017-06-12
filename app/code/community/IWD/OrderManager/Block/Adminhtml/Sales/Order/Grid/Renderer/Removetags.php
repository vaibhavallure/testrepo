<?php
class IWD_OrderManager_Block_Adminhtml_Sales_Order_Grid_Renderer_Removetags extends IWD_OrderManager_Block_Adminhtml_Sales_Order_Grid_Renderer_Abstract
{
    protected $col;

    public function __construct($col)
    {
        $this->col = $col;
    }

    protected function Grid()
    {
        return strip_tags($this->row[$this->col]);
    }

    protected function Export()
    {
        return strip_tags($this->row[$this->col]);
    }
}
