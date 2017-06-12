<?php

class Ebizmarts_BakerlooRestful_Model_Observer_Adminuser
{

    /**
     * Observe event admin_roles_save_after.
     *
     * @param $observer
     * @return $this
     */
    public function refreshUsersPermissions($observer)
    {
        $role = $observer->getEvent()->getObject();

        if (!is_object($role)) {
            return $this;
        }

        $roleUsers = $role->getRoleUsers();

        foreach ($roleUsers as $_userId) {
            $user = $this->getUserModel()->load($_userId);

            if ($user->getId()) {
                $user->setModified(Mage::getModel('core/date')->gmtDate())
                    ->save();
            }
        }
    }

    public function getUserModel()
    {
        return Mage::getModel('admin/user');
    }

    public function getPincodeModel()
    {
        return Mage::getModel('bakerloo_restful/pincode');
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function savePincode(Varien_Event_Observer $observer)
    {
        $user = $observer->getEvent()->getObject();

        if (!is_object($user)) {
            return $this;
        }

        $userId = $user->getId();
        $userPincode = Mage::app()->getRequest()->getParam('pos_pin_code');

        if (isset($userPincode) and $userPincode != '****') {
            try {
                $pincode = $this->getPincodeModel()->load($userId, 'admin_user_id');

                if (!$pincode->getAdminUserId()) {
                    $pincode->setAdminUserId($userId);
                }

                if ($pincode->getPincode() != $userPincode) {
                    $pincode->savePincode($userPincode);
                }
            } catch (Mage_Exception $e) {
                Mage::getSingleton('core/session')->addError('Couldn\'t save your pin: ' . $e->getMessage());
            }
        }

        return $this;
    }
}
