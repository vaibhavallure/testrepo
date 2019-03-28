<?php

class Allure_HarrodsInventory_Model_Cron
{
    public function generateHarrodsFiles()
    {
        Mage::helper("harrodsinventory/data")->add_log("model cron : cron call");
        Mage::helper("harrodsinventory/cron")->generateHarrodsFiles();
    }

    /*public function updateHarrodsInventory()
    {

        if(!Mage::helper("harrodsinventory/config")->getModuleStatus())
        {
            Mage::helper("harrodsinventory")->add_log("Module Disabled----");
            return;
        }

        $attribute_code = "harrods_inventory";
        $attribute_details =
            Mage::getSingleton("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute_code);
        $attribute = $attribute_details->getData();
        $attrbute_id=$attribute['attribute_id'];

        if ($attrbute_id):

            $resource = Mage::getSingleton('core/resource');

            $writeAdapter = $resource->getConnection('core_write');



            $query="INSERT IGNORE INTO catalog_product_entity_varchar(entity_id,entity_type_id,attribute_id) 
SELECT en.entity_id,4,{$attrbute_id} from catalog_product_entity en where en.type_id = 'configurable'
";
            $writeAdapter->query($query);

            try {
                $writeAdapter->commit();
            }catch (Exception $e)
            {

                Mage::helper("harrodsinventory")->add_log($e->getMessage());

            }


            $query = "update catalog_product_entity_varchar a INNER JOIN(
  select link.product_id,link.parent_id,sum(varc.value) as stock from catalog_product_super_link link 
  join catalog_product_entity_varchar varc on (link.product_id = varc.entity_id and varc.attribute_id = {$attrbute_id})
  group by link.parent_id 
  ) b
  set a.value =  b.stock
  where a.entity_id = b.parent_id and a.attribute_id = {$attrbute_id}";

            $writeAdapter->query($query);

            try {
                $writeAdapter->commit();
                Mage::helper("harrodsinventory")->add_log("Parent Harrods Inventory Updated Successfully");

            }catch (Exception $e)
            {
                Mage::helper("harrodsinventory")->add_log($e->getMessage());
            }
        endif;



    }*/




}