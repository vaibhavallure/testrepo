<?php
/**
 * Allure_InstaCatalog
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @copyright   Copyright© 2016, Allure Inc
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 * @author      Team Allure <extensions@allureinc.co>
 */
/**
 * Feed admin grid block
 *
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @author      Team Allure <extensions@allureinc.co>
 */
class Allure_InstaCatalog_Block_Adminhtml_Feed_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * constructor
     *
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('feedGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * prepare collection
     *
     * @access protected
     * @return Allure_InstaCatalog_Block_Adminhtml_Feed_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('allure_instacatalog/feed')
            ->getCollection();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare grid collection
     *
     * @access protected
     * @return Allure_InstaCatalog_Block_Adminhtml_Feed_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            array(
                'header' => Mage::helper('allure_instacatalog')->__('Id'),
                'index'  => 'entity_id',
                'type'   => 'number'
            )
        );
        
        
        $this->addColumn( 
        		'image', 
        		array(
	        		'header' => Mage::helper( 'allure_instacatalog' )->__( 'Image' ),
	        		'type' => 'image',
	        		'width' => '75px',
	        		'index' => 'image',
	        		'filter'    => false,
	        		'sortable'  => false,
	        		'renderer' => 'allure_instacatalog/adminhtml_template_grid_renderer_image',
        		)
        	);
        
        
        /* $this->addColumn(
            'media_id',
            array(
                'header'    => Mage::helper('allure_instacatalog')->__('Media Id'),
                'align'     => 'left',
                'index'     => 'media_id',
            )
        ); */
        
        
        $this->addColumn(
        		'text',
        		array(
        				'header'    => Mage::helper('allure_instacatalog')->__('Caption'),
        				'align'     => 'left',
        				'index'     => 'text',
        		)
        		);
        
        $this->addColumn(
            'status',
            array(
                'header'  => Mage::helper('allure_instacatalog')->__('Status'),
                'index'   => 'status',
                'type'    => 'options',
                'options' => array(
                    '1' => Mage::helper('allure_instacatalog')->__('Enabled'),
                    '0' => Mage::helper('allure_instacatalog')->__('Disabled'),
                )
            )
        );
        $this->addColumn(
        		'lookbook_mode',
        		array(
        				'header'  => Mage::helper('allure_instacatalog')->__('Type'),
        				'index'   => 'lookbook_mode',
        				'type'    => 'options',
        				'options' => array(
        						'0' => Mage::helper('allure_instacatalog')->__('Instagram'),
        						'1' => Mage::helper('allure_instacatalog')->__('Shop by Look'),
        				)
        		)
        );
       /*  $this->addColumn(
            'username',
            array(
                'header' => Mage::helper('allure_instacatalog')->__('Link'),
                'index'  => 'username',
                'type'=> 'text',

            )
        ); */
       /*  $this->addColumn(
            'caption',
            array(
                'header' => Mage::helper('allure_instacatalog')->__('Caption'),
                'index'  => 'caption',
                'type'=> 'text',

            )
        ); */
        /* if (!Mage::app()->isSingleStoreMode() && !$this->_isExport) {
            $this->addColumn(
                'store_id',
                array(
                    'header'     => Mage::helper('allure_instacatalog')->__('Store Views'),
                    'index'      => 'store_id',
                    'type'       => 'store',
                    'store_all'  => true,
                    'store_view' => true,
                    'sortable'   => false,
                    'filter_condition_callback'=> array($this, '_filterStoreCondition'),
                )
            );
        }
        $this->addColumn(
            'created_at',
            array(
                'header' => Mage::helper('allure_instacatalog')->__('Created at'),
                'index'  => 'created_at',
                'width'  => '120px',
                'type'   => 'datetime',
            )
        );
        $this->addColumn(
            'updated_at',
            array(
                'header'    => Mage::helper('allure_instacatalog')->__('Updated at'),
                'index'     => 'updated_at',
                'width'     => '120px',
                'type'      => 'datetime',
            )
        ); */
        $this->addColumn(
            'action',
            array(
                'header'  =>  Mage::helper('allure_instacatalog')->__('Action'),
                'width'   => '100',
                'type'    => 'action',
                'getter'  => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('allure_instacatalog')->__('Edit'),
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
        $this->addExportType('*/*/exportCsv', Mage::helper('allure_instacatalog')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('allure_instacatalog')->__('Excel'));
        $this->addExportType('*/*/exportXml', Mage::helper('allure_instacatalog')->__('XML'));
        return parent::_prepareColumns();
    }

    /**
     * prepare mass action
     *
     * @access protected
     * @return Allure_InstaCatalog_Block_Adminhtml_Feed_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('feed');
        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label'=> Mage::helper('allure_instacatalog')->__('Delete'),
                'url'  => $this->getUrl('*/*/massDelete'),
                'confirm'  => Mage::helper('allure_instacatalog')->__('Are you sure?')
            )
        );
        $this->getMassactionBlock()->addItem(
            'status',
            array(
                'label'      => Mage::helper('allure_instacatalog')->__('Change status'),
                'url'        => $this->getUrl('*/*/massStatus', array('_current'=>true)),
                'additional' => array(
                    'status' => array(
                        'name'   => 'status',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => Mage::helper('allure_instacatalog')->__('Status'),
                        'values' => array(
                            '1' => Mage::helper('allure_instacatalog')->__('Enabled'),
                            '0' => Mage::helper('allure_instacatalog')->__('Disabled'),
                        )
                    )
                )
            )
        );
        return $this;
    }

    /**
     * get the row url
     *
     * @access public
     * @param Allure_InstaCatalog_Model_Feed
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * get the grid url
     *
     * @access public
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    /**
     * after collection load
     *
     * @access protected
     * @return Allure_InstaCatalog_Block_Adminhtml_Feed_Grid
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    /**
     * filter store column
     *
     * @access protected
     * @param Allure_InstaCatalog_Model_Resource_Feed_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return Allure_InstaCatalog_Block_Adminhtml_Feed_Grid
     */
    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $collection->addStoreFilter($value);
        return $this;
    }
}
