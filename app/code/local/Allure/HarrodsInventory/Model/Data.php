<?php

class Allure_HarrodsInventory_Model_Data extends Mage_Core_Model_Abstract{
    public function writeConnection()
    {
        $resource = Mage::getSingleton('core/resource');
        return $resource->getConnection('core_write');
    }
    private function cron() {
        return Mage::helper("harrodsinventory/cron");
    }
    public function add_log($message) {
        Mage::helper("harrodsinventory/data")->add_log($message);
    }
    private function readConnection()
    {
        $resource = Mage::getSingleton('core/resource');
        return $resource->getConnection('core_read');
    }


    public function fileTransfer($file)
    {
        try {
            $query = "INSERT INTO `allure_harrodsinventory_file_transfer`(`file`, `date`) VALUES ('".$file."','".date('Y-m-d h:i:s',$this->cron()->getCurrentDatetime())."')";
            $this->writeConnection()->query($query);
        }
        catch(Exception $e)
        {
            $this->add_log("fileTransfer() Exception".$e->getMessage());
        }
    }
    public function checkFileTransferred($file)
    {

        try {
             $query = 'SELECT * FROM `allure_harrodsinventory_file_transfer` WHERE `file` LIKE "%' . $file . '%" AND `date` LIKE "%' . date('Y-m-d', $this->cron()->getCurrentDatetime()) . '%"';

            $row = $this->readConnection()->fetchCol($query);

            if (count($row))
                return true;
            else
                return false;
        }
        catch(Exception $e)
        {
            $this->add_log("checkFileTransferred() Exception".$e->getMessage());
        }
    }
}
