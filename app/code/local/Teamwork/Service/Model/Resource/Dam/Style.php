<?php

class Teamwork_Service_Model_Resource_Dam_Style extends Mage_Core_Model_Mysql4_Abstract
{

    protected $__addCHQStyleData = false;
    protected $__addCHQItemsData = false;
    protected $__CHQChannelId = false;


    protected $_imageTableFields = array(
        'img_entity_id' => 'img.entity_id',
        //'img_style_id' => 'img.style_id',
        'img_media_id' => 'img.media_id',
        'img_url' => 'img.url',
        'img_file_name' => 'img.file_name',
        'img_sort' => 'img.sort',
        'img_excluded' => 'img.excluded',
        'img_base' => 'img.base',
        'img_thumbnail' => 'img.thumbnail',
        'img_small' => 'img.small',
        'img_attributevalue1' => 'img.attributevalue1',
        'img_attributevalue1_name' => 'img.attributevalue1_name',
        'img_label' => 'img.label',
    );

    protected $__chqStyleTableFields = null;
    protected $__chqItemTableFields = null;

    public function _construct()
    {
        $this->_init('teamwork_service/service_media_dam_style', 'entity_id');
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param Mage_Core_Model_Abstract $object
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        return $this->getRawSelect($this->__addCHQStyleData, $this->__addCHQItemsData)->order('img.sort ASC')->where("main.{$field}=?", $value);
    }

    public function addCHQStyleData($flag = null)
    {
        if (!is_null($flag)) {
            $this->__addCHQStyleData = $flag ? true : false;
            return $this;
        }
        return $this->__addCHQStyleData;
    }

    public function addCHQItemsData($flag = null)
    {
        if (!is_null($flag)) {
            $this->__addCHQItemsData = $flag ? true : false;
            return $this;
        }
        return $this->__addCHQItemsData;
    }


    public function getRawSelect($addCHQStyleData=false, $addCHQItemData=false)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('main'=>$this->getMainTable())/*, $this->_mainTableFields*/)
            ->joinLeft(array('img' => $this->getTable('teamwork_service/service_media_dam_image')), 'img.style_id=main.style_id', $this->_imageTableFields);
        if ($addCHQStyleData)
        {
            $select->joinLeft(array('sty' => $this->getTable('teamwork_service/service_style')), 'sty.style_id=main.style_id', $this->getChqStyleTableFields());
        }
        if ($addCHQItemData)
        {
            $select->joinLeft(array('item' => $this->getTable('teamwork_service/service_items')), 'item.style_id=main.style_id', $this->getChqItemTableFields());
        }

        return $select;
    }


    public function load(Mage_Core_Model_Abstract $object, $value, $field = null)
    {
        if (is_null($field)) {
            $field = $this->getIdFieldName();
        }

        $read = $this->_getReadAdapter();
        if ($read && !is_null($value)) {
            $select = $this->_getLoadSelect($field, $value, $object);
            $data = $read->fetchAll($select);

            if ($data) {
                $object->setData(reset($data));
                foreach($this->getNonStyleTableFields() as $fieldAlias => $imageField) {
                    $object->unsetData($fieldAlias);
                }
                $images = array();
                $chqStyleData = array();
                $chqItems = array();
                foreach($data as $n => $imageData) {

                    if ($this->__addCHQItemsData
                        && !empty($imageData['item_entity_id'])
                        && !array_key_exists($imageData['item_entity_id'], $chqItems))
                    {
                        $chqItems[$imageData['item_entity_id']] = array();
                        foreach($this->getChqItemTableFields() as $fieldAlias => $imageField) {
                            $fieldName = explode('.', $imageField);
                            $fieldName = $fieldName[1];
                            $chqItems[$imageData['item_entity_id']][$fieldName] = $imageData[$fieldAlias];
                        }
                    }

                    if ($this->__addCHQStyleData && !$chqStyleData
                        && !empty($imageData['sty_entity_id']))
                    {
                        foreach($this->getChqStyleTableFields() as $fieldAlias => $imageField) {
                            $fieldName = explode('.', $imageField);
                            $fieldName = $fieldName[1];
                            $chqStyleData[$fieldName] = $imageData[$fieldAlias];
                        }
                    }

                    if (!empty($imageData['img_entity_id'])
                        && !array_key_exists($imageData['img_entity_id'], $images))
                    {
                        $images[$imageData['img_entity_id']] = array();
                        foreach($this->_imageTableFields as $fieldAlias => $imageField) {
                            $fieldName = explode('.', $imageField);
                            $fieldName = $fieldName[1];
                            $images[$imageData['img_entity_id']][$fieldName] = $imageData[$fieldAlias];
                        }
                    }


                }
                $object->setData('images', $images);
                if ($this->__addCHQStyleData) $object->setData('chq_style_data', $chqStyleData);
                if ($this->__addCHQItemsData) $object->setData('chq_items', $chqItems);
            }
        }

        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
    }

    /**
     * Delete the object - not allowed
     *
     * @param Varien_Object $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    public function delete(Mage_Core_Model_Abstract $object)
    {
        return $this;
    }

    /**
     * Save object object data - update style images
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $writeAdapter = $this->_getWriteAdapter();
        $imageTable = $this->getTable('teamwork_service/service_media_dam_image');
        if ($object->getData('images')) {
            foreach ($object->getData('images') as $image) {
                if (array_key_exists('entity_id', $image) && $image['entity_id']) {
                    if (array_key_exists('is_deleted', $image) && $image['is_deleted']){
                        //delete image
                        $writeAdapter->delete($imageTable, "entity_id={$image['entity_id']}");
                    } else {
                        //update image data
                        unset($image['is_deleted']);
                        $entityId = $image['entity_id'];
                        unset($image['entity_id']);
                        unset($image['style_id']);
                        $writeAdapter->update($imageTable, $image, "entity_id={$entityId}");
                    }
                } else {
                    $writeAdapter->insert($imageTable, $image);
                }
            }
        }
    }


    public function getImageTableFields()
    {
        return $this->_imageTableFields;
    }

    public function getChqStyleTableFields()
    {
        if (is_null($this->__chqStyleTableFields))
        {
            $styleTableName = $this->getTable('teamwork_service/service_style');
            $metadata = $this->_getReadAdapter()->describeTable($styleTableName);
            $columnNames = array_keys($metadata);
            $this->__chqStyleTableFields = array();
            foreach($columnNames as $columnName)
            {
                $this->__chqStyleTableFields['sty_' . $columnName] = 'sty.' . $columnName;
            }
        }
        return $this->__chqStyleTableFields;
    }

    public function getChqItemTableFields()
    {
        if (is_null($this->__chqItemTableFields))
        {
            $styleTableName = $this->getTable('teamwork_service/service_items');
            $metadata = $this->_getReadAdapter()->describeTable($styleTableName);
            $columnNames = array_keys($metadata);
            $this->__chqItemTableFields = array();
            foreach($columnNames as $columnName)
            {
                $this->__chqItemTableFields['item_' . $columnName] = 'item.' . $columnName;
            }
        }
        return $this->__chqItemTableFields;
    }

    public function getNonStyleTableFields()
    {
        return array_merge($this->_imageTableFields, $this->getChqStyleTableFields(), $this->getChqItemTableFields());
    }

}
