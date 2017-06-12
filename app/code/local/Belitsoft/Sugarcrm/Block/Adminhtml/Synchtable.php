<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Block_Adminhtml_Synchtable extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_blockGroup = 'sugarcrm';
		$this->_controller = 'adminhtml_synchtable';

		$this->_headerText = Mage::helper('sugarcrm')->__('Table Synchronization');

		$this->_addButtonLabel = $this->__('Add Custom'); 

		parent::__construct();

		$this->addButton('export',
			array(
				'label'			=> $this->getCustomerExportButtonLabel(),
				'onclick'		=> $this->_isAllowedAction('export') ? 'exportCustomers()' : 'alert(\''.Mage::helper('adminhtml')->__('Access denied').'\')',
				'class'			=> 'export',
				'after_html'	=> $this->_isAllowedAction('export') ? $this->_getCustomerExportHtml() : '',
			),
			-1
		);

		$this->addButton('export_orders',
			array(
				'label'			=> $this->getOrderExportButtonLabel(),
				'onclick'		=> $this->_isAllowedAction('exportorder') ? 'exportOrders()' : 'alert(\''.Mage::helper('adminhtml')->__('Access denied').'\')',
				'class'			=> 'export',
				'after_html'	=> $this->_isAllowedAction('exportorder') ? $this->_getOrderExportHtml() : '',
			),
			-1
		);
	}

	public function getCustomerExportButtonLabel()
	{
		return Mage::helper('sugarcrm')->__('Export customers to SugarCRM');
	}

	public function getOrderExportButtonLabel()
	{
		return Mage::helper('sugarcrm')->__('Export orders to SugarCRM');
	}

	protected function _getCustomerExportHtml()
	{
		return '
		<style>
		.export-loader {width:400px !important; margin-left:-200px !important;}
		</style>
		<script type="text/javascript">
		var exportFinished = false;
		function exportCustomers() {
			$("loading_mask_loader").addClassName("export-loader");
			var loading_mask_loader_html = document.getElementById("loading_mask_loader").innerHTML;
			document.getElementById("loading_mask_loader").innerHTML = loading_mask_loader_html + "<br /><span id=\"loading_mask_loader_customers\" style=\"width:200px\"></span>";
			checkCustomersSynch();
			new Ajax.Request(\''.$this->getCustomerExportUrl(true).'\', {
				evalScripts: true,
				onSuccess: function(transport) {
					try {
						exportFinished = true;
						'.$this->getGridId().'JsObject.doFilter();
						$("loading_mask_loader").removeClassName("export-loader");
						document.getElementById("loading_mask_loader").innerHTML = loading_mask_loader_html;
					} catch(e) {
						exportFinished = true;
						'.$this->getGridId().'JsObject.doFilter();
					}
				}.bind(this)
			});
		}

		function checkCustomersSynch() {
			new Ajax.Request(\''.$this->getCountCustomerExportUrl().'\', {
				onSuccess: function(transport) {
					if(!exportFinished && transport.responseText != "error") {
						$("loading_mask_loader_customers").update(transport.responseText);
						checkCustomersSynch();
					}
				}.bind(this)
			});
		}
		</script>
		';
	}

	protected function _getOrderExportHtml()
	{
		return '
		<style>
		.export-loader {width:400px !important; margin-left:-200px !important;}
		</style>
		<script type="text/javascript">
		var exportOrderFinished = false;
		function exportOrders() {
			$("loading_mask_loader").addClassName("export-loader");
			var loading_mask_loader_html = document.getElementById("loading_mask_loader").innerHTML;
			document.getElementById("loading_mask_loader").innerHTML = loading_mask_loader_html + "<br><span id=\"loading_mask_loader_orders\" style=\"width:200px\"></span>";
			checkOrderSynch();
			new Ajax.Request(\''.$this->getOrderExportUrl(true).'\', {
				evalScripts: true,
				onSuccess: function(transport) {
					try {
						exportOrderFinished = true;
						'.$this->getGridId().'Object.doFilter();
						$("loading_mask_loader").removeClassName("export-loader");
						document.getElementById("loading_mask_loader").innerHTML = loading_mask_loader_html;
					} catch(e) {
						exportFinished = true;
						'.$this->getGridId().'JsObject.doFilter();
					}
				}.bind(this)
			});
		}

		function checkOrderSynch() {
			new Ajax.Request(\''.$this->getCountOrderExportUrl().'\', {
				onSuccess: function(transport) {
					if(!exportOrderFinished && transport.responseText != "error") {
						$("loading_mask_loader_orders").update(transport.responseText);
						checkOrderSynch();
					}
				}.bind(this)
			});
		}
		</script>
		';
	}

	public function getGridId()
	{
		return 'fieldsmap_grid';
	}

	public function getCustomerExportUrl($ajax = false)
	{
		return $this->getUrl('*/*/export', array('ajax'=>$ajax));
	}

	public function getOrderExportUrl($ajax = false)
	{
		return $this->getUrl('*/*/exportorder', array('ajax'=>$ajax));
	}

	public function getCountCustomerExportUrl($ajax = false)
	{
		return $this->getUrl('*/*/countexport', array('ajax'=>$ajax));
	}

	public function getCountOrderExportUrl($ajax = false)
	{
		return $this->getUrl('*/*/countorderexport', array('ajax'=>$ajax));
	}

	/**
	 * Simple access control
	 *
	 * @return boolean True if user is allowed to edit operations
	 */
	protected function _isAllowedAction($action)
	{
		return Mage::getSingleton('admin/session')->isAllowed('admin/sugarcrm/synchtable/actions/'.$action);
	}
}