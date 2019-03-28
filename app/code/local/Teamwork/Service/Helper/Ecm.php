<?php
class Teamwork_Service_Helper_Ecm extends Mage_Core_Helper_Abstract
{
    public function validate($channelGuid)
    {
        $errorMessage = null;
        if( !$this->channelExists($channelGuid) )
        {
            $errorMessage = Mage::helper('teamwork_service')->__('Wrong channel name');
        }
        
		// TODO: remove mapping validation for CHQ 5.0
        if( !$this->mappingExists($channelGuid) )
        {
            $errorMessage = Mage::helper('teamwork_service')->__('Setting not found. Please resave channel.');
        }
        
        if( !empty($errorMessage) )
        {
            throw new Exception($errorMessage);
        }
    }
    
    public function channelExists($channelGuid)
    {
        $channelName = Mage::helper('teamwork_service')->getChannelNameById($channelGuid);
        if( !empty($channelName) )
        {
            $channelName = trim(strtolower($channelName));
            foreach(Mage::app()->getStores() as $store)
            {
                if($channelName == $store->getCode())
                {
                    return true;
                }
            }
        }
        return false;
    }
    
    public function mappingExists($channelGuid)
    {
        $mapping = Mage::getModel('teamwork_service/mapping')->getMapping(true);
        if( !empty($mapping) )
        {
            foreach($mapping as $map)
            {
                if(isset($map[$channelGuid]))
                {
                    return true;
                }
            }
        }
        return false;
    }
    
    public function writeLog($content,$request_id,$counter,$xml)
    {
        $directory = Mage::helper('teamwork_service')->getTempDir();
        $counter_for_folder = str_pad((string)$counter, strlen($xml->NumberOfChunks), '0', STR_PAD_LEFT);
        $file = $directory . date('Ymd') . DS .  "{$request_id}" . DS . "{$counter_for_folder} - {$xml->Chunk}.xml";
        file_put_contents($file, $content);
    }
    
    public function prepareImageFolder()
    {
        $directory = Mage::helper('teamwork_service')->getTempDir();
        if(!is_dir($directory . 'images'))
        {
            mkdir($directory . 'images', 0777, true);
        }
    }
    
    public function prepareEcmFolder($request_id)
    {
        $directory = Mage::helper('teamwork_service')->getTempDir();
        if(!is_dir($directory . date('Ymd') . '/' . $request_id))
        {
            mkdir($directory . date('Ymd') . '/' . $request_id, 0777, true);
        }
    }
}