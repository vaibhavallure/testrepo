<?php
class Allure_HarrodsInventory_Helper_Cron extends Mage_Core_Helper_Abstract
{
    private function harrodsConfig() {
        return Mage::helper("harrodsinventory/config");
    }
    private function data() {
        return Mage::helper("harrodsinventory/data");
    }
    private function sftp() {
        return Mage::helper("harrodsinventory/sftp");
    }
    public function add_log($message) {
        Mage::helper("harrodsinventory/data")->add_log($message);
    }


    public function generateHarrodsFiles()
    {
        $this->add_log("generateHarrodsFiles => cron call");
        if (!$this->harrodsConfig()->getModuleStatus()) {
            $this->add_log("generateHarrodsFiles => Module Disabled----");
            return;
        }
        $this->PLU_Report();
        $this->STK_Report();
        $this->PPC_Report();
    }

    /*---------------------trigger reports----------------------------------*/
    public function PLU_Report()
    {
        if ($this->harrodsConfig()->getHourProductCron() != $this->getHour($this->getCurrentDatetime()) /*|| $this->harrodsConfig()->getMinuteProductCron() != $this->getMinute($this->getCurrentDatetime())*/)
            return;

            if (!$this->harrodsConfig()->isEnabledProductCron()) {
            $this->add_log("productReport=> product report cron disabled from backend setting");
            return;
        }
        $files= $this->data()->generateReport();
        $this->fileTransfer($files);
    }
    public function STK_Report()
    {
        if ($this->harrodsConfig()->getHourStockCron() != $this->getHour($this->getCurrentDatetime()) /*|| $this->harrodsConfig()->getMinuteStockCron() != $this->getMinute($this->getCurrentDatetime()*/)
            return;
        if (!$this->harrodsConfig()->isEnabledStockCron()) {
            $this->add_log("stockReport=> stock report cron disabled from backend setting");
            return;
        }
        $files= $this->data()->generateSTKReport();
        $this->fileTransfer($files);

    }
    public function PPC_Report()
    {
        if ($this->harrodsConfig()->getHourPriceCron() != $this->getHour($this->getCurrentDatetime()) /*|| $this->harrodsConfig()->getMinutePriceCron() != $this->getMinute($this->getCurrentDatetime())*/)
            return;
        if (!$this->harrodsConfig()->isEnabledPriceCron()) {
            $this->add_log("priceReport=> price report cron disabled from backend setting");
            return;
        }
        $files= $this->data()->generatePPCReport();
        $this->fileTransfer($files);
    }

    /*-----------------------reports trigger end-------------------------------*/


    public function fileTransfer($files)
    {
        if($files)
        {
            $localFilePathTxt=$files['txt'];
            $remoteFilePathTxt= $this->harrodsConfig()->getLocationSFTP()."".pathinfo($files['txt'])['basename'];

            $localFilePathOk=$files['ok'];
            $remoteFilePathOk = $this->harrodsConfig()->getLocationSFTP()."".pathinfo($files['ok'])['basename'];

            $this->sftp()->transferFile($localFilePathTxt,$remoteFilePathTxt);
            $this->sftp()->transferFile($localFilePathOk,$remoteFilePathOk);

        }

    }


    public function getDiffUtc()
    {
         /* -- utc and backend set timezone -- */

        $local_tz = new DateTimeZone('UTC');
        $local = new DateTime('now', $local_tz);


        $user_tz = new DateTimeZone($this->harrodsConfig()->getTimeZone());
        $user = new DateTime('now', $user_tz);

        $usersTime = new DateTime($user->format('Y-m-d H:i:s'));
        $localsTime = new DateTime($local->format('Y-m-d H:i:s'));
        $offset = $local_tz->getOffset($local) - $user_tz->getOffset($user);
        $interval = $usersTime->diff($localsTime);

        if($offset > 0)
            return  $diffZone=$interval->h .' hours'.' '. $interval->i .' minutes';
        else
            return  $diffZone= '-'.$interval->h .' hours'.' '. $interval->i .' minutes';

    }

    public function getCurrentDatetime()
    {
        $user_tz = new DateTimeZone($this->harrodsConfig()->getTimeZone());
        $user = new DateTime('now', $user_tz);
        $usersTime = new DateTime($user->format('Y-m-d H:i:s'));
        $ar=(array)$usersTime;
        $date = $ar['date'];
        return $date = strtotime($date);
    }

    public function getHour($datetime)
    {
       return date('H',  $datetime);
    }

    public function getMinute($datetime)
    {
        return date('i',  $datetime);
    }


}
