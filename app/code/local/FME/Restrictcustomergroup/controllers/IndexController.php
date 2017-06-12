<?php
class FME_Restrictcustomergroup_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/restrictcustomergroup?id=15 
    	 *  or
    	 * http://site.com/restrictcustomergroup/id/15 	
    	 */
    	/* 
		$restrictcustomergroup_id = $this->getRequest()->getParam('id');

  		if($restrictcustomergroup_id != null && $restrictcustomergroup_id != '')	{
			$restrictcustomergroup = Mage::getModel('restrictcustomergroup/restrictcustomergroup')->load($restrictcustomergroup_id)->getData();
		} else {
			$restrictcustomergroup = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($restrictcustomergroup == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$restrictcustomergroupTable = $resource->getTableName('restrictcustomergroup');
			
			$select = $read->select()
			   ->from($restrictcustomergroupTable,array('restrictcustomergroup_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$restrictcustomergroup = $read->fetchRow($select);
		}
		Mage::register('restrictcustomergroup', $restrictcustomergroup);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}