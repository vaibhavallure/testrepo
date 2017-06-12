<?php

class Allure_AdminPermissions_Model_Permissions extends Mage_Core_Model_Abstract
{

    /**
     * Get all of the admin user store restrictions if they exist
     *
     * @param bool $user
     * @return array|bool
     */
    public function getStoreRestrictions($user = false)
    {
        if (!$user) {
            $user = Mage::getSingleton('admin/session')->getUser();
        }
        if (!$user) {
            return false;
        }

        $roles = $user->getRoles();
        $totalRestrictions = array();
        foreach ($roles as $roleId) {
            $role = Mage::getModel('admin/role')->load($roleId);
            if ($role && $role->getRestrictByStore()) {
                if ($storeRestrictions = $user->getStoreRestrictions()) {
                    foreach (explode(',', $storeRestrictions) as $storeRestriction) {
                        $totalRestrictions[] = $storeRestriction;
                    }
                }
            }
        }
        $totalRestrictions = array_unique($totalRestrictions);

        if (empty($totalRestrictions)) {
            return false;
        }

        return $totalRestrictions;
    }

}
