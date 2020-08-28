<?php
/**
 * 
 * @author allure
 *
 */
class Allure_CustomUrl_Block_Adminhtml_CustomUrl_Grid extends Mage_Adminhtml_Block_Widget_Grid 
{
    
    public function __construct()
    {
        parent::__construct();
        
        $this->setDefaultSort('id');
        $this->setId('allure_customurl_grid');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }
    
    protected function _getCollectionClass()
    {
        return 'allure_customurl/url_collection';
    }
    
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        
        $this->addColumn('url_id',
            array(
                'header'=> $this->__('ID'),
                'align' =>'right',
                'width' => '50px',
                'index' => 'url_id'
            )
            );
        
        $this->addColumn('store_id', array(
            'header' => $this->__('Store'),
            'type' => 'options',
            'options' => Mage::getSingleton('adminhtml/system_store')->getStoreOptionHash(),
            'index' => 'store_id',
            'sortable' => false,
        ));
        
        $this->addColumn('current_url',
            array(
                'header'=> $this->__('Current Url'),
                'index' => 'current_url'
            )
            );
        
        $this->addColumn('request_path',
            array(
                'header'=> $this->__('Request Path'),
                'index' => 'request_path'
            )
            );
        
        $this->addColumn('target_path',
            array(
                'header'=> $this->__('Target Path'),
                'index' => 'target_path'
            )
            );
        
        $this->addColumn('is_rewrite_url', array(
            'header' => $this->__('Is Rewrite'),
            'type' => 'options',
            'options' => array(
                0 => "No",
                1 => "Yes"
            ),
            'index' => 'is_rewrite_url',
            'sortable' => false,
        ));
        
        $this->addColumn('rewrite_url_id',
            array(
                'header'=> $this->__('Rewrite Id'),
                'index' => 'rewrite_url_id'
            )
            );
        
        $this->addColumn('options',
            array(
                'header'=> $this->__('Redirect'),
                'index' => 'options'
            )
            );
        
        $this->addColumn(
            'action',
            array(
                'header'  =>  Mage::helper('allure_customurl')->__('Action'),
                'width'   => '100',
                'type'    => 'action',
                'getter'  => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('allure_customurl')->__('Edit'),
                        'url'     => array('base'=> '*/*/edit'),
                        'field'   => 'id',
                        'popup'=>true
                    )
                ),
                'filter'    => false,
                'is_system' => true,
                'sortable'  => false,
            )
            );
        return parent::_prepareColumns();
    }   
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
    
}