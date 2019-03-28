<?php
/**
 * Data helper
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Helper_Reindex extends Mage_Core_Helper_Abstract
{
    protected $reindexMode;
    public function __construct()
    {
        $this->reindexMode = Mage::getStoreConfig(Teamwork_Transfer_Helper_Config::XML_PATH_ECM_REINDEX_MODE);
    }
    
    public function registerReindex()
    {
        if( $this->reindexMode != 0 )
        {
            $processes = Mage::getSingleton('index/indexer')->getProcessesCollection();
            $processes->walk('setMode', array(Mage_Index_Model_Process::MODE_MANUAL));
        }
    }

    public function runIndexer($ecm, $extraEcm)
    {
        if($this->reindexMode == 0 || ($this->reindexMode == 1 && $extraEcm) )
        {
            return;
        }
        
        $processes = Mage::getSingleton('index/indexer')->getProcessesCollection();
        $processes->walk('setMode', array(Mage_Index_Model_Process::MODE_REAL_TIME));
        
        foreach(Mage::getSingleton('index/indexer')->getProcessesCollection() as $process)
        {
            try
            {
                $process->reindexEverything();
            }
            catch(Exception $e)
            {
                Mage::log($e->getMessage(), null, 'reindex_exception.log');
            }
            if(!empty($ecm))
            {
                $ecm->checkLastUpdateTime();
            }
        }
    }
    
    public function runIndexerByCode($code,$force=false)
    {
        if($this->reindexMode == 0 || $force)
        {
            $process = Mage::getSingleton('index/indexer')->getProcessByCode($code);
            if( !empty($process) )
            {
                try
                {
                    $process->reindexAll();
                }
                catch(Exception $e)
                {
                    Mage::log($e->getMessage(), null, 'reindex_exception.log');
                }
            }
        }
    }
}