<?php
/**
 * Paradox Labs, Inc.
 * http://www.paradoxlabs.com
 * 717-431-3330
 * 
 * Need help? Open a ticket in our support system:
 *  http://support.paradoxlabs.com
 * 
 * Want to customize or need help with your store?
 *  Phone: 717-431-3330
 *  Email: sales@paradoxlabs.com
 *
 * @category	ParadoxLabs
 * @package		TokenBase
 * @author		Ryan Hoerr <magento@paradoxlabs.com>
 * @license		http://store.paradoxlabs.com/license.html
 */

try {
	/**
	 * Use filesystem locking to avoid duplication.
	 */
	$processLock = Mage::getModel('index/process');
	$processLock->setId('tokenbase_setup');
	
	if( !$processLock->isLocked() ) {	
		$processLock->lockAndBlock();
		
		$this->startSetup();
		
		/**
		 * Add card table
		 */
		
		$table = $this->getTable('tokenbase/card');
		
		$this->run("CREATE TABLE IF NOT EXISTS {$table} (
			id int auto_increment primary key,
			customer_id int,
			customer_email varchar(255),
			customer_ip varchar(32),
			profile_id int,
			payment_id int,
			method varchar(32),
			active tinyint(1) default '1',
			created_at datetime,
			updated_at datetime,
			last_use datetime,
			expires datetime,
			address mediumtext,
			additional mediumtext,
			hash varchar(40) comment 'Unique Hash',
			unique (`hash`)
		);");
		
		/**
		 * Add payment columns
		 * Manual column defs. for CE 1.5 compatibility.
		 */
		$this->getConnection()->addColumn(
			$this->getTable('sales/quote_payment'),
			'tokenbase_id',
			"INT(11) UNSIGNED NULL COMMENT 'ParadoxLabs_TokenBase Card ID'"
		);
		
		$this->getConnection()->addColumn(
			$this->getTable('sales/order_payment'),
			'tokenbase_id',
			"INT(11) UNSIGNED NULL COMMENT 'ParadoxLabs_TokenBase Card ID'"
		);
		
		/**
		 * Add index to sales_flat_order.ext_customer_id to speed up legacy data conversion (1507075)
		 */
		$table		= $this->getTable('sales/order');
		
		$needIndex	= true;
		$indexes	= $this->getConnection()->getIndexList( $table );
		foreach( $indexes as $index ) {
			if( isset( $index['COLUMNS_LIST'] ) && in_array( 'ext_customer_id', $index['COLUMNS_LIST'] ) ) {
				$needIndex = false;
				break;
			}
		}
		
		if( $needIndex === true ) {
			// addKey rather than addIndex for CE 1.5 compatibility.
			// Static index name and type for CE 1.5 compatibility.
			$this->getConnection()->addKey(
				$table,
				'IDX_SALES_FLAT_ORDER_EXT_CUSTOMER_ID',
				array( 'ext_customer_id' ),
				'index'
			);
		}
		
		$this->endSetup();
		
		$processLock->unlock();
	}
}
catch( Exception $e ) {
	Mage::log( 'SETUP FAILURE in tokenbase_setup:mysql4-install-2.2.0: ' . (string)$e, null, 'tokenbase.log', true );
	
	if( isset( $processLock ) && $processLock instanceof Mage_Index_Model_Process ) {
		$processLock->unlock();
	}
	
	throw $e;
}
