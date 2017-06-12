<?php

class Allure_AdminPermissions_Block_Role_Store extends Mage_Core_Block_Template
{

    /**
     * Get restrict by store ids option
     *
     * @return bool
     */
    public function getRestrictByStore()
    {
        $role = Mage::registry('current_role');
        if ($role && $role->getId()) {
            return (bool) $role->getRestrictByStore();
        }

        return false;
    }
}
