<?php
class Teamwork_Service_Block_Adminhtml_Service_Edit_Grid extends Mage_Adminhtml_Block_Widget_Grid
{   
    private $_valuesCollection = null;
    
    protected function _prepareCollection()
    {
        $this->setCollection($this->_valuesCollection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('teamwork_service');
    	
    	$entityName = $this->getRequest()->getParam('table');
        
        $db = Mage::getSingleton('core/resource')->getConnection('read');
        
        $entityName = Mage::getSingleton('core/resource')->getTableName($entityName);
        
        //$this->_valuesCollection = new Varien_Data_Collection_Db($db);
        $this->_valuesCollection = new Teamwork_Service_Model_Adminhtml_Collectiondb($db);
        
        $this->_valuesCollection
            ->getSelect()
            ->from(array($entityName));
            
        $this->_valuesCollection->_entityName = $entityName;
            
    	$metadata = $db->describeTable($entityName);
		$columnNames = array_keys($metadata);
        
        foreach ($columnNames AS $columnName)
        {
        	if($columnName != "entity_id")
            {
                $this->addColumn("$columnName", array(
                    'header' => $helper->__("$columnName"),
                    'index' => "$columnName",
                    'type' => 'text',
                ));
            }
        }

        return parent::_prepareColumns();
    }
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName($this->getRequest()->getParam('table'));
        
        $this->getMassactionBlock()->addItem('delete', array(
            'label' => $this->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete/table/'.$this->getRequest()->getParam('table')),
            'confirm' => $this->__('Are you sure you want to delete the selected listing(s)?')
        ));
        return $this;
    }
    
    public function getRowUrl($model)
    {
        return $this->getUrl('*/*/edit', array(
                    'entity_id' => $model->getEntityId(),
                    'entity' => $this->getRequest()->getParam('table'),
                ));
    }
}