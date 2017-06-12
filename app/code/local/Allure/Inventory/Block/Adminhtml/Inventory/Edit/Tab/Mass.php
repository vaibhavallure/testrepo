<?php

class Allure_Inventory_Block_Adminhtml_Inventory_Edit_Tab_Mass
    extends Mage_Adminhtml_Block_Widget_Grid_Massaction 
{  
	
	public function getSelectedJson()
	{
		//$gridIds = $this->getSelectedIds();
		//$gridIds = $this->getParentBlock()->getCollection()->getAllIds();
		if(!empty($gridIds)) {
			return join(",", $this->getSelectedIds());
		}
		return '';
	}
	
	protected function getSelectedIds()
	{
		$model = $this->getModel();
		if($model!=null){
			$warehouse_id=$model->getId();
			$collection = Mage::getModel('inventory/inventory')
				->getCollection()
				->addFieldtoFilter('warehouse_id', array('in'=>array($warehouse_id)))->addFieldtoSelect('id');
			$ids=array();
			foreach ($collection as $fetchid)
			{
				$ids[] = $fetchid['id'];
			}
			return $ids;
		}
	}
	

	
}
	

