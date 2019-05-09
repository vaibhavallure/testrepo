<?php
class Allure_BrownThomas_Helper_Cron extends Mage_Core_Helper_Abstract
{
    private function config() {
        return Mage::helper("brownthomas/config");
    }
    private function data() {
        return Mage::helper("brownthomas/data");
    }
    private function sftp() {
        return Mage::helper("brownthomas/sftp");
    }
    public function add_log($message) {
        Mage::helper("brownthomas/data")->add_log($message);
    }

    public function generateBrownthomasFiles()
    {
        if (!$this->config()->getModuleStatus()) {
            $this->add_log("generateBrownthomasFiles => Module Disabled----");
            return;
        }

        $this->callStockFile();
        $this->callFoundationFile();
        $this->callEnrichFile();
    }

    public function callStockFile()
    {
        if ($this->config()->getHourStockCron() != $this->getHour($this->getCurrentDatetime()))
            return;

        if (!$this->config()->isEnabledStockCron()) {
            $this->add_log("callStockFile=> stock report cron disabled from backend setting");
            return;
        }

        if($this->data()->checkFileTransferred(Allure_BrownThomas_Helper_Data::STOCK_FILE))
        {
            $this->add_log(Allure_BrownThomas_Helper_Data::STOCK_FILE." File Already sent");
            return;
        }


        $file = $this->data()->generateStockFile();
        $location=$this->getFileWriteLocation('stock');
        $this->fileTransfer($file,$location);
    }

    public function callFoundationFile()
    {
        if ($this->config()->getHourDataFileCron() != $this->getHour($this->getCurrentDatetime()))
            return;

        if (!$this->config()->isEnabledDataFileCron()) {
            $this->add_log("callFoundationFile=> Foundation report cron disabled from backend setting");
            return;
        }

        if($this->data()->checkFileTransferred(Allure_BrownThomas_Helper_Data::FOUNDATION_FILE))
        {
            $this->add_log(Allure_BrownThomas_Helper_Data::FOUNDATION_FILE." File Already sent");
            return;
        }


        $file = $this->data()->generateFoundationFile();

        if($this->data()->checkFileTransferred(Allure_BrownThomas_Helper_Data::FOUNDATION_FILE))
        {
            $this->add_log(Allure_BrownThomas_Helper_Data::FOUNDATION_FILE." File Already sent");
            return;
        }

        $location=$this->getFileWriteLocation('data');
        $this->fileTransfer($file,$location);
    }

    public function callEnrichFile()
    {
        if ($this->config()->getHourEnrichmentCron() != $this->getHour($this->getCurrentDatetime()))
            return;

        if (!$this->config()->isEnabledEnrichmentCron()) {
            $this->add_log("callFoundationFile=> Foundation report cron disabled from backend setting");
            return;
        }

        if($this->data()->checkFileTransferred(Allure_BrownThomas_Helper_Data::ENRICHMENT_FILE))
        {
            $this->add_log(Allure_BrownThomas_Helper_Data::ENRICHMENT_FILE." File Already sent");
            return;
        }

        $location=$this->getFileWriteLocation('Enrich');
        $this->fileTransfer($this->data()->getEnrichmentFilePath(),$location);
    }

    public function fileTransfer($file,$location)
    {
        if($file)
        {
            $localFilePath=$file;
            $remoteFilePath= $location."".pathinfo($file)['basename'];
            $this->sftp()->transferFile($localFilePath,$remoteFilePath);
        }
    }


    public function getDiffUtc()
    {
         /* -- utc and backend set timezone -- */

        $local_tz = new DateTimeZone('UTC');
        $local = new DateTime('now', $local_tz);


        $user_tz = new DateTimeZone($this->config()->getTimeZone());
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
        $user_tz = new DateTimeZone($this->config()->getTimeZone());
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

    public function getFileWriteLocation($file_type)
    {
       $fileLocationFun="getFileLocation".ucfirst($file_type);

       $location=$this->config()->$fileLocationFun();

       if(!empty($location))
           return $location;
       else
           return $this->config()->getLocationSFTP();
    }


}
