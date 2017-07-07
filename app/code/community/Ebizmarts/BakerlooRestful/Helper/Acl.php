<?php

class Ebizmarts_BakerlooRestful_Helper_Acl extends Mage_Core_Helper_Abstract
{
    /** @var Mage_Admin_Model_User  */
    private $_user;

    /** @var Mage_Admin_Model_Acl  */
    private $_acl;

    public function __construct(
        Mage_Admin_Model_User $user = null,
        Mage_Admin_Model_Acl $acl = null
    )
    {
        if (is_null($user)) {
            $this->_user = Mage::getModel('admin/user');
        } else {
            $this->_user = $user;
        }

        if (is_null($acl)) {
            $this->_acl = Mage::getResourceModel('admin/acl')->loadAcl();
        } else {
            $this->_acl = $acl;
        }
    }

    /**
     * Check if a set of resources is available for a given user.
     *
     * @param $username
     * @param array $perms
     * @return bool
     */
    public function checkPermission($username, array $perms)
    {

        $allow = true;

        foreach ($perms as $_perm) {
            $isUserAllowed = $this->isAllowed($username, $_perm);

            if (!$isUserAllowed) {
                $allow = false;
                break;
            }
        }

        if (!$allow) {
            Mage::throwException($this->__("Not enough privileges or user is not active."));
        }

        return $allow;
    }

    /**
     * Check if given username is allowed to access $resource.
     * If user is not active, it won't be allowed no matter what the role permissions are.
     *
     * @param string $username
     * @param string $resource
     * @return boolean
     */
    public function isAllowed($username, $resource)
    {

        $allowed = false;

        $user = $this->getUser($username);

        if ($user->getId() && (1 === (int)$user->getIsActive())) {

            if (!preg_match('/^admin/', $resource)) {
                $resource = 'admin/' . $resource;
            }

            try {
                if ($this->_acl->isAllowed($user->getAclRole(), 'all', null)) {
                    $allowed = true;
                }
            } catch (Exception $e) {
                $allowed = false;
            }

            try {
                $allowed = $this->_acl->isAllowed($user->getAclRole(), $resource, null);
            } catch (Exception $e) {
                $allowed = false;
            }
        }

        return $allowed;
    }

    /**
     * Retrieve admin/user from database
     *
     * @param string $username
     * @return Mage_Admin_Model_User
     */
    public function getUser($username)
    {
        $this->_user->unsetData();
        $this->_user->loadByUsername($username);
        return $this->_user;
    }
}
