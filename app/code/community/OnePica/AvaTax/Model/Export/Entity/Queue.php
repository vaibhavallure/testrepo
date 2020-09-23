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
class OnePica_AvaTax_Model_Export_Entity_Queue extends OnePica_AvaTax_Model_Export_Entity_Abstract
{
    /**
     * Get export columns list
     *
     * @return array
     */
    protected function _getExportColumns()
    {
        return array(
            'queue_id',
            'store_id',
            'entity_id',
            'entity_increment_id',
            'type',
            'status',
            'attempt',
            'message',
            'created_at',
            'updated_at',
            'quote_id',
            'quote_address_id',
        );
    }

    /**
     * Get collection
     *
     * @return OnePica_AvaTax_Model_Records_Mysql4_Queue_Collection
     */
    protected function _getCollection()
    {
        return Mage::getResourceModel('avatax_records/queue_collection');
    }
}
