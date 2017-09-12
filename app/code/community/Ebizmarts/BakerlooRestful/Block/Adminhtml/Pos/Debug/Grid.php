<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Pos_Debug_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();

        $this->setId('bakerloo_restful_debug');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('debug_at');
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('bakerloo_restful/debug')->getCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('bakerloo_restful');

        $this->addColumn(
            'debug_at',
            array(
                'header' => $helper->__('Time'),
                'index'  => 'debug_at',
                'type'   => 'datetime',
            )
        );

        $this->addColumn(
            'request_method',
            array(
                'header' => $helper->__('Method'),
                'index'  => 'request_method',
            )
        );

        $this->addColumn(
            'resource',
            array(
                'header' => $helper->__('Resource'),
                'index'  => 'resource',
            )
        );

        $this->addColumn(
            'remote_addr',
            array(
                'header'    => $helper->__('IP'),
                'index'     => 'remote_addr',
                'renderer'  => 'adminhtml/customer_online_grid_renderer_ip'
            )
        );

        $this->addColumn(
            'response_code',
            array(
                'header' => $helper->__('Response Code'),
                'index'  => 'response_code',
            )
        );

        $this->addColumn(
            'user_agent',
            array(
                'header' => $helper->__('User Agent'),
                'index'  => 'user_agent'
            )
        );

        $this->addColumn(
            'call_time',
            array(
                'header' => $helper->__('Call Time'),
                'index'  => 'call_time',
                'align'  => 'right',
                'filter' => false
            )
        );

        $this->addColumn(
            'action',
            array(
                'header'  => $helper->__('Action'),
                'width'   => '80px',
                'type'    => 'action',
                'align'   => 'center',
                'getter'  => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('bakerloo_restful')->__('view full'),
                        'url'     => array('base' => 'adminhtml/pos_debug/view'),
                        'field'   => 'id',
                    ),
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
            )
        );

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    /**
     * Return row url for js event handlers
     *
     * @param Varien_Object
     * @return string
     */
    public function getRowUrl($log)
    {
        return false;
    }
}
