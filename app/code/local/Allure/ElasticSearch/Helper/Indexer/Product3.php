<?php

/**
 * The technical support is guaranteed for all modules proposed by Allure.
 * The below code is obfuscated in order to protect the module's copyright as well as the integrity of the license and of the source code.
 * The support cannot apply if modifications have been made to the original source code (https://www.allure.com/terms-and-conditions.html).
 * Nonetheless, Allure remains available to answer any question you might have and find the solutions adapted to your needs.
 * Feel free to contact our technical team from your Allure account in My account > My tickets.
 * Copyright © 2019 Allure. All rights reserved.
 * See LICENSE.txt for license details.
 */
  class Allure_ElasticSearch_Helper_Indexer_Product extends Allure_ElasticSearch_Helper_Indexer_Abstract {
      public $x95=null;
      public $x8d=null;
      public $xc7=null;
      protected $_searchableAttributes;
      protected $_blockClass = 'Allure_ElasticSearch_Block_Autocomplete_Product';
      private $x1349 = null;
      public $error = "Elasticsearch Product Index : Invalid License!";

      public function __construct() {
          $x4dad = "helper";
          $x5311 = "getModel";
          $x57ab = "app";
          $x551a = "getSingleton";
          $x574f = "getStoreConfig";
          $x576c = "dispatchEvent";
          $x55c9 = "getResourceModel";
          $x57ca = "getStoreConfigFlag";
          $this->_construct();
      }
      public function _construct() {

          $x4dad = "helper";
          $x5311 = "getModel";
          $x57ab = "app";
          $x551a = "getSingleton";
          $x574f = "getStoreConfig";
          $x576c = "dispatchEvent";
          $x55c9 = "getResourceModel";
          $x57ca = "getStoreConfigFlag";
          
          $this->x1349 = Mage::helper("licensemanager/data");
          $this->x1349->constructor($this, func_get_args());
      }

      public function export($xb8f = array(), $xc06 = 2000) {
          $xac4 = $this->x95->x12fe->{$this->xc7->x12fe->{$this->x8d->x12fe->x28fa}};
          $xaae = $this->x95->x12fe->x2906;
          $xd28 = $this->x95->x1327->{$this->x95->x1327->{$this->x8d->x1327->{$this->x95->x1327->x3a36}}};
          $xb45 = $this->x95->x1327->x3a40;
          $x1135 = $this->x95->x1338->x4bb1;
          $xad6 = $this->x8d->x12fe->{$this->xc7->x12fe->{$this->xc7->x12fe->{$this->xc7->x12fe->{$this->x8d->x12fe->x2955}}}};
          $x10a1 = $this->x95->x12cd->x1869;
          $xb89 = $this->x95->x12cd->{$this->x8d->x12cd->x1879};
          $xeb7 = $this->x95->x12cd->x187e;
          $xbfe = $this->x8d->x12fe->{$this->x95->x12fe->{$this->x95->x12fe->{$this->xc7->x12fe->x2985}}};
          $xc2b = $this->x8d->x12fe->{$this->x95->x12fe->{$this->x8d->x12fe->{$this->xc7->x12fe->{$this->xc7->x12fe->x2998}}}};
          $xce7 = $this->xc7->x1338->{$this->x95->x1338->x4c11};
          $xe6e = $this->xc7->x1327->x3ab6;
          $xeb5 = $this->x8d->x12cd->x18ca;
          $xf16 = $this->x8d->x12fe->{$this->xc7->x12fe->x29d1};
          $x10b5 = $this->xc7->x1327->{$this->x8d->x1327->{$this->x8d->x1327->x3aeb}};
          $x4dad = "helper";
          $x5311 = "getModel";
          $x57ab = "app";
          $x551a = "getSingleton";
          $x574f = "getStoreConfig";
          $x576c = "dispatchEvent";
          $x55c9 = "getResourceModel";
          $x57ca = "getStoreConfigFlag";

          try {
              ${$this->x8d->x1327->{$this->xc7->x1327->{$this->x8d->x1327->{$this->x8d->x1327->x35c1}}}} = $this;
              ${$this->x95->x12cd->{$this->xc7->x12cd->x13c5}} = "Mage";
              ${$this->x95->x1327->{$this->x95->x1327->{$this->x95->x1327->x35d4}}} = "helper";
              ${$this->x8d->x1327->{$this->x95->x1327->x35e3}} = "throwException";
              ${$this->x8d->x12cd->{$this->x95->x12cd->x13e6}} = $xac4($xaae());
              ${$this->xc7->x12cd->x13b4}->{$this->x8d->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->x134e}}}->{$this->xc7->x12cd->x195d}(${$this->x8d->x1338->x4735}, ${$this->x8d->x12cd->{$this->x95->x12cd->x13e6}});
              if (${$this->x8d->x1338->x4735}->{$this->xc7->x12cd->x197b}(${$this->x8d->x12fe->x2507}) != $xac4(${$this->xc7->x1327->x35e6})) {
                  ${$this->x95->x1327->{$this->x95->x1327->{$this->xc7->x1327->{$this->x8d->x1327->{$this->xc7->x1327->x35cf}}}}}::${$this->xc7->x12cd->x13d4}(${$this->x95->x1338->{$this->xc7->x1338->{$this->x8d->x1338->x4741}}}::${$this->x95->x1327->{$this->x8d->x1327->x35d2}}("elasticsearch")->{$this->x95->x12cd->x1984}(${$this->xc7->x12cd->x13b4}->{$this->x8d->x12cd->{$this->x95->x12cd->{$this->x8d->x12cd->x1358}}}));
             }
             $xad6(0);
                     ${$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->{$this->xc7->x12cd->x13f8}}}} = array();
                     ${$this->x95->x12fe->{$this->x8d->x12fe->x251e}} = Mage::getModel('catalog/product');

                     ${$this->xc7->x1327->{$this->x95->x1327->{$this->x95->x1327->{$this->x8d->x1327->x3614}}}} = ${$this->xc7->x1327->x35f8}->{$this->x95->x12cd->x199d}()->{$this->x8d->x12cd->x19ab}(${$this->x95->x12fe->{$this->xc7->x12fe->{$this->x8d->x12fe->x2522}}})->{$this->xc7->x12cd->x19c3}();
                     ${$this->x8d->x1327->{$this->x95->x1327->x361d}} = ${$this->xc7->x1327->{$this->xc7->x1327->{$this->x8d->x1327->{$this->x95->x1327->{$this->x8d->x1327->x3607}}}}}->{$this->x95->x12cd->x199d}()->{$this->x95->x12cd->x19d7}('catalog_product_entity');
                     ${$this->x8d->x12fe->{$this->xc7->x12fe->x2545}} = $this->{$this->xc7->x12cd->x19e4}();
                     ${$this->x8d->x1338->{$this->xc7->x1338->{$this->xc7->x1338->x4795}}} = $this->{$this->x95->x12cd->x19f0}();
                     ${$this->xc7->x1327->{$this->xc7->x1327->{$this->x8d->x1327->{$this->x8d->x1327->x3602}}}} = new Varien_Object();
                     ${$this->xc7->x1327->x3639} = Mage::helper('core')->{$this->x95->x12cd->x1a07}('Enterprise_UrlRewrite');
                     foreach (Mage::app()->{$this->x95->x12cd->x1a23}() as ${$this->xc7->x12fe->{$this->x8d->x12fe->x255e}}) {  if (!${$this->xc7->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x143e}}}}->{$this->x95->x12cd->x1a30}()) { continue;
                     } ${$this->x8d->x1327->{$this->x8d->x1327->x3650}} = (int)${$this->xc7->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x143e}}}}->{$this->xc7->x12cd->x1a42}();
                     if (isset(${$this->x95->x1338->{$this->xc7->x1338->{$this->x95->x1338->x4727}}}['store_id'])) { if (!$xd28(${$this->x95->x1327->x35a0}['store_id'])) { ${$this->xc7->x12cd->{$this->x95->x12cd->{$this->xc7->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x13a9}}}}}['store_id'] = array(${$this->xc7->x12cd->{$this->x95->x12cd->{$this->xc7->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x13a9}}}}}['store_id']);
                     } if (!$xb45(${$this->x95->x12fe->x256b}, ${$this->x95->x1338->{$this->xc7->x1338->{$this->x95->x1338->x4727}}}['store_id'])) { continue;
                     } } ${$this->x95->x12cd->x1447} = $this->{$this->xc7->x12fe->{$this->x95->x12fe->x28c7}}(${$this->xc7->x12fe->{$this->x8d->x12fe->{$this->xc7->x12fe->x2563}}});
                     ${$this->xc7->x1327->{$this->x95->x1327->{$this->x95->x1327->x3662}}} = Mage::helper('tax');
                     ${$this->x95->x1327->{$this->xc7->x1327->x3669}} = ${$this->xc7->x12cd->{$this->xc7->x12cd->x1454}}->{$this->x8d->x12cd->x1a6d}(${$this->xc7->x12fe->{$this->x8d->x12fe->{$this->x8d->x12fe->{$this->xc7->x12fe->x2566}}}});
                     ${$this->x8d->x1327->{$this->x8d->x1327->{$this->xc7->x1327->{$this->xc7->x1327->{$this->xc7->x1327->x367c}}}}} = ${$this->xc7->x12cd->{$this->xc7->x12cd->x1454}}->{$this->x95->x12cd->x1a84}(${$this->xc7->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->x143c}}});
                     ${$this->x8d->x12cd->x1476} = $xb45(${$this->x95->x12fe->x2585}, array( Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX, Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH, ));
                     ${$this->xc7->x1338->{$this->xc7->x1338->{$this->x8d->x1338->{$this->xc7->x1338->x47ee}}}} = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
                     ${$this->xc7->x12fe->{$this->x8d->x12fe->{$this->x95->x12fe->{$this->x95->x12fe->x25a9}}}} = Mage::getModel('customer/group')->{$this->x8d->x12cd->x1a9f}(${$this->x95->x1338->x47e6});
                     $this->{$this->x95->x12cd->x1ab1}(' > Exporting products of store %s', ${$this->x8d->x1327->x3643}->{$this->x95->x12cd->x1ac9}());
                     ${$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->{$this->xc7->x12cd->x13f8}}}}[${$this->xc7->x12cd->x143f}] = array();
                     ${$this->x95->x1327->{$this->x95->x1327->x36a1}} = ${$this->x8d->x12cd->x1420}->{$this->x95->x12cd->x1ad4}()->{$this->xc7->x12cd->x1ade}(array('e' => ${$this->x95->x12fe->{$this->x8d->x12fe->{$this->x95->x12fe->{$this->x95->x12fe->{$this->x8d->x12fe->x253f}}}}}), 'entity_id');
                      ${$this->x8d->x12cd->x1494}->join( array('product_website' => ${$this->x8d->x12fe->{$this->x95->x12fe->{$this->x8d->x12fe->x2547}}}->{$this->xc7->x12cd->x1ae9}('catalog/product_website')), 'product_website.product_id = e.entity_id AND ' . ${$this->xc7->x12fe->x254b}->{$this->x95->x12cd->x1af7}('product_website.website_id = ?', ${$this->xc7->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x143e}}}}->{$this->xc7->x12cd->x1b0b}()), array() );
                      if (!$this->{$this->xc7->x12fe->x28da}(${$this->x95->x1327->{$this->x95->x1327->x3648}})) { ${$this->xc7->x12fe->{$this->xc7->x12fe->{$this->x95->x12fe->{$this->x8d->x12fe->{$this->x95->x12fe->x25bd}}}}} = array("stock_status.product_id = e.entity_id", ${$this->x8d->x1338->{$this->xc7->x1338->{$this->xc7->x1338->{$this->xc7->x1338->x4797}}}}->{$this->x95->x12cd->x1af7}("stock_status.website_id = ?", ${$this->xc7->x12fe->{$this->x8d->x12fe->{$this->xc7->x12fe->x2563}}}->{$this->xc7->x12cd->x1b0b}()), "stock_status.stock_status=1" );
                     ${$this->xc7->x1327->x36a0}->join(array("stock_status" => ${$this->x8d->x12cd->{$this->xc7->x12cd->x141e}}->{$this->xc7->x12cd->x1ae9}("cataloginventory_stock_status")), $xb89(" AND ", ${$this->x95->x1327->{$this->xc7->x1327->{$this->x8d->x1327->{$this->x95->x1327->x36af}}}}), null);
                     } if (!empty(${$this->x95->x1338->{$this->xc7->x1338->{$this->x95->x1338->x4727}}})) { foreach (${$this->x8d->x1327->{$this->xc7->x1327->x35a3}} as ${$this->x95->x1338->{$this->x95->x1338->{$this->xc7->x1338->{$this->x8d->x1338->x481e}}}} => ${$this->x8d->x12fe->{$this->x95->x12fe->x25c8}}) { if (${$this->x95->x1338->{$this->x95->x1338->{$this->xc7->x1338->x4819}}} == 'store_id' || ${$this->x8d->x12fe->x25c4} === null) { continue;
                     } if ($xd28(${$this->xc7->x1327->x36bc})) { ${$this->x8d->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->x1499}}}->{$this->x95->x12cd->x1b60}("e.${$this->x95->x1338->{$this->x95->x1338->{$this->xc7->x1338->x4819}}} IN (?)", ${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x95->x12cd->x14bb}}});
                     } else { ${$this->x8d->x12cd->{$this->x8d->x12cd->{$this->x95->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->x149f}}}}}->{$this->x95->x12cd->x1b60}("e.${$this->x95->x12fe->{$this->xc7->x12fe->x25c1}} = ?", ${$this->x8d->x1327->{$this->x8d->x1327->{$this->x8d->x1327->x36c0}}});
                     } } }  ${$this->xc7->x1338->{$this->x8d->x1338->{$this->x8d->x1338->x482e}}} = Mage::getSingleton('eav/entity_attribute')->{$this->x95->x12cd->x1b81}(Mage_Catalog_Model_Product::ENTITY, 'status');
                     if (${$this->x8d->x1327->{$this->xc7->x1327->{$this->x95->x1327->{$this->xc7->x1327->{$this->x95->x1327->x36d4}}}}}) { ${$this->xc7->x12cd->x14c7} = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
                     ${$this->xc7->x1327->x36a0}->join( array('status' => ${$this->x8d->x1327->{$this->x8d->x1327->{$this->x8d->x1327->x3625}}}->{$this->xc7->x12cd->x1ae9}('catalog_product_entity_int')), "status.attribute_id = ${$this->x8d->x12fe->x25cc} AND status.entity_id = e.entity_id", array() );
                     ${$this->x95->x12fe->{$this->x95->x12fe->x25b0}}->{$this->x95->x12cd->x1b60}('status.value = ?', ${$this->x8d->x1327->{$this->x8d->x1327->x36da}});
                     ${$this->x95->x12fe->{$this->x95->x12fe->x25b0}}->{$this->x95->x12cd->x1b60}('status.store_id IN (?)', array(0, ${$this->x95->x1338->{$this->xc7->x1338->x47b2}}));
                     }  ${$this->xc7->x12fe->{$this->x95->x12fe->x25e9}} = ${$this->xc7->x1327->{$this->x95->x1327->{$this->xc7->x1327->{$this->x8d->x1327->{$this->xc7->x1327->x3636}}}}}->{$this->x95->x12cd->x1bc0}(${$this->x8d->x12fe->x25ab});
                     ${$this->x95->x12cd->x14d6} = $xeb7(${$this->x8d->x1338->{$this->x95->x1338->{$this->x95->x1338->{$this->x95->x1338->x4846}}}});
                     $this->{$this->x95->x12cd->x1ab1}(' > Found %d products', $xbfe(${$this->x8d->x1338->{$this->x95->x1338->{$this->x95->x1338->{$this->x95->x1338->x4846}}}}));
                     ${$this->x8d->x1338->{$this->x95->x1338->{$this->x8d->x1338->x4844}}} = $xc2b(${$this->x95->x1338->x483b}, ${$this->x8d->x1338->x472b});
                     ${$this->x95->x12fe->{$this->x8d->x12fe->x25f0}} = $xbfe(${$this->x8d->x1327->{$this->x8d->x1327->x36e0}});
                     if (${$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->{$this->x95->x12cd->{$this->x8d->x12cd->x14ea}}}}} > 1) { $this->{$this->x95->x12cd->x1ab1}(' > Split products array into %d chunks for better performances', ${$this->x95->x12cd->{$this->x95->x12cd->{$this->x95->x12cd->x13b2}}});
                     } ${$this->x8d->x1338->{$this->xc7->x1338->{$this->x8d->x1338->x485e}}} = array();
                      foreach (${$this->x8d->x1338->{$this->x95->x1338->{$this->x8d->x1338->x4844}}} as ${$this->xc7->x12fe->{$this->xc7->x12fe->x2606}} => ${$this->xc7->x1327->{$this->x95->x1327->x370e}}) { if (${$this->x8d->x12cd->x14de} > 1) { $this->{$this->x95->x12cd->x1ab1}(' > %d/%d', ${$this->x95->x1327->{$this->xc7->x1327->{$this->x95->x1327->{$this->x95->x1327->x3702}}}} + 1, ${$this->x95->x1338->{$this->x95->x1338->{$this->x95->x1338->{$this->xc7->x1338->x4854}}}});
                     } ${$this->x8d->x1327->{$this->xc7->x1327->x3713}} = array();
                     foreach (${$this->xc7->x1327->{$this->x95->x1327->x360d}} as ${$this->x95->x1327->{$this->x8d->x1327->{$this->x95->x1327->x371b}}} => ${$this->xc7->x1327->{$this->xc7->x1327->{$this->x95->x1327->{$this->x95->x1327->{$this->x95->x1327->x372f}}}}}) { ${$this->x8d->x12cd->x1521} = $xc2b(${$this->x8d->x12fe->{$this->x8d->x12fe->x2624}}, 25);
                     foreach (${$this->x95->x1338->{$this->xc7->x1338->x4898}} as ${$this->xc7->x12fe->{$this->xc7->x12fe->{$this->xc7->x12fe->x262d}}}) { ${$this->x8d->x1338->{$this->x8d->x1338->{$this->x95->x1338->x4806}}} = ${$this->xc7->x1327->x362a}->{$this->x95->x12cd->x1ad4}()->{$this->xc7->x12cd->x1ade}(array('e' => ${$this->x95->x12fe->{$this->x95->x12fe->x2535}}), array('id' => 'entity_id', 'sku', 'type_id'));
                     foreach (${$this->x95->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x152b}}} as ${$this->x8d->x12cd->x152f}) { if (!$this->{$this->xc7->x12cd->x1c05}(${$this->xc7->x1327->{$this->x8d->x1327->x3738}})) { continue;
                     } ${$this->x8d->x12fe->{$this->xc7->x12fe->x25cf}} = ${$this->xc7->x1338->{$this->x95->x1338->x48a5}}->{$this->xc7->x12cd->x1c12}();
                     ${$this->x95->x1327->x373c} = ${$this->x8d->x12cd->x152f}->{$this->xc7->x12cd->x1c22}();
                     if (!isset(${$this->x95->x1327->{$this->xc7->x1327->x36f4}}[${$this->x8d->x12fe->{$this->xc7->x12fe->x2640}}]) && $this->{$this->xc7->x12cd->x1c2a}(${$this->x95->x12fe->x2637})) { ${$this->x8d->x12cd->x1544} = ${$this->xc7->x1327->{$this->x8d->x1327->x3738}}->{$this->xc7->x12cd->x1c3c}(${$this->x8d->x1327->{$this->x8d->x1327->x3650}})->{$this->x8d->x12cd->x1c45}()->{$this->x95->x12cd->x1c51}();
                     foreach (${$this->xc7->x1338->{$this->x95->x1338->{$this->x8d->x1338->{$this->x8d->x1338->x48b5}}}} as ${$this->xc7->x1327->x3753}) { if (!${$this->xc7->x12cd->x154b}['value']) { continue;
                     } ${$this->x95->x1327->{$this->xc7->x1327->x36f4}}[${$this->x95->x1327->x373c}][${$this->x8d->x1327->{$this->x95->x1327->{$this->x95->x1327->{$this->x8d->x1327->x375d}}}}['value']] = ${$this->xc7->x1327->x3753}['label'];
                     } } ${$this->x95->x12fe->{$this->x95->x12fe->x2655}} = ${$this->x95->x12fe->x263d} . '_default';
                     ${$this->x8d->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->x1499}}}->{$this->x8d->x12cd->x1c58}( array(${$this->xc7->x12cd->x1558} => ${$this->xc7->x1327->x362a}->{$this->xc7->x12cd->x1ae9}(${$this->xc7->x12fe->{$this->x8d->x12fe->{$this->xc7->x12fe->{$this->x95->x12fe->x261c}}}})), "${$this->xc7->x12cd->x1558}.attribute_id = ${$this->xc7->x1338->{$this->x8d->x1338->{$this->x8d->x1338->x482e}}} AND ${$this->x95->x12fe->{$this->xc7->x12fe->{$this->xc7->x12fe->x2656}}}.entity_id = e.entity_id AND ${$this->x95->x12fe->{$this->xc7->x12fe->{$this->xc7->x12fe->x2656}}}.store_id = 0", array() );
                     ${$this->x8d->x12fe->{$this->xc7->x12fe->x265f}} = ${$this->x95->x1327->x373c} . '_store';
                     ${$this->x8d->x12fe->{$this->xc7->x12fe->x266a}} = ${$this->x8d->x12cd->x1420}->{$this->x8d->x12cd->x1c72}("${$this->x8d->x1338->{$this->x95->x1338->{$this->x95->x1338->x48d1}}}.value IS NULL", "${$this->x95->x12fe->{$this->x95->x12fe->x2655}}.value", "${$this->x8d->x1327->{$this->x95->x1327->{$this->xc7->x1327->x3769}}}.value");
                     ${$this->x8d->x1338->x47fe}->{$this->x8d->x12cd->x1c58}( array(${$this->x8d->x1338->{$this->x95->x1338->{$this->x95->x1338->x48d1}}} => ${$this->x95->x12cd->{$this->x8d->x12cd->x1423}}->{$this->xc7->x12cd->x1ae9}(${$this->x95->x12cd->{$this->xc7->x12cd->{$this->x8d->x12cd->{$this->x95->x12cd->{$this->x95->x12cd->x151c}}}}})), "${$this->x8d->x1338->{$this->x95->x1338->{$this->xc7->x1338->{$this->x95->x1338->x48d6}}}}.attribute_id = ${$this->xc7->x1338->{$this->x95->x1338->x482d}} AND ${$this->x8d->x1338->{$this->x95->x1338->{$this->xc7->x1338->{$this->x95->x1338->x48d6}}}}.entity_id = e.entity_id AND ${$this->x8d->x1338->{$this->x95->x1338->{$this->x95->x1338->x48d1}}}.store_id = {${$this->x8d->x12fe->x2559}->{$this->xc7->x12cd->x1a42}()}", array(${$this->x8d->x1338->{$this->xc7->x1338->x48ab}} => ${$this->x8d->x12fe->{$this->xc7->x12fe->x266a}}) );
                     } ${$this->x8d->x1338->{$this->x8d->x1338->{$this->x95->x1338->x4806}}}->{$this->x95->x12cd->x1b60}('e.entity_id IN (?)', ${$this->xc7->x1327->{$this->x95->x1327->x370e}});
                     ${$this->x95->x1338->{$this->x95->x1338->x48e9}} = ${$this->xc7->x12fe->x254b}->{$this->xc7->x12cd->x1cb8}(${$this->x8d->x12cd->x1494});
                     ${$this->x95->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->x1589}}} = Mage::getStoreConfig('elasticsearch/product/image_size');
                      while (${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x159c}}}}} = ${$this->x8d->x12fe->{$this->xc7->x12fe->{$this->x95->x12fe->x2675}}}->{$this->x8d->x12cd->x1cd3}()) { ${$this->x8d->x1327->x3798} = $xce7(${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x159c}}}}}, 'strlen');
                     ${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x159c}}}}}['id'] = (int)${$this->x95->x1338->{$this->x95->x1338->x48fd}}['id'];
                     ${$this->x95->x1327->{$this->x95->x1327->{$this->x8d->x1327->{$this->x95->x1327->x37b0}}}} = ${$this->xc7->x1327->{$this->x8d->x1327->x379a}}['id'];
                     if (!isset(${$this->xc7->x12fe->{$this->x95->x12fe->x2614}}[${$this->x8d->x12cd->{$this->x95->x12cd->x159f}}])) { ${$this->x8d->x1327->{$this->xc7->x1327->x3713}}[${$this->x8d->x12fe->{$this->x8d->x12fe->x268e}}] = array();
                     } foreach (${$this->x8d->x12fe->x2682} as ${$this->x95->x12fe->x2695} => &${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x95->x12cd->x14bb}}}) { if (isset(${$this->x95->x12cd->{$this->x95->x12cd->{$this->x95->x12cd->{$this->xc7->x12cd->x1409}}}}[${$this->xc7->x12fe->{$this->x8d->x12fe->{$this->xc7->x12fe->{$this->x95->x12fe->x261c}}}}][${$this->x95->x12fe->{$this->x8d->x12fe->x2698}}])) { ${$this->x95->x12cd->{$this->x8d->x12cd->x14b6}} = $this->{$this->x8d->x12cd->x1cde}(${$this->x95->x12fe->x2527}[${$this->xc7->x12fe->x2615}][${$this->x8d->x1338->{$this->x8d->x1338->{$this->x95->x1338->{$this->x8d->x1338->x4910}}}}], ${$this->x8d->x1327->{$this->x95->x1327->x36bd}}, ${$this->xc7->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->x143c}}});
                     } if (isset(${$this->x95->x12fe->{$this->x8d->x12fe->x25fb}}[${$this->xc7->x1327->{$this->xc7->x1327->{$this->x95->x1327->x37be}}}])) { if ($xd28(${$this->xc7->x1338->{$this->xc7->x1338->x4829}})) { ${$this->x8d->x12fe->x269a} = array();
                     foreach (${$this->xc7->x1338->x4825} as ${$this->xc7->x1338->{$this->x8d->x1338->{$this->x95->x1338->{$this->xc7->x1338->x4923}}}}) { if (isset(${$this->x95->x12fe->{$this->xc7->x12fe->{$this->xc7->x12fe->x25fd}}}[${$this->x95->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->{$this->xc7->x12cd->{$this->xc7->x12cd->x15ac}}}}}][${$this->xc7->x1327->x37d1}])) { ${$this->x8d->x1338->{$this->xc7->x1338->{$this->xc7->x1338->x4918}}}[] = ${$this->x95->x1327->{$this->xc7->x1327->{$this->x8d->x1327->x36f7}}}[${$this->x8d->x1338->{$this->x95->x1338->x490a}}][${$this->xc7->x1338->{$this->x8d->x1338->{$this->x95->x1338->{$this->xc7->x1338->x4923}}}}];
                     } } if (!empty(${$this->x8d->x12fe->x269a})) { ${$this->x8d->x12fe->{$this->x8d->x12fe->x2686}}[${$this->x95->x12fe->{$this->xc7->x12fe->{$this->x8d->x12fe->x2699}}}] = ${$this->x8d->x12fe->{$this->xc7->x12fe->{$this->x95->x12fe->x26a0}}};
                     } } elseif (isset(${$this->x95->x12fe->x25f9}[${$this->x8d->x1338->{$this->x95->x1338->x490a}}][${$this->xc7->x1338->x4825}])) { ${$this->x8d->x1327->x3798}[${$this->x8d->x12cd->x15a5}] = ${$this->x8d->x12cd->{$this->xc7->x12cd->x14ec}}[${$this->x8d->x12cd->x15a5}][${$this->x8d->x12fe->{$this->x95->x12fe->x25c8}}];
                     } } } unset(${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x95->x12cd->x14bb}}});
                     ${$this->x95->x12cd->{$this->x95->x12cd->{$this->x8d->x12cd->x150e}}}[${$this->x95->x1327->{$this->x95->x1327->{$this->x8d->x1327->x37ab}}}] = $x1135(${$this->x95->x12cd->{$this->xc7->x12cd->x150b}}[${$this->x95->x1338->{$this->x8d->x1338->x4905}}], ${$this->x95->x1338->{$this->x95->x1338->x48fd}});
                     if (!isset(${$this->xc7->x12fe->{$this->x95->x12fe->x2614}}[${$this->x95->x1327->{$this->x8d->x1327->x37a6}}]['image']) || ${$this->x8d->x1338->x487a}[${$this->x95->x1327->{$this->x95->x1327->{$this->x8d->x1327->{$this->x8d->x1327->{$this->xc7->x1327->x37b3}}}}}]['image'] == "") {  } } } }  if (Mage::getStoreConfig('elasticsearch/product/custom_options') === "1") { ${$this->xc7->x12fe->{$this->xc7->x12fe->x26b3}} = "custom_options";
                     ${$this->xc7->x12cd->{$this->x95->x12cd->x15e0}} = ${$this->x95->x1327->x3621}->{$this->xc7->x12cd->x1ae9}('catalog_product_option');
                     ${$this->xc7->x12fe->{$this->x95->x12fe->{$this->x8d->x12fe->{$this->xc7->x12fe->x26d0}}}} = ${$this->xc7->x12fe->x2544}->{$this->xc7->x12cd->x1ae9}('catalog_product_option_title');
                     ${$this->x95->x1327->{$this->x8d->x1327->x37f3}} = ${$this->x95->x1338->{$this->xc7->x1338->{$this->x8d->x1338->x4788}}}->{$this->xc7->x12cd->x1ae9}('catalog_product_option_type_value');
                     ${$this->x8d->x12fe->{$this->x8d->x12fe->x26d8}} = ${$this->x95->x1338->{$this->x95->x1338->x4784}}->{$this->xc7->x12cd->x1ae9}('catalog_product_option_type_title');
                     ${$this->x95->x1327->x37ff} = ${$this->x8d->x1327->{$this->xc7->x1327->x3623}}->{$this->xc7->x12cd->x1ae9}('catalog_product_option_type_price');
                     ${$this->x8d->x1338->x47fe} = ${$this->x95->x1327->{$this->x95->x1327->x36a1}} = ${$this->x95->x12cd->{$this->x8d->x12cd->x1423}}->{$this->x95->x12cd->x1ad4}();
                     ${$this->x95->x12fe->{$this->x95->x12fe->x25b0}}->{$this->xc7->x12cd->x1ade}(array('cpo' => ${$this->x8d->x12fe->{$this->x8d->x12fe->{$this->x95->x12fe->x26bf}}}), array('product_id'))->{$this->x8d->x12cd->x1d4d}(array("group_concat(`cpott`.`title`) as values"))->{$this->x8d->x12cd->x1d52}( array('cpot' => ${$this->xc7->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->x15eb}}}}}), 'cpot.option_id=cpo.option_id AND cpot.store_id=0', array('option' => 'title', 'option_id', 'store_id') )->{$this->x8d->x12cd->x1d52}( array('cpotv' => ${$this->x95->x1327->{$this->x8d->x1327->x37f3}}), 'cpotv.option_id = cpo.option_id', 'sku' )->{$this->x8d->x12cd->x1d52}( array('cpott' => ${$this->xc7->x1327->{$this->x95->x1327->x37fe}}), 'cpott.option_type_id=cpotv.option_type_id AND cpott.store_id=cpot.store_id', 'title AS value' )->{$this->x8d->x12cd->x1d52}( array('cpotp' => ${$this->x95->x1327->x37ff}), 'cpotp.option_type_id=cpotv.option_type_id AND cpotp.store_id=cpot.store_id', array('price', 'price_type') )->{$this->xc7->x12cd->x1d87}(array('product_id', 'cpotv.sort_order ASC'))->{$this->x95->x12cd->x1b60}('product_id IN (?)', ${$this->xc7->x1327->{$this->x95->x1327->{$this->xc7->x1327->x370f}}})->{$this->x95->x12cd->x1da6}(array("product_id", "cpot.title"));
                     ${$this->x8d->x1327->{$this->x8d->x1327->x377b}} = ${$this->x95->x1338->x478d}->{$this->xc7->x12cd->x1cb8}(${$this->x95->x1327->{$this->x8d->x1327->{$this->xc7->x1327->{$this->xc7->x1327->x36a4}}}});
                     while (${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->x1598}}} = ${$this->x8d->x1327->{$this->x8d->x1327->{$this->x95->x1327->x377f}}}->{$this->x8d->x12cd->x1cd3}()) { if (${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->x1599}}}}['values'] != "") { ${$this->x8d->x12fe->{$this->xc7->x12fe->{$this->x95->x12fe->{$this->x95->x12fe->x2693}}}} = ${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->x1598}}}['product_id'];
                     ${$this->x8d->x1338->x487a}[${$this->x8d->x12cd->{$this->x95->x12cd->x159f}}][${$this->x8d->x1338->{$this->x95->x1338->x492a}}][] = $xe6e(',', ${$this->xc7->x1327->{$this->x8d->x1327->x379a}}['values']);
                     } } }  ${$this->x8d->x12cd->{$this->x95->x12cd->x15cc}} = '_parent_ids';
                     ${$this->x8d->x12fe->x25ab} = ${$this->x8d->x1338->{$this->xc7->x1338->{$this->xc7->x1338->x4795}}}->{$this->x95->x12cd->x1ad4}()->{$this->xc7->x12cd->x1ade}(${$this->x95->x1338->{$this->x95->x1338->x4784}}->{$this->xc7->x12cd->x1ae9}('catalog_product_relation'), array('parent_id', 'child_id'))->{$this->x95->x12cd->x1b60}('child_id IN (?)', ${$this->x8d->x1338->{$this->x95->x1338->{$this->x8d->x1338->x4878}}});
                     ${$this->x8d->x1327->{$this->x8d->x1327->x377b}} = ${$this->x8d->x1338->{$this->xc7->x1338->{$this->xc7->x1338->{$this->xc7->x1338->x4797}}}}->{$this->xc7->x12cd->x1cb8}(${$this->x95->x1327->{$this->x8d->x1327->{$this->xc7->x1327->{$this->xc7->x1327->x36a4}}}});
                     while (${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->x1598}}} = ${$this->x95->x12fe->x266e}->{$this->x8d->x12cd->x1cd3}()) { ${$this->x8d->x12fe->x268b} = ${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x159c}}}}}['child_id'];
                     if (!isset(${$this->x95->x1327->x3711}[${$this->x8d->x12cd->{$this->x95->x12cd->x159f}}][${$this->xc7->x12cd->x15c8}])) { ${$this->xc7->x12fe->{$this->x95->x12fe->x2614}}[${$this->x95->x1327->{$this->x95->x1327->{$this->x8d->x1327->x37ab}}}][${$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x8d->x12cd->x15d1}}}] = array();
                     } ${$this->x8d->x1338->x487a}[${$this->x8d->x12fe->x268b}][${$this->xc7->x1338->x4925}][] = (int)${$this->x8d->x12fe->{$this->x8d->x12fe->x2686}}['parent_id'];
                     }  ${$this->x8d->x12fe->x26af} = '_categories';
                     ${$this->xc7->x1327->x3809} = array( 'product_id' => 'product_id', 'category_ids' => new Zend_Db_Expr( "TRIM(
                                                BOTH ',' FROM CONCAT(
                                                    TRIM(BOTH ',' FROM GROUP_CONCAT(IF(is_parent = 0, category_id, '') SEPARATOR ',')),
                                                    ',',
                                                    TRIM(BOTH ',' FROM GROUP_CONCAT(IF(is_parent = 1, category_id, '') SEPARATOR ','))
                                                )
                                            )"), );
                     ${$this->x95->x12fe->{$this->x95->x12fe->x25b0}} = ${$this->xc7->x1327->{$this->x95->x1327->{$this->xc7->x1327->{$this->x8d->x1327->{$this->xc7->x1327->x3636}}}}}->{$this->x95->x12cd->x1ad4}()->{$this->xc7->x12cd->x1ade}(array(${$this->x95->x1327->x3621}->{$this->xc7->x12cd->x1ae9}('catalog_category_product_index')), ${$this->xc7->x1338->x4966})->{$this->x95->x12cd->x1b60}('product_id IN (?)', ${$this->x95->x12fe->{$this->x8d->x12fe->{$this->x95->x12fe->x260a}}})->{$this->x95->x12cd->x1b60}('store_id = ?', ${$this->x8d->x1327->{$this->xc7->x1327->{$this->x95->x1327->x3655}}})->{$this->x95->x12cd->x1b60}('category_id > 1')->{$this->x95->x12cd->x1b60}('category_id != ?', ${$this->xc7->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x143e}}}}->{$this->x95->x12cd->x1e65}())->{$this->x95->x12cd->x1da6}('product_id');
                     ${$this->x8d->x1327->{$this->x8d->x1327->{$this->x95->x1327->x377f}}} = ${$this->xc7->x12fe->x254b}->{$this->xc7->x12cd->x1cb8}(${$this->x8d->x1338->{$this->x8d->x1338->x4803}});
                     while (${$this->x95->x1338->{$this->x95->x1338->x48fd}} = ${$this->x8d->x12fe->{$this->x95->x12fe->x2670}}->{$this->x8d->x12cd->x1cd3}()) { ${$this->xc7->x12fe->{$this->xc7->x12fe->x26f9}} = $xe6e(',', ${$this->x8d->x12fe->{$this->x8d->x12fe->x2686}}['category_ids']);
                     if (empty(${$this->xc7->x12cd->{$this->x8d->x12cd->x161d}})) { continue;
                     } ${$this->x95->x12cd->x159d} = ${$this->xc7->x1327->{$this->x8d->x1327->{$this->xc7->x1327->{$this->xc7->x1327->x37a1}}}}['product_id'];
                     if (!isset(${$this->x8d->x1338->{$this->x95->x1338->x487c}}[${$this->x8d->x12fe->x268b}][${$this->xc7->x1327->{$this->x95->x1327->x37de}}])) { ${$this->xc7->x12fe->x2613}[${$this->x95->x1338->x4900}][${$this->xc7->x1327->x37dd}] = array();
                     } foreach (${$this->x8d->x12fe->x26f8} as ${$this->x95->x1327->{$this->x95->x1327->{$this->xc7->x1327->{$this->x8d->x1327->{$this->x95->x1327->x3822}}}}}) { if (isset(${$this->x8d->x1338->x47b7}[${$this->xc7->x12cd->x161e}])) { ${$this->x8d->x1338->x487a}[${$this->x95->x1338->{$this->x8d->x1338->x4905}}][${$this->xc7->x1327->{$this->xc7->x1327->{$this->x8d->x1327->x37e2}}}][] = ${$this->x95->x1338->{$this->xc7->x1338->{$this->x8d->x1338->x47bf}}}[${$this->x95->x1327->{$this->xc7->x1327->x3819}}];
                     } } ${$this->xc7->x12fe->x2613}[${$this->x8d->x12fe->{$this->xc7->x12fe->{$this->x95->x12fe->x2691}}}][${$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x8d->x12cd->x15d1}}}] = $xeb5($xeb7(${$this->x8d->x1338->x487a}[${$this->x8d->x12cd->{$this->x95->x12cd->x159f}}][${$this->xc7->x12fe->{$this->xc7->x12fe->x26b3}}]));
                     }  ${$this->xc7->x12fe->{$this->x95->x12fe->{$this->xc7->x12fe->x26b4}}} = '_prices';
                     ${$this->xc7->x12fe->{$this->xc7->x12fe->{$this->xc7->x12fe->x2709}}} = ${$this->xc7->x1327->x362a}->{$this->x95->x12cd->x1e9e}(array('prices.min_price', 'prices.tier_price'));
                     ${$this->xc7->x1327->x3831} = ${$this->x95->x1338->x478d}->{$this->x8d->x12cd->x1c72}('prices.tier_price IS NOT NULL', ${$this->x8d->x1338->{$this->xc7->x1338->{$this->x95->x1338->x4990}}}, 'prices.min_price');
                     ${$this->xc7->x1338->{$this->xc7->x1338->x49a2}} = array( 'customer_group_id', 'entity_id', 'price', 'final_price', 'minimal_price' => ${$this->x95->x1338->{$this->xc7->x1338->x499a}}, 'min_price', 'max_price', 'tier_price' );
                     ${$this->x8d->x1338->{$this->x8d->x1338->{$this->x95->x1338->x4806}}} = ${$this->x95->x1338->x478d}->{$this->x95->x12cd->x1ad4}()->{$this->xc7->x12cd->x1ade}(array('prices' => ${$this->x95->x1338->{$this->x95->x1338->x4784}}->{$this->xc7->x12cd->x1ae9}('catalog_product_index_price')), ${$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x1635}}})->{$this->x95->x12cd->x1b60}('prices.entity_id IN (?)', ${$this->x95->x12fe->{$this->x8d->x12fe->{$this->x8d->x12fe->{$this->x8d->x12fe->x260c}}}})->{$this->x95->x12cd->x1b60}('prices.website_id = ?', ${$this->xc7->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x143e}}}}->{$this->xc7->x12cd->x1b0b}());
                      ${$this->x8d->x12cd->{$this->x95->x12cd->x157c}} = ${$this->x95->x12cd->{$this->x8d->x12cd->x1423}}->{$this->xc7->x12cd->x1cb8}(${$this->xc7->x1327->x36a0});
                     while (${$this->xc7->x1327->{$this->x8d->x1327->{$this->xc7->x1327->x379f}}} = ${$this->x8d->x1338->x48e8}->{$this->x8d->x12cd->x1cd3}()) { ${$this->x95->x1327->x37a3} = ${$this->x8d->x1327->x3798}['entity_id'];
                     ${$this->xc7->x1338->{$this->xc7->x1338->{$this->x95->x1338->x49ae}}} = ${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x159c}}}}}['customer_group_id'];
                     unset(${$this->x8d->x1327->x3798}['customer_group_id']);
                     unset(${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->x1599}}}}['entity_id']);
                     ${$this->xc7->x1327->{$this->x8d->x1327->{$this->xc7->x1327->x379f}}}['price'] = (float)$xf16((float)${$this->x95->x1338->x48f9}['price'], 2, '.', '');
                     ${$this->x8d->x12fe->x2682}['final_price'] = (float)$xf16((float)${$this->x8d->x1327->x3798}['final_price'], 2, '.', '');
                     if (null !== ${$this->x95->x12cd->x1593}['minimal_price']) { ${$this->x95->x1338->x48f9}['minimal_price'] = (float)${$this->x95->x12cd->{$this->x8d->x12cd->x1597}}['minimal_price'];
                     } if (null !== ${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x159c}}}}}['min_price']) { ${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x159c}}}}}['min_price'] = (float)${$this->x8d->x1327->x3798}['min_price'];
                     } if (null !== ${$this->x95->x12cd->{$this->x8d->x12cd->x1597}}['max_price']) { ${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->x1598}}}['max_price'] = (float)${$this->x8d->x1327->x3798}['max_price'];
                     } if (null !== ${$this->x95->x12cd->x1593}['tier_price']) { ${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->x1598}}}['tier_price'] = (float)${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->x1599}}}}['tier_price'];
                     } if (isset(${$this->x95->x1338->{$this->x95->x1338->x48fd}}['group_price']) && null !== ${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x159c}}}}}['group_price']) { ${$this->xc7->x1327->{$this->x8d->x1327->{$this->xc7->x1327->x379f}}}['group_price'] = (float)${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->x1598}}}['group_price'];
                     } if (isset(${$this->xc7->x12fe->{$this->x95->x12fe->x2614}}[${$this->x95->x1327->{$this->x95->x1327->{$this->x8d->x1327->{$this->x8d->x1327->{$this->xc7->x1327->x37b3}}}}}]['tax_class_id'])) { ${$this->x8d->x1338->x49b1} = ${$this->x95->x12cd->{$this->xc7->x12cd->x150b}}[${$this->x95->x1327->{$this->x95->x1327->{$this->x8d->x1327->x37ab}}}]['tax_class_id'];
                     if (${$this->xc7->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->xc7->x12cd->{$this->x8d->x12cd->x164c}}}}} && !${$this->xc7->x1338->x47cf}) { ${$this->xc7->x1327->{$this->x8d->x1327->x35f9}} = new Varien_Object();
                     ${$this->xc7->x1327->{$this->x8d->x1327->x35f9}}->{$this->xc7->x12cd->x1f3f}(${$this->xc7->x12fe->x272c});
                     foreach (${$this->xc7->x1327->{$this->x8d->x1327->{$this->xc7->x1327->{$this->xc7->x1327->x37a1}}}} as &${$this->x95->x12fe->x2738}) { ${$this->x95->x12fe->x2738} = ${$this->xc7->x12cd->{$this->xc7->x12cd->x1454}}->{$this->xc7->x12cd->x1f4d}( ${$this->xc7->x1338->x4769}, ${$this->x95->x12fe->x2738}, ${$this->xc7->x12cd->{$this->x95->x12cd->x147a}}, null, null, ${$this->x8d->x1338->{$this->x95->x1338->{$this->x95->x1338->{$this->x95->x1338->x47fc}}}}, ${$this->xc7->x12fe->{$this->x8d->x12fe->{$this->x8d->x12fe->{$this->xc7->x12fe->x2566}}}} );
                     } } } if (${$this->xc7->x12fe->x2613}[${$this->x95->x1327->x37a3}]['type_id'] == "bundle" && (${$this->xc7->x1327->{$this->x95->x1327->{$this->x8d->x1327->{$this->xc7->x1327->x3665}}}}->{$this->x8d->x12cd->x1f5c}() || ${$this->xc7->x12cd->{$this->xc7->x12cd->x1454}}->{$this->x95->x12cd->x1f65}())) { ${$this->xc7->x12cd->{$this->xc7->x12cd->x165e}} = Mage::getModel('catalog/product')->{$this->x95->x12cd->x1f8d}(${$this->x95->x1338->{$this->x8d->x1338->x4905}});
                     ${$this->xc7->x1327->x3860}->{$this->xc7->x12cd->x1c3c}(${$this->x95->x1338->{$this->xc7->x1338->x47b2}});
                     list(${$this->x8d->x1327->{$this->x95->x1327->{$this->x95->x1327->{$this->x8d->x1327->x386b}}}}, ${$this->xc7->x1338->{$this->x8d->x1338->{$this->xc7->x1338->x49e3}}}) = ${$this->xc7->x1327->x3860}->{$this->xc7->x12cd->x1f9e}()->{$this->x8d->x12cd->x1fa2}(${$this->x95->x1338->{$this->x8d->x1338->x49d3}}, null, true, true);
                     ${$this->x8d->x1327->x3798}['min_price'] = ${$this->x95->x12fe->x274d};
                     ${$this->x8d->x12fe->{$this->x8d->x12fe->x2686}}['max_price'] = ${$this->xc7->x1338->{$this->x8d->x1338->{$this->xc7->x1338->{$this->x8d->x1338->{$this->x8d->x1338->x49ea}}}}};
                     } ${$this->x8d->x1338->{$this->x95->x1338->x487c}}[${$this->x95->x1327->{$this->x95->x1327->{$this->x8d->x1327->{$this->x8d->x1327->{$this->xc7->x1327->x37b3}}}}}][${$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x8d->x12cd->x15d1}}} . "_" . ${$this->xc7->x1338->x49a5}] = ${$this->x8d->x12fe->{$this->x8d->x12fe->x2686}};
                     } if (${$this->xc7->x12cd->{$this->xc7->x12cd->{$this->xc7->x12cd->x147e}}} && ${$this->x95->x1327->{$this->xc7->x1327->x3669}} == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH) { ${$this->x8d->x12cd->{$this->x95->x12cd->x15cc}} = '_prices_ht';
                     ${$this->x8d->x1327->{$this->x8d->x1327->{$this->x8d->x1327->x382a}}} = ${$this->x95->x12cd->{$this->x8d->x12cd->x1423}}->{$this->x95->x12cd->x1e9e}(array('prices.min_price', 'prices.tier_price'));
                     ${$this->x95->x1338->{$this->xc7->x1338->x499a}} = ${$this->x95->x12cd->{$this->x95->x12cd->{$this->x95->x12cd->x1426}}}->{$this->x8d->x12cd->x1c72}('prices.tier_price IS NOT NULL', ${$this->x95->x12fe->x2705}, 'prices.min_price');
                     ${$this->x8d->x1327->{$this->x8d->x1327->{$this->xc7->x1327->{$this->xc7->x1327->x384a}}}} = array( 'customer_group_id', 'entity_id', 'price', 'final_price', 'minimal_price' => ${$this->x95->x1338->{$this->xc7->x1338->x499a}}, 'min_price', 'max_price', 'tier_price' );
                     ${$this->x95->x1327->{$this->x95->x1327->x36a1}} = ${$this->xc7->x1327->{$this->xc7->x1327->x362e}}->{$this->x95->x12cd->x1ad4}()->{$this->xc7->x12cd->x1ade}(array('prices' => ${$this->xc7->x12cd->x1419}->{$this->xc7->x12cd->x1ae9}('catalog_product_index_price')), ${$this->x8d->x1327->{$this->x8d->x1327->{$this->xc7->x1327->{$this->xc7->x1327->x384a}}}})->{$this->x95->x12cd->x1b60}('prices.entity_id IN (?)', ${$this->x95->x12fe->{$this->x8d->x12fe->{$this->x95->x12fe->x260a}}})->{$this->x95->x12cd->x1b60}('prices.website_id = ?', ${$this->xc7->x12fe->{$this->x8d->x12fe->{$this->x8d->x12fe->{$this->xc7->x12fe->x2566}}}}->{$this->xc7->x12cd->x1b0b}());
                      ${$this->x8d->x1327->{$this->x8d->x1327->x377b}} = ${$this->x95->x12cd->{$this->x8d->x12cd->x1423}}->{$this->xc7->x12cd->x1cb8}(${$this->x95->x12fe->{$this->x95->x12fe->x25b0}});
                     while (${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->x1599}}}} = ${$this->x8d->x12cd->x1579}->{$this->x8d->x12cd->x1cd3}()) { ${$this->x95->x12cd->x159d} = ${$this->x8d->x12fe->{$this->x8d->x12fe->x2686}}['entity_id'];
                     ${$this->xc7->x1327->{$this->x8d->x1327->x3850}} = ${$this->x95->x1338->{$this->x95->x1338->x48fd}}['customer_group_id'];
                     unset(${$this->x8d->x12fe->x2682}['customer_group_id']);
                     unset(${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x159c}}}}}['entity_id']);
                     ${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->x1598}}}['price'] = (float)${$this->x8d->x12fe->{$this->x8d->x12fe->x2686}}['price'];
                     ${$this->x95->x12cd->x1593}['final_price'] = (float)${$this->xc7->x1327->{$this->x8d->x1327->x379a}}['final_price'];
                     if (null !== ${$this->xc7->x1327->{$this->x8d->x1327->{$this->xc7->x1327->x379f}}}['minimal_price']) { ${$this->xc7->x1327->{$this->x8d->x1327->x379a}}['minimal_price'] = (float)${$this->xc7->x1327->{$this->x8d->x1327->{$this->xc7->x1327->{$this->xc7->x1327->x37a1}}}}['minimal_price'];
                     } if (null !== ${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x159c}}}}}['min_price']) { ${$this->xc7->x1327->{$this->x8d->x1327->{$this->xc7->x1327->{$this->xc7->x1327->x37a1}}}}['min_price'] = (float)${$this->xc7->x1327->{$this->x8d->x1327->x379a}}['min_price'];
                     } if (null !== ${$this->x8d->x12fe->{$this->x8d->x12fe->x2686}}['max_price']) { ${$this->x95->x12cd->x1593}['max_price'] = (float)${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x159c}}}}}['max_price'];
                     } if (null !== ${$this->x8d->x12fe->x2682}['tier_price']) { ${$this->x8d->x1327->x3798}['tier_price'] = (float)${$this->xc7->x1327->{$this->x8d->x1327->{$this->xc7->x1327->{$this->xc7->x1327->x37a1}}}}['tier_price'];
                     } if (isset(${$this->x8d->x12fe->{$this->x8d->x12fe->x2686}}['group_price']) && null !== ${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x159c}}}}}['group_price']) { ${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->x1599}}}}['group_price'] = (float)${$this->x95->x12cd->{$this->x8d->x12cd->x1597}}['group_price'];
                     } if (isset(${$this->x8d->x1338->{$this->x95->x1338->x487c}}[${$this->x8d->x12fe->{$this->xc7->x12fe->{$this->x95->x12fe->x2691}}}]['tax_class_id'])) { ${$this->xc7->x1327->x3851} = ${$this->x95->x12cd->{$this->x95->x12cd->{$this->x8d->x12cd->x150e}}}[${$this->x8d->x12fe->{$this->xc7->x12fe->{$this->x95->x12fe->x2691}}}]['tax_class_id'];
                     if (${$this->x95->x1327->{$this->xc7->x1327->x3852}}) { ${$this->xc7->x1327->{$this->xc7->x1327->{$this->x8d->x1327->{$this->x8d->x1327->x3602}}}}->{$this->xc7->x12cd->x1f3f}(${$this->x8d->x1338->{$this->x8d->x1338->{$this->x8d->x1338->{$this->x95->x1338->{$this->x8d->x1338->x49bb}}}}});
                     foreach (${$this->x8d->x1327->x3798} as &${$this->x8d->x12cd->{$this->x95->x12cd->{$this->x8d->x12cd->x1654}}}) { ${$this->x8d->x12fe->{$this->x8d->x12fe->x273c}} = ${$this->xc7->x1327->{$this->x95->x1327->{$this->x8d->x1327->{$this->xc7->x1327->x3665}}}}->{$this->xc7->x12cd->x1f4d}( ${$this->xc7->x1327->{$this->xc7->x1327->{$this->x8d->x1327->{$this->x8d->x1327->x3602}}}}, ${$this->x8d->x12fe->{$this->x95->x12fe->{$this->x8d->x12fe->x2740}}}, false, null, null, ${$this->xc7->x12fe->{$this->x8d->x12fe->{$this->x95->x12fe->{$this->x95->x12fe->x25a9}}}}, ${$this->x8d->x12fe->x2559} );
                     } unset(${$this->x95->x12cd->x1650});
                     } } ${$this->x95->x12cd->{$this->x95->x12cd->{$this->x8d->x12cd->x150e}}}[${$this->x95->x1327->{$this->x95->x1327->{$this->x8d->x1327->{$this->x95->x1327->x37b0}}}}][${$this->xc7->x1338->x4925} . "_" . ${$this->x8d->x12fe->x2720}] = ${$this->x95->x1338->x48f9};
                     } }  ${$this->x8d->x12cd->{$this->xc7->x12cd->{$this->xc7->x12cd->{$this->xc7->x12cd->x15d6}}}} = '_url';
                     ${$this->x8d->x1327->{$this->x8d->x1327->{$this->x8d->x1327->x387e}}} = '';
                     if (${$this->x95->x12fe->{$this->x8d->x12fe->{$this->x95->x12fe->x2556}}}) { ${$this->xc7->x12cd->{$this->xc7->x12cd->x167a}} = Enterprise_Catalog_Model_Product::URL_REWRITE_ENTITY_TYPE;
                     ${$this->x8d->x12cd->{$this->x95->x12cd->x1495}} = ${$this->x95->x1338->x478d}->{$this->x95->x12cd->x1ad4}()->{$this->xc7->x12cd->x1ade}( array('url_key' => ${$this->x8d->x12fe->{$this->x95->x12fe->{$this->x8d->x12fe->x2547}}}->{$this->xc7->x12cd->x1ae9}(array('catalog/product', 'url_key'))), array('product_id' => 'entity_id') )->join( array('url_rewrite' => ${$this->xc7->x12fe->x2544}->{$this->xc7->x12cd->x1ae9}('enterprise_urlrewrite/url_rewrite')), 'url_key.value_id = url_rewrite.value_id AND url_rewrite.entity_type = ' . ${$this->x8d->x1338->x49f7}, array('request_path') )->{$this->x95->x12cd->x1b60}('entity_id IN (?) AND url_key.store_id IN (0, ' . ${$this->x8d->x1327->{$this->xc7->x1327->{$this->x95->x1327->x3655}}} . ')', ${$this->xc7->x1338->x486f})->{$this->xc7->x12cd->x1d87}("url_key.store_id ASC");
                     ${$this->x8d->x1327->{$this->x8d->x1327->{$this->x95->x1327->{$this->xc7->x1327->x3881}}}} = ${$this->xc7->x12fe->{$this->x8d->x12fe->{$this->x8d->x12fe->{$this->xc7->x12fe->x2566}}}}->{$this->x8d->x12cd->x20a3}(Mage_Catalog_Helper_Product::XML_PATH_PRODUCT_URL_SUFFIX);
                     if (${$this->x8d->x12cd->{$this->x8d->x12cd->x1675}}) { if ($x10b5(${$this->x8d->x1338->{$this->xc7->x1338->x49ee}}, ".") !== false) { ${$this->x8d->x1338->{$this->x8d->x1338->{$this->x8d->x1338->x49f0}}} = ${$this->x95->x12fe->{$this->x8d->x12fe->x276a}};
                     } else { ${$this->x8d->x1338->{$this->x8d->x1338->{$this->xc7->x1338->{$this->x95->x1338->x49f2}}}} = '.' . ${$this->x8d->x1327->{$this->x8d->x1327->{$this->x8d->x1327->x387e}}};
                     } } } else { ${$this->x8d->x1338->{$this->x8d->x1338->{$this->x95->x1338->x4806}}} = ${$this->x95->x1338->x478d}->{$this->x95->x12cd->x1ad4}()->{$this->xc7->x12cd->x1ade}(${$this->x8d->x1327->{$this->x8d->x1327->{$this->x8d->x1327->x3625}}}->{$this->xc7->x12cd->x1ae9}('core_url_rewrite'), array('product_id', 'request_path'))->{$this->x95->x12cd->x1b60}('store_id = ?', ${$this->x95->x12cd->{$this->x8d->x12cd->x1442}})->{$this->x95->x12cd->x1b60}('category_id IS NULL')->{$this->x95->x12cd->x1b60}("(options IS NULL OR options = '')")->{$this->x95->x12cd->x1b60}('product_id IN (?)', ${$this->x95->x12cd->{$this->xc7->x12cd->{$this->xc7->x12cd->x1503}}});
                     } ${$this->x8d->x1327->{$this->x8d->x1327->{$this->xc7->x1327->{$this->xc7->x1327->x3783}}}} = ${$this->x8d->x1338->{$this->xc7->x1338->{$this->xc7->x1338->{$this->xc7->x1338->x4797}}}}->{$this->xc7->x12cd->x1cb8}(${$this->x8d->x1338->{$this->x8d->x1338->x4803}});
                     while (${$this->xc7->x1327->{$this->x8d->x1327->{$this->xc7->x1327->x379f}}} = ${$this->x8d->x1327->{$this->x8d->x1327->{$this->xc7->x1327->{$this->xc7->x1327->x3783}}}}->{$this->x8d->x12cd->x1cd3}()) { ${$this->x8d->x12fe->{$this->xc7->x12fe->{$this->x95->x12fe->{$this->x95->x12fe->x2693}}}} = ${$this->x95->x1338->{$this->x95->x1338->x48fd}}['product_id'];
                     ${$this->x8d->x1327->x3798}['product_id'] = (int)${$this->x95->x12cd->x1593}['product_id'];
                     ${$this->x8d->x1327->{$this->xc7->x1327->x3713}}[${$this->x95->x12cd->x159d}][${$this->x8d->x12cd->{$this->xc7->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->{$this->x95->x12cd->x15d9}}}}}] = ${$this->x95->x1338->x47a8}->{$this->xc7->x12cd->x211e}() . ${$this->x8d->x12fe->{$this->x8d->x12fe->x2686}}['request_path'] . ${$this->x95->x12fe->{$this->xc7->x12fe->{$this->x8d->x12fe->x276c}}};
                     } ${$this->xc7->x1327->{$this->x95->x1327->{$this->x95->x1327->{$this->xc7->x1327->x388d}}}} = array( 'indexer' => $this, 'store' => ${$this->xc7->x12fe->{$this->x8d->x12fe->{$this->xc7->x12fe->x2563}}}, 'products' => ${$this->xc7->x12fe->x2613}, );
                     ${$this->xc7->x1327->x3895} = new Varien_Object(${$this->xc7->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x1692}}}}});
                     Mage::dispatchEvent('allure_elasticsearch_index_export', array("data" => ${$this->x8d->x12fe->x2781}));
                     ${$this->xc7->x12fe->{$this->x95->x12fe->x2614}} = ${$this->xc7->x12fe->{$this->x95->x12fe->x2786}}->{$this->x95->x12cd->x2142}();
                     if (!empty(${$this->x95->x12cd->{$this->x95->x12cd->{$this->x8d->x12cd->x150e}}})) { ${$this->x8d->x12cd->{$this->x8d->x12cd->x13f0}}[${$this->xc7->x12cd->x143f}] = $x1135(${$this->x8d->x12fe->{$this->x8d->x12fe->{$this->x95->x12fe->x2511}}}[${$this->x95->x1338->{$this->xc7->x1338->x47b2}}], ${$this->x8d->x1338->{$this->x95->x1338->x487c}});
                     } } $this->{$this->x95->x12cd->x1ab1}(' > Products exported');
                     } return ${$this->xc7->x12fe->x250c};
                     } catch (Exception $e) { throw $e;
                     }
                    }
                    public function getAdditionalFields() {
                        return array('_parent_ids');
                    }
                    public function getCategoryNames($x1161 = null) {
                        $x4dad = "helper";
                        $x5311 = "getModel";
                        $x57ab = "app";
                        $x551a = "getSingleton";
                        $x574f = "getStoreConfig";
                        $x576c = "dispatchEvent";
                        $x55c9 = "getResourceModel";
                        $x57ca = "getStoreConfigFlag";
                        ${$this->x95->x1338->x4a94} = Mage::app()->{$this->x95->x12cd->x2172}(${$this->xc7->x1327->{$this->x8d->x1327->{$this->xc7->x1327->{$this->x8d->x1327->x391e}}}});
                        ${$this->x95->x12fe->{$this->xc7->x12fe->x281a}} = $this->{$this->x95->x12cd->x19f0}();
                        ${$this->xc7->x12fe->{$this->x95->x12fe->{$this->x95->x12fe->x2820}}} = Mage::getSingleton('eav/entity_attribute')->{$this->x95->x12cd->x1b81}(Mage_Catalog_Model_Category::ENTITY, 'name');
                        ${$this->x8d->x12fe->x2823} = ${$this->x8d->x12cd->{$this->x95->x12cd->{$this->x95->x12cd->{$this->xc7->x12cd->x1744}}}}->{$this->x95->x12cd->x1ad4}()->{$this->xc7->x12cd->x1ade}($this->{$this->xc7->x12cd->x19e4}()->{$this->xc7->x12cd->x1ae9}('catalog_category_entity_varchar'), array('entity_id', 'value'))->{$this->x95->x12cd->x1b60}('attribute_id = ?', ${$this->x95->x1338->{$this->xc7->x1338->{$this->x95->x1338->{$this->xc7->x1338->{$this->xc7->x1338->x4ab4}}}}})->{$this->x95->x12cd->x1b60}('store_id IN (?)', array(0, ${$this->x95->x1338->{$this->x95->x1338->x4a98}}->{$this->xc7->x12cd->x1a42}()))->{$this->xc7->x12cd->x1d87}(array('entity_id ASC', 'store_id ASC'));
                        return ${$this->x95->x1338->x4aa4}->{$this->x95->x12cd->x220c}(${$this->x8d->x12cd->{$this->x8d->x12cd->x175a}});
                }
                public function getSearchableAttributes($x11ac = null) {$x11a7 = $this->xc7->x1338->x4c95;
                    $x4dad = "helper";
                    $x5311 = "getModel";
                    $x57ab = "app";
                    $x551a = "getSingleton";
                    $x574f = "getStoreConfig";
                    $x576c = "dispatchEvent";
                    $x55c9 = "getResourceModel";
                    $x57ca = "getStoreConfigFlag";
                    if (null === $this->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x136d}}}) {
                        $this->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->xc7->x12cd->{$this->xc7->x12cd->x136e}}}} = array();
                        ${$this->xc7->x12cd->x1765} = $this->{$this->x95->x12cd->x221c}()->{$this->xc7->x12cd->x222d}('catalog_product');
                        ${$this->x95->x1327->{$this->x8d->x1327->{$this->x8d->x1327->{$this->x95->x1327->x3964}}}} = ${$this->x95->x12fe->x283f}->{$this->xc7->x12cd->x223f}();
                        ${$this->xc7->x12cd->{$this->xc7->x12cd->{$this->xc7->x12cd->{$this->xc7->x12cd->{$this->xc7->x12cd->x1786}}}}} = Mage::getResourceModel('catalog/product_attribute_collection')->{$this->x8d->x12cd->x225f}(${$this->x95->x1327->{$this->x95->x1327->x3956}}->{$this->x95->x12cd->x226a}())->{$this->x8d->x12cd->x2278}()->{$this->xc7->x12cd->x228a}(true);
                        ${$this->xc7->x12fe->x2859} = ${$this->xc7->x12fe->{$this->x95->x12fe->{$this->x8d->x12fe->x2857}}}->{$this->x8d->x12cd->x2293}();
                        foreach (${$this->x95->x12cd->{$this->xc7->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x1790}}}} as ${$this->xc7->x1338->{$this->xc7->x1338->{$this->x8d->x1338->{$this->x8d->x1338->{$this->x95->x1338->x4b07}}}}}) {
                            ${$this->x8d->x12fe->{$this->xc7->x12fe->{$this->xc7->x12fe->x286d}}}->{$this->x95->x12cd->x22a2}(${$this->xc7->x1338->{$this->x8d->x1338->{$this->x8d->x1338->{$this->x8d->x1338->x4ad7}}}});
                            $this->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->x95->x12cd->x136d}}}[${$this->x95->x1327->{$this->x8d->x1327->x3982}}->{$this->xc7->x12cd->x1c22}()] = ${$this->xc7->x1338->{$this->x8d->x1338->x4afd}};
                        }
                    }
                    if (null !== ${$this->xc7->x1338->{$this->xc7->x1338->x4ac0}}) {
                         ${$this->x95->x12fe->{$this->x95->x12fe->{$this->xc7->x12fe->{$this->x95->x12fe->x2837}}}} = (array)${$this->xc7->x1327->{$this->xc7->x1327->{$this->x8d->x1327->x394c}}};
                         ${$this->xc7->x12fe->{$this->x8d->x12fe->{$this->x95->x12fe->{$this->x8d->x12fe->x285f}}}} = array();
                         foreach ($this->{$this->x8d->x12cd->{$this->xc7->x12cd->{$this->xc7->x12cd->{$this->xc7->x12cd->x136e}}}} as ${$this->x8d->x12fe->{$this->xc7->x12fe->x2868}}) {
                            if ($x11a7(${$this->x8d->x12fe->{$this->xc7->x12fe->x2868}}->{$this->xc7->x12cd->x22bc}(), ${$this->xc7->x1327->{$this->x95->x1327->x3949}})) {
                                ${$this->x95->x1327->x3978}[${$this->xc7->x12fe->x2867}->{$this->xc7->x12cd->x1c22}()] = ${$this->xc7->x1338->{$this->xc7->x1338->{$this->x8d->x1338->{$this->x8d->x1338->x4b06}}}};
                            }
                        }
                        return ${$this->xc7->x12fe->{$this->x8d->x12fe->{$this->x95->x12fe->{$this->x95->x12fe->{$this->x95->x12fe->x2863}}}}};
                    }

                    return $this->{$this->x8d->x12cd->{$this->x8d->x12cd->x136a}};
            }
            public function getStoreIndexProperties($x1290 = null) {
                $x11d9 = $this->xc7->x12fe->x29fa;
                $x180 = $this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->{$this->x95->x12cd->x1911}}};
                $x12a3 = $this->xc7->x1338->x4c7e;
                $x1247 = $this->x95->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->{$this->x95->x12cd->{$this->x8d->x12cd->x192b}}}};
                $x4dad = "helper";
                $x5311 = "getModel";
                $x57ab = "app";
                $x551a = "getSingleton";
                $x574f = "getStoreConfig";
                $x576c = "dispatchEvent";
                $x55c9 = "getResourceModel";
                $x57ca = "getStoreConfigFlag";
                ${$this->xc7->x12fe->x279b} = Mage::app()->{$this->x95->x12cd->x2172}(${$this->x8d->x12fe->{$this->x8d->x12fe->x27a0}});
                 ${$this->x8d->x1327->x38bd} = 'elasticsearch_product_index_properties_' . ${$this->xc7->x1327->{$this->xc7->x1327->{$this->x8d->x1327->x38b5}}}->{$this->xc7->x12cd->x1a42}();
                 if (Mage::app()->{$this->x95->x12cd->x2300}('config')) { ${$this->x95->x1338->{$this->x8d->x1338->x4a49}} = Mage::app()->{$this->x95->x12cd->x231b}(${$this->x8d->x12cd->x16c0});
                 if (${$this->x95->x12cd->{$this->x95->x12cd->{$this->xc7->x12cd->x16d7}}}) { return $x11d9(${$this->x95->x12cd->{$this->x95->x12cd->{$this->x8d->x12cd->{$this->x95->x12cd->x16dc}}}});
                 } } ${$this->x95->x1338->{$this->x8d->x1338->x4a49}} = array();
                 ${$this->xc7->x12cd->x16de} = $this->{$this->x95->x12cd->x2336}(${$this->x8d->x12cd->{$this->x8d->x12cd->{$this->x95->x12cd->x16b8}}});
                 ${$this->xc7->x1327->{$this->x8d->x1327->{$this->xc7->x1327->{$this->xc7->x1327->x38da}}}} = $this->{$this->xc7->x12fe->{$this->x8d->x12fe->{$this->x95->x12fe->{$this->x8d->x12fe->{$this->x8d->x12fe->x28d8}}}}}(array('varchar', 'int'));
                 foreach (${$this->x95->x1327->x38d3} as ${$this->x95->x12fe->{$this->x95->x12fe->{$this->xc7->x12fe->{$this->x95->x12fe->{$this->x95->x12fe->x27db}}}}}) {  if ($this->{$this->xc7->x12cd->x1c05}(${$this->xc7->x1338->{$this->x8d->x1338->x4a60}})) { ${$this->x95->x12fe->{$this->xc7->x12fe->{$this->x95->x12fe->x27e4}}} = ${$this->x8d->x1327->{$this->x8d->x1327->x38e6}}->{$this->xc7->x12cd->x1c22}();
                 ${$this->x95->x12cd->{$this->x95->x12cd->{$this->x8d->x12cd->{$this->x95->x12cd->x16dc}}}}[${$this->x95->x12fe->{$this->x8d->x12fe->x27e2}}] = $this->{$this->x8d->x12cd->x236a}(${$this->x95->x12cd->x16ff}, ${$this->xc7->x12fe->x279b});
                 } } ${$this->xc7->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->{$this->x8d->x12cd->{$this->x95->x12cd->x16fb}}}}} = $this->{$this->xc7->x12cd->{$this->x95->x12cd->{$this->x95->x12cd->{$this->x8d->x12cd->x17f5}}}}('text');
                 foreach (${$this->xc7->x12fe->x27c4} as ${$this->x8d->x12cd->{$this->xc7->x12cd->{$this->xc7->x12cd->x1706}}}) {  ${$this->x8d->x1338->{$this->x95->x1338->{$this->x8d->x1338->x4a74}}} = ${$this->x95->x12fe->{$this->x95->x12fe->{$this->xc7->x12fe->x27d7}}}->{$this->xc7->x12cd->x1c22}();
                 ${$this->x95->x1338->{$this->x95->x1338->{$this->x95->x1338->{$this->x95->x1338->{$this->xc7->x1338->x4a4e}}}}}[${$this->x8d->x12cd->{$this->xc7->x12cd->x1710}}] = $this->{$this->x8d->x12cd->x236a}(${$this->x8d->x1338->x4a5d}, ${$this->xc7->x12fe->x279b});
                 } ${$this->x95->x12fe->{$this->x95->x12fe->{$this->x95->x12fe->x27c9}}} = $this->{$this->xc7->x12cd->{$this->x95->x12cd->{$this->x95->x12cd->{$this->x8d->x12cd->{$this->x95->x12cd->x17f6}}}}}(array('static', 'varchar', 'decimal', 'datetime'));
                 foreach (${$this->xc7->x1338->{$this->xc7->x1338->x4a5c}} as ${$this->xc7->x12fe->x27cd}) {  ${$this->x95->x12fe->{$this->x8d->x12fe->x27e2}} = ${$this->x8d->x12cd->{$this->xc7->x12cd->{$this->xc7->x12cd->{$this->x8d->x12cd->x1708}}}}->{$this->xc7->x12cd->x1c22}();
                 if ($this->{$this->xc7->x12cd->x1c05}(${$this->x95->x1327->x38e1}) && !isset(${$this->x8d->x12cd->x16d0}[${$this->xc7->x12fe->x27dd}])) { ${$this->x95->x1327->{$this->x95->x1327->{$this->x95->x1327->x3906}}} = $this->{$this->x8d->x12cd->x23cb}(${$this->xc7->x1338->{$this->x8d->x1338->x4a60}});
                if (${$this->x8d->x1338->x4a7d} === 'option') {
                    continue;
                }
                ${$this->x95->x12cd->{$this->x8d->x12cd->{$this->x95->x12cd->{$this->x8d->x12cd->x1728}}}} = ${$this->x95->x12fe->{$this->xc7->x12fe->x27d2}}->{$this->x95->x12cd->x23db}();
                ${$this->x8d->x1327->{$this->xc7->x1327->{$this->xc7->x1327->x38cc}}}[${$this->x95->x1327->{$this->x8d->x1327->{$this->xc7->x1327->x38f1}}}] = array( 'type' => ${$this->xc7->x1338->{$this->x8d->x1338->x4a81}}, 'include_in_all' => (bool)${$this->xc7->x1338->{$this->x8d->x1338->{$this->x95->x1338->{$this->x8d->x1338->x4a65}}}}->{$this->x8d->x12cd->x23e2}(), );
                if (${$this->xc7->x1327->{$this->x8d->x1327->{$this->xc7->x1327->{$this->xc7->x1327->x3911}}}}) {
                    ${$this->x95->x1338->{$this->x95->x1338->{$this->x95->x1338->{$this->x95->x1338->{$this->xc7->x1338->x4a4e}}}}}[${$this->x95->x12fe->{$this->x8d->x12fe->x27e2}}]['boost'] = $x1247(${$this->x95->x12cd->{$this->xc7->x12cd->x1722}});
                }

                if (${$this->x8d->x12cd->{$this->x8d->x12cd->{$this->x95->x12cd->x1713}}} == 'sku') {
                    if (Mage::getStoreConfig('catalog/search/elasticsearch_analyze_sku')) {
                        ${$this->x8d->x1338->x4a46}[${$this->x95->x12fe->{$this->x8d->x12fe->x27e2}}]['fields'] = array( 'keyword' => array( 'type' => 'string', 'analyzer' => 'keyword', ), 'prefix' => array( 'type' => 'string', 'analyzer' => 'keyword_prefix', 'search_analyzer' => 'keyword', ), 'suffix' => array( 'type' => 'string', 'analyzer' => 'keyword_suffix', 'search_analyzer' => 'keyword', ), );
                    } else {
                        ${$this->x8d->x12cd->x16d0}[${$this->x8d->x12cd->{$this->xc7->x12cd->x1710}}]['fields'] = array( 'keyword' => array( 'type' => 'string', 'analyzer' => 'std' ) );
                    }
                }
                if (${$this->x95->x12fe->{$this->x8d->x12fe->x27e2}} == 'price') {
                    ${$this->x95->x12cd->{$this->xc7->x12cd->x16d2}}[${$this->x8d->x1338->{$this->xc7->x1338->x4a6f}}]['fields'] = array( 'keyword' => array( 'type' => 'string', 'index' => 'not_analyzed', ), );
                }
                if (${$this->x8d->x1327->{$this->x8d->x1327->x38e6}}->{$this->xc7->x12cd->x22bc}() == 'datetime') {
                    ${$this->x8d->x1327->{$this->xc7->x1327->{$this->xc7->x1327->x38cc}}}[${$this->x8d->x12cd->{$this->xc7->x12cd->x1710}}]['format'] = $this->_dateFormat;
                    ${$this->x8d->x1327->{$this->xc7->x1327->{$this->xc7->x1327->x38cc}}}[${$this->x8d->x1338->{$this->x95->x1338->{$this->x8d->x1338->x4a74}}}]['ignore_malformed'] = true;
                }
            }
        }

        ${$this->xc7->x12fe->{$this->x8d->x12fe->x27ad}}['_categories'] = array( 'type' => 'string', 'include_in_all' => true, 'analyzer' => 'std', );

        if (isset(${$this->x95->x1327->{$this->x95->x1327->x38d2}}['analysis']['analyzer']['language'])) {
            ${$this->x8d->x1327->x38c7}['_categories']['analyzer'] = 'language';
        }

        ${$this->x8d->x1327->x38c7}['_parent_ids'] = array( 'type' => 'integer', 'store' => true, 'index' => 'no', );
        ${$this->x95->x1338->{$this->x95->x1338->{$this->x95->x1338->{$this->x95->x1338->{$this->xc7->x1338->x4a4e}}}}}['_url'] = array( 'type' => 'string', 'store' => true, 'index' => 'no', );
        ${$this->x95->x12cd->{$this->xc7->x12cd->x16d2}} = new Varien_Object(${$this->xc7->x12fe->x27aa});

        Mage::dispatchEvent('allure_elasticsearch_index_properties', array( 'indexer' => $this, 'store' => ${$this->x8d->x1338->x4a32}, 'properties' => ${$this->xc7->x12fe->x27aa}, ));

        ${$this->x8d->x1338->x4a46} = ${$this->xc7->x12fe->{$this->x8d->x12fe->x27ad}}->{$this->xc7->x12cd->x197b}();

        if (Mage::app()->{$this->x95->x12cd->x2300}('config')) {
            ${$this->xc7->x1338->{$this->x8d->x1338->x4a8d}} = $this->{$this->x95->x12cd->x2458}();
            Mage::app()->{$this->xc7->x12cd->x2474}($x12a3(${$this->x8d->x1327->{$this->xc7->x1327->{$this->xc7->x1327->x38cc}}}), ${$this->xc7->x12fe->x27a3}, array('config'), ${$this->x8d->x1327->{$this->x8d->x1327->x3918}});
        }

        return ${$this->x8d->x1327->{$this->xc7->x1327->{$this->xc7->x1327->x38cc}}};
    }
    public function isIndexOutOfStockProducts($x12b2 = null) {
        $x4dad = "helper";
        $x5311 = "getModel";
        $x57ab = "app";
        $x551a = "getSingleton";
        $x574f = "getStoreConfig";
        $x576c = "dispatchEvent";
        $x55c9 = "getResourceModel";
        $x57ca = "getStoreConfigFlag";

        return Mage::getStoreConfigFlag(Mage_CatalogInventory_Helper_Data::XML_PATH_SHOW_OUT_OF_STOCK, ${$this->xc7->x1338->x4b0b});
    }
} ?>
