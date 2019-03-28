<?php
class Teamwork_Service_Block_Adminhtml_Chqmapping_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('teamwork_service/mappingproperty')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('teamwork_service');

        $this->addColumn('attribute_id', array(
            'header' => $helper->__('Magento attribute'),
            'index' => 'attribute_id',
            'options' => $helper->getAttributeList(),
            'type' => 'options',
        ));
        
        $this->addColumn('field_id', array(
            'header' => $helper->__('CHQ Field'),
            'index' => 'field_id',
            'options' => $helper->getMappingfieldList(),
            'type' => 'options',   
        ));
        
        $this->addColumn('type_id', array(
            'header' => $helper->__('Type'),
            'index' => 'type_id',
            'options' => array('Style' => 'configurable', 'Item' => 'simple'),
            'type' => 'options',   
        ));
        
        $this->addColumn('channel_id', array(
            'header' => $helper->__('Channel name'),
            'index' => 'channel_id',
            'options' => $helper->getChannelsList(),
            'type'  => 'options',
        ));
        
        $this->addColumn('push_once', array(
            'header' => $helper->__('Push Once'),
            'index' => 'push_once',
            'options' => array(
                0 => $helper->__('No'),
                1 => $helper->__('Yes')),
            'type'  => 'options',
        ));
        
        return parent::_prepareColumns();
    }
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('chqmapping');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => $this->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
        ));
        return $this;
    }
    
    public function getRowUrl($model)
    {
        return $this->getUrl('*/*/edit', array(
            'type' =>   $model->getTypeId(),
            'channel_id' => $model->getChannelId(),
            'id' => $model->getEntityId(),
        ));
    }
}