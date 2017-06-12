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
class Ecp_Tattoo_Block_Adminhtml_Consultations_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('consultationsGrid');
      $this->setDefaultSort('consultation_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('ecp_tattoo/tattoo_consultations')->getCollection();
      /*@var $collection Ecp_Tattoo_Model_Mysql4_Tattoo_Collection*/
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('consultation_id', array(
          'header'    => Mage::helper('ecp_tattoo')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'consultation_id',
      ));

      $this->addColumn('artist_name', array(
            'header'    => Mage::helper('ecp_celebrities')->__('Artist'),
            'width'     => '150px',
            'index'     => 'artist_name',
            'align'     => 'center'
      ));     
      
      $this->addColumn('from_email', array(
          'header'    => Mage::helper('ecp_tattoo')->__('From email'),
          'align'     =>'left',
          'index'     => 'from_email',
      ));
      
      $this->addColumn('from_name', array(
          'header'    => Mage::helper('ecp_tattoo')->__('From name'),
          'align'     =>'left',
          'index'     => 'from_name',
      ));
      
      $this->addColumn('created_time', array(
          'header'    => Mage::helper('ecp_tattoo')->__('Date'),
          'align'     =>'left',
          'index'     => 'created_time',
      ));
	  
//    $this->addColumn('action',
//        array(
//            'header'    =>  Mage::helper('ecp_tattoo')->__('Action'),
//            'width'     => '100',
//            'type'      => 'action',
//            'getter'    => 'getId',
//            'actions'   => array(
//                array(
//                    'caption'   => Mage::helper('ecp_tattoo')->__('Edit'),
//                    'url'       => array('base'=> '*/*/edit'),
//                    'field'     => 'id'
//                )
//            ),
//            'filter'    => false,
//            'sortable'  => false,
//            'index'     => 'stores',
//            'is_system' => true,
//    ));
		
//		$this->addExportType('*/*/exportCsv', Mage::helper('ecp_tattoo')->__('CSV'));
//		$this->addExportType('*/*/exportXml', Mage::helper('ecp_tattoo')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
//        $this->setMassactionIdField('tattoo_id');
//        $this->getMassactionBlock()->setFormFieldName('tattoo');
//
//        $this->getMassactionBlock()->addItem('delete', array(
//             'label'    => Mage::helper('ecp_tattoo')->__('Delete'),
//             'url'      => $this->getUrl('*/*/massDelete'),
//             'confirm'  => Mage::helper('ecp_tattoo')->__('Are you sure?')
//        ));
//
//        $statuses = Mage::getSingleton('ecp_tattoo/status')->getOptionArray();
//
//        array_unshift($statuses, array('label'=>'', 'value'=>''));
//        $this->getMassactionBlock()->addItem('status', array(
//             'label'=> Mage::helper('ecp_tattoo')->__('Change status'),
//             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
//             'additional' => array(
//                    'visibility' => array(
//                         'name' => 'status',
//                         'type' => 'select',
//                         'class' => 'required-entry',
//                         'label' => Mage::helper('ecp_tattoo')->__('Status'),
//                         'values' => $statuses
//                     )
//             )
//        ));
//        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/seeConsultation', array('id' => $row->getId()));
  }

}