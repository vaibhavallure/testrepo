<?php
class Allure_Catalog_Model_Cron {
    
    public function autoProcessGTINNumber(){
        
        $basePrefix='2901369';
        Mage::app()->setCurrentStore(0);
        $collection=Mage::getModel("catalog/product")->getCollection();
        $collection->addAttributeToFilter('status', array('eq' => 1));
        foreach ($collection as $product){
            $product=Mage::getModel("catalog/product")->load($product->getId());
            $gtin=$product->getGtinNumber();
            if(!empty($gtin))
                continue;
            
            $plu=$product->getTeamworkPlu();
            
            $countValue=0;
            $countValue=count(str_split(trim($plu)));
            if($countValue==2){
                $plu='000'.$plu;
            }elseif ($countValue==3){
                $plu='00'.$plu;
            }elseif ($countValue==4){
                $plu='0'.$plu;
            }
            $countValue=count(str_split(trim($plu)));
            
            if ($countValue==5){
                $gtin="";
                $checkNumber=$this->calculateChecknumber($basePrefix,$plu);
                $gtin=$basePrefix.$plu.$checkNumber;
                $product->setGtinNumber($gtin);
                $product->save();
                Mage::log($product->getSku()."::".$gtin,Zend_log::DEBUG,'gtin.log',true);
                
            }else {
                Mage::log("FOR::".$product->getSku()." PLU lenth::".$plu,Zend_log::DEBUG,'gtin_error.log',true);
            }
            
        }
    }
    
   public function calculateChecknumber($basePrefix,$plu){
        
        $tempGSTN=$basePrefix.$plu;
        $arr = str_split($tempGSTN);
        $length = count($arr);
        
        
        //Setp 1
        $step1=0;
        for($i=$length-1;$i>0;$i=$i-2){
            $step1=$step1+$arr[$i];
        }
        
        //Setp 2
        $step2=$step1*3;
        
        //Setp 3
        $step3=0;
        for($i=$length-2;$i>=00;$i=$i-2){
            $step3=$step3+$arr[$i];
        }
        
        //Setp 4
        $setp4=$step3+$step2;
        
        //Setp 5
        $digit=0;
        for ($x = 1; $x <= 100; $x++) {
            $digit=$x*10;
            
            if($digit == $setp4){
                break;
            }
            
            if($digit > $setp4){
                break;
            }
        }
        $checkNumber=$digit-$setp4;
        return $checkNumber;
    }
}