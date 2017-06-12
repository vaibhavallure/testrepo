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
 * @package     Ecp_Reviews
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;

$installer->startSetup();
//$installer->run("DELETE FROM {$this->getTable('review_entity')} WHERE entity_code='Site';");
//$installer->run("ALTER TABLE {$this->getTable('review_detail')} DROP email;");

/*
$installer->run("INSERT INTO {$this->getTable('review_entity')} VALUES (null,'Site');");
$installer->run("ALTER TABLE {$this->getTable('review_detail')} ADD nameofuser VARCHAR(120) AFTER customer_id;");
$installer->run("ALTER TABLE {$this->getTable('review_detail')} ADD email VARCHAR(120) AFTER nameofuser;");
*/

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('ecp_reviews')};
CREATE TABLE {$this->getTable('ecp_reviews')} (
  `reviews_id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `review` varchar(2000) NOT NULL default '',
  `publish_date` date NULL,  
  `status` int(1) NOT NULL default '1',
  PRIMARY KEY (`reviews_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup(); 