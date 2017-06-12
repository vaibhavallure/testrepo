<?php
//web.com/mariatash/admin/system_backup/create/key/1d22c5d80392a62cb68f68ad85d2c429/?isAjax=true
require_once '../app/Mage.php';
umask(0);

Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

    $ajax = true;   
    $response = new Varien_Object();

    /**
     * @var Mage_Backup_Helper_Data $helper
     */
    $helper = Mage::helper('backup');

    try {
        $type = 'db';

        $backupManager = Mage_Backup::getBackupInstance($type)
            ->setBackupExtension($helper->getExtensionByType($type))
            ->setTime(time())
            ->setBackupsDir($helper->getBackupsDir());
       $backupName = 'Quyna';
        $backupManager->setName($backupName);

        Mage::register('backup_manager', $backupManager);
        $maintenance_mode = 0;
        if ($maintenance_mode) {
            $turnedOn = $helper->turnOnMaintenanceMode();

            if (!$turnedOn) {
                $response->setError(
                    Mage::helper('backup')->__('You do not have sufficient permissions to enable Maintenance Mode during this operation.')
                        . ' ' . Mage::helper('backup')->__('Please either unselect the "Put store on the maintenance mode" checkbox or update your permissions to proceed with the backup."')
                );
                $backupManager->setErrorMessage(Mage::helper('backup')->__("System couldn't put store on the maintenance mode"));
                return $this->getResponse()->setBody($response->toJson());
            }
        }

        if ($type != Mage_Backup_Helper_Data::TYPE_DB) {
            $backupManager->setRootDir(Mage::getBaseDir())
                ->addIgnorePaths($helper->getBackupIgnorePaths());
        }

        $successMessage = $helper->getCreateSuccessMessageByType($type);

        $backupManager->create();

      

       
    } catch (Mage_Backup_Exception_NotEnoughFreeSpace $e) {
        $errorMessage = Mage::helper('backup')->__('Not enough free space to create backup.');
    } catch (Mage_Backup_Exception_NotEnoughPermissions $e) {
        Mage::log($e->getMessage());
        $errorMessage = Mage::helper('backup')->__('Not enough permissions to create backup.');
    } catch (Exception  $e) {
        Mage::log($e->getMessage());
        $errorMessage = Mage::helper('backup')->__('An error occurred while creating the backup.');
    }