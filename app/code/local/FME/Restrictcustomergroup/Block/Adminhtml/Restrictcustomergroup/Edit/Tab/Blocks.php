<?php
class FME_Restrictcustomergroup_Block_Adminhtml_Restrictcustomergroup_Edit_Tab_Blocks
    extends Mage_Adminhtml_Block_Widget_Grid {
        
    public function __construct() {
		
		parent::__construct();
		$this->setId('blocksGrid');
		$this->setUseAjax(true);
		$this->setDefaultSort('block_id');
		//$this->setSaveParametersInSession(true);
		$this->setDefaultFilter(array('in_item'=>1));
	}
    
    /**
     * Checks when this block is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return 0;
    }
    
	/**
     * Add filter
     *
     * @param object $column
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Related
     */
    protected function _addColumnFilterToCollection($column) {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_item') {
			
		    $blockIds = $this->_getSelectedBlocks();
            if (empty($blockIds)) {
                $blockIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('block_id', array('in'=>$blockIds));
            } else {
                if($blockIds) {
                    $this->getCollection()->addFieldToFilter('block_id', array('nin'=>$blockIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
		
        return $this;
    }
	
    protected function _prepareCollection() {
		
      $collection = Mage::getModel('cms/block')->getCollection();
      if ($this->isReadonly()) {
        
            $ids = $this->_getSelectedBlocks();
            if (empty($ids)) {
                
                $ids = array(0);
            }
            $collection->addFieldToFilter('block_id', array('in'=>$ids));
      }
      /* @var $collection Mage_Cms_Model_Mysql4_Block_Collection */
      $this->setCollection($collection);
      return parent::_prepareCollection();
   }
   
   protected function _prepareColumns() {
	   
        $baseUrl = $this->getUrl();
        if (!$this->isReadonly()) {
            
            $this->addColumn('in_item', array(
                'header_css_class'  => 'a-center',
                'type'              => 'checkbox',
                'field_name'        => 'in_item',
                'values' => $this->_getSelectedBlocks(),
                'align'             => 'center',
                'index'             => 'block_id'
            ));
        }
		
		$this->addColumn('block_id', array(
            'header'    => Mage::helper('cms')->__('ID'),
            'sortable'  => true,
            'width'     => 60,
            'index'     => 'block_id'
        ));
        
        $this->addColumn('title_block', array(
            'header'    => Mage::helper('restrictcustomergroup')->__('Title'),
            'align'     => 'left',
            'index'     => 'title',
        ));
        
        $this->addColumn('identifier', array(
            'header'    => Mage::helper('restrictcustomergroup')->__('Identifier'),
            'align'     => 'left',
            'index'     => 'identifier'
        ));
        
        return parent::_prepareColumns();
      
   }
          
   /**
   * Retrieve selected related products
   *
   * @return array
   */
   public function _getSelectedBlocks()
   {
        $blocks = $this->getBlocksRelated();	
        if (!is_array($blocks)) {
          $blocks = array_keys($this->getStaticBlocks());
        }
        
        return $blocks;
   }
   
   /**
     * Retrieve related products
     *
     * @return array
     */
    public function getStaticBlocks()
    {
		$id = $this->getRequest()->getParam('id');
       	$blocksArr = array();
        
        foreach (Mage::registry('current_static_blocks')->getBlocksRelated($id) as $block) {
           $blocksArr[$block["block_id"]] = array('position' => '0');
        }
        
        return $blocksArr;
    }
    
   public function getGridUrl() { 
	   
	   return $this->_getData('grid_url') ? $this->_getData('grid_url') : $this->getUrl('*/*/grid', array('_current'=>true)); 
   }
   
}
    