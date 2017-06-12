<?php
/**
 * @package Ecp
 * @todo  
 */
class Ecp_Backup_Adminhtml_BackupController extends Mage_Adminhtml_Controller_action {

    /**
     *
     * @var string
     * @access private 
     */
    private $xml = 'local.xml';
    
    /**
     *
     * @var string
     * @access private 
     */
    private $filePath = null;
    
    /**
     *
     * @var string
     * @access private
     */
    
    private $zipFile = null;

    /**
     * @return file
     */
       
    public function indexAction() {
        if (is_dir(Mage::getBaseDir('var'). DS .'tmp') == false){
            mkdir(Mage::getBaseDir('var'). DS .'tmp'); 
        }            
        $this->xml = simplexml_load_file(Mage::getBaseDir('etc'). DS .$this->xml, NULL, LIBXML_NOCDATA);

        $db['host'] = $this->xml->global->resources->default_setup->connection->host;
        $db['name'] = $this->xml->global->resources->default_setup->connection->dbname;
        $db['user'] = $this->xml->global->resources->default_setup->connection->username;
        $db['pass'] = $this->xml->global->resources->default_setup->connection->password;
        $db['pref'] = $this->xml->global->resources->db->table_prefix;

        $this->filePath = Mage::getBaseDir('tmp'). DS .$db['name'].'.sql';
        $this->zipFile = $db['name'].'_DB_'.date('Ymd').'.zip';

        $command = "mysqldump --opt --host=" . $db['host'] . " --password=" . $db['pass'] . " --user=" . $db['user'] . " " . $db['name'] . " -r \"" . $this->filePath . "\" 2>&1 ";
        system($command);
        $replaceFile = Mage::getStoreConfig('ecp_backup/backup/value');
        $localUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);       

        $string = file_get_contents($this->filePath);
        $fileName = str_replace($localUrl, $replaceFile, $string);
        $putFile = file_put_contents($this->filePath, $fileName);
        /// comprime archivo
        $enzip = new ZipArchive();        
        if ($enzip->open(Mage::getBaseDir('tmp'). DS .$this->zipFile, ZIPARCHIVE::CREATE) !== TRUE) {
            Mage::getSingleton('core/session')->addError('error al cargar');
            $this->_redirect('ecp_backup/backupcontroller');
        }
        //$enzip->addFile($this->filePath);
        $enzip->addFromString($db['name'].'.sql',file_get_contents($this->filePath));
        $enzip->close();
        header("Content-type: application/octet-stream");
        header("Content-Description: File Transfer"); 
        header("Content-Disposition: attachment; filename= $this->zipFile");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download"); 
        header("Expires: 0");
        $fp = fopen(Mage::getBaseDir('tmp'). DS .$this->zipFile, "r");
        echo fpassthru($fp);
        fclose($fp);
    }


    // Fixed SUPEE 6285 ACL bug
    function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/ecp_backup');
    }
}
