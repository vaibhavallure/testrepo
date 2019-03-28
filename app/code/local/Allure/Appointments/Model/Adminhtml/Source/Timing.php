<?php
class Allure_Appointments_Model_Adminhtml_Source_Timing
{
    public function toOptionArray()
    {
		$lower = 0;
		$upper = 24;

		$step = 0.5;

		foreach ( range( $lower, $upper, $step ) as $time ) {
			$array[] = array('value' => $time, 'label' => sprintf("%02d:%02d", $time % 60, ($time * 60) % 60 ));
		}

    	return $array;
    }

    public function toSpecialOptionArray()
    {
		$lower = 0;
		$upper = 24*60;

		$step = 20;

		foreach ( range( $lower, $upper, $step ) as $time ) {
            $hours = $time / 60;
            $minutes = $time % 60;
			$array[] = array('value' => $hours, 'label' => sprintf("%02d:%02d", $time / 60, $minutes));
		}

    	return $array;
    }
}
