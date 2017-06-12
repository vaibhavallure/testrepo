<?php
$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
->newTable($installer->getTable('allure_geolocation/geoinfo'))
->addColumn('ip', Varien_Db_Ddl_Table::TYPE_VARCHAR, 15, array(
        'nullable'  => false,
        'primary'   => true,
), 'IP Address')
->addColumn('country', Varien_Db_Ddl_Table::TYPE_VARCHAR, 12, array(
        'nullable'  => false,
), 'Country')
->addColumn('country_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        'nullable'  => false,
), 'Country Name')
->addColumn('region', Varien_Db_Ddl_Table::TYPE_VARCHAR, 12, array(
        'nullable'  => false,
), 'Region')
->addColumn('region_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        'nullable'  => false,
), 'Region Name')
->addColumn('city', Varien_Db_Ddl_Table::TYPE_VARCHAR, 64, array(
        'nullable'  => false,
), 'City')
->addColumn('zip', Varien_Db_Ddl_Table::TYPE_VARCHAR, 12, array(
        'nullable'  => false,
), 'Postal Code')
->addColumn('timezone', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        'nullable'  => false,
), 'Time Zone')
->addColumn('isp', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
        'nullable'  => true,
), 'ISP')
->addColumn('lat', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        'nullable'  => true,
), 'Latitude')
->addColumn('lon', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        'nullable'  => true,
), 'Longitude')
->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'  => false,
), 'Created At');

$installer->getConnection()->createTable($table);

$installer->endSetup();