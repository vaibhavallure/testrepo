<?php
/**
 * Description of Celebrities
 *
 * @category    Ecp
 * @package     Ecp_Celebrities
 */
class Ecp_Celebrities_Block_Adminhtml_Celebrities_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('celebritiesGrid');
      $this->setDefaultSort('celebrity_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('ecp_celebrities/celebrities')->getCollection();
      /*@var $collection Ecp_Celebrities_Model_Mysql4_Celebrities_Collection*/
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('celebrity_id', array(
          'header'    => Mage::helper('ecp_celebrities')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'celebrity_id',
      ));

      $this->addColumn('celebrity_name', array(
          'header'    => Mage::helper('ecp_celebrities')->__('Celebrity name'),
          'align'     =>'left',
          'index'     => 'celebrity_name',
          'type'      => 'image',
//          'renderer'  => 'ecp_slideshow_Block_Adminhtml_slideshow_Renderer_Image'
      ));

      $this->addColumn('ordering', array(
          'header'    => Mage::helper('ecp_celebrities')->__('Order'),
          'align'     =>'left',
          'index'     => 'ordering',
      ));

      $this->addColumn('default_image', array(
            'header'    => Mage::helper('ecp_celebrities')->__('Default image'),
            'width'     => '150px',
            'index'     => 'default_image',
            'align'     => 'center',
            'type'      => 'image',
            'renderer'  => 'Ecp_Celebrities_Block_Adminhtml_Celebrities_Renderer_Image'
      ));     
      
      $this->addColumn('url', array(
            'header'    => Mage::helper('ecp_celebrities')->__('URL'),
            'width'     => '150px',
            'index'     => 'url',
      ));

      $this->addColumn('status', array(
          'header'    => Mage::helper('ecp_celebrities')->__('Status'),
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
                'header'    =>  Mage::helper('ecp_celebrities')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('ecp_celebrities')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    ),
                    array(
                        'caption'   => Mage::helper('ecp_celebrities')->__('Outfit'),
                        'url'       => array('base'=> '*/*/outfit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('ecp_celebrities')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('ecp_celebrities')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('celebrity_id');
        $this->getMassactionBlock()->setFormFieldName('celebrities');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('ecp_celebrities')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('ecp_celebrities')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('ecp_celebrities/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('ecp_celebrities')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('ecp_celebrities')->__('Status'),
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