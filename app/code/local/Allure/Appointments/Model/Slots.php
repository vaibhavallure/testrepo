<?php

/*

 * */

class Allure_Appointments_Model_Slots
{
    /* SLOTS[NUMBER_OF_PEOPLE][NUMBER_OF_PIERCING] */

    /*MSA-68 slot for 1 guest piercing added extra 5 min*/
    /*MT-1548 slot for 1 guest piercing remove extra 5 min*/
    const SLOTS = array(
        0=>array(0=>0),
        1 => array(0=>0, 1 => 20,2 => 30,3 => 40,4 => 50),
        2 => array(0=>0, 2 => 40,3 => 50,4 => 60,5 => 60,6 => 70,7 => 80,8 => 90),
        3 => array(0=>0, 3 => 50,4 => 50,5 => 60,6 => 70,7 => 80,8 => 80,9 => 90,10 => 90,11 => 90,12 => 90),
        4 => array(0=>0, 4 => 60,5 => 60,6 => 70,7 => 80,8 => 90,9 => 90,10 => 120,11 => 120,12 => 120,13 => 120,14 => 120,15 => 120,16 => 120)
    );

    /*MSA-68 checkup time changed from 10 to 15*/
    /*MT-1548 checkup time changed from 15 to 10*/
    const CHECKUP=10;

    public function getSlot($number_of_people,$number_of_piercing,$checkup=0)
    {
        $checkup=self::CHECKUP*$checkup;
        return (self::SLOTS[$number_of_people][$number_of_piercing]+$checkup);
    }

}