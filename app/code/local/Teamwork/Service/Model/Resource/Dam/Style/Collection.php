<?php

class Teamwork_Service_Model_Resource_Dam_Style_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $__addCHQStylesData = false;
    protected $__addCHQItemsData = false;

    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_service/dam_style');
    }

    public function _initSelect()
    {
        $this->_select = $this->getResource()->getRawSelect();
        return $this;
    }

    public function addStyleIdFilter($styleId)
    {
        $this->addFieldToFilter('style_id', $styleId);
        return $this;
    }

    public function addStyleNoFilter($styleNo)
    {
        $this->addFieldToFilter('style_no', $styleNo);
        return $this;
    }

    public function addDAMMarkerFilter($damMarker)
    {
        $this->addFieldToFilter('dam_marker', $damMarker);
        return $this;
    }

    public function addCHQStylesData($flag = null)
    {
        if (!is_null($flag))
        {
            $this->__addCHQStylesData = $flag ? true : false;
            return $this;
        }
        return $this->__addCHQStylesData;
    }

    public function addCHQItemsData($flag = null)
    {
        if (!is_null($flag))
        {
            $this->__addCHQItemsData = $flag ? true : false;
            return $this;
        }
        return $this->__addCHQItemsData;
    }


    /**
     * Before load action
     *
     * @return Varien_Data_Collection_Db
     */
    protected function _beforeLoad()
    {
        if ($this->__addCHQStylesData)
        {
            $this->_select->joinLeft(array('sty' => $this->getResource()->getTable('teamwork_service/service_style')), 'sty.style_id=main.style_id', $this->getResource()->getChqStyleTableFields());
        }

        if ($this->__addCHQItemsData)
        {
            $this->_select->joinLeft(array('item' => $this->getResource()->getTable('teamwork_service/service_items')), 'item.style_id=main.style_id', $this->getResource()->getChqItemTableFields());
        }

        return $this;
    }

    /**
     * Proces loaded collection data - group by styles
     *
     * @return Varien_Data_Collection_Db
     */
    protected function _afterLoadData()
    {
        $data = $this->_data;
        $groupedData = array();
        if ($data)
        {
            $imageTableFields = $this->getResource()->getImageTableFields();

            foreach ($imageTableFields as $fieldAlias => $field)
            {
                $field = explode('.', $field);
                $field = $field[1];
                $imageTableFields[$fieldAlias] = $field;
            }

            $chqStyleTableFields = $this->getResource()->getChqStyleTableFields();
            foreach ($chqStyleTableFields as $fieldAlias => $field)
            {
                $field = explode('.', $field);
                $field = $field[1];
                $chqStyleTableFields[$fieldAlias] = $field;
            }

            $chqItemTableFields = $this->getResource()->getChqItemTableFields();
            foreach ($chqItemTableFields as $fieldAlias => $field)
            {
                $field = explode('.', $field);
                $field = $field[1];
                $chqItemTableFields[$fieldAlias] = $field;
            }

            $nonStyleFields = $this->getResource()->getNonStyleTableFields();

            foreach ($data as $record)
            {
                if (!array_key_exists($record['entity_id'], $groupedData))
                {
                    $groupedData[$record['entity_id']] = $record;
                    foreach ($nonStyleFields as $fieldAlias => $field)
                    {
                        unset($groupedData[$record['entity_id']][$fieldAlias]);
                    }
                    $groupedData[$record['entity_id']]['images'] = array();
                    if ($this->__addCHQStylesData) $groupedData[$record['entity_id']]['chq_style_data'] = array();
                    if ($this->__addCHQItemsData) $groupedData[$record['entity_id']]['chq_items'] = array();
                }
                $imageData = array();
                foreach ($imageTableFields as $fieldAlias => $field)
                {
                    $imageData[$field] = $record[$fieldAlias];
                }
                $groupedData[$record['entity_id']]['images'][$imageData['entity_id']] = $imageData;

                if ($this->__addCHQStylesData
                    && !$groupedData[$record['entity_id']]['chq_style_data']
                    && !empty($record['sty_entity_id']))
                {
                    $chqStyleData = array();
                    foreach ($chqStyleTableFields as $fieldAlias => $field)
                    {
                        $chqStyleData[$field] = $record[$fieldAlias];
                    }
                    $groupedData[$record['entity_id']]['chq_style_data'] = $chqStyleData;
                }

                if ($this->__addCHQItemsData
                    && !empty($record['item_entity_id'])
                    && !array_key_exists($record['item_entity_id'], $groupedData[$record['entity_id']]['chq_items']))
                {
                    $chqItemData = array();
                    foreach ($chqItemTableFields as $fieldAlias => $field)
                    {
                        $chqItemData[$field] = $record[$fieldAlias];
                    }
                    $groupedData[$record['entity_id']]['chq_items'][$record['item_entity_id']] = $chqItemData;
                }
            }
            //sort images
            foreach ($groupedData as $k => $styleData)
            {
                $images = $styleData['images'];
                $sortedImages = array();
                foreach($images as $entity_id => $image)
                {
                    $sortedImages[$entity_id] = $image['sort'];
                }
                asort($sortedImages);
                $groupedData[$k]['images'] = array();
                foreach($sortedImages as $entity_id => $sortVal)
                {
                    $groupedData[$k]['images'][] = $images[$entity_id];
                }
            }
        }
        $this->_data = $groupedData;
        return $this;
    }



}
