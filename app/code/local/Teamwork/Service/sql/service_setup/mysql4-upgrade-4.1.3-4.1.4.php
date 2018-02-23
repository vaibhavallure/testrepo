<?php
$installer = $this;
$installer->startSetup();

$installer->run("
    ALTER TABLE `{$this->getTable('service_location')}`
        ADD COLUMN `channel_id` CHAR(36) NOT NULL AFTER `location_id`,
    DROP PRIMARY KEY,
        DROP INDEX `code`,
    ADD PRIMARY KEY (`location_id`, `channel_id`),
        ADD INDEX `enabled` (`enabled`);
");

Mage::app()->getCacheInstance()->flush();

$channelIds = $installer->getConnection()->fetchCol("SELECT channel_id FROM `{$this->getTable('service_channel')}`");

if(!empty($channelIds))
{
    $locations = $installer->getConnection()->fetchAll("SELECT * FROM `{$this->getTable('service_location')}`");
    
    if(!empty($locations))
    {
        $into = implode(',', array_keys(current($locations)));
        
        foreach($channelIds as $channelId)
        {
            foreach($locations as $location)
            {
                $location['channel_id'] = $channelId;
                $sql = "INSERT INTO `{$this->getTable('service_location')}`({$into}) VALUES (";
                
                foreach($location as $key => $element)
                {
                    $sql .= ($element === null) ? 'null,' : "'{$element}',";
                }
                $sql = substr($sql,0,-1) . ")";
                $installer->run($sql);
            }
        }
        $installer->run("DELETE FROM `{$this->getTable('service_location')}` WHERE channel_id=''");
    }
}

$installer->endSetup();