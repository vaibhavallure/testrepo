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
    private function model_data() {
        return Mage::getModel("harrodsinventory/data");
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

        if($this->model_data()->checkFileTransferred("PLU"))
        {
            $this->add_log("PLU File Already sent Or Empty");
            return;
        }

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

        if($this->model_data()->checkFileTransferred("STK"))
        {
            $this->add_log("STK File Already sent");
            return;
        }


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

        if($this->model_data()->checkFileTransferred("PPC"))
        {
            $this->add_log("PPC File Already sent");
            return;
        }


        if (!$this->harrodsConfig()->isEnabledPriceCron()) {
            $this->add_log("priceReport=> price report cron disabled from backend setting");
            return;
        }
        $files= $this->data()->generatePPCReport();
        $this->fileTransfer($files);
    }


    public function sendDailySales()
    {
        if (!$this->harrodsConfig()->getDailySales('enabled')) {
            $this->add_log("sendDailySales=> daily sales report cron disabled from backend setting");
            return;
        }

        $file=$this->sftp()->readFile($this->getPath(),$this->getPath('write'));
        if(file_exists($file))
            $this->sendEmail($file);
        else
            $this->add_log("File Not Found to Sent");
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

    public function getPath($operation='read')
    {
        $tag='<date>';
        $path = Mage::getBaseDir('var') . DS . 'harrodsFiles';
        $remoteFile=$this->harrodsConfig()->getDailySales('location');
        $dateFormat = $this->get_string_between($remoteFile, $tag, $tag);
        $date =date($dateFormat, $this->getCurrentDatetime('yesterday'));
        $remoteFile=str_replace($tag.''.$dateFormat.''.$tag,$date,$remoteFile);

        if($operation=='write')
            return  $path . DS .pathinfo($remoteFile)['basename'];
        else
            return $remoteFile;
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

    public function getCurrentDatetime($day='now')
    {
        $user_tz = new DateTimeZone($this->harrodsConfig()->getTimeZone());
        $user = new DateTime($day, $user_tz);
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

    public function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }




    public function sendEmail($file)
    {

            $templateId = $this->harrodsConfig()->getDailySales('email_temp');

            $mailTemplate = Mage::getModel('core/email_template');
            $storeId = Mage::app()->getStore()->getId();
            $senderName = 'harrods daily sales report';
            $senderEmail = 'harrodsinv@mariatash.com';

            $sender = array('name' => $senderName,
                'email' => $senderEmail);
            $recieverEmails = $this->harrodsConfig()->getDailySales('group_emails');
            $recieverNames = $this->harrodsConfig()->getDailySales('group_names');

            $recipientEmails = explode(',',$recieverEmails);
            $recipientNames = explode(',',$recieverNames);


            $emailTemplateVariables['store_name'] = Mage::app()->getStore()->getName();
            $emailTemplateVariables['store_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

            if(file_exists($file)){
                    $name = pathinfo($file)['basename'];
                    $mailTemplate->getMail()->createAttachment(
                        file_get_contents($file),
                        Zend_Mime::TYPE_OCTETSTREAM,
                        Zend_Mime::DISPOSITION_ATTACHMENT,
                        Zend_Mime::ENCODING_BASE64,
                        $name
                    );
            }


            try {
                $mailTemplate
                    ->sendTransactional(
                        $templateId,
                        $sender,
                        $recipientEmails, //here comes recipient emails
                        $recipientNames, // here comes recipient names
                        $emailTemplateVariables,
                        $storeId
                    );

                if ($mailTemplate->getSentSuccess()) {
                    $this->add_log("daily sales report email sent");
                    return true;
                }

                return false;
            }
            catch (Exception $e){
                    $this->add_log('send email daily sales report exception=>'.$e->getMessage());
            }

    }


}
