<?php
class Magecomp_Notificationbar_Model_Source_Position{
	public function toOptionArray(){
		return array(
			array('value'=>'top','label'=>'Top'),
			array('value'=>'bottom','label'=>'Bottom')
		);	
	}
}