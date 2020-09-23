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
 * Admin export controller
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 */
class OnePica_AvaTax_Adminhtml_AvaTax_ExportController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Check if is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('avatax');
    }

    /**
     * Log action
     *
     * @return $this
     */
    public function logAction()
    {
        $fileName = 'avatax-log-' . $this->_getDateModel()->gmtDate('U') . '.sql';
        $content = Mage::getModel('avatax/export')
            ->setAdapter(Mage::getModel('avatax/export_adapter_sql'))
            ->setEntity(Mage::getModel('avatax/export_entity_log'))
            ->getContent();
        $this->_sendResponse($fileName, $content);
        return $this;
    }

    /**
     * Queue action
     *
     * @return $this
     */
    public function queueAction()
    {
        $fileName = 'avatax-queue-' . $this->_getDateModel()->gmtDate('U') . '.sql';
        $content = Mage::getModel('avatax/export')
            ->setAdapter(Mage::getModel('avatax/export_adapter_sql'))
            ->setEntity(Mage::getModel('avatax/export_entity_queue'))
            ->getContent();
        $this->_sendResponse($fileName, $content);
        return $this;
    }

    /**
     * Config action
     *
     * @return $this
     */
    public function configAction()
    {
        $fileName = 'avatax-config-' . $this->_getDateModel()->gmtDate('U') . '.json';

        $content = Mage::getModel('avatax/export')
                       ->setAdapter(Mage::getModel('avatax/export_adapter_json'))
                       ->setEntity(Mage::getModel('avatax/export_entity_config'))
                       ->getContent();

        $this->_sendResponse($fileName, $content);

        return $this;
    }

    /**
     * Order Info action
     *
     * @return $this
     */
    public function orderinfoAction()
    {
        try {
            $fileNameArray = array(
                'avatax_sales',
                'order_' . $this->getRequest()->getParam('order_id'),
                $this->_getDateModel()->gmtDate('U')
            );

            $fileName = implode('-', $fileNameArray) . '.sql';
            $storeId = $this->getRequest()->getParam('store_id');
            $quoteId = $this->getRequest()->getParam('quote_id');
            $content = '';

            /** @var \OnePica_AvaTax_Model_Export_Entity_Order_Log $logEntity */
            $logEntity = Mage::getModel('avatax/export_entity_order_log');
            $content .= Mage::getModel('avatax/export')
                            ->setAdapter(Mage::getModel('avatax/export_adapter_sql'))
                            ->setEntity($logEntity->setQuoteId($quoteId))
                            ->getContent();

            $entities = array(
                Mage::getModel('avatax/export_entity_order_queue'),
                Mage::getModel('avatax/export_entity_order_quote'),
                Mage::getModel('avatax/export_entity_order_quote_address'),
                Mage::getModel('avatax/export_entity_order_quote_item', $storeId),
                Mage::getModel('avatax/export_entity_order_order'),
                Mage::getModel('avatax/export_entity_order_order_address'),
                Mage::getModel('avatax/export_entity_order_order_item'),
                Mage::getModel('avatax/export_entity_order_invoice'),
                Mage::getModel('avatax/export_entity_order_invoice_item'),
                Mage::getModel('avatax/export_entity_order_creditmemo'),
                Mage::getModel('avatax/export_entity_order_creditmemo_item')
            );

            /** @var \OnePica_AvaTax_Model_Export_Entity_Order_Abstract $entity */
            foreach ($entities as $entity) {
                $content .= $this->_getOrderSqlContent($entity->setQuoteId($quoteId));
            }

            $this->_sendResponse($fileName, $content);
        } catch (Exception $exception) {
            Mage::getSingleton('adminhtml/session')->addError($exception->getMessage().'. Trace: '.$exception->getTraceAsString());

            $this->_redirectReferer();
        }

        return $this;
    }

    /**
     * Send response
     *
     * @param string $fileName
     * @param string $content
     * @param string $contentType
     * @return $this
     */
    protected function _sendResponse($fileName, $content, $contentType = 'application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', $this->_getDateModel()->date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        return $this;
    }

    /**
     * Get core date model
     *
     * @return Mage_Core_Model_Date
     */
    protected function _getDateModel()
    {
        return Mage::getSingleton('core/date');
    }

    /**
     * @param OnePica_AvaTax_Model_Export_Entity_Order_Abstract $entity
     * @return string
     */
    protected function _getOrderSqlContent($entity)
    {
        $cols = $entity->getExportColumns();
        $adapter = Mage::getModel('avatax/export_adapter_order_sql')->setColumnsToExport($cols);

        return Mage::getModel('avatax/export')
                   ->setAdapter($adapter)
                   ->setEntity($entity)
                   ->getContent();
    }
}
