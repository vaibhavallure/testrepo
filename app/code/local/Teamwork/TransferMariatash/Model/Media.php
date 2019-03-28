<?php
class Teamwork_TransferMariatash_Model_Media extends Teamwork_Transfer_Model_Media
{
    protected function _getMediaImagesInfoDAM($styleId, $styleNo = false)
    {
        $imagesInfo = array();
        if (empty($styleId) && empty($styleNo)) return $imagesInfo;

        $style = Mage::getSingleton('teamwork_service/dam_style');
        if (!empty($styleId)) $style->load($styleId, 'style_id');
        else $style->load($styleNo, 'style_no');

        if ($style->getId())
        {
            $images = $style->getData('images');
            foreach($images as $image)
            {
                $mediaAttributes = array();
                $itemMediaAttributes = array();
                if ($image['base']) $mediaAttributes[] = 'image';
                if ($image['thumbnail']) $mediaAttributes[] = 'thumbnail';
                if ($image['small']) $mediaAttributes[] = 'small_image';
                
                if ($image['base_item']) $itemMediaAttributes[] = 'image';
                if ($image['thumbnail_item']) $itemMediaAttributes[] = 'thumbnail';
                if ($image['small_item']) $itemMediaAttributes[] = 'small_image';

                $imagesInfo[] = array(
                    'media_id' => $image['media_id'],
                    'media_uri' => $image['file_name'],
                    'host_id' =>  $style->getData('style_id'),
                    'media_index' => -1,
                    'attribute1' => $image['attributevalue1'],
                    'direct_uri' => str_replace(' ', '%20', $image['url']),
                    'media_name' => $image['file_name'],
                    'media_type' => 'LargeImages',
                    'attribute2' => null,
                    'attribute3' => null,
                    'order' => $image['sort'],
                    'media_attributes' => $mediaAttributes,
                    'item_media_attributes' => $itemMediaAttributes,
                    'excluded' => $image['excluded'],
                    'label' => (isset($image['label']) && $image['label'] != 'None') ? $image['label'] : null
                );

            }
        }

        $this->_attachMagentoImagesIds($imagesInfo);
        return $imagesInfo;
    }
}