<?php
/**
 * Entrepids
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future. If you wish to customize for your
 * needs please refer to Entrepids Event-Observer for more information.
 * 
 * @category    Ecp
 * @package     Ecp_Customercarenavleft
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;

$installer->startSetup();

//$installer->run("
//
//-- DROP TABLE IF EXISTS {$this->getTable('customercarenavleft')};
//CREATE TABLE {$this->getTable('customercarenavleft')} (
//  `customercarenavleft_id` int(11) unsigned NOT NULL auto_increment,
//  `title` varchar(255) NOT NULL default '',
//  `filename` varchar(255) NOT NULL default '',
//  `content` text NOT NULL default '',
//  `status` smallint(6) NOT NULL default '0',
//  `created_time` datetime NULL,
//  `update_time` datetime NULL,
//  PRIMARY KEY (`customercarenavleft_id`)
//) ENGINE=InnoDB DEFAULT CHARSET=utf8;
//
//    ");
$installer->run("
  ALTER TABLE  `cms_page` ADD  `customer_care_navigation` INT(1) NOT NULL DEFAULT '0';
");

$installer->endSetup(); 