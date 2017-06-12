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
 * @package     Ecp_Alertstock
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Alertstock
 *
 * @category    Ecp
 * @package     Ecp_Alertstock
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Alertstock_Block_Adminhtml_Alertstock_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('alertstockGrid');
      $this->setDefaultSort('alertstock_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('ecp_alertstock/alertstock')->getCollection();
      /*@var $collection Ecp_Alertstock_Model_Mysql4_Alertstock_Collection*/
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('alertstock_id', array(
          'header'    => Mage::helper('ecp_alertstock')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'alertstock_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('ecp_alertstock')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));

	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('ecp_alertstock')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */

      $this->addColumn('status', array(
          'header'    => Mage::helper('ecp_alertstock')->__('Status'),
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
                'header'    =>  Mage::helper('ecp_alertstock')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('ecp_alertstock')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('ecp_alertstock')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('ecp_alertstock')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('alertstock_id');
        $this->getMassactionBlock()->setFormFieldName('alertstock');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('ecp_alertstock')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('ecp_alertstock')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('ecp_alertstock/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('ecp_alertstock')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('ecp_alertstock')->__('Status'),
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