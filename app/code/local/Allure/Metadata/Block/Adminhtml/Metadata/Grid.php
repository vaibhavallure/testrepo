<?php

class Allure_Metadata_Block_Adminhtml_Metadata_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("metadataGrid");
				$this->setDefaultSort("id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("metadata/metadata")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("id", array(
				"header" => Mage::helper("metadata")->__("ID"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "id",
				));
                
				$this->addColumn("term", array(
				"header" => Mage::helper("metadata")->__("Terms"),
				"index" => "term",
				));
				$this->addColumn("title", array(
				"header" => Mage::helper("metadata")->__("Title"),
				"index" => "title",
				));
				$this->addColumn("description", array(
				"header" => Mage::helper("metadata")->__("Description"),
				"index" => "description",
				));
				$this->addColumn("status", array(
				"header" => Mage::helper("metadata")->__("Status"),
				"index" => "status",
				"type"  => 'options',
				    'options'     => array(
				        1 => 'Enabled',
				        2 => 'Disabled',
				    ),
				));
				
			$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
			$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{
			   return $this->getUrl("*/*/edit", array("id" => $row->getId()));
		}


		
		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('id');
			$this->getMassactionBlock()->setFormFieldName('ids');
			$this->getMassactionBlock()->setUseSelectAll(true);
			$this->getMassactionBlock()->addItem('remove_metadata', array(
					 'label'=> Mage::helper('metadata')->__('Remove Meta Information'),
					 'url'  => $this->getUrl('*/adminhtml_metadata/massRemove'),
					 'confirm' => Mage::helper('metadata')->__('Are you sure?')
				));
			return $this;
		}
			

}