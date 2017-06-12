<?php
/**
 * Allure_InstaCatalog
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @copyright   Copyright© 2016, Allure Inc
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 * @author      Team Allure <extensions@allureinc.co>
/**
 * InstaCatalog module install script
 *
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @author      Team Allure <extensions@allureinc.co>
 */
$this->startSetup();
$table = $this->getConnection()
    ->newTable($this->getTable('allure_instacatalog/feed'))
    ->addColumn(
        'entity_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'identity'  => true,
            'nullable'  => false,
            'primary'   => true,
        ),
        'Feed ID'
    )
    ->addColumn(
        'media_id',
        Varien_Db_Ddl_Table::TYPE_TEXT, 255,
        array(
            'nullable'  => false,
        ),
        'Media Id'
    )
    ->addColumn(
        'username',
        Varien_Db_Ddl_Table::TYPE_TEXT, 255,
        array(
            'nullable'  => false,
        ),
        'Username'
    )
    ->addColumn(
        'caption',
        Varien_Db_Ddl_Table::TYPE_TEXT,null,
        array(
            'nullable'  => false,
        	'default' => ''
        ),
        'Caption'
    )
    ->addColumn(
        'updated_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        array(),
        'Feed Modification Time'
    )
    ->addColumn(
        'created_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        array(),
        'Feed Creation Time'
    ) 
    ->addColumn(
    	'instagram_data',
    	Varien_Db_Ddl_Table::TYPE_TEXT,null,
    	array(
    		'nullable'  => false,
    		'default' => ''
    	),
    	'Instagram Data'
    )
    ->addColumn(
    	'image',
    	Varien_Db_Ddl_Table::TYPE_TEXT,null,
    	array(
    		'nullable'  => false,
    		'default' => ''
    	),
    	'Image'
    )
    ->addColumn(
    	'hotspots',
    	Varien_Db_Ddl_Table::TYPE_TEXT,null,
    	array(
    		'nullable'  => false,
    		'default' => ''
    	),
    	'Hotspots'
    )
    ->addColumn(
    	'status',
    	Varien_Db_Ddl_Table::TYPE_SMALLINT,null,
    	array('default'=>'1'),
    	'Enabled'
    )
    ->addColumn(
    	'lookbook_mode',
    	Varien_Db_Ddl_Table::TYPE_SMALLINT,null,
    	array('default'=>'0'),
    	'Lookbook Mode'
    )
    ->addColumn(
    	'product_ids',
    	Varien_Db_Ddl_Table::TYPE_TEXT,null,
    	array(
    		'nullable'  => false,
    		'default' => ''
    	),
    	'Product Ids'
    )
    
    ->addColumn(
    		'thumbnail',
    		Varien_Db_Ddl_Table::TYPE_TEXT,null,
    		array(
    			'nullable'  => false,
    			'default' => ''
    		),
    		'Thumbnail'
    	)
    		
    ->addColumn(
    		'low_resolution',
    		Varien_Db_Ddl_Table::TYPE_TEXT,null,
    		array(
    			'nullable'  => false,
    			'default' => ''
    		),
    		'Low Resolution'
    	)
    ->addColumn(
    		'standard_resolution',
    		Varien_Db_Ddl_Table::TYPE_TEXT,null,
    		array(
    			'nullable'  => false,
    			'default' => ''
    		),
    		'Standard Resolution'
    	)
    	
    	->addColumn(
    			'text',
    			Varien_Db_Ddl_Table::TYPE_TEXT,null,
    			array(
    					'nullable'  => false,
    					'default' => ''
    			),
    			'Text'
    		)
    		
    		->addColumn(
    				'created_timestamp',
    				Varien_Db_Ddl_Table::TYPE_TEXT, 255,
    				array(
    						'nullable'  => false,
    				),
    				'Created Timestamp'
    				)
    
    ->setComment('Feeds Table');

    
    
$this->getConnection()->createTable($table);
$table = $this->getConnection()
    ->newTable($this->getTable('allure_instacatalog/feed_store'))
    ->addColumn(
        'feed_id',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(
            'nullable'  => false,
            'primary'   => true,
        ),
        'Feed ID'
    )
    ->addColumn(
        'store_id',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ),
        'Store ID'
    )
    ->addIndex(
        $this->getIdxName(
            'allure_instacatalog/feed_store',
            array('store_id')
        ),
        array('store_id')
    )
    ->addForeignKey(
        $this->getFkName(
            'allure_instacatalog/feed_store',
            'feed_id',
            'allure_instacatalog/feed',
            'entity_id'
        ),
        'feed_id',
        $this->getTable('allure_instacatalog/feed'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $this->getFkName(
            'allure_instacatalog/feed_store',
            'store_id',
            'core/store',
            'store_id'
        ),
        'store_id',
        $this->getTable('core/store'),
        'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Feeds To Store Linkage Table');
$this->getConnection()->createTable($table);
$this->endSetup();
