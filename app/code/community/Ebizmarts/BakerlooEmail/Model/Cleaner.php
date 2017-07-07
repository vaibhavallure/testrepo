<?php

class Ebizmarts_BakerlooEmail_Model_Cleaner
{
    const RECEIPTS_STORAGE = 'var';
    const RECEIPTS_PATH = 'receipts';
    const LOG_NAME = 'pos-emails.log';

    /** @var Ebizmarts_BakerlooEmail_Model_Queue  */
    private $_model;

    public function __construct($args)
    {
        if (isset($args['queue'])) {
            $this->_model = $args['queue'];
        } else {
            $this->_model = Mage::getModel('bakerloo_email/queue');
        }
    }

    public function cleanReceiptStorage()
    {
        /** @var Ebizmarts_BakerlooEmail_Model_Mysql4_Queue_Collection $emails */
        $emails = $this->_model->getCollection();
        $emails->addFieldToFilter('delete_attachment', array('eq' => 1));
        $emails->join(
            array('order' => 'sales/order'),
            'main_table.order_id = order.entity_id',
            'store_id'
        );
        $emails = $emails->getItems();

        $basePath = Mage::getBaseDir(self::RECEIPTS_STORAGE) . DS . 'pos';
        $io = new Varien_Io_File();

        foreach ($emails as $_email) {
            try {
                $path = $basePath . DS . $_email->getStoreId() . DS . self::RECEIPTS_PATH . DS . $_email->getAttachment();
                if ($io->fileExists($path)) {
                    $io->rm($path);
                    $_email->setDeleteAttachment(0);
                    $_email->save();
                }
            } catch (Exception $e) {
                Mage::log($e->getMessage(), null, self::LOG_NAME);
            }
        }
    }
}