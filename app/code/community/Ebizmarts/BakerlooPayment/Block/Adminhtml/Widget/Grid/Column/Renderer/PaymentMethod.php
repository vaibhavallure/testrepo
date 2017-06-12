<?php

class Ebizmarts_BakerlooPayment_Block_Adminhtml_Widget_Grid_Column_Renderer_PaymentMethod extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $result = parent::render($row);

        if ($row->getId()) {
            $json = unserialize($row->getPaymentData());

            if ($json) {
                $methodCode = $row->getPaymentMethod();
                $methodName = isset($json->payMethod->label) ? $json->payMethod->label : '';

                $result = "[{$methodCode}]  <br/> {$methodName}";
            }
        }



        return $result;
    }
}
