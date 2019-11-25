<?php
/**
 * Ecp
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
 * needs please refer to Ecp Event-Observer for more information.
 * 
 * @category    Ecp
 * @package     Ecp_Slideshow
 * @copyright   Copyright (c) 2010 Ecp Inc. (http://www.ecp.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Slideshow
 *
 * @category    Ecp
 * @package     Ecp_Slideshow
 * @author      Ecp Core Team <core@ecp.com>
 */
class Ecp_Slideshow_Block_Adminhtml_Slideshow_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('slideshowGrid');
      $this->setDefaultSort('slideshow_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('ecp_slideshow/slideshow')->getCollection();
      /*@var $collection Ecp_Slideshow_Model_Mysql4_Slideshow_Collection*/
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('slideshow_id', array(
          'header'    => Mage::helper('ecp_slideshow')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'slideshow_id',
      ));

      $this->addColumn('slide_background', array(
          'header'    => Mage::helper('ecp_slideshow')->__('Background'),
          'align'     =>'left',
          'index'     => 'slide_background',
          'type'      => 'image',
          'renderer'  => 'ecp_slideshow_Block_Adminhtml_slideshow_Renderer_Image'
      ));
      
      $this->addColumn('slide_thumb', array(
          'header'    => Mage::helper('ecp_slideshow')->__('Thumbnail'),
          'align'     =>'left',
          'index'     => 'slide_thumb',
          'type'      => 'image',
          'renderer'  => 'ecp_slideshow_Block_Adminhtml_slideshow_Renderer_Image'
      ));
      
      $this->addColumn('url', array(
          'header'    => Mage::helper('ecp_slideshow')->__('Url'),
          'align'     =>'left',
          'index'     => 'url',
      ));
 $this->addColumn('slide_content', array(
          'header'    => Mage::helper('ecp_slideshow')->__('Slide Content'),
          'align'     =>'left',
          'index'     => 'slide_content',
      ));
	  
	  $this->addColumn('position', array(
          'header'    => Mage::helper('ecp_slideshow')->__('Position'),
          'align'     =>'left',
          'index'     => 'position',
      ));
      
      $this->addColumn('status', array(
          'header'    => Mage::helper('ecp_slideshow')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              0 => 'Disabled',
          ),
      ));
      
      $this->addColumn('created_date', array(
          'header'    => Mage::helper('ecp_slideshow')->__('Date'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'date',         
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('ecp_slideshow')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('ecp_slideshow')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('ecp_slideshow')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('ecp_slideshow')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('slideshow_id');
        $this->getMassactionBlock()->setFormFieldName('slideshow');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('ecp_slideshow')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('ecp_slideshow')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('ecp_slideshow/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('ecp_slideshow')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('ecp_slideshow')->__('Status'),
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
