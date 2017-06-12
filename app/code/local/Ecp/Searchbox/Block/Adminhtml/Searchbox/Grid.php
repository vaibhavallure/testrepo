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
 * @package     Ecp_Searchbox
 * @copyright   Copyright (c) 2010 Ecp Inc. (http://www.ecp.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Searchbox
 *
 * @category    Ecp
 * @package     Ecp_Searchbox
 * @author      Ecp Core Team <core@ecp.com>
 */
class Ecp_Searchbox_Block_Adminhtml_Searchbox_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('searchboxGrid');
      $this->setDefaultSort('searchbox_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('ecp_searchbox/searchbox')->getCollection();
      /*@var $collection Ecp_Searchbox_Model_Mysql4_Searchbox_Collection*/
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('searchbox_id', array(
          'header'    => Mage::helper('ecp_searchbox')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'searchbox_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('ecp_searchbox')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));

	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('ecp_searchbox')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */

      $this->addColumn('status', array(
          'header'    => Mage::helper('ecp_searchbox')->__('Status'),
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
                'header'    =>  Mage::helper('ecp_searchbox')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('ecp_searchbox')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('ecp_searchbox')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('ecp_searchbox')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('searchbox_id');
        $this->getMassactionBlock()->setFormFieldName('searchbox');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('ecp_searchbox')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('ecp_searchbox')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('ecp_searchbox/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('ecp_searchbox')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('ecp_searchbox')->__('Status'),
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