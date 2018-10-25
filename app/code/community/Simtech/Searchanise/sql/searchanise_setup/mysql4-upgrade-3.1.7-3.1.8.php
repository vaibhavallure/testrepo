<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

$installer = $this;

$installer->startSetup();

$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
$tableName = $resource->getTableName('searchanise/queue');

$writeConnection->query("ALTER TABLE {$tableName} ADD INDEX `StoreAction` (`store_id`, `action`);");

$installer->endSetup();
