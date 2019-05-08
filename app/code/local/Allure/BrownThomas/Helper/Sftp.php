<?php
class Allure_BrownThomas_Helper_Sftp extends Mage_Core_Helper_Abstract
{
    private function config() {
        return Mage::helper("brownthomas/config");
    }

    public function add_log($message) {
        Mage::helper("brownthomas/data")->add_log($message);
    }

    public function connectSFTP()
    {
        if(!$this->config()->isEnabledSFTP())
        {
               $this->add_log("connectSFTP => SFTP disabled from Configuration Setting");
               return false;
        }

       $sftp = new Varien_Io_Sftp();
        try{
            $sftp->open(
                array(
                    'host'      => $this->config()->getHostSFTP(),
                    'username'  => $this->config()->getUsernameSFTP(),
                    'password'  => $this->config()->getPasswordSFTP(),
                    'timeout'   => $this->config()->getTimeoutSFTP()
                )
            );

            return $sftp;

        }catch(Exception $e){
            $this->add_log("connectSFTP => Exception:".$e->getMessage());
        }

        return false;
    }

    public function transferFile($localfilepath,$remotefilepath)
    {

        $sftp=$this->connectSFTP();

        if($sftp)
        {
            if(!file_exists($localfilepath))
            {
                $this->add_log("transferFile => File Not Exist:".$localfilepath);
                return;
            }

            try{
                $file = new Varien_Io_File();
                $filedata=$file->read($localfilepath);
                $sftp->write($remotefilepath,$filedata);
                $this->add_log("File Transfer Successfully=>".$remotefilepath);
                Mage::getModel("brownthomas/data")->fileTransferred($remotefilepath);
                $this->sendEmail($remotefilepath);
            }catch (Exception $e)
            {
                $this->add_log("transferFile => Exception:".$e->getMessage());
            }
        }
    }

    public function sendEmail($msg)
    {
        try{
            $mailSubject="Brown Thomas Inventory Debug";
            $sender         = 'brownthomas@mariatash.com';
            $emails = $this->config()->getDebugEmails();

            $header="from: Brown Thomas INV <".$sender.">";
            mail($emails,$mailSubject,$msg,$header);

        }catch(Exception $e)
        {
            $this->add_log("Exception:".$e->getMessage());
        }

    }


}
