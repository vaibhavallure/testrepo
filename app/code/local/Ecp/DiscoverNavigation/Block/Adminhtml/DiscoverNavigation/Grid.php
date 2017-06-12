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
 * @package     Ecp_DiscoverNavigation
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of DiscoverNavigation
 *
 * @category    Ecp
 * @package     Ecp_DiscoverNavigation
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_DiscoverNavigation_Block_Adminhtml_DiscoverNavigation_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('discovernavigationGrid');
      $this->setDefaultSort('discover_mariatash_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('ecp_discovernavigation/discovernavigation')->getCollection()->addFieldToFilter('type',2);
      /*@var $collection Ecp_DiscoverNavigation_Model_Mysql4_DiscoverNavigation_Collection*/
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('discover_mariatash_id', array(
          'header'    => Mage::helper('ecp_discovernavigation')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'discover_mariatash_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('ecp_discovernavigation')->__('Title'),
          'align'     =>'left',
          'index'     => 'category_name',
      ));	
	  
//    $this->addColumn('action',
//        array(
//            'header'    =>  Mage::helper('ecp_discovernavigation')->__('Action'),
//            'width'     => '100',
//            'type'      => 'action',
//            'getter'    => 'getId',
//            'actions'   => array(
//                array(
//                    'caption'   => Mage::helper('ecp_discovernavigation')->__('Edit'),
//                    'url'       => array('base'=> '*/*/edit'),
//                    'field'     => 'id'
//                )
//            ),
//            'filter'    => false,
//            'sortable'  => false,
//            'index'     => 'stores',
//            'is_system' => true,
//    ));
		
//		$this->addExportType('*/*/exportCsv', Mage::helper('ecp_discovernavigation')->__('CSV'));
//		$this->addExportType('*/*/exportXml', Mage::helper('ecp_discovernavigation')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('discovernavigation_id');
        $this->getMassactionBlock()->setFormFieldName('discovernavigation');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('ecp_discovernavigation')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('ecp_discovernavigation')->__('Are you sure?')
        ));

//        $statuses = Mage::getSingleton('ecp_discovernavigation/status')->getOptionArray();
//
//        array_unshift($statuses, array('label'=>'', 'value'=>''));
//        $this->getMassactionBlock()->addItem('status', array(
//             'label'=> Mage::helper('ecp_discovernavigation')->__('Change status'),
//             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
//             'additional' => array(
//                    'visibility' => array(
//                         'name' => 'status',
//                         'type' => 'select',
//                         'class' => 'required-entry',
//                         'label' => Mage::helper('ecp_discovernavigation')->__('Status'),
//                         'values' => $statuses
//                     )
//             )
//        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}