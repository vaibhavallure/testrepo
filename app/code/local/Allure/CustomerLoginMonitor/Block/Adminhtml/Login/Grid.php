<?php
class Allure_CustomerLoginMonitor_Block_Adminhtml_Login_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('loginmonitor');
        $this->setDefaultSort('row_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('customerloginmonitor/login')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('row_id',
            array(
                'header' => 'ID',
                'align' =>'right',
                'width' => '50px',
                'index' => 'row_id',
            ));

        $this->addColumn('customer_id',
            array(
                'header' => 'Customer Id',
                'align' =>'left',
                'index' => 'customer_id',
            ));
        $this->addColumn('customer_name',
            array(
                'header' => 'Name',
                'align' =>'left',
                'index' => 'customer_name',
            ));
        $this->addColumn('customer_email',
            array(
                'header' => 'Email',
                'align' =>'left',
                'index' => 'customer_email',
            ));
        $this->addColumn('remote_ip',
            array(
                'header' => 'IP',
                'align' =>'left',
                'index' => 'remote_ip',
            ));
        $this->addColumn('browser',
            array(
                'header' => 'Client software info',
                'align' =>'left',
                'index' => 'browser',
            ));
        $this->addColumn('status',
            array(
                'header' => 'Status',
                'align' =>'left',
                'index' => 'status',
            ));
        $this->addColumn('additional_info',
            array(
                'header' => 'Detail',
                'align' =>'left',
                'index' => 'additional_info',
            ));
        $this->addColumn('date',
            array(
                'header' => 'Date and time',
                'type' => 'datetime',
                'align' =>'left',
                'index' => 'date',
            ));
        return parent::_prepareColumns();
    }


}