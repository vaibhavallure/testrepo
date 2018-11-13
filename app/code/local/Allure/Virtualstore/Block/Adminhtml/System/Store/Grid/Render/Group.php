<?php


class Allure_Virtualstore_Block_Adminhtml_System_Store_Grid_Render_Group
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if (!$row->getData($this->getColumn()->getIndex())) {
            return null;
        }
        return '<a title="' . Mage::helper('allure_virtualstore')->__('Edit Store') . '"
            href="' . $this->getUrl('*/*/editGroup', array('group_id' => $row->getGroupId())) . '">'
            . $this->escapeHtml($row->getData($this->getColumn()->getIndex())) . '</a>';
    }
}
