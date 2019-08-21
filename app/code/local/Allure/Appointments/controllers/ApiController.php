<?php
/*
 * Appointment API for get and set Data
 *
 * Company : ALLURE
 * Developer: Aditya
 *
 * Last Modified Date:2019/07/25
 * */

class Allure_Appointments_ApiController extends Mage_Core_Controller_Front_Action
{


    const TOKEN = "9eeae7-2bf54b-e0ee9b-5d0fbf-88224b";
    const MODEL_CUSTOMER = "appointments/customers";
    const MODEL_APPOINTMENT = "appointments/appointments";

    private $result = array();

    private $api = array(
        "getcustomer" => "getCustomer",
        "getappointment" => "getAppointment",
        "setcustomer" => "setCustomer"
    );
    private $table = array(
        "getcustomer" => "allure_appointment_customers",
        "getappointment" => "allure_piercing_appointments",
        "setcustomer" => "allure_appointment_customers",

    );

    public function indexAction()
    {
        if ($this->validateToken()) {
            if ($this->validateApi()) {
                $function = $this->api[$this->getReq()];
                $this->$function();
            }
        }
        $this->result();
    }

    private function validateApi()
    {
        $this->result['success'] = true;

        if (array_key_exists($this->getReq(), $this->api)) {
            $this->result['api'] = 'Found';
        } else {
            $this->result['success'] = false;
            $this->result['api'] = 'Not Found';
        }
        if (count($this->getFilter()) < 1) {
            $this->result['success'] = false;
            $this->result['api'] = 'One Filter Mandatory';
        }
        if (count($this->getSelect()) < 1) {
            $this->result['success'] = false;
            $this->result['api'] = 'Invalid field to select';
        }

        if (!$this->validateFilter($this->table[$this->getReq()])) {
            $this->result['success'] = false;
        }

        if (!$this->validateSelect($this->table[$this->getReq()])) {
            $this->result['success'] = false;
        }

        if ($this->getReq() == "setcustomer") {
            if (!$this->validateChange($this->table[$this->getReq()])) {
                $this->result['success'] = false;
            }
        }

        if ($this->result['success'])
            return true;
        else
            return false;

    }

    private function getCustomer()
    {

        if ($this->validateCustomer()) {
            $this->result['customer'] = $this->getCustomerCollection()->getData();
        }

    }


    private function getAppointment()
    {
        if ($this->validateAppointment()) {
            $this->result['appointment'] = $this->getAppointmentCollection()->getData();
        }
    }

    private function setCustomer()
    {
        if ($this->validateCustomer()) {
            $this->updateCustomer();
        }
    }

    private function updateCustomer()
    {
        try {
            foreach ($this->getCustomerCollection() as $customer) {
                $model = Mage::getModel(self::MODEL_CUSTOMER)->load($customer->getId());
                $model->addData($this->getChange());
                $model->save();
            }
        } catch (Exception $e) {
            $this->result['success'] = false;
            $this->result['error'][] = $e->getMessage();
        }
    }

    private function validateAppointment()
    {
        if ($this->getAppointmentCollection()->getSize()) {
            $this->result['success'] = true;
            return true;
        } else {
            $this->result['success'] = false;
            $this->result['error'][] = 'record not found';
            return false;
        }
    }

    private function validateCustomer()
    {
        if ($this->getCustomerCollection()->getSize()) {
            $this->result['success'] = true;
            return true;
        } else {
            $this->result['success'] = false;
            $this->result['error'][] = 'record not found';
            return false;
        }
    }

    private function getAppointmentCollection()
    {
        try {
            $collection = Mage::getModel(self::MODEL_APPOINTMENT)->getCollection();

            foreach ($this->getFilter() as $filter_key => $filter_value) {
                $collection->addFieldToFilter($filter_key, $filter_value);
            }
            $collection->addFieldToSelect($this->getSelect());
            return $collection;
        } catch (Exception $e) {
            $this->result['success'] = false;
            $this->result['error'][] = $e->getMessage();
        }
    }

    private function getCustomerCollection()
    {
        try {
            $collection = Mage::getModel(self::MODEL_CUSTOMER)->getCollection();

            foreach ($this->getFilter() as $filter_key => $filter_value) {
                $collection->addFieldToFilter($filter_key, $filter_value);
            }

            $collection->addFieldToSelect($this->getSelect());
            return $collection;
        } catch (Exception $e) {
            $this->result['success'] = false;
            $this->result['error'][] = $e->getMessage();
        }
    }

    private function validateToken()
    {
        if ($_SERVER['HTTP_TOKEN'] != self::TOKEN) {
            $this->result['success'] = false;
            $this->result['error'][] = "Invalid Token";
            return false;
        }

        return true;
    }

    private function validateColumns($table, $column_name)
    {
        $query = "SHOW COLUMNS FROM `" . $table . "` LIKE '" . $column_name . "'";
        $column = $this->read()->fetchCol($query);

        return count($column);
    }

    private function validateFilter($table)
    {
        foreach ($this->getFilter() as $filter_key => $filter_value) {
            if ($this->validateColumns($table, $filter_key) == 0)
                $this->result['error'][] = $filter_key . " Invalid Column Name In Filter";
        }

        if (count($this->result['error']) > 0) {
            $this->result['success'] = false;
            return false;
        } else {
            $this->result['success'] = true;
            return true;
        }
    }

    private function validateChange($table)
    {
        foreach ($this->getChange() as $change_key => $filter_value) {
            if ($this->validateColumns($table, $change_key) == 0)
                $this->result['error'][] = $change_key . " Invalid Column Name In Change";
        }

        if (count($this->result['error']) > 0 || empty($this->getChange())) {
            $this->result['success'] = false;
            return false;
        } else {
            $this->result['success'] = true;
            return true;
        }
    }

    private function validateSelect($table)
    {
        if (count($this->getSelect()) == 1)
            if ($this->getSelect()[0] == '*')
                return $this->result['success'] = true;

        foreach ($this->getSelect() as $select) {
            if ($this->validateColumns($table, $select) == 0)
                $this->result['error'][] = $select . " Invalid Column Name In Select";
        }

        if (count($this->result['error']) > 0)
            return $this->result['success'] = false;
        else
            return $this->result['success'] = true;

    }

    private function read()
    {
        $resource = Mage::getSingleton('core/resource');
        return $readConnection = $resource->getConnection('core_read');
    }

    private function getReq()
    {
        return $this->getRequest()->getParam('api_name');
    }

    private function getFilter()
    {
        return $this->getRequest()->getParam('filter');
    }

    private function getChange()
    {
        return $this->getRequest()->getParam('change');
    }

    private function getSelect()
    {
        if (empty($this->getRequest()->getParam('select')))
            return array('*');

        return $this->getRequest()->getParam('select');
    }

    private function result()
    {
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($this->result));
    }
}
