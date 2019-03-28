<?php

class Allure_Virtualstore_Block_Adminhtml_System_Store_Grid_Render_Website
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        return '<a title="' . Mage::helper('allure_virtualstore')->__('Edit Website') . '"
            href="' . $this->getUrl('*/*/editWebsite', array('website_id' => $row->getWebsiteId())) . '">'
            . $this->escapeHtml($row->getData($this->getColumn()->getIndex())) . '</a>';
    }

}
