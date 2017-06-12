<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_FieldsmapController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction()
	{
		$this->loadLayout()
			->_setActiveMenu('sugarcrm/fieldsmap')
			->_addBreadcrumb($this->__('Fields Mapping'), $this->__('Fields Mapping'));

		return $this;
	}

	public function indexAction()
	{
		$this->_fieldsmapInit();
		$this->_fieldsmapAction();
	}

	public function newAction()
	{
		$this->_fieldsmapInit();

		$this->_title($this->__('New Item Field'));

		$this->_editFieldsmapAction();
	}

	public function editAction()
	{
		$this->_fieldsmapInit();

		$this->_title($this->__('Edit Item Field'));

		$this->_editFieldsmapAction();
	}

	public function saveAction()
	{
		$module_name = $this->getRequest()->getParam('module');
		if(!$module_name) {
			$this->_getSession()->addError($this->__('SugarCRM bean name is not specified'));
			$this->_redirect('*/config/index');
			return;
		}

		$fieldsmapModel = Mage::getModel('sugarcrm/fieldsmap');
		$id = $this->getRequest()->getParam('id');
		if (!is_null($id)) {
			$fieldsmapModel->load($id);
		}

		try {
			$data = $this->getRequest()->getPost();
			$fieldsmapModel->setData($data);
			
/*		var_dump($fieldsmapModel); die;	
			$fieldsmapModel->setModuleName($module_name)
				->setSugarcrmField($this->getRequest()->getParam('sugarcrm_field'))
				->setUserField($this->getRequest()->getParam('sugarcrm_field'))
				->setFieldsMappingType($this->getRequest()->getParam('fields_mapping_type'))
				->setMageCustomerField($this->getRequest()->getParam('mage_customer_field'))
				->setEvalCode($this->getRequest()->getParam('eval_code'))
				->save();
*/
			$fieldsmapModel->save();
			
			$this->_getSession()->addSuccess($this->__('The item field has been saved.'));
		} catch (Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		}

		$this->_redirect('*/*/index', array('module' => $module_name));
	}

	public function deleteAction()
	{
		$module_name = $this->getRequest()->getParam('module');
		if(!$module_name) {
			Mage::getSingleton('core/session')->addError($this->__('SugarCRM bean name is not specified'));
			$this->_redirect($this->getUrl('*/config/index', array('_secure' => true)));
			return;
		}

		try {
			$id = $this->getRequest()->getParam('id');
			$model = Mage::getModel('sugarcrm/fieldsmap');
			$model->load($id);
			$model->delete();
			$this->_getSession()->addSuccess($this->__('Item Field was deleted'));
		} catch (Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		}

		$this->_redirect('*/*/index', array('module' => $module_name));
	}

	protected function _fieldsmapInit()
	{
		try {
			$module_name = $this->getRequest()->getParam('module');
			if(!$module_name) {
				$this->_getSession()->addError($this->__('SugarCRM bean name is not specified'));
				$this->_redirect('*/config/index');
				return;
			}
			$bean_name = ucfirst($module_name);

			$connection = Mage::getSingleton('sugarcrm/connection')->setModuleName($bean_name);
			$connection->getModuleFields();
			Mage::register('connection_model', $connection);

		} catch(Belitsoft_Sugarcrm_Exception $bse) {
			if($bse->getCode() == Belitsoft_Sugarcrm_Exception::FATAL_ERROR) {
				throw $bse;
			} else {
				$this->_getSession()->addError($bse->getMessage());
				$this->_redirect('*/config/index');
			}
		} catch(SoapFault $sf) {
			$this->_getSession()->addError($sf->getMessage());
			$this->_redirect('*/config/index');
		} catch(Exception $e) {
			throw $e;
		}

		$this->_title($this->__('SugarCRM'))
			->_title($this->__('Fields Mapping'))
			->_title($module_name);
	}



	protected function _fieldsmapAction()
	{
		try {
			$block = $this->getLayout()->createBlock('sugarcrm/adminhtml_fieldsmap');
			if(!$block) {
				//$this->_getSession()->addError('Check configuration settings');
				return $this->_redirect('*/config/index');			
			}
			$this->_initAction()
				->_addContent($block)
				->renderLayout();
			 
		} catch(Exception $e) {
			$this->_getSession()->addError($e->getMessage());
			$this->_redirect('*/config/index');				
		}
	}

	protected function _editFieldsmapAction()
	{
		try {
			$module = $this->getRequest()->getParam('module');
			
			$model = Mage::getModel('sugarcrm/fieldsmap');
			$id = $this->getRequest()->getParam('id');
			if (!empty($id)) {
				$model->load($id);
			} else {
				$model->setModuleName($module);
			}
			Mage::register('current_item_sugarcrm_field', $model);

			$this->_initAction()
				->_addContent($this->getLayout()->createBlock('sugarcrm/adminhtml_fieldsmap_edit'))
				->renderLayout();

		} catch(Exception $e) {
			$this->_getSession()->addError($e->getMessage());
			$this->_redirect('*/config/index');
		}
	}

	/**
	 * Simple access control
	 *
	 * @return boolean True if user is allowed to edit config
	 */
	protected function _isAllowed()
	{
		$module_name = strtolower($this->getRequest()->getParam('module'));

		$action = $this->getRequest()->getActionName();

		if ($action && ($action != 'index')) {
			return Mage::getSingleton('admin/session')->isAllowed('admin/sugarcrm/fieldsmap/fieldsmap_'.$module_name.'/actions/'.$action);
		}

		return Mage::getSingleton('admin/session')->isAllowed('admin/sugarcrm/fieldsmap/fieldsmap_'.$module_name);
	}
}