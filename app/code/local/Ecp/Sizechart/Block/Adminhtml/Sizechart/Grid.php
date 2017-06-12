<?php
/**
 * Description of Sizechart
 *
 * @category    Ecp
 * @package     Ecp_Sizechart
 */
class Ecp_Sizechart_Block_Adminhtml_Sizechart_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('sizechartGrid');
      $this->setDefaultSort('sizechart_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('ecp_sizechart/sizechart')->getCollection();
      /*@var $collection Ecp_Sizechart_Model_Mysql4_Sizechart_Collection*/
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('sizechart_id', array(
          'header'    => Mage::helper('ecp_sizechart')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'sizechart_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('ecp_sizechart')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));

      $this->addColumn('status', array(
          'header'    => Mage::helper('ecp_sizechart')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('ecp_sizechart')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('ecp_sizechart')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
//		$this->addExportType('*/*/exportCsv', Mage::helper('ecp_sizechart')->__('CSV'));
//		$this->addExportType('*/*/exportXml', Mage::helper('ecp_sizechart')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('sizechart_id');
        $this->getMassactionBlock()->setFormFieldName('sizechart');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('ecp_sizechart')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('ecp_sizechart')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('ecp_sizechart/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('ecp_sizechart')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('ecp_sizechart')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}