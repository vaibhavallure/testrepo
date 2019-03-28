<?php
class Teamwork_Common_Model_Staging_Service extends Teamwork_Common_Model_Staging_Abstractchanneled
{
    const PROCESSABLE_TYPE_CATEGORIES = 'Categories';
    const PROCESSABLE_TYPE_STYLES  = 'Styles';
    const PROCESSABLE_TYPE_PRICES = 'Prices';
    const PROCESSABLE_TYPE_QTYS = 'Qtys';
    const PROCESSABLE_TYPE_ATTRIBUTESETS = 'AttributeSets';
    const PROCESSABLE_TYPE_PACKAGES = 'Packages';
    const PROCESSABLE_TYPE_LOCATIONS = 'Locations';
    
    const STATUS_LOADING = 'loading';
    const STATUS_NEW = 'new';
    const STATUS_PROCESSING = 'processing';
    const STATUS_DONE = 'done';
    const STATUS_ERROR = 'error';
    const STATUS_REINDEX = 'reindex';
    
    protected $_guidField = 'request_id';
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/service');
    }
}