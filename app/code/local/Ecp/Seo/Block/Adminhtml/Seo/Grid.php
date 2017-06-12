<?php
/**
 * @category    Ecp
 * @package     Ecp_Seo
 */

/**
 * Description of Seo
 *
 * @category    Ecp
 * @package     Ecp_Seo
 */
class Ecp_Seo_Block_Adminhtml_Seo_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('seoGrid');
      $this->setDefaultSort('seo_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('ecp_seo/seo')->getCollection();
      /*@var $collection Ecp_Seo_Model_Mysql4_Seo_Collection*/
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('seo_id', array(
          'header'    => Mage::helper('ecp_seo')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'seo_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('ecp_seo')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));

	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('ecp_seo')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */

      $this->addColumn('status', array(
          'header'    => Mage::helper('ecp_seo')->__('Status'),
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
                'header'    =>  Mage::helper('ecp_seo')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('ecp_seo')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('ecp_seo')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('ecp_seo')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('seo_id');
        $this->getMassactionBlock()->setFormFieldName('seo');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('ecp_seo')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('ecp_seo')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('ecp_seo/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('ecp_seo')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('ecp_seo')->__('Status'),
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