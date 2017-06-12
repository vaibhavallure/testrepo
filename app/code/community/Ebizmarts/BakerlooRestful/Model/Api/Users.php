<?php

class Ebizmarts_BakerlooRestful_Model_Api_Users extends Ebizmarts_BakerlooRestful_Model_Api_Api
{
    const USER_ID       = 'user_id';
    const FIRSTNAME     = 'firstname';
    const LASTNAME      = 'lastname';
    const EMAIL         = 'email';
    const USERNAME      = 'username';
    const CREATED       = 'created';
    const MODIFIED      = 'modified';
    const IS_ACTIVE     = 'is_active';
    const PERMISSIONS   = 'permissions';
    const PINCODE       = 'pin_code';

    public $defaultSort   = "modified";
    public $pageSize      = 200;
    protected $_model     = "admin/user";
    private $_permissions = array();

    protected function _getIndexId()
    {
        return 'user_id';
    }

    public function post()
    {
        Mage::throwException('Not implemented.');
    }

    /**
     * Validate provided credentials
     *
     * PUT
     */
    public function put()
    {

        $data = $this->getJsonPayload();

        /** @var $user Mage_Admin_Model_User */
        $user = $this->_authenticate($data->username, $data->password);

        $return = array();

        if ($user->getId()) {
            $return = $this->_createDataObject(null, $user);
        } else {
            Mage::throwException('Invalid login.');
        }

        return $return;
    }

    private function _authenticate($username, $password)
    {
        $config = Mage::getStoreConfigFlag('admin/security/use_case_sensitive_login');

        try {
            /** @var Mage_Admin_Model_User $user */
            $user      = $this->getModel($this->_model)->loadByUsername($username);
            $sensitive = ($config) ? $username == $user->getUsername() : true;

            if ($sensitive && $user->getId() && $this->getHelper('core')->validateHash($password, $user->getPassword())) {
                if ($user->getIsActive() != '1') {
                    Mage::throwException('This account is inactive.');
                }
                if (!$user->hasAssigned2Role($user->getId())) {
                    Mage::throwException('Access denied.');
                }
            } else {
                $user = new Varien_Object;
            }
        } catch (Mage_Core_Exception $e) {
            throw $e;
        }

        return $user;
    }

    public function _createDataObject($id = null, $data = null)
    {

        if (is_null($data)) {
            $user = $this->getModel($this->_model)->load($id);
        } else {
            $user = $data;
        }

        /** @var Mage_Admin_Model_User $user */
        if (is_null($user->getCreated()) or is_null($user->getModified())) {
            $user->save();
        }

        $result = array(
            self::USER_ID       => (int) $user->getId(),
            self::FIRSTNAME     => $user->getFirstname(),
            self::LASTNAME      => $user->getLastname(),
            self::EMAIL         => $user->getEmail(),
            self::USERNAME      => $user->getUsername(),
            self::CREATED       => $user->getCreated(),
            self::MODIFIED      => $user->getModified(),
            self::IS_ACTIVE     => (int) $user->getIsActive(),
            self::PERMISSIONS   => $this->getPermissions($user->getRole()->getId()),
            self::PINCODE       => $this->_getUserPincode($user->getId())
        );

        return $result;
    }

    public function getPermissions($roleId)
    {
        if (!array_key_exists($roleId, $this->_permissions)) {
            $resources = $this->getModel('admin/roles')->getResourcesList();

            $rulesSet = $this->getResourceModel('admin/rules_collection')
                            ->getByRoles($roleId)
                            ->load();
            $acl = array();
            foreach ($rulesSet->getItems() as $item) {
                $itemResourceId = $item->getResourceId();

                if ('all' == $itemResourceId) {
                    $acl ['all']= $item->getPermission();
                    continue;
                }

                if (!preg_match('/^admin\/bakerloo_api/', $itemResourceId)) {
                    continue;
                }

                if ($itemResourceId == 'admin/bakerloo_api') {
                    continue;
                }

                if (array_key_exists(strtolower($itemResourceId), $resources)) {
                    $acl [ str_replace('admin/bakerloo_api/', '', $itemResourceId) ]= $item->getPermission();
                }
            }

            if (empty($acl)) {
                $acl = new stdClass;
            }

            $this->_permissions[$roleId] = $acl;
        }

        return $this->_permissions[$roleId];
    }

    private function _getUserPincode($userId)
    {
        return (string)$this->getModel('bakerloo_restful/pincode')->load($userId, 'admin_user_id')->getPincode();
    }
}
