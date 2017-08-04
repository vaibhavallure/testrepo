<?php
class Allure_Vendor_Helper_Data extends Mage_Core_Helper_Abstract {
	
	public function getVanderEmail($vendorId){
		$config = Mage::getStoreConfig('allure_vendor/manage_users/usermapping');
        $config = unserialize($config);
        $email = array();
        
        foreach ($config as $conf) {
            if ($conf['vendor'] == $vendorId) {
                $user_data = Mage::getModel('admin/user')->load($conf['user']);
                if ($user_data->getEmail())
                    array_push($email, $user_data->getEmail());
            }
        }
        
        return array_unique($email);
	}
	public function getVanderName($vendorId){
        $atributeCode = 'primary_vendor';
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $atributeCode);
        $options = $attribute->getSource()->getAllOptions();
        $name = "";
        
        foreach ($options as $key => $value) {
            if ($value['value'] == $vendorId) {
                $name = $value['label'];
                break;
            }
        }
        return $name;
	}
	public function getCurrentUserVendor(){
		$config = Mage::getStoreConfig('allure_vendor/manage_users/usermapping');
        $roleId = Mage::getModel('admin/role')->load('Vendor', 'role_name')->getRoleId();
        $admin_user_session = Mage::getSingleton('admin/session');
        $adminuserId = $admin_user_session->getUser()->getUserId();
        $role_data = Mage::getModel('admin/user')->load($adminuserId)
            ->getRole()
            ->getData();
        $config = unserialize($config);
        $vendor = array();
        if ($role_data['role_id'] == $roleId) {
            foreach ($config as $conf) {
                if ($conf['user'] == $adminuserId) {
                    array_push($vendor, $conf['vendor']);
                }
            }
        }
        /*
         * print_r($vendor);
         * die;
         */
        return $vendor;
	}
	public function isUserVendor(){
		$roleId = Mage::getModel('admin/role')->load('Vendor', 'role_name')->getRoleId();
        $admin_user_session = Mage::getSingleton('admin/session');
        $adminuserId = $admin_user_session->getUser()->getUserId();
        $role_data = Mage::getModel('admin/user')->load($adminuserId)
            ->getRole()
            ->getData();
        if ($role_data['role_id'] == $roleId) {
            return true;
        }
        return false;
	}
	public function getCurrentUserVendorName(){
		$config = Mage::getStoreConfig('allure_vendor/manage_users/usermapping');
        $roleId = Mage::getModel('admin/role')->load('Vendor', 'role_name')->getRoleId();
        $admin_user_session = Mage::getSingleton('admin/session');
        $adminuserId = $admin_user_session->getUser()->getUserId();
        $role_data = Mage::getModel('admin/user')->load($adminuserId)
            ->getRole()
            ->getData();
        $config = unserialize($config);
        $name = "";
        if ($role_data['role_id'] == $roleId) {
            foreach ($config as $conf) {
                if ($conf['user'] == $adminuserId) {
                    $name = $this->getVanderName($conf['vendor']);
                    break;
                }
            }
        }
        /*
         * print_r($vendor);
         * die;
         */
        return $name;
	}
}