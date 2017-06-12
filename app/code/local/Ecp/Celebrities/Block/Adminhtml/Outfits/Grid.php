<?php
/**
 * Description of Outfits
*
* @category    Ecp
* @package     Ecp_Outfits
*/
class Ecp_Celebrities_Block_Adminhtml_Outfits_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('outfitsGrid');
		$this->setDefaultSort('celebrity_outfit_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$id     = $this->getRequest()->getParam('id');
		$collection = Mage::getModel('ecp_celebrities/outfits')->getCollection()->addFieldToFilter('celebrity_id',$id);
		//$collection = Mage::getModel('ecp_celebrities/outfits')->getCollection();
		/*@var $collection Ecp_Celebrities_Model_Mysql4_Celebrities_Collection*/
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('celebrity_outfit_id', array(
				'header'    => Mage::helper('ecp_celebrities')->__('Outfit ID'),
				'align'     =>'right',
				'width'     => '50px',
				'index'     => 'celebrity_outfit_id',
		));

                $this->addColumn('outfit_image', array(
                    'header'    => Mage::helper('ecp_celebrities')->__('Default image'),
                    'width'     => '150px',
                    'index'     => 'outfit_image',
                    'align'     => 'center',
                    'type'      => 'image',
                    'renderer'  => 'Ecp_Celebrities_Block_Adminhtml_Celebrities_Renderer_Image'
                ));

		$this->addColumn('related_products', array(
				'header'    => Mage::helper('ecp_celebrities')->__('Related products'),
				'align'     => 'left',
				'index'     => 'related_products',
                                'type'      => 'text',
                                'renderer'  => 'Ecp_Celebrities_Block_Adminhtml_Outfits_Renderer_Products'
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
										'url'       => array('base'=> '*/*/editOutfit'),
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
		$this->setMassactionIdField('celebrity_outfit_id');
		$this->getMassactionBlock()->setFormFieldName('celebrities');

		$this->getMassactionBlock()->addItem('delete', array(
				'label'    => Mage::helper('ecp_celebrities')->__('Delete'),
				'url'      => $this->getUrl('*/*/massDeleteOutfits'),
				'confirm'  => Mage::helper('ecp_celebrities')->__('Are you sure?')
		));

		$statuses = Mage::getSingleton('ecp_celebrities/status')->getOptionArray();

		array_unshift($statuses, array('label'=>'', 'value'=>''));
		$this->getMassactionBlock()->addItem('status', array(
				'label'=> Mage::helper('ecp_celebrities')->__('Change status'),
				'url'  => $this->getUrl('*/*/massStatusOutfits', array('_current'=>true)),
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
		return $this->getUrl('*/*/editOutfit', array('id' => $row->getId()));
	}

}