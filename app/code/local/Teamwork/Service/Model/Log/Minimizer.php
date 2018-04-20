<?php
class Teamwork_Service_Model_Log_Minimizer extends Mage_Core_Helper_Abstract
{
    public $archiver, $minimizeAmount;
    protected $_filesToMinimize = array(
        'teamwork_service.log',
        'chqapi.log',
        'teamwork_transfer.log',
        'teamwork_cegiftcards.log',
        'teamwork_eegiftcards.log',
        'uc.log',
        'rta.log',
    );
    const DEBUG_LOG_FILE = 'teamwork_transfer.log';
    
    public function __construct()
    {
        $this->archiver = Mage::helper('teamwork_service/archiver');
        $this->minimizeAmount = (int)(Mage::getStoreConfig(Teamwork_Service_Helper_Config::XML_PATH_LOG_API_MINIMIZE_SIZE));
    }
    
    public function archive()
    {
        if( $this->minimizeAmount > 0 )
        {
            foreach($this->_filesToMinimize as $file)
            {
                $filepath = Mage::getBaseDir('var') . DS . 'log' . DS . $file;
                if( file_exists($filepath) &&
                    ((filesize($filepath) / pow(1024, 2)) > $this->minimizeAmount)
                )
                {
                    try
                    {
                        $created = $this->archiver->ownCompress($filepath);
                    }
                    catch(Exception $e)
                    {
                        Mage::log($e->getMessage(), null, self::DEBUG_LOG_FILE);
                        Mage::log($e->getTraceAsString(), null, self::DEBUG_LOG_FILE);
                    }
                    if($created)
                    {
                        unlink($filepath);
                    }
                }
            }
        }
    }
}