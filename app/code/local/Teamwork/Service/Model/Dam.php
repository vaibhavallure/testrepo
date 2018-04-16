<?php

class Teamwork_Service_Model_Dam extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_service/dam');
    }

    public function updateRecordsForStyle($styleId, $styleNo, $data = false, $modifiedAfterTime = null)
    {
        if (!$data) $data = $this->getResource()->requestProductDAMData($styleId, $styleNo);
        if ($data)
        {
            $style = Mage::getModel('teamwork_service/dam_style')->load($data['styleID'], 'style_id');

            if ($style->getId())
            {
                $images = $style->getData('images');
            }
            else
            {
                $style->setData('style_id', $data['styleID']);
                $style->setData('style_no', $data['styleNo']);
                $style->setData('attributeset1', $data['attributeID1']);
                $style->setData('attributeset1_name', $data['attributeName1']);
                $images = array();
            }

            foreach($images as $k => $exImage)
            {
                $found = false;
                foreach($data['images'] as $j => $image)
                {
                    if ($exImage['url'] == $image['url'])
                    {
                        $found = true;
                        $this->_fillImageData($image, $images[$k], $style->getData('style_id'));
                        $data['images'][$j]['__used__'] = true;
                        break;
                    }
                }
                if (!$found) $images[$k]['is_deleted'] = true;
            }

            foreach($data['images'] as $image)
            {
                if (empty($image['__used__']))
                {
                    $newRec = array('style_id' => $data['styleID']);
                    $this->_fillImageData($image, $newRec, $style->getData('style_id'));
                    $images[] = $newRec;
                }
            }

            $style->setData('images', $images);
            $style->setData('dam_marker', $modifiedAfterTime);
            $style->save();

            return true;
        }
        return false;
    }

    public function updateRecords($modifiedAfterTime=false)
    {
        return Mage::getModel('teamwork_service/dam')->getResource()->requestBatchDAMData($modifiedAfterTime, array($this, 'updateBatchOfProducts'));
    }

    public function updateBatchOfProducts($batchData, $modifiedAfterTime)
    {
        foreach($batchData as $productData)
        {
            if (!$this->updateRecordsForStyle(false, false, $productData, $modifiedAfterTime)) return false;
        }
        return true;
    }

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
    }

}
