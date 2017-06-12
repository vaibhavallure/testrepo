<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_ErrorController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction()
	{
		$this->loadLayout()
			->_setActiveMenu('sugarcrm/error')
			->_addBreadcrumb($this->__('Errors'), $this->__('Errors'))
			->_title($this->__('SugarCRM'))
			->_title($this->__('Errors'));

		return $this;
	}

	protected function _synchtableAction()
	{
		try {
			$this->_initAction()
				->_addContent($this->getLayout()->createBlock('sugarcrm/adminhtml_errors'))
				->renderLayout();
		} catch(Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			$this->_redirect('*/config/index');
		}
	}

	public function indexAction()
	{
		if ($this->getRequest()->getParam('ajax')) {
			$this->_forward('grid');
			return;
		}
		
		try {
			$this->_initAction()
				->_addContent($this->getLayout()->createBlock('sugarcrm/adminhtml_errors'))
				->renderLayout();
		} catch(Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			$this->_redirect('*/config/index');
		}
	}

	public function gridAction()
	{
		$this->loadLayout();
		$this->getResponse()->setBody(
			$this->getLayout()->createBlock('sugarcrm/adminhtml_errors_grid')->toHtml()
		);
	}

	public function resynchAction()
	{
		$id = $this->getRequest()->getParam('error_id');
		$this->_resync($id);
		$this->_redirect('*/*/index');
	}

	public function massResynchAction()
	{
		$errortableIds = $this->getRequest()->getParam('errortable');
		if (!is_array($errortableIds)) {
			 Mage::getSingleton('adminhtml/session')->addError($this->__('Please select synchronization error(s)'));
		} else {
			$total_success = 0;
			$total_errors = 0;
			
			try {
				foreach ($errortableIds as $errortableId) {
					if($this->_resync($errortableId, true)) {
						$total_success++;
					} else {
						$total_errors++;
					}
				}
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
				
			if($total_success) {
				Mage::getSingleton('adminhtml/session')->addSuccess(
					Mage::helper('adminhtml')->__('Total of %d record(s) were synced', $total_success)
				);
			}
			
			if($total_errors) {
				Mage::getSingleton('adminhtml/session')->addError(
					$this->__('Total of %d record(s) were NOT synced', $total_errors)
				);
			}
		}

		$this->_redirect('*/*/index');		
	}

	public function resynchdeleteAction()
	{
		$id = $this->getRequest()->getParam('error_id');
		if($this->_resync($id)) {
			return $this->_forward('delete');
		}
		$this->_redirect('*/*/index');
	}

	public function massResynchdeleteAction()
	{
		$errortableIds = $this->getRequest()->getParam('errortable');
		if (!is_array($errortableIds)) {
			 Mage::getSingleton('adminhtml/session')->addError($this->__('Please select synchronization error(s)'));
		} else {
			$total_success = 0;
			$total_errors = 0;
			
			try {
				foreach ($errortableIds as $errortableId) {
					if($this->_resync($errortableId, true)) {
						$total_success++;
						
						$error = Mage::getModel('sugarcrm/error')->load($errortableId);
						$error->delete();
					} else {
						$total_errors++;
					}
				}
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
				
			if($total_success) {
				Mage::getSingleton('adminhtml/session')->addSuccess(
					Mage::helper('adminhtml')->__('Total of %d record(s) were synced and deleted', $total_success)
				);
			}
			
			if($total_errors) {
				Mage::getSingleton('adminhtml/session')->addError(
					$this->__('Total of %d record(s) were NOT synced', $total_errors)
				);
			}
		}

		$this->_redirect('*/*/index');		
	}
	
	public function deleteAction()
	{
		try {
			$id = $this->getRequest()->getParam('error_id');
			$model = Mage::getModel('sugarcrm/error')->load($id);
			$model->delete();
			$this->_getSession()->addSuccess($this->__('Synchronization error was deleted'));
		} catch (Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		}

		$this->_redirect('*/*/index');
	}

	public function massDeleteAction()
	{
		$errortableIds = $this->getRequest()->getParam('errortable');
		if (!is_array($errortableIds)) {
			 Mage::getSingleton('adminhtml/session')->addError($this->__('Please select synchronization error(s)'));
		} else {
			try {
				foreach ($errortableIds as $errortableId) {
					$error = Mage::getModel('sugarcrm/error')->load($errortableId);
					$error->delete();
				}

				Mage::getSingleton('adminhtml/session')->addSuccess(
					Mage::helper('adminhtml')->__('Total of %d record(s) were deleted', count($errortableIds))
				);
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}

		$this->_redirect('*/*/index');
	}
	
	protected function _resync($id, $mass=false)
	{
		$return = true;

		try {
			$model = Mage::getModel('sugarcrm/error')->load($id);
			if(!$model->getId()) {
				Mage::getSingleton('adminhtml/session')->addError(
					$this->__('This error does not exist')
				);
				return false;
			}
			
			$error = false;
			$message = '';
			switch($model->getOperation()) {
				case 'processSaveCustomer':
				case 'processSaveCustomerAddress':
					if($error = !$this->_syncCustomer($model)) {
						$message = $this->__('Error during customer sync. Try re-sync later.');
					} else {
						$message = $this->__('Customer was successfully synced');
					}
				break;
				
				case 'processSalesOrderSaveAfter':
					if($error = !$this->_syncOrder($model)) {
						$message = $this->__('Error during sync order. Try re-sync later.');
					} else {
						$message = $this->__('Order was successfully synced');
					}
				break;

				case 'processSalesQuoteSaveAfter':
					if($error = !$this->_syncQuote($model)) {
						$message = $this->__('Error during sync quote. Try re-sync later.');
					} else {
						$message = $this->__('Quote was successfully synced');
					}
				break;

				case 'processDeleteAfterCustomer':
					if($error = !$this->_deleteCustomer($model)) {
						$message = $this->__('Error during customer sync. Try re-sync later.');
					} else {
						$message = $this->__('Customer was successfully synced');
					}
				break;

				case 'processSalesOrderDeleteAfter':
					if($error = !$this->_deleteOrder($model)) {
						$message = $this->__('Error during sync order. Try re-sync later.');
					} else {
						$message = $this->__('Order was successfully synced');
					}
				break;
			}
			
			if($message) {
				if($error) {
					$method = 'addError';
					$return = false;
				} else {
					$method = 'addSuccess';
				}
				
				if(!$mass) {
					Mage::getSingleton('adminhtml/session')->$method($message);
				} 
			}
		} catch(Exception $e) {
			Mage::logException($e);
		}
		
		return $return;
	}
	
	protected function _syncCustomer($error)
	{
		$customer = Mage::getModel('customer/customer')->load($error->getEntityId());
		if(!$customer->getId()) {
			$error->setStatus(Belitsoft_Sugarcrm_Model_Error::STATUS_CANTSYNCED);
			$error->save();
			return false;
		}
		
		$customer->getAddresses();
		
		$return = true;
		try {
			if($error->getParams()) {
				$params = unserialize($error->getParams());
			}
			
			$operation = null;
			if(!empty($params['operation'])) {
				$operation = $params['operation'];
			}
			
			Mage::getModel('sugarcrm/connection')->synchCustomer($customer, $operation);
			
			$error->setStatus(Belitsoft_Sugarcrm_Model_Error::STATUS_RESYNCED);
			
			$error->save();

		} catch(Exception $e) {
			$return = false;
			Mage::logException($e);
		}

		return $return;
	}

	protected function _syncOrder($error)
	{
		$order = Mage::getModel('sales/order')->load($error->getEntityId());
		if(!$order->getId()) {
			$error->setStatus(Belitsoft_Sugarcrm_Model_Error::STATUS_CANTSYNCED);
			$error->save();
			return false;
		}
		
		$return = true;
		try {
			$order->setOrderObjectName(Belitsoft_Sugarcrm_Model_Synchmap::ORDER_MODEL);
			Mage::getModel('sugarcrm/connection')->synchOrder($order);
			
			$error->setStatus(Belitsoft_Sugarcrm_Model_Error::STATUS_RESYNCED);
			
			$error->save();

		} catch(Exception $e) {
			$return = false;
			Mage::logException($e);
		}

		return $return;
	}

	protected function _syncQuote($error)
	{
		$quote = Mage::getModel('sales/quote')->load($error->getEntityId());
		if(!$quote->getId()) {
			$error->setStatus(Belitsoft_Sugarcrm_Model_Error::STATUS_CANTSYNCED);
			$error->save();
			return false;
		}
		
		$return = true;
		try {
			if($error->getParams()) {
				$params = unserialize($error->getParams());
			}
			
			if(!empty($params['merge']) && !empty($params['merge']['quote']) && !empty($params['merge']['source'])) {
				$mergeQuote = Mage::getModel('sales/quote')->load($params['merge']['quote']);
				$sourceQuote = Mage::getModel('sales/quote')->load($params['merge']['source']);
				if($mergeQuote->getId() && $sourceQuote->getId()) {
					Mage::getModel('sugarcrm/connection')->setSalesQuoteMergeAfter($mergeQuote, $sourceQuote);
				}
			}
			
			if(!empty($params['state'])) {
				$quote->setState($params['state']);
			}
			
			$quote->setOrderObjectName(Belitsoft_Sugarcrm_Model_Synchmap::QUOTE_MODEL);
			
			Mage::getModel('sugarcrm/connection')->synchOrder($quote);
			
			$error->setStatus(Belitsoft_Sugarcrm_Model_Error::STATUS_RESYNCED);
			
			$error->save();

		} catch(Exception $e) {
			$return = false;
			Mage::logException($e);
		}

		return $return;
	}
	
	protected function _deleteCustomer($error)
	{
		$return = true;
		try {
			if($error->getParams()) {
				$params = unserialize($error->getParams());
			}

			$customerId = null;
			if($error->getEntityId()) {
				$customerId = $error->getEntityId();
			} else if(!empty($params['entity_id'])) {
				$customerId = $params['entity_id'];
			}
			
			if(!$customerId) {
				$error->setStatus(Belitsoft_Sugarcrm_Model_Error::STATUS_CANTSYNCED);
				$error->save();
				return false;
			}
			
			Mage::getModel('sugarcrm/connection')->deleteCustomer($customerId);
			
			$error->setStatus(Belitsoft_Sugarcrm_Model_Error::STATUS_RESYNCED);
			
			$error->save();

		} catch(Exception $e) {
			$return = false;
			Mage::logException($e);
		}

		return $return;		
	}
	
	protected function _deleteOrder($error)
	{
		$return = true;
		try {
			if($error->getParams()) {
				$params = unserialize($error->getParams());
			}

			$orderId = null;
			if($error->getEntityId()) {
				$orderId = $error->getEntityId();
			} else if(!empty($params['entity_id'])) {
				$orderId = $params['entity_id'];
			}
			
			if(!$orderId) {
				$error->setStatus(Belitsoft_Sugarcrm_Model_Error::STATUS_CANTSYNCED);
				$error->save();
				return false;
			}
			
			Mage::getModel('sugarcrm/connection')->deleteOrder($orderId);
			
			$error->setStatus(Belitsoft_Sugarcrm_Model_Error::STATUS_RESYNCED);
			
			$error->save();

		} catch(Exception $e) {
			$return = false;
			Mage::logException($e);
		}

		return $return;		
	}

	/**
	 * Simple access control
	 *
	 * @return boolean True if user is allowed to edit operations
	 */
	protected function _isAllowed()
	{
		$action = $this->getRequest()->getActionName();
		if ($action && ($action != 'index') && ($action != 'filter') && ($action != 'grid')) {
			switch($action) {
				case 'massDelete':
					$action = 'delete';
				break;

				case 'massResynch':
					$action = 'resynch';
				break;

				case 'massResynchdelete':
					$action = 'resynchdelete';
				break;
			}
			
			if($action == 'resynchdelete') {
				return Mage::getSingleton('admin/session')->isAllowed('admin/sugarcrm/error/actions/resynch') && Mage::getSingleton('admin/session')->isAllowed('admin/sugarcrm/error/actions/delete');
			} else {
				return Mage::getSingleton('admin/session')->isAllowed('admin/sugarcrm/error/actions/'.$action);
			}
		}

		return Mage::getSingleton('admin/session')->isAllowed('admin/sugarcrm/error');
	}
}