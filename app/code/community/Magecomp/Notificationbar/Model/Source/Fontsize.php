<?php
class Magecomp_Notificationbar_Model_Source_Fontsize{
	public function toOptionArray(){
		return array(
			array('value'=>'14','label'=>'Small'),
			array('value'=>'20','label'=>'Medium'),
			array('value'=>'26','label'=>'Large')
		);	
	}
}