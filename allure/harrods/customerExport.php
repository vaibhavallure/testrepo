<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
$profileId = 5;
$logfile  = 'export_data.log'; 
$profile = Mage::getModel('dataflow/profile');
$userModel = Mage::getModel('admin/user');
$userModel->setUserId(0);
Mage::getSingleton('admin/session')->setUser($userModel);
$profile->load($profileId);
if (!$profile->getId()) {
    Mage::getSingleton('adminhtml/session')->addError('ERROR: Incorrect profile id');
}
Mage::log('Export ' . $profileId . ' Started.', Zend_log::DEBUG, $logfile,true);
Mage::register('current_convert_profile', $profile);
$profile->run();
$recordCount = 0;
$batchModel = Mage::getSingleton('dataflow/batch');
Mage::log('Export '.$profileId.' Complete. BatchID: '.$batchModel->getId(), Zend_log::DEBUG, $logfile.true);
echo "Export Complete. BatchID: " . $batchModel->getId() . "\n";
