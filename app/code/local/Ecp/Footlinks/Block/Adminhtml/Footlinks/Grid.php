<?php

class Ecp_Footlinks_Block_Adminhtml_Footlinks_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('footlinksGrid');
      $this->setDefaultSort('sort_order');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
        $collection = Mage::getModel('ecp_footlinks/footlinks')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {      
        $this->addColumn('sort order', array(
            'header'    => Mage::helper('footlinks')->__('Sort Order'),
            'align'     => 'left',
            'index'     => 'sort_order'
        ));

        $this->addColumn('title', array(
            'header'    => Mage::helper('footlinks')->__('Title'),
            'align'     => 'left',
            'index'     => 'title'
        ));
        
        $this->addColumn('link', array(
            'header'    => Mage::helper('footlinks')->__('Link'),
            'align'     => 'left',
            'index'     => 'link'
        ));
        
        $this->addColumn('type', array(
            'header'    => Mage::helper('footlinks')->__('Type'),
            'align'     => 'left',
            'index'     => 'type',
            'renderer'  => 'Ecp_Footlinks_Block_Adminhtml_Footlinks_Renderer_RendererType'
        ));
        
        $this->addColumn('block value', array(
            'header'    => Mage::helper('footlinks')->__('Block Value'),
            'align'     => 'left',
            'index'     => 'block_value',
            'renderer'  => 'Ecp_Footlinks_Block_Adminhtml_Footlinks_Renderer_RendererValue'
        ));
        
        $this->addColumn('url value', array(
            'header'    => Mage::helper('footlinks')->__('Url Value'),
            'align'     => 'left',
            'index'     => 'url_value'
        ));
        
        $this->addColumn('status', array(
            'header'    => Mage::helper('footlinks')->__('Status'),
            'align'     => 'left',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(
                            1 => 'Enabled',
                            2 => 'Disabled')
            ));
                
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {        
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}