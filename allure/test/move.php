<?php
require_once '../../app/Mage.php';
umask(0);

Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);




Mage::getModel('brownthomas/data')->getFITEM();


die();

$localfilename = 'allure_testing_file.txt';
$remotefilename = 'allure_testing_file.txt';
$sftp = new Varien_Io_Sftp();
try{
    $sftp->open(
        array(
            'host'      => 'harrodscdmftp.harrods.com',
            'username'  => 'hrdscdm_venus',
            'password'  => 'S_s2A1hi=_pe',
            'timeout'   => '10'
        )
    );
    $sftp->write($remotefilename,$localfilename);
}catch(Exception $e){
    echo $e->getMessage();
}