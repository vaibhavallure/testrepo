<?php

class Allure_TeamworkDam_Model_Cron
{
    private const NUMBER_OF_IMAGES_SEND_IN_ONE_PROCESS=100;
    private  const XML_PATH_MODULE_ENABLED = 'teamworkdam/module_status/module_enabled';



    public function sendImages()
    {
        $this->log("new scheduled call to start process");

        if(!$this->isModuleEnabled())
        {
            $this->log("Module disabled");
            return;
        }
        if(!$this->hasImagesToSend())
        {
            $this->log("no new images found to send");
            return;
        }

        if($this->prcessRunning()) {
            $this->log("Another process is running");
            return;
        }

        $process = $this->startProcess("sync Images start");
        $this->syncImages();
        $process->end("sync Images end");

    }
    private function syncImages()
    {
        $collection = Mage::getModel('teamworkdam/image')->getCollection();
        $collection->getSelect()->limit($this->getNumberOfImagesPerProcess());

        $this->log("STARTING SYNC FOR IMAGES:".$collection->getSize());

        foreach ($collection as $image)
        {
            $imageData=[
                'plu'=>$image->getTeamworkPlu(),
                'name'=>$image->getImageName(),
                'data'=>$image->getImage()
            ];

            if($this->getDamClient()->syncImage($imageData))
            {
                $image->delete();
                $this->log("image sent successfully and deleted from table".$image->getProductId());
            }
            else{
                $this->log("image not sent".$image->getProductId());
            }
        }
    }

    private function startProcess($info)
    {
        return Mage::getModel('teamworkdam/process')->start($info);
    }


    private function prcessRunning()
    {
        $collection = Mage::getModel('teamworkdam/process')->getCollection();
        $collection->addFieldToFilter('process_status', array('eq' => Allure_TeamworkDam_Model_Process::STATUS_RUNNING));

        if ($collection->getSize())
            return true;

        return false;
    }
    private function hasImagesToSend()
    {
        $collection = Mage::getModel('teamworkdam/image')->getCollection();

        if ($collection->getSize())
            return true;

        return false;
    }

    private function log($message)
    {
        $message="CRON::".$message." (".Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s').")";
        Mage::log($message,7,"teamwork_dam_api.log",true);
    }

    private function isModuleEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_MODULE_ENABLED);
    }

    private function getNumberOfImagesPerProcess()
    {
        if(is_numeric(Mage::getStoreConfig("teamworkdam/module_status/number_of_images")))
        {
            return  Mage::getStoreConfig("teamworkdam/module_status/number_of_images");
        }

        return self::NUMBER_OF_IMAGES_SEND_IN_ONE_PROCESS;  
    }


    private function getDamClient(){
        return Mage::helper("teamworkdam/teamworkDAMClient");
    }

}