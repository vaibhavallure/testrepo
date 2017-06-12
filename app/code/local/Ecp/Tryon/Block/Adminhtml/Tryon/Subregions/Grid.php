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
 * @package     Ecp_Tryon
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Tryon
 *
 * @category    Ecp
 * @package     Ecp_Tryon
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Tryon_Block_Adminhtml_Tryon_Subregions_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('tryonGrid');
        $this->setDefaultSort('tryon_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $regions = Mage::getStoreConfig('ecp_tryon/regions');
        $collection = new Varien_Data_Collection();
        foreach ($regions as $region) {
            $tmp = array();
            if($region['code'] == $this->getRequest()->getParam('code')) foreach ($region['subregions'] as $subregion) {
                $obj = new Varien_Object();
                $obj->setName($subregion['name'])
                    ->setCode($subregion['code']);
                $collection->addItem($obj);
            }
        }
        /* @var $collection Ecp_Tryon_Model_Mysql4_Tryon_Collection */
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('code', array(
            'header' => Mage::helper('ecp_tryon')->__('Code'),
            'align' => 'right',
            'width' => '100px',
            'index' => 'code',
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('ecp_tryon')->__('Name'),
            'align' => 'left',
            'index' => 'name',
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('ecp_tryon')->__('Action'),
            'width' => '150px',
            'type' => 'action',
            'getter' => 'getCode',
            'actions' => array(
                /*array(
                    'caption' => Mage::helper('ecp_tryon')->__('Subregions'),
                    'url' => array('base' => ' * /adminhtml_tryon_subregions/index'),
                    'field' => 'code'
                ),*/
                array(
                    'caption' => Mage::helper('ecp_tryon')->__('Products per subregion'),
                    'url' => array('base' => '*/*/productspersubregion'),
                    'field' => 'code'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        //$this->addExportType('*/*/exportCsv', Mage::helper('ecp_tryon')->__('CSV'));
        //$this->addExportType('*/*/exportXml', Mage::helper('ecp_tryon')->__('XML'));

        return parent::_prepareColumns();
    }
}