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
 * @package     Ecp_Press
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Press
 *
 * @category    Ecp
 * @package     Ecp_Press
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Press_Block_Adminhtml_Press_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('pressGrid');
      $this->setDefaultSort('press_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('ecp_press/press')->getCollection();
      /*@var $collection Ecp_Press_Model_Mysql4_Press_Collection*/
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('press_id', array(
          'header'    => Mage::helper('ecp_press')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'press_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('ecp_press')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));
      
      $this->addColumn('description', array(
          'header'    => Mage::helper('ecp_press')->__('Description'),
          'align'     =>'left',
          'index'     => 'description',
      ));

      $this->addColumn('image_one', array(
          'header'    => Mage::helper('ecp_press')->__('Image one'),
          'align'     =>'left',
          'index'     => 'image_one',
          'type'      => 'image',
          'renderer'  => 'ecp_press_Block_Adminhtml_press_Renderer_Image'
      ));
      
      $this->addColumn('image_two', array(
          'header'    => Mage::helper('ecp_press')->__('Image two'),
          'align'     =>'left',
          'index'     => 'image_two',
          'type'      => 'image',
          'renderer'  => 'ecp_press_Block_Adminhtml_press_Renderer_Image'
      ));

      $this->addColumn('image_tree', array(
          'header'    => Mage::helper('ecp_press')->__('Image tree'),
          'align'     =>'left',
          'index'     => 'image_tree',
          'type'      => 'image',
          'renderer'  => 'ecp_press_Block_Adminhtml_press_Renderer_Image'
      ));
      
       $this->addColumn('image_four', array(
          'header'    => Mage::helper('ecp_press')->__('Image four'),
          'align'     =>'left',
          'index'     => 'image_four',
          'type'      => 'image',
          'renderer'  => 'ecp_press_Block_Adminhtml_press_Renderer_Image'
      ));
      
      $this->addColumn('status', array(
          'header'    => Mage::helper('ecp_press')->__('Status'),
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
                'header'    =>  Mage::helper('ecp_press')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('ecp_press')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('ecp_press')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('ecp_press')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('press_id');
        $this->getMassactionBlock()->setFormFieldName('press');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('ecp_press')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('ecp_press')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('ecp_press/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('ecp_press')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('ecp_press')->__('Status'),
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