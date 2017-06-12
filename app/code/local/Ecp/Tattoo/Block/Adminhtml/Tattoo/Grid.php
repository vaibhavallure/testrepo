<?php
/**
 * Entrepids
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future. If you wish to customize for your
 * needs please refer to Entrepids Event-Observer for more information.
 * 
 * @category    Ecp
 * @package     Ecp_Tattoo
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Tattoo
 *
 * @category    Ecp
 * @package     Ecp_Tattoo
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Tattoo_Block_Adminhtml_Tattoo_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('tattooGrid');
      $this->setDefaultSort('tattoo_artist_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('ecp_tattoo/tattoo_artist')->getCollection();
      /*@var $collection Ecp_Tattoo_Model_Mysql4_Tattoo_Collection*/
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('tattoo_id', array(
          'header'    => Mage::helper('ecp_tattoo')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'tattoo_id',
      ));

      /*$this->addColumn('image', array(
            'header'    => Mage::helper('ecp_celebrities')->__('Image'),
            'width'     => '150px',
            'index'     => 'image',
            'align'     => 'center',
            'type'      => 'image',
            'renderer'  => 'Ecp_Tattoo_Block_Adminhtml_Tattoo_Renderer_Image'
      ));*/     
      
      $this->addColumn('name', array(
          'header'    => Mage::helper('ecp_tattoo')->__('Name'),
          'align'     =>'left',
          'index'     => 'name',
      ));
      
      $this->addColumn('email', array(
          'header'    => Mage::helper('ecp_tattoo')->__('Email'),
          'align'     =>'left',
          'index'     => 'email',
      ));
      
      /*$this->addColumn('url', array(
          'header'    => Mage::helper('ecp_tattoo')->__('url'),
          'align'     =>'left',
          'index'     => 'url',
      ));*/
      
      

	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('ecp_tattoo')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */

      $this->addColumn('status', array(
          'header'    => Mage::helper('ecp_tattoo')->__('Status'),
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
                'header'    =>  Mage::helper('ecp_tattoo')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('ecp_tattoo')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('ecp_tattoo')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('ecp_tattoo')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('tattoo_id');
        $this->getMassactionBlock()->setFormFieldName('tattoo');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('ecp_tattoo')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('ecp_tattoo')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('ecp_tattoo/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('ecp_tattoo')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('ecp_tattoo')->__('Status'),
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