<?php
class Teamwork_ServiceMariatash_Model_Dam extends Teamwork_Service_Model_Dam
{
    protected function _fillImageData(&$src, &$dest, $styleId)
    {
        $dest['media_id'] = Mage::helper('teamwork_service')->getGuidFromString($styleId . $src['url']);
        $dest['url'] = $src['url'];
        $dest['file_name'] = $src['filename'];
        $dest['sort'] = $src['sort'];
        $dest['excluded'] = $src['excluded'];
        $dest['base'] = $src['base'];
        $dest['thumbnail'] = $src['thumbnail'];
        $dest['small'] = $src['small'];
        $dest['attributevalue1'] = $src['attributeValueID1'];
        $dest['attributevalue1_name'] = $src['attributeValueName1'];
        $dest['label'] = $src['label'];
        
        $dest['base_item'] = 0;
        $dest['thumbnail_item'] = 0;
        $dest['small_item'] = 0;
        
        if( !empty($src['imageTypes']) )
        {
            $alternateImage = 'Item Base Image';
            if( in_array($alternateImage,$src['imageTypes']) )
            {
                $dest['base_item'] = 1;
            }
            
            $alternateImage = 'Item Thumbnail';
            if( in_array($alternateImage,$src['imageTypes']) )
            {
                $dest['thumbnail_item'] = 1;
            }
            
            $alternateImage = 'Item Small Image';
            if( in_array($alternateImage,$src['imageTypes']) )
            {
                $dest['small_item'] = 1;
            }
        }
    }
}