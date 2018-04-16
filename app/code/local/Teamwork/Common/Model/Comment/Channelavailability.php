<?php
class Teamwork_Common_Model_Comment_Channelavailability
{
    public function getCommentText()
    {
        $comment = '';
        foreach(Mage::app()->getStores() as $store)
        {
            $color = Mage::getStoreConfig('teamwork_common/chq/channel_usage', $store->getId()) ? 'green' : 'red';
            $comment .= "<strong style='color:{$color}'>{$store->getCode()}</strong>&nbsp;";
        }
        return $comment;
    }
}