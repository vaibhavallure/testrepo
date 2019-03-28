<?php

class Allure_Catalog_Model_Observer
{
	/**
	 * Add indexes to product flat table
	 *
	 * @param Varien_Event_Observer $observer observer
	 *
	 * @return void
	 */
	public function catalogProductFlatPrepareIndexes(Varien_Event_Observer $observer)
	{
	    /** @var Varien_Object $indexesObject */
	    $indexesObject = $observer->getIndexes();
	    /** @var array $indexes */
	    $indexes = $indexesObject->getIndexes();

	    /**
	     * We add indexes to these fields for faster request processes
	     */
	    $addIndexes = array(
	        array('status', 'sku'),
			array('status', 'entity_id')
	    );

	    foreach ($addIndexes as $index) {
			if (is_array($index)) {
		        $indexes['IDX_'.strtoupper(implode('_',$index))] = array(
		            'type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX,
		            'fields' => $index
		        );
			} else {
		        $indexes['IDX_'.strtoupper($index)] = array(
		            'type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX,
		            'fields' => array($index)
		        );
			}
	    }

	    $indexesObject->setIndexes($indexes);
	}
}
