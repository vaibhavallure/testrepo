<?php
class Teamwork_Service_Helper_Archiver extends Mage_Core_Helper_Abstract
{
    protected $_archiveClassMapping = array(
        'gz'    => 'Zend_Filter_Compress_Gz',
        'zip'   => 'Zend_Filter_Compress_Zip',
        'tar'   => 'Zend_Filter_Compress_Tar',
        'rar'   => 'Zend_Filter_Compress_Rar',
        'bz2'   => 'Zend_Filter_Compress_Bz2',
    );
    public $archiveSuffix, $fullArchiveName;
    
    public function __construct()
    {
        $this->archiveSuffix = '_' . date('YmdHis');
    }
    
    public function ownCompress($fullFilePath)
    {
        $zip = Mage::getModel('teamwork_service/library_zip');
        $this->generateArchiveName($fullFilePath, 'zip');
        
        $zip->setZipFile($this->fullArchiveName);
        $zip->addFile(fopen($fullFilePath, "rb"), basename($fullFilePath));
        $zip->finalize();
        
        if( $this->getDataToCompress($this->fullArchiveName) )
        {
            $zip = null;
            return true;
        }
    }
    
    /* function is deprecated due to a server memory limitation */
    public function compress($fullFilePath)
    {
        if( $content = $this->getDataToCompress($fullFilePath) )
        {
            $target = basename($fullFilePath);
            switch(true)
            {
                case $zip = $this->isMethodAvailable($this->_archiveClassMapping['zip']):
                    $this->generateArchiveName($fullFilePath, $zip->toString());
                    $zip->setArchive($this->fullArchiveName);
                    $zip->setTarget( $target );
                    $zip->compress($content);
                break;
                case ($tar = $this->isMethodAvailable($this->_archiveClassMapping['tar'])) && ($gz = $this->isMethodAvailable($this->_archiveClassMapping['gz'])):
                    $this->generateArchiveName($fullFilePath, $tar->toString());
                    $tar->setMode( 'Gz' );
                    $tar->setArchive($this->fullArchiveName);
                    $tar->setTarget( $target );
                    $tarPath = $tar->compress($content);
                    
                    if($content = $this->getDataToCompress($tarPath))
                    {
                        $this->generateArchiveName($fullFilePath, 'tgz');
                        $gz->setArchive($this->fullArchiveName);
                        $gz->compress( $content );
                        unlink($tarPath);
                    }
                break;
                case ($tar = $this->isMethodAvailable($this->_archiveClassMapping['tar'])) && ($bz2 = $this->isMethodAvailable($this->_archiveClassMapping['bz2'])):
                    $this->generateArchiveName($fullFilePath, $tar->toString());
                    $tar->setMode( 'Bz2' );
                    $tar->setArchive($this->fullArchiveName);
                    $tar->setTarget( $target );
                    $tarPath = $tar->compress($content);
                    
                    if($content = $this->getDataToCompress($tarPath))
                    {
                        $this->generateArchiveName($fullFilePath, 'tbz2');
                        $bz2->setArchive($this->fullArchiveName);
                        $bz2->compress( $content );
                        unlink($tarPath);
                    }
                break;
                case $rar = $this->isMethodAvailable($this->_archiveClassMapping['rar']):
                    $this->generateArchiveName($fullFilePath, $rar->toString());
                    $rar->setArchive($this->fullArchiveName);
                    $rar->setTarget( $target );
                    $rar->compress($content);
                break;
                default:
                    return false;
                break;
            }
            if( $this->getDataToCompress($this->fullArchiveName) )
            {
                return true;
            }
        }
    }
    
    protected function generateArchiveName($fullFilePath, $format)
    {
        $archiveName = basename($fullFilePath) . $this->archiveSuffix. '.' . strtolower($format);
        $path = pathinfo($fullFilePath);
        $this->fullArchiveName = $path['dirname'] . DS . $archiveName;
    }
    
    protected function isMethodAvailable($className)
    {
        $return = true;
        try
        {
            return new $className();
        }
        catch(Exception $e)
        {
            $return = false;
        }
        return $return;
    }
    
    protected function getDataToCompress($filepath)
    {
        if( file_exists($filepath) && ($content = file_get_contents($filepath)) )
        {
            return $content;
        }
        return false;
    }
}