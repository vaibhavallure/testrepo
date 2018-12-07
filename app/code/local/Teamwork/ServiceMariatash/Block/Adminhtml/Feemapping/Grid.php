<?php
class Teamwork_ServiceMariatash_Block_Adminhtml_Feemapping_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _prepareCollection()
    {
		$collection = Mage::getModel('teamwork_servicemariatash/feemapping')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
		$helper = Mage::helper('teamwork_servicemariatash/feemapping');
        
        $this->addColumn('shipping_id', array(
            'header' => $helper->__('Shipping'),
            'index' => 'shipping_id',
			'options' => $helper->getServiceFeeMappingList(),
            'type' => 'options',
        ));
		
		$this->addColumn('fee_id', array(
            'header' => $helper->__('Fee'),
            'index' => 'fee_id',
			'options' => $helper->getServiceFeeList(),
            'type' => 'options',
        ));
        
        return parent::_prepareColumns();
    }
	
	protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('feemapping');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => $this->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
        ));
        return $this;
    }
	
	public function getRowUrl($model)
    {
        return $this->getUrl('*/*/edit', array(
            'id' => $model->getEntityId(),
        ));
    }
}