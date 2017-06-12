<?php

class Ecp_Alertstock_Model_Backend_Cron extends Mage_Core_Model_Config_Data {

    const CRON_STRING_PATH = 'crontab/jobs/ecp_alertstock/schedule/cron_expr';
    const CRON_MODEL_PATH =  'crontab/jobs/ecp_alertstock/run/model';

    protected function _afterSave() {

        $hours = $this->getData('groups/cronjobs/fields/cron_hours/value');
        $minutes = $this->getData('groups/cronjobs/fields/cron_minutes/value');
		
        $customExpression = '';

        if (!empty($minutes)) {
            $customExpression .= $minutes;
        }
        
        if (!empty($hours)) {
            $customExpression .= ' ' . $hours;    
        }
        
        $customExpression .= ' * * *';
        
        try {

            Mage::getModel('core/config_data')
                ->load(self::CRON_STRING_PATH, 'path')
                ->setValue($customExpression)
                ->setPath(self::CRON_STRING_PATH)
                ->save();
            Mage::getModel('core/config_data')
                ->load(self::CRON_MODEL_PATH, 'path')
                ->setValue((string) Mage::getConfig()->getNode(self::CRON_MODEL_PATH))
                ->setPath(self::CRON_MODEL_PATH)
                ->save();
            
            $this->cleanCronCache();

        } catch (Exception $e) {
            throw new Exception(Mage::helper('cron')->__('Unable to save the cron expression.'));
        }

    }
    
    private function cleanCronCache() {

        // Get the resource model
        $resource = Mage::getSingleton('core/resource');

        // Retrieve the write connection
        $writeConnection = $resource->getConnection('core_write');                

        $sql = "DELETE FROM cron_schedule WHERE job_code LIKE '%emarsys%'";
        
        $writeConnection->query($sql);
        
        Mage::app()->removeCache('cron_last_schedule_generate_at');
        
        Mage::getConfig()->removeCache();
        
        // Mage::app()->getCacheInstance()->flush();
    }
}