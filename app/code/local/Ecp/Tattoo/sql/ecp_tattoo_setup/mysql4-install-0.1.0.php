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
 * @package     Ecp_Tattoo
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('ecp_tattoo_artist')};
CREATE TABLE {$this->getTable('ecp_tattoo_artist')} (
  `tattoo_artist_id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `image` varchar(255) NOT NULL default '',
  `description` text NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  `banner` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  PRIMARY KEY (`tattoo_artist_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('ecp_tattoo_artist_work')};
CREATE TABLE {$this->getTable('ecp_tattoo_artist_work')} (
  `tattoo_artist_work_id` int(11) unsigned NOT NULL auto_increment,
  `tattoo_artist_id` int(11) unsigned NOT NULL,
  `image` varchar(255) NOT NULL default '',
  `categoryartist` varchar(255) NOT NULL default '',
  PRIMARY KEY (`tattoo_artist_work_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 