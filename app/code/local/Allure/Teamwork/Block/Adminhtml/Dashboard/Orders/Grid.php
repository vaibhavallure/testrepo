<?php

/**
 * Adminhtml dashboard recent orders grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Allure_Teamwork_Block_Adminhtml_Dashboard_Orders_Grid extends Mage_Adminhtml_Block_Dashboard_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('lastOrdersGrid');
    }

    /**
     *get user role to show teamwork orders
     */
    private function getRolesToShowTeamworkOrder(){
        return array(1);//"Super Administrator"
    }

    protected function _prepareCollection()
    {
        if (!Mage::helper('core')->isModuleEnabled('Mage_Reports')) {
            return $this;
        }
        $collection = Mage::getResourceModel('reports/order_collection')
            ->addItemCountExpr()
            ->joinCustomerName('customer')
            ->orderByCreatedAt();

        if($this->getParam('store') || $this->getParam('website') || $this->getParam('group')) {
            if ($this->getParam('store')) {
                $collection->addAttributeToFilter('store_id', $this->getParam('store'));
            } else if ($this->getParam('website')){
                $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
                $collection->addAttributeToFilter('store_id', array('in' => $storeIds));
            } else if ($this->getParam('group')){
                $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
                $collection->addAttributeToFilter('store_id', array('in' => $storeIds));
            }

            $collection->addRevenueToSelect();
        } else {
            $collection->addRevenueToSelect(true);
        }

        $roles = $this->getRolesToShowTeamworkOrder();
        $user = Mage::getSingleton('admin/session')->getUser();

        if($user != null){
            $userRole = $user->getRole()->getData();
            $roleName = $userRole["role_id"];
            if($roleName != 1){
                $collection->addFieldToFilter('main_table.create_order_method', array('nin' => array(2)));
            }
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepares page sizes for dashboard grid with las 5 orders
     *
     * @return void
     */
    protected function _preparePage()
    {
        $this->getCollection()->setPageSize($this->getParam($this->getVarNameLimit(), $this->_defaultLimit));
        // Remove count of total orders $this->getCollection()->setCurPage($this->getParam($this->getVarNamePage(), $this->_defaultPage));
    }

    protected function _prepareColumns()
    {
        $this->addColumn('customer', array(
            'header'    => $this->__('Customer'),
            'sortable'  => false,
            'index'     => 'customer',
            'default'   => $this->__('Guest'),
        ));

        $this->addColumn('items', array(
            'header'    => $this->__('Items'),
            'align'     => 'right',
            'type'      => 'number',
            'sortable'  => false,
            'index'     => 'items_count'
        ));

        $baseCurrencyCode = Mage::app()->getStore((int)$this->getParam('store'))->getBaseCurrencyCode();

        $this->addColumn('total', array(
            'header'    => $this->__('Grand Total'),
            'align'     => 'right',
            'sortable'  => false,
            'type'      => 'currency',
            'currency_code'  => $baseCurrencyCode,
            'index'     => 'revenue'
        ));

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/sales_order/view', array('order_id'=>$row->getId()));
    }
}
