<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_SynchtableController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction()
	{
		$this->loadLayout()
			->_setActiveMenu('sugarcrm/synchtable')
			->_addBreadcrumb($this->__('Table Synchronization'), $this->__('Table Synchronization'))
			->_title($this->__('SugarCRM'))
			->_title($this->__('Table Synchronization'));

		return $this;
	}

	protected function _synchtableAction()
	{
		try {
			$this->_initAction()
				->_addContent($this->getLayout()->createBlock('sugarcrm/adminhtml_synchtable'))
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
		$this->_synchtableAction();
	}

	public function gridAction()
	{
		$this->loadLayout();
		$this->getResponse()->setBody(
			$this->getLayout()->createBlock('sugarcrm/adminhtml_synchtable_grid')->toHtml()
		);
	}

	public function newAction()
	{
		$this->_forward('edit');
	}
	
	public function editAction()
	{
		try {
			$model = Mage::getModel('sugarcrm/synchmap');
			$id = $this->getRequest()->getParam('id');
			if (!empty($id)) {
				$model->load($id);
			}
			Mage::register('sugarcrm_synchmap_model', $model);

			$this->_initAction()
				->_addContent($this->getLayout()->createBlock('sugarcrm/adminhtml_synchtable_edit'))
				->renderLayout();

		} catch(Exception $e) {
			$this->_getSession()->addError($e->getMessage());
			$this->_redirect('*/*/index');
		}
	}
	
	public function saveAction()
	{
		// check if data sent
		if($data = $this->getRequest()->getPost()) {
			// init model and set data
			$model = Mage::getModel('sugarcrm/synchmap');
			$model->setData($data);

		// try to save it
			try {
				// save the data
				$model->save();

				// display success message
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Sync data was successfully saved'));
				// clear previously saved data from session
				Mage::getSingleton('adminhtml/session')->setFormData(false);
				// check if 'Save and Continue'
				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				// go to grid
				$this->_redirect('*/*/index');
				return;

			} catch(Exception $e) {
				// display error message
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				// save data in session
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				// redirect to edit form
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}

		// go to grid
		$this->_redirect('*/*/index');
	}
	

	public function exportAction()
	{
		Mage::getSingleton('adminhtml/session')->setData('number_customer_was_synch', 0);

		ini_set('max_execution_time', '36000');
		set_time_limit(36000);
		ignore_user_abort(true);

		$beans = array_keys((array) Mage::getModel('sugarcrm/config')->getBeans());

		try {
			$synch_customers = Mage::getResourceModel('sugarcrm/synchmap_collection')->getCustomersIds();

			$customers = Mage::getResourceModel('customer/customer_collection')->load()->getItems();
			$total_operations = array();
			Mage::getSingleton('adminhtml/session')->setData('number_customer_for_synch', count($customers));
			foreach($customers as $col_customer) {
				/* @var $customer Mage_Customer_Model_Customer */
				$customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
				$customer->load($col_customer->getEntityId());
				$customer->getAddresses();

				$is_update = in_array($customer->getId(), $synch_customers);
				$operation = $is_update ? 'update' : 'insert';

				$connection = Mage::getModel('sugarcrm/connection')->synchCustomer($customer, $operation);

				$operation_name = $is_update ? 'updated' : 'inserted';
				foreach($beans as $bean) {
					if($connection->isOperationComplete($operation, $bean)) {
						if(empty($total_operations[$operation_name][$bean])) {
							$total_operations[$operation_name][$bean] = 1;
						} else {
							$total_operations[$operation_name][$bean]++;
						}
					}
				}
				Mage::getSingleton('adminhtml/session')->setData('number_customer_was_synch', $total_operations);
			}

		} catch(Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			$this->_redirect('*/*/index');
			return;
		}

		$alert = array();
		foreach($total_operations as $operation=>$beans) {
			foreach($beans as $bean_name=>$count) {
				$alert[] = $this->__('Total of %d record(s) of SugarCRM %s were %s', $count, $bean_name, $operation);
				Mage::getSingleton('adminhtml/session')->addSuccess(
					$this->__('Total of %d record(s) of SugarCRM %s were %s', $count, $bean_name, $operation)
				);
			}
		}

		if(empty($alert)) {
			Mage::getSingleton('adminhtml/session')->addSuccess(
				$this->__('0 records were exported')
			);
		}

		if($this->getRequest()->getParam('ajax')) {
			return;
		} else {
			$this->_redirect('*/*/index');
		}
	}

	public function countexportAction()
	{
		$total_operations = Mage::getSingleton('adminhtml/session')->getData('number_customer_was_synch');

		if(empty($total_operations) || !is_array($total_operations)) {
			die();
		}

		$return = array();
		foreach($total_operations as $operation=>$beans) {
			foreach($beans as $bean_name=>$count) {
				$return[] = $this->__('%d record(s) of SugarCRM %s were %s', $count, $bean_name, $operation);
			}
		}

		if(!empty($return)) {
			echo implode('<br />', $return);
		}

		die;
	}

	public function exportorderAction()
	{
		Mage::getSingleton('adminhtml/session')->setData('number_orders_was_synch', 0);

		ini_set('max_execution_time', '36000');
		set_time_limit(36000);
		ignore_user_abort(true);

		try {
			$bean = Mage::helper('sugarcrm')->getSugarOrderBean();

			$synch_orders = Mage::getResourceModel('sugarcrm/synchmap_collection')->getOrderIds();

			$orders = Mage::getResourceModel('sales/order_collection')
				->addFieldToSelect('*')
				->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
				->setOrder('created_at', 'desc')
				->load()
				->getItems();

			$total_operations = array();
			Mage::getSingleton('adminhtml/session')->setData('number_orders_for_synch', count($orders));
			foreach($orders as $col_order) {
				/* @var $customer Mage_Sales_Model_Order */
				$order = Mage::getModel('sales/order')->load($col_order->getEntityId());

				$is_update = in_array($order->getId(), $synch_orders);
				$operation = $is_update ? 'update' : 'insert';

				$connection = Mage::getModel('sugarcrm/connection')->synchOrder($order, $operation);

				$operation_name = $is_update ? 'updated' : 'inserted';
				if($connection->isOperationComplete($operation, $bean)) {
					if(empty($total_operations[$operation_name][$bean])) {
						$total_operations[$operation_name][$bean] = 1;
					} else {
						$total_operations[$operation_name][$bean]++;
					}
				}
				Mage::getSingleton('adminhtml/session')->setData('number_orders_was_synch', $total_operations);
			}

		} catch(Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			$this->_redirect('*/*/index');
			return;
		}


		$alert = array();
		foreach($total_operations as $operation=>$beans) {
			foreach($beans as $bean_name=>$count) {
				$alert[] = $this->__('Total of %d record(s) of SugarCRM %s were %s', $count, $bean_name, $operation);
				Mage::getSingleton('adminhtml/session')->addSuccess(
					$this->__('Total of %d record(s) of SugarCRM %s were %s', $count, $bean_name, $operation)
				);
			}
		}

		if(empty($alert)) {
			Mage::getSingleton('adminhtml/session')->addSuccess(
				$this->__('0 records were exported')
			);
		}

		if($this->getRequest()->getParam('ajax')) {
			return;
		} else {
			$this->_redirect('*/*/index');
		}
	}

	public function countorderexportAction()
	{
		$total_operations = Mage::getSingleton('adminhtml/session')->getData('number_orders_was_synch');

		if(empty($total_operations) || !is_array($total_operations)) {
			die;
		}

		$return = array();
		foreach($total_operations as $operation=>$beans) {
			foreach($beans as $bean_name=>$count) {
				$return[] = $this->__('%d record(s) of SugarCRM %s were %s', $count, $bean_name, $operation);
			}
		}

		if(!empty($return)) {
			echo implode('<br />', $return);
		}

		die;
	}

	public function deleteAction()
	{
		try {
			$id = $this->getRequest()->getParam('id');
			$model = Mage::getModel('sugarcrm/synchmap')->load($id);
			$model->delete();
			$this->_getSession()->addSuccess($this->__('Item was deleted'));
		} catch (Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		}

		$this->_redirect('*/*/index');
	}

	public function massDeleteAction()
	{
		$synchtableIds = $this->getRequest()->getParam('synchtable');
		if (!is_array($synchtableIds)) {
			 Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
		} else {
			try {
				foreach ($synchtableIds as $synchtableId) {
					$subscriber = Mage::getModel('sugarcrm/synchmap')->load($synchtableId);
					$subscriber->delete();
				}

				Mage::getSingleton('adminhtml/session')->addSuccess(
					Mage::helper('adminhtml')->__(
						'Total of %d record(s) were deleted', count($synchtableIds)
					)
				);
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}

		$this->_redirect('*/*/index');
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

				case 'countexport':
					$action = 'export';
				break;

				case 'countorderexport':
					$action = 'exportorder';
				break;
			}

			return Mage::getSingleton('admin/session')->isAllowed('admin/sugarcrm/synchtable/actions/'.$action);
		}

		return Mage::getSingleton('admin/session')->isAllowed('admin/sugarcrm/synchtable');
	}
}