<?php
class Allure_CustomerLoginMonitor_Helper_Data extends Mage_Core_Helper_Abstract
{
    private function config()
    {
        return Mage::helper('customerloginmonitor/config');
    }

    public function add_log($message)
    {
        if (!$this->config()->getDebugStatus()) {
            return;
        }
        Mage::log($message, Zend_log::DEBUG, "customerloginmonitor.log", true);
    }

    public function addLoginInfo($result)
    {
        if (!$this->config()->getModuleStatus()) {
            $this->add_log('Module Disabled');
            return;
        }

        if (!$this->logOf($result))
            return;


        $customer = $this->getCustomer();

        if (!$customer->getId()) {
            $this->add_log($this->getUsername().' customer not found');
        }

        $data['customer_id'] = $customer->getId();
        $data['customer_email'] = ($customer->getEmail())? $customer->getEmail() : $this->getUsername();
        $data['customer_name'] = $customer->getFirstname() . " " . $customer->getLastname();
        $data['remote_ip'] = $this->get_client_ip();
        $data['browser'] = $this->getClientSoftwareInfo('browser');
        $data['status'] = $result['success'] == true ? 'Success' : 'Failed';
        $data['additional_info'] = $result['success'] == false ? $result['error'] : '';

        try {
            Mage::getModel('customerloginmonitor/login')->addData($data)->save();
        } catch (Exception $e) {
            $this->add_log('Exception:' . $e->getMessage());
        }
    }

    private function logOf($result)
    {
        if ($result['success'] == true && ($this->config()->getLogOf() == 'success' || $this->config()->getLogOf() == 'all'))
            return true;
        else if ($result['success'] == false && ($this->config()->getLogOf() == 'failed' || $this->config()->getLogOf() == 'all'))
            return true;
        else
            return false;
    }

    private function get_client_ip()
    {
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if (isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    private function getClientSoftwareInfo($key)
    {
        $data=get_browser(null, true);

        if($data)
        return $data[$key];
    }

    private function getCustomer()
    {
        $username = $this->getUsername();
        return Mage::getModel('customer/customer')->loadByEmail($username);
        return false;
    }

    private function getUsername()
    {
      extract(Mage::app()->getRequest()->getParam('login'));
      return $username;
    }

    public function isWholesaler()
    {
        if(Mage::getSingleton('customer/session')->isLoggedIn()){
            $groupId    = Mage::getSingleton('customer/session')->getCustomerGroupId();
            $group      = Mage::getModel('customer/group')->load($groupId);

            if(strtolower($group->getCode())=="wholesale")
                return true;
        }

        return false;
    }

}