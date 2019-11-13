<?php
class Allure_Appointments_Block_Adminhtml_Appointments_Edit_Renderer_Modify
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $key = Mage::getSingleton('adminhtml/url')->getSecretKey("admin_appointments/adminhtml_index/", "modify");

        if (!empty($row->getId()) && !empty($row->getEmail())) {
//            if ($row->getSpecialStore()) {

                if ($row->getSpecialStore() == 2 || $row->getSpecialStore() == 0) {
                    echo '<a target="_blank"  href=' . Mage::helper('adminhtml')->getUrl('admin_appointments/adminhtml_appointments/newsystem/user/admin/system/'.$row->getSpecialStore().'/id/' . $row->getId()), array('_secure' => true, 'key' => $key) . '>Modify</a>';
                } else {
                    echo '<a target="_blank"  href=' . Mage::helper('adminhtml')->getUrl('admin_appointments/adminhtml_appointments/newspecial/user/admin/id/' . $row->getId()), array('_secure' => true, 'key' => $key) . '>Modify</a>';
                }

            /*} else {
                echo '<a target="_blank"  href=' . Mage::helper('adminhtml')->getUrl('admin_appointments/adminhtml_index/modify/id/' . $row->getId() . '/email/' . $row->getEmail()), array('_secure' => true, 'key' => $key) . '>Modify</a>';
            }*/

        }
    }
}
?>