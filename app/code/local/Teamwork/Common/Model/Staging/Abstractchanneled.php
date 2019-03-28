<?php

/**
 * Abstract model for entities:
 *  - having GUID (GUID field is different for styles, items, categories etc.)
 *  - having channel_id field on table (i.g., styles, items and categories but not attributes)
 */
class Teamwork_Common_Model_Staging_Abstractchanneled extends Teamwork_Common_Model_Staging_Abstract
{
    protected $_channelField = 'channel_id';

    /**
     * Useful method for loading by GUID (style_id, category_id etc.) and channel id
     *
     * @param  string $channelId
     * @param  string $guid
     *
     * @return 
     */
    public function loadByChannelAndGuid($channelId, $guid)
    {
        $this->setData($this->getResource()->loadByAttributes(
            array(
                $this->_channelField => $channelId,
                $this->_guidField => $guid,
            )
        ));
        $this->_afterLoad();
        return $this;
    }

    /**
     * Returns channel_id (method has sanse when $this->_channelField differ from 'channel_id')
     *
     * @return string
     */
    public function getChannelId()
    {
        return $this->getData($this->_channelField);
    }
    
    protected function _beforeSave()
    {
        if( $this->getRequestId() && !$this->getNoSalt() )
        {
            $this->setRequestId(
                Mage::helper('teamwork_common/staging_abstract')->getSaltedRequestId( $this->getRequestId(), $this->getChannelId() )
            );
        }
        
        return parent::_beforeSave();
    }
}