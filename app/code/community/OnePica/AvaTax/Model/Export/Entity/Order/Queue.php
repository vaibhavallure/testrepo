<?php
/**
 * OnePica_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0), a
 * copy of which is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 * @copyright  Copyright (c) 2009 One Pica, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * Queue export entity model
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 */
class OnePica_AvaTax_Model_Export_Entity_Order_Queue extends OnePica_AvaTax_Model_Export_Entity_Order_Abstract
{
    /**
     * Get export columns list
     *
     * @return array
     */
    protected function _getExportColumns()
    {
        $tableName = $this->getResource()->getTableName('avatax_records/queue');

        return array_keys($this->getReadConnection()->describeTable($tableName));
    }

    /**
     * Get collection
     *
     * @return Mage_Core_Model_Mysql4_Collection_Abstract
     */
    protected function _getCollection()
    {
        /** @var OnePica_AvaTax_Model_Records_Mysql4_Queue_Collection $collection */
        $collection = Mage::getResourceModel('avatax_records/queue_collection');

        /* collection to export only for one quote */
        if ($this->getQuoteId()) {
            $collection->selectOnlyForQuote($this->getQuoteId());
        }

        return $collection;
    }
}
