<?php

class Teamwork_Common_Model_Staging_Relation extends Teamwork_Common_Model_Staging_Abstractchanneled
{
    protected $_guidField = 'style_id';
    
    const DB_RELATION_CROSS_SELLS = 'CrossSell';
    const DB_RELATION_RELATED = 'Related';
    const DB_RELATION_UP_SELLS = 'UpSell';
    
    const DB_RELATION_KIND_STYLE_TO_STYLE = 'StyleToStyle';
    const DB_RELATION_KIND_STYLE_TO_ITEM = 'StyleToItem';
    const DB_RELATION_KIND_ITEM_TO_STYLE = 'ItemToStyle';
    const DB_RELATION_KIND_ITEM_TO_ITEM = 'ItemToItem';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/relation');
    }
}