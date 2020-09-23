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
 * Log export entity model
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 */
class OnePica_AvaTax_Model_Export_Entity_Log extends OnePica_AvaTax_Model_Export_Entity_Abstract
{
    /**
     * Get export columns list
     *
     * @return array
     */
    protected function _getExportColumns()
    {
        return array(
            'log_id',
            'store_id',
            'level',
            'type',
            'request',
            'result',
            'additional',
            'created_at',
            'soap_request',
            'soap_request_headers',
            'soap_result',
            'soap_result_headers',
            'quote_id',
            'quote_address_id'
        );
    }

    /**
     * Get collection
     *
     * @return Mage_Core_Model_Mysql4_Collection_Abstract
     */
    protected function _getCollection()
    {
        /** @var OnePica_AvaTax_Model_Records_Mysql4_Log_Collection $collection */
        $collection = Mage::getResourceModel('avatax_records/log_collection');

        /* add related info about order, invoice and credit memo */
        $collection->addRelatedInfoToSelect();

        return $collection;
    }
}
