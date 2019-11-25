<?php
class IWD_OrderManager_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    protected function _prepareCollection()
    {
        if (!Mage::helper("iwd_ordermanager")->enableCustomGrid()){
            return parent::_prepareCollection();
        }

        $filter = $this->prepareFilters();
        $collection = Mage::getResourceModel("sales/order_grid_collection");
        $collection = Mage::getModel('iwd_ordermanager/order_grid')->prepareCollection($filter, $collection);

        $this->setCollection($collection);

        Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        if (!Mage::helper("iwd_ordermanager")->enableCustomGrid()) {
            return parent::_prepareColumns();
        } else {
            $helper = Mage::helper('iwd_ordermanager');
            $grid = Mage::getModel('iwd_ordermanager/order_grid')->prepareColumns($this);
            $grid = Mage::getModel('iwd_ordermanager/order_grid')->addHiddenColumnWithStatus($grid);

            $grid->addRssList('rss/order/new', $helper->__('New Order RSS'));
            $grid->addExportType('*/*/exportCsv', $helper->__('CSV'));
            $grid->addExportType('*/*/exportExcel', $helper->__('Excel XML'));
            $grid->sortColumnsByOrder();
            return $grid;
        }
    }

    protected function prepareFilters(){
        $filter   = $this->getParam($this->getVarNameFilter(), null);

        if (is_null($filter)) {
            $filter = $this->_defaultFilter;
        }

        if (is_string($filter)) {
            $filter = $this->helper('adminhtml')->prepareFilterString($filter);
        }

        return $filter;
    }

    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view'))
            return $this->getUrl('*/sales_order/view', array('order_id' => $row->getId()));
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function _toHtml()
    {
        $fix_header = "";
        if(Mage::getModel('iwd_ordermanager/order_grid')->isFixGridHeader()) {
            $fix_header = 'if($ji.isFunction($ji.fn.stickyTableHeaders)){$ji("#sales_order_grid_table").stickyTableHeaders();}';
        }

        $script = '<script type="text/javascript">
                      if(typeof(jQueryIWD) == "undefined"){if(typeof(jQuery) != "undefined") {jQueryIWD = jQuery;}} $ji = jQueryIWD;
                      if($ji("#sales_order_grid_table").length) {
                        IWD.OrderManager.Grid.ColorGridRow();
                        '.$fix_header.'
                      }
                 </script>';

        return parent::_toHtml() . $script .  $this->updateFilters();
    }

    protected function updateFilters(){
        $is_limit_period = Mage::getModel('iwd_ordermanager/order_grid')->isLimitPeriod();
        if(!$is_limit_period){
            return "";
        }

        $from = Mage::getSingleton('adminhtml/session')->getData("created_at_from");
        $to = Mage::getSingleton('adminhtml/session')->getData("created_at_to");

        return '<script type="text/javascript">
                      if(typeof(jQueryIWD) == "undefined"){if(typeof(jQuery) != "undefined") {jQueryIWD = jQuery;}} $ji = jQueryIWD;
                      $ji("input[name=\'created_at[from]\']").val("'.$from.'");
                      $ji("input[name=\'created_at[to]\']").val("'.$to.'");
                 </script>';
    }
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);
        
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/cancel')) {
            $this->getMassactionBlock()->addItem('cancel_order', array(
                'label'=> Mage::helper('sales')->__('Cancel'),
                'url'  => $this->getUrl('*/sales_order/massCancel'),
            ));
        }
        
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/hold')) {
            $this->getMassactionBlock()->addItem('hold_order', array(
                'label'=> Mage::helper('sales')->__('Hold'),
                'url'  => $this->getUrl('*/sales_order/massHold'),
            ));
        }
        
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/unhold')) {
            $this->getMassactionBlock()->addItem('unhold_order', array(
                'label'=> Mage::helper('sales')->__('Unhold'),
                'url'  => $this->getUrl('*/sales_order/massUnhold'),
            ));
        }
        
        $this->getMassactionBlock()->addItem('pdforders_order', array(
            'label'=> Mage::helper('sales')->__('Print Orders'),
            'url'  => $this->getUrl('*/sales_order/pdforders'),
        ));
        
        $this->getMassactionBlock()->addItem('pdfinvoices_order', array(
            'label'=> Mage::helper('sales')->__('Print Invoices'),
            'url'  => $this->getUrl('*/sales_order/pdfinvoices'),
        ));
        
        $this->getMassactionBlock()->addItem('pdfshipments_order', array(
            'label'=> Mage::helper('sales')->__('Print Packingslips'),
            'url'  => $this->getUrl('*/sales_order/pdfshipments'),
        ));
        
        $this->getMassactionBlock()->addItem('pdfcreditmemos_order', array(
            'label'=> Mage::helper('sales')->__('Print Credit Memos'),
            'url'  => $this->getUrl('*/sales_order/pdfcreditmemos'),
        ));
        
        $this->getMassactionBlock()->addItem('pdfdocs_order', array(
            'label'=> Mage::helper('sales')->__('Print All'),
            'url'  => $this->getUrl('*/sales_order/pdfdocs'),
        ));
        
        $this->getMassactionBlock()->addItem('print_shipping_label', array(
            'label'=> Mage::helper('sales')->__('Print Shipping Labels'),
            'url'  => $this->getUrl('*/sales_order_shipment/massPrintShippingLabel'),
        ));
        
        /**
         * Generate gift item receipt.
         */
        $this->getMassactionBlock()->addItem('pdforders_gift_receipt', array(
            'label'=> Mage::helper('sales')->__('Print Orders Gift Receipt'),
            'url'  => $this->getUrl('*/sales_order/pdfOrdersGiftReceipt'),
        ));
        
        return $this;
    }
    
}