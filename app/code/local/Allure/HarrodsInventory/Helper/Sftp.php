<?php
class Allure_HarrodsInventory_Helper_Sftp extends Mage_Core_Helper_Abstract
{
    private function harrodsConfig() {
        return Mage::helper("harrodsinventory/config");
    }

    public function add_log($message) {
        Mage::helper("harrodsinventory/data")->add_log($message);
    }

    public function connectSFTP()
    {
        if(!$this->harrodsConfig()->isEnabledSFTP())
        {
               $this->add_log("connectSFTP => SFTP disabled from Configuration Setting");
               return false;
        }

       $sftp = new Varien_Io_Sftp();
        try{
            $sftp->open(
                array(
                    'host'      => $this->harrodsConfig()->getHostSFTP(),
                    'username'  => $this->harrodsConfig()->getUsernameSFTP(),
                    'password'  => $this->harrodsConfig()->getPasswordSFTP(),
                    'timeout'   => $this->harrodsConfig()->getTimeoutSFTP()
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

                $this->sendEmail("File Transfer Successfully=>".$remotefilepath);
                Mage::getModel("harrodsinventory/data")->fileTransfer($remotefilepath);

            }catch (Exception $e)
            {
                $this->add_log("transferFile => Exception:".$e->getMessage());
            }
        }
    }

    public function readFile($remotefilepath,$localfilepath)
    {

        $sftp=$this->connectSFTP();

        if($sftp)
        {
            try{
                $file = new Varien_Io_File();
                $sftp->read($remotefilepath,$localfilepath);

                if(file_exists($localfilepath))
                $this->add_log("File Downloaded On Server=>".$remotefilepath);

            }catch (Exception $e)
            {
                $this->add_log("readFile => Exception:".$e->getMessage());
            }
        }
    }



    public function sendEmail($msg)
    {
        try{
            $mailSubject="Harrods Inventory Debug";
            $sender         = 'harrodsinv@mariatash.com';
            $emails = $this->harrodsConfig()->getDebugEmails();

            $header="from: Harrods INV <".$sender.">";
            mail($emails,$mailSubject,$msg,$header);

        }catch(Exception $e)
        {
            $this->add_log("Exception:".$e->getMessage());
        }

    }

}
