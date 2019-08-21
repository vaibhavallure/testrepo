<?php
class Allure_Appointments_Block_Adminhtml_CustomStoreInformation extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    
    protected $_addRowButtonHtml = array();
    protected $_removeRowButtonHtml = array();
    
    /**
     * Returns html part of the setting
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        
        $html = '<div id="appointmentblocker_template" style="display:none">';
        $html .= $this->_getRowTemplateHtml();
        $html .= '</div>';
        
        $html .= '<ul id="appointmentblocker_container">';
        if ($this->_getValue('appears')) {
            foreach ($this->_getValue('appears') as $i => $f) {
                if ($i) {
                    $html .= $this->_getRowTemplateHtml($i);
                }
            }
        }
        $html .= '</ul>';
        $html .= $this->_getAddRowButtonHtml('appointmentblocker_container',
            'appointmentblocker_template', $this->__('Add New Store'));




        $html .= '<script>';
        $html .= 'jQuery(document).ready(function(){
        });

        /*function enableStoreContainer(evt,inx){
            //alert(jQuery(evt).parent().next().next().attr("class"));
            var selectVal = jQuery(evt).val();
            if(selectVal == 1){
                jQuery(evt).parent().next().next().addClass("active");
                jQuery(evt).parent().next().next().next().addClass("active");
            }else{
               jQuery(evt).parent().next().next().removeClass("active");
               jQuery(evt).parent().next().next().next().removeClass("active");
            }
        } */

        function enableCustomerEmail(evt,inx){
            var selectVal = jQuery(evt).val();
            if(selectVal == 1){
                 jQuery(evt).parent().next().addClass("active");
            }else{
                 jQuery(evt).parent().next().removeClass("active");
            }
        } 

        function enablePiercerEmail(evt,inx){
            var selectVal = jQuery(evt).val();
            if(selectVal == 1){
                 jQuery(evt).parent().next().addClass("active");
            }else{
                 jQuery(evt).parent().next().removeClass("active");
            }
        } 


        function enableAdminEmail(evt,inx){
            var selectVal = jQuery(evt).val();
            if(selectVal == 1){
                 jQuery(evt).parent().next().addClass("active");
            }else{
                 jQuery(evt).parent().next().removeClass("active");
            }
        } 

        function openCollapseView(evt){
            jQuery(evt).parent().next().toggle();
            if(jQuery(evt).parent().next().is(":hidden")){
                jQuery(evt).addClass("unopen");
                jQuery(evt).removeClass("open");
            }else{
                jQuery(evt).addClass("open");
                jQuery(evt).removeClass("unopen");
            }
        }

        ';
        $html .= '</script>';
        
        return $html;
    }
    
    /**
     * Retrieve html template for setting
     *
     * @param int $rowIndex
     * @return string
     */
    protected function _getRowTemplateHtml($rowIndex = 0)
    {
        $html = '<li>';
        
        $html .= '<div style="margin:5px 0 10px;">';
        
        $styleCss = "";
        
        $html .= '<div class="entry-edit-head collapseable appointment-collapse">
                <a id="appointments_opt-head" class="unopen" onclick="openCollapseView(this);" >'.$this->getStoreName($rowIndex).'</a>
                </div>';
        
        $html .= '<div class="fieldset appointment-fieldset unopen">';
        
        $html .= '<div class="appointment-setting-common apt-row-1">';
        $timeOptArr = array("0"=>"No","1"=>"Yes");
        $EnableStoreOpt = '';
        foreach ($timeOptArr as $key=>$name){
            $selectOpt = "";
            if($this->_getValue('enable_store/' . $rowIndex) == $key){
                $selectOpt = "selected='selected'";
            }
            $EnableStoreOpt .= '<option '.$selectOpt.' value="'.$key.'">'.$name.'</option>';
        }
        $html .= '<label for="appointments_enable_store">Enable Store </label>';
        $html .= '<select onclick="enableStoreContainer(this,'.$rowIndex.')" class="appointment-setting-select" name="'. $this->getElement()->getName().'[enable_store][]'.'" style="">'.$EnableStoreOpt.'</select>';
        $html .= '</div>';
        
        $html .= '<div class="appointment-setting-common apt-row-1">';
        $html .= '<label for="appointments_store_name">Store Name </label>';
        $html .= $this->prepareStoreData($rowIndex ,$styleCss);
        $html .= '</div>';
        
        $isEnableStore = $this->_getValue('enable_store/' . $rowIndex);
        $enableStoreClass = ($isEnableStore)?"active":"";
        
        $html .= '<hr id="enable-store-hr-'.$rowIndex.'" class="appointment-setting-hr enable-store-hr '.$enableStoreClass.'">';
        
        $html .= '<div id="enable-store-container-'.$rowIndex.'" class="enable-store-container '.$enableStoreClass.' active" >';
        
        
        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="appointments_appear_name">Appear Name </label>';
        $html .= '<input class="appointment-setting-input" style="" name="'
            . $this->getElement()->getName() . '[appears][]" value="'
                . $this->_getValue('appears/' . $rowIndex) . '" ' . $this->_getDisabled() . '/> ';
        $html .= '</div>';
        
        
        $html .= '<div class="appointment-setting-common apt-row-1 left">';
        $timeOptArr = array("12"=>"12 Hrs","24"=>"24 Hrs");
        $timeOpt = '';
        foreach ($timeOptArr as $key=>$name){
            $selectOpt = "";
            if($this->_getValue('time_pref/' . $rowIndex) == $key){
                $selectOpt = "selected='selected'";
            }
            $timeOpt .= '<option '.$selectOpt.' value="'.$key.'">'.$name.'</option>';
        }
        $html .= '<label for="appointments_time_pref">Time Pref </label>';
        $html .= '<select class="appointment-setting-select" name="'. $this->getElement()->getName().'[time_pref][]'.'" style="">'.$timeOpt.'</select>';
        $html .= '</div>';
        
        
        
        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="appointments_timezones">Time Zone </label>';
        $html .= $this->prepareTimeZone($rowIndex,$styleCss);
        $html .= '</div>';
        
        
        $html .= '<div class="appointment-setting-common apt-row-1 left">';
        $html .= '<label for="appointments_timezone_abbr">Timezone Abbr </label>';
        $html .= '<input class="appointment-setting-input" name="'
            . $this->getElement()->getName() . '[timezone_abbr][]" value="'
                . $this->_getValue('timezone_abbr/' . $rowIndex) . '" ' . $this->_getDisabled() . '/> ';
        $html .= '</div>';
        
        
        //start work time
        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="appointments_working_starting_time">Working Starting time </label>';
        $html .= $this->prepareWorkingTime($rowIndex,1,$styleCss);
        $html .= '</div>';
        
        //end work time
        $html .= '<div class="appointment-setting-common apt-row-1 left">';
        $html .= '<label for="appointments_working_ending_time">Working Ending time </label>';
        $html .= $this->prepareWorkingTime($rowIndex,2,$styleCss);
        $html .= '</div>';
        
        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="appointments_message_from">Message From </label>';
        $html .= '<input class="appointment-setting-input" name="'
        . $this->getElement()->getName() . '[message_from][]" value="'
        . $this->_getValue('message_from/' . $rowIndex) . '" ' . $this->_getDisabled() . '/> ';
        $html .= '</div>';
        
        
        
        $html .= '<div class="appointment-setting-common apt-row-1 left">';
        $html .= '<label for="appointments_store_name">Store Name </label>';
        $html .= '<input class="appointment-setting-input" name="'
            . $this->getElement()->getName() . '[store_name][]" value="'
                . $this->_getValue('store_name/' . $rowIndex) . '" ' . $this->_getDisabled() . '/> ';
        $html .= '</div>';
        
        
        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="appointments_store_phone">Store Phone </label>';
        $html .= '<input class="appointment-setting-input" name="'
            . $this->getElement()->getName() . '[store_phone][]" value="'
                . $this->_getValue('store_phone/' . $rowIndex) . '" ' . $this->_getDisabled() . '/> ';
        $html .= '</div>';

        $html .= '<div class="appointment-setting-common apt-row-1">';
        $html .= '<label for="appointments_store_email">Store Email </label>';
        $html .= '<input class="appointment-setting-input" name="'
            . $this->getElement()->getName() . '[store_email][]" value="'
                . $this->_getValue('store_email/' . $rowIndex) . '" ' . $this->_getDisabled() . '/> ';
        $html .= '</div>';
        
        
        $html .= '<div class="appointment-setting-common apt-row-1 left">';
        $html .= '<label for="appointments_store_address">Store Address </label>';
        $html .= '<textarea class="appointment-setting-textarea" name="'
            . $this->getElement()->getName() . '[store_address][]" value="'
                . $this->_getValue('store_address/' . $rowIndex) . '" ' . $this->_getDisabled() . '>'.$this->_getValue('store_address/' . $rowIndex).'</textarea> ';
        $html .= '</div>';
        
        
        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="appointments_store_hour_of_operation">Store Hours of operation </label>';
        $html .= '<textarea class="appointment-setting-textarea" name="'
            . $this->getElement()->getName() . '[store_hours_operation][]" value="'
                . $this->_getValue('store_hours_operation/' . $rowIndex) . '" ' . $this->_getDisabled() . '>'.$this->_getValue('store_hours_operation/' . $rowIndex).'</textarea> ';
        $html .= '</div>';


        $html .= '<div class="appointment-setting-common apt-row-1 left">';
        $html .= '<label for="appointments_limit_popup_message">Limit Popup Message </label>';
        $html .= '<textarea class="appointment-setting-textarea" name="'
            . $this->getElement()->getName() . '[limit_message][]" value="'
            . $this->_getValue('limit_message/' . $rowIndex) . '" ' . $this->_getDisabled() . '>'.$this->_getValue('limit_message/' . $rowIndex).'</textarea> ';
        $html .= '</div>';
        
        
        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="appointments_store_map">Store Map </label>';
        $html .= '<textarea class="appointment-setting-textarea" name="'
            . $this->getElement()->getName() . '[store_map][]" value="" ' . $this->_getDisabled() . '>'.$this->_getValue('store_map/' . $rowIndex).'</textarea> ';
        $html .= '</div>';

        $html .= '<div class="appointment-setting-common apt-row-1">';
        $html .= '<label for="digital_form_url">Digital Release URL </label>';
        $html .= '<input class="appointment-setting-input" name="'
            . $this->getElement()->getName() . '[digital_form_url][]" value="'
            . $this->_getValue('digital_form_url/' . $rowIndex) . '" ' . $this->_getDisabled() . '/> ';
        $html .= '</div>';



        $html .= '<hr class="appointment-setting-hr">';
        $html .= '<div style="text-align:center"><h3>SMS body for First Language</h3></div>';

        /*SMS 1st Language START*/
        $html .= '<div class="appointment-setting-common apt-row-1 left">';
        $html .= '<label for="appointments_book_sms_message">Book Sms Message </label>';
        $html .= '<textarea class="appointment-setting-textarea" name="'
            . $this->getElement()->getName() . '[book_sms_message][]" value="'
            . $this->_getValue('book_sms_message/' . $rowIndex) . '" ' . $this->_getDisabled() . '>'.$this->_getValue('book_sms_message/' . $rowIndex).'</textarea> ';
        $html .= '</div>';


        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="appointments_reminder_sms_message">Reminder Sms Message </label>';
        $html .= '<textarea class="appointment-setting-textarea" name="'
            . $this->getElement()->getName() . '[reminder_sms_message][]" value="'
            . $this->_getValue('reminder_sms_message/' . $rowIndex) . '" ' . $this->_getDisabled() . '>'.$this->_getValue('reminder_sms_message/' . $rowIndex).'</textarea> ';
        $html .= '</div>';



        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="appointments_week_reminder_sms_message">Week Before Reminder Sms Message </label>';
        $html .= '<textarea class="appointment-setting-textarea" name="'
            . $this->getElement()->getName() . '[week_reminder_sms_message][]" value="'
            . $this->_getValue('week_reminder_sms_message/' . $rowIndex) . '" ' . $this->_getDisabled() . '>'.$this->_getValue('week_reminder_sms_message/' . $rowIndex).'</textarea> ';
        $html .= '</div>';


        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="appointments_day_reminder_sms_message">Day Before Reminder Sms Message </label>';
        $html .= '<textarea class="appointment-setting-textarea" name="'
            . $this->getElement()->getName() . '[day_reminder_sms_message][]" value="'
            . $this->_getValue('day_reminder_sms_message/' . $rowIndex) . '" ' . $this->_getDisabled() . '>'.$this->_getValue('day_reminder_sms_message/' . $rowIndex).'</textarea> ';
        $html .= '</div>';


        $html .= '<div class="appointment-setting-common apt-row-1 left">';
        $html .= '<label for="appointments_modified_sms_message">Modified Sms Message </label>';
        $html .= '<textarea class="appointment-setting-textarea" name="'
            . $this->getElement()->getName() . '[modified_sms_message][]" value="'
            . $this->_getValue('modified_sms_message/' . $rowIndex) . '" ' . $this->_getDisabled() . '>'.$this->_getValue('modified_sms_message/' . $rowIndex).'</textarea> ';
        $html .= '</div>';


        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="appointments_cancel_sms_message">Cancel Sms Message </label>';
        $html .= '<textarea class="appointment-setting-textarea" name="'
            . $this->getElement()->getName() . '[cancel_sms_message][]" value="'
            . $this->_getValue('cancel_sms_message/' . $rowIndex) . '" ' . $this->_getDisabled() . '>'.$this->_getValue('cancel_sms_message/' . $rowIndex).'</textarea> ';
        $html .= '</div>';


        $html .= '<div class="appointment-setting-common apt-row-1 left">';
        $html .= '<label for="release_sms_message">Release Sms Message </label>';
        $html .= '<textarea class="appointment-setting-textarea" name="'
            . $this->getElement()->getName() . '[release_sms_message][]" value="'
            . $this->_getValue('release_sms_message/' . $rowIndex) . '" ' . $this->_getDisabled() . '>'.$this->_getValue('release_sms_message/' . $rowIndex).'</textarea> ';
        $html .= '</div>';

        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="week_release_sms_message">Release Week Reminder Sms Message </label>';
        $html .= '<textarea class="appointment-setting-textarea" name="'
            . $this->getElement()->getName() . '[week_release_sms_message][]" value="'
            . $this->_getValue('week_release_sms_message/' . $rowIndex) . '" ' . $this->_getDisabled() . '>'.$this->_getValue('week_release_sms_message/' . $rowIndex).'</textarea> ';
        $html .= '</div>';

        $html .= '<div class="appointment-setting-common apt-row-1">';
        $html .= '<label for="day_release_sms_message">Release Day Reminder Sms Message </label>';
        $html .= '<textarea class="appointment-setting-textarea" name="'
            . $this->getElement()->getName() . '[day_release_sms_message][]" value="'
            . $this->_getValue('day_release_sms_message/' . $rowIndex) . '" ' . $this->_getDisabled() . '>'.$this->_getValue('day_release_sms_message/' . $rowIndex).'</textarea> ';
        $html .= '</div>';

/*SMS 1st Language End*/

        $html .= '<hr class="appointment-setting-hr">';
        $html .= '<div style="text-align:center"><h3>SMS body for Second Language</h3></div>';
        /*SMS 2nd Language START*/
        $html .= '<div class="appointment-setting-common apt-row-1 left">';
        $html .= '<label for="appointments_book_sms_message_second">Book Sms Message </label>';
        $html .= '<textarea class="appointment-setting-textarea" name="'
            . $this->getElement()->getName() . '[book_sms_message_second][]" value="'
            . $this->_getValue('book_sms_message_second/' . $rowIndex) . '" ' . $this->_getDisabled() . '>'.$this->_getValue('book_sms_message_second/' . $rowIndex).'</textarea> ';
        $html .= '</div>';


        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="appointments_reminder_sms_message_second">Reminder Sms Message </label>';
        $html .= '<textarea class="appointment-setting-textarea" name="'
            . $this->getElement()->getName() . '[reminder_sms_message_second][]" value="'
            . $this->_getValue('reminder_sms_message_second/' . $rowIndex) . '" ' . $this->_getDisabled() . '>'.$this->_getValue('reminder_sms_message_second/' . $rowIndex).'</textarea> ';
        $html .= '</div>';



        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="appointments_week_reminder_sms_message_second">Week Before Reminder Sms Message </label>';
        $html .= '<textarea class="appointment-setting-textarea" name="'
            . $this->getElement()->getName() . '[week_reminder_sms_message_second][]" value="'
            . $this->_getValue('week_reminder_sms_message_second/' . $rowIndex) . '" ' . $this->_getDisabled() . '>'.$this->_getValue('week_reminder_sms_message_second/' . $rowIndex).'</textarea> ';
        $html .= '</div>';


        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="appointments_day_reminder_sms_message_second">Day Before Reminder Sms Message </label>';
        $html .= '<textarea class="appointment-setting-textarea" name="'
            . $this->getElement()->getName() . '[day_reminder_sms_message_second][]" value="'
            . $this->_getValue('day_reminder_sms_message_second/' . $rowIndex) . '" ' . $this->_getDisabled() . '>'.$this->_getValue('day_reminder_sms_message_second/' . $rowIndex).'</textarea> ';
        $html .= '</div>';


        $html .= '<div class="appointment-setting-common apt-row-1 left">';
        $html .= '<label for="appointments_modified_sms_message_second">Modified Sms Message </label>';
        $html .= '<textarea class="appointment-setting-textarea" name="'
            . $this->getElement()->getName() . '[modified_sms_message_second][]" value="'
            . $this->_getValue('modified_sms_message_second/' . $rowIndex) . '" ' . $this->_getDisabled() . '>'.$this->_getValue('modified_sms_message_second/' . $rowIndex).'</textarea> ';
        $html .= '</div>';


        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="appointments_cancel_sms_message_second">Cancel Sms Message </label>';
        $html .= '<textarea class="appointment-setting-textarea" name="'
            . $this->getElement()->getName() . '[cancel_sms_message_second][]" value="'
            . $this->_getValue('cancel_sms_message_second/' . $rowIndex) . '" ' . $this->_getDisabled() . '>'.$this->_getValue('cancel_sms_message_second/' . $rowIndex).'</textarea> ';
        $html .= '</div>';


        $html .= '<div class="appointment-setting-common apt-row-1 left">';
        $html .= '<label for="release_sms_message_second">Release Sms Message </label>';
        $html .= '<textarea class="appointment-setting-textarea" name="'
            . $this->getElement()->getName() . '[release_sms_message_second][]" value="'
            . $this->_getValue('release_sms_message_second/' . $rowIndex) . '" ' . $this->_getDisabled() . '>'.$this->_getValue('release_sms_message_second/' . $rowIndex).'</textarea> ';
        $html .= '</div>';

        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="week_release_sms_message_second">Release Week Reminder Sms Message </label>';
        $html .= '<textarea class="appointment-setting-textarea" name="'
            . $this->getElement()->getName() . '[week_release_sms_message_second][]" value="'
            . $this->_getValue('week_release_sms_message_second/' . $rowIndex) . '" ' . $this->_getDisabled() . '>'.$this->_getValue('week_release_sms_message_second/' . $rowIndex).'</textarea> ';
        $html .= '</div>';

        $html .= '<div class="appointment-setting-common apt-row-1">';
        $html .= '<label for="day_release_sms_message_second">Release Day Reminder Sms Message </label>';
        $html .= '<textarea class="appointment-setting-textarea" name="'
            . $this->getElement()->getName() . '[day_release_sms_message_second][]" value="'
            . $this->_getValue('day_release_sms_message_second/' . $rowIndex) . '" ' . $this->_getDisabled() . '>'.$this->_getValue('day_release_sms_message_second/' . $rowIndex).'</textarea> ';
        $html .= '</div>';

        /*SMS 2nd Language End*/

        $html .= '<hr class="appointment-setting-hr">';

        $html .= '<div style="text-align:center"><h3>Email template settings for First Language</h3></div>';
        /*1st Language EMAIL SETTINGS*/
        $html .= '<div class="appointment-setting-common apt-row-1">';
        $timeOptArr = array("0"=>"No","1"=>"Yes");
        $custEmailEnableOpt = '';
        foreach ($timeOptArr as $key=>$name){
            $selectOpt = "";
            if($this->_getValue('customer_email_enable/' . $rowIndex) == $key){
                $selectOpt = "selected='selected'";
            }
            $custEmailEnableOpt .= '<option '.$selectOpt.' value="'.$key.'">'.$name.'</option>';
        }
        $html .= '<label for="appointments_customer_email_enable">Enable Customer Email </label>';
        $html .= '<select onclick="enableCustomerEmail(this,'.$rowIndex.')" class="appointment-setting-select" name="'. $this->getElement()->getName().'[customer_email_enable][]'.'" style="">'.$custEmailEnableOpt.'</select>';
        $html .= '</div>';
        
        $isCustomerEmailActive = $this->_getValue('customer_email_enable/' . $rowIndex);
        $customerEmailContainerClass = ($isCustomerEmailActive)?"active":"";
        
        $html .= '<div id="customer-email-container-'.$rowIndex.'" class="customer-email-container '.$customerEmailContainerClass.'">';
        
        $html .= '<div class="appointment-setting-common apt-row-1 left">';
        $html .= '<label for="customer_eamil_template_appointment">Customer Email Temaplate Appointment </label>';
        $html .= $this->prepareEmailTemplate($rowIndex,null,'');
        $html .= '</div>';
        
        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="customer_eamil_template_appointment_remind">Customer Email Temaplate Appointment Reminder </label>';
        $html .= $this->prepareEmailTemplate($rowIndex,"remind");
        $html .= '</div>';


        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="customer_eamil_template_appointment_remind_day">Customer Email Temaplate Appointment Reminder Day</label>';
        $html .= $this->prepareEmailTemplate($rowIndex,"remind_day");
        $html .= '</div>';


        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="customer_eamil_template_appointment_remind">Customer Email Temaplate Appointment Reminder Week</label>';
        $html .= $this->prepareEmailTemplate($rowIndex,"remind_week");
        $html .= '</div>';

        
        $html .= '<div class="appointment-setting-common apt-row-1 left">';
        $html .= '<label for="customer_eamil_template_appointment_cancel">Customer Email Temaplate Appointment Cancel </label>';
        $html .= $this->prepareEmailTemplate($rowIndex,"cancel");
        $html .= '</div>';
        
        
        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="customer_eamil_template_appointment_modify">Customer Email Temaplate Appointment Modify </label>';
        $html .= $this->prepareEmailTemplate($rowIndex,"modify");
        $html .= '</div>';

        $html .= '<div class="appointment-setting-common apt-row-1 left">';
        $html .= '<label for="customer_email_template_release">Customer Email : Release</label>';
        $html .= $this->prepareEmailTemplate($rowIndex,"release");
        $html .= '</div>';

        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="customer_email_template_release_remind_week">Customer Email : Release Reminder Week </label>';
        $html .= $this->prepareEmailTemplate($rowIndex,"release_reminder_week");
        $html .= '</div>';

        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="customer_email_template_release_remind_day">Customer Email : Release Reminder Day</label>';
        $html .= $this->prepareEmailTemplate($rowIndex,"release_reminder_day");
        $html .= '</div>';
        
        $html .= '</div>';
        /*End of 1st Language Email Settings*/

        $html .= '<hr class="appointment-setting-hr">';

        /*2nd Language EMAIL SETTINGS*/
        $html .= '<div style="text-align:center"><h3>Email template settings for Second Language</h3></div>';
        $html .= '<div class="appointment-setting-common apt-row-1">';
        $timeOptArr = array("0"=>"No","1"=>"Yes");
        $custEmailEnableOptSecond = '';
        foreach ($timeOptArr as $key=>$name){
            $selectOpt = "";
            if($this->_getValue('customer_email_enable_second/' . $rowIndex) == $key){
                $selectOpt = "selected='selected'";
            }
            $custEmailEnableOptSecond .= '<option '.$selectOpt.' value="'.$key.'">'.$name.'</option>';
        }
        $html .= '<label for="appointments_customer_email_enable_second">Enable Customer Email </label>';
        $html .= '<select onclick="enableCustomerEmail(this,'.$rowIndex.')" class="appointment-setting-select" name="'. $this->getElement()->getName().'[customer_email_enable_second][]'.'" style="">'.$custEmailEnableOptSecond.'</select>';
        $html .= '</div>';

        $isCustomerEmailActiveSecond = $this->_getValue('customer_email_enable_second/' . $rowIndex);
        $customerEmailContainerClassSecond = ($isCustomerEmailActiveSecond)?"active":"";

        $html .= '<div id="customer-email-container-'.$rowIndex.'_second" class="customer-email-container '.$customerEmailContainerClassSecond.'">';

        $html .= '<div class="appointment-setting-common apt-row-1 left">';
        $html .= '<label for="customer_email_template_appointment_second">Customer Email Temaplate Appointment </label>';
        $html .= $this->prepareEmailTemplate($rowIndex,null,"_second");
        $html .= '</div>';

        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="customer_email_template_appointment_remind_second">Customer Email Temaplate Appointment Reminder </label>';
        $html .= $this->prepareEmailTemplate($rowIndex,"remind","_second");
        $html .= '</div>';


        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="customer_email_template_appointment_remind_day_second">Customer Email Temaplate Appointment Reminder Day</label>';
        $html .= $this->prepareEmailTemplate($rowIndex,"remind_day","_second");
        $html .= '</div>';


        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="customer_email_template_appointment_remind_second">Customer Email Temaplate Appointment Reminder Week</label>';
        $html .= $this->prepareEmailTemplate($rowIndex,"remind_week","_second");
        $html .= '</div>';


        $html .= '<div class="appointment-setting-common apt-row-1 left">';
        $html .= '<label for="customer_email_template_appointment_cancel_second">Customer Email Temaplate Appointment Cancel </label>';
        $html .= $this->prepareEmailTemplate($rowIndex,"cancel","_second");
        $html .= '</div>';


        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="customer_email_template_appointment_modify_second">Customer Email Temaplate Appointment Modify </label>';
        $html .= $this->prepareEmailTemplate($rowIndex,"modify","_second");
        $html .= '</div>';

        $html .= '<div class="appointment-setting-common apt-row-1 left">';
        $html .= '<label for="customer_email_template_release">Customer Email : Release</label>';
        $html .= $this->prepareEmailTemplate($rowIndex,"release","_second");
        $html .= '</div>';

        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="customer_email_template_release_remind_week_second">Customer Email : Release Reminder Week </label>';
        $html .= $this->prepareEmailTemplate($rowIndex,"release_reminder_week","_second");
        $html .= '</div>';

        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="customer_email_template_release_remind_day_second">Customer Email : Release Reminder Day</label>';
        $html .= $this->prepareEmailTemplate($rowIndex,"release_reminder_day","_second");
        $html .= '</div>';

        $html .= '</div>';
//        /*End of 2nd Language Email Settings*/
        $html .= '<hr class="appointment-setting-hr">';

        
        $html .= '<div class="appointment-setting-common apt-row-1">';
        $timeOptArr = array("0"=>"No","1"=>"Yes");
        $custEmailEnableOpt = '';
        foreach ($timeOptArr as $key=>$name){
            $selectOpt = "";
            if($this->_getValue('piercer_email_enable/' . $rowIndex) == $key){
                $selectOpt = "selected='selected'";
            }
            $custEmailEnableOpt .= '<option '.$selectOpt.' value="'.$key.'">'.$name.'</option>';
        }
        $html .= '<label for="appointments_piercer_email_enable">Piercer Email Enable </label>';
        $html .= '<select onclick="enablePiercerEmail(this,'.$rowIndex.')" class="appointment-setting-select" name="'. $this->getElement()->getName().'[piercer_email_enable][]'.'" style="">'.$custEmailEnableOpt.'</select>';
        $html .= '</div>';
        
        $isPiercerEmailEnable = $this->_getValue('piercer_email_enable/' . $rowIndex);
        $piercerContainerClass = ($isPiercerEmailEnable)?"active":"";
        
        $html .= '<div id="piercer-email-container-'.$rowIndex.'" class="piercer-email-container '.$piercerContainerClass.'">';
        
        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="piercer_welcome_email">Piercer Welcome Email  </label>';
        $html .= $this->preparePircerEmailTemplate($rowIndex,"welcome");
        $html .= '</div>';
        
        
        $html .= '<div class="appointment-setting-common apt-row-1 left">';
        $html .= '<label for="piercer_email_template">Piercer Email Template  </label>';
        $html .= $this->preparePircerEmailTemplate($rowIndex);
        $html .= '</div>';
        
        
        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="piercer_email_template_cancel">Piercer Email Template Cancel  </label>';
        $html .= $this->preparePircerEmailTemplate($rowIndex,"cancel");
        $html .= '</div>';
        
        $html .= '<div class="appointment-setting-common apt-row-1 ">';
        $html .= '<label for="piercer_email_template_modify">Piercer Email Template Modify  </label>';
        $html .= $this->preparePircerEmailTemplate($rowIndex,"modify");
        $html .= '</div>';
        
        $html .= '</div>';
        
        $html .= '<hr class="appointment-setting-hr">';
        
        $html .= '<div class="appointment-setting-common apt-row-1">';
        $timeOptArr = array("0"=>"No","1"=>"Yes");
        $custEmailEnableOpt = '';
        foreach ($timeOptArr as $key=>$name){
            $selectOpt = "";
            if($this->_getValue('admin_email_enable/' . $rowIndex) == $key){
                $selectOpt = "selected='selected'";
            }
            $custEmailEnableOpt .= '<option '.$selectOpt.' value="'.$key.'">'.$name.'</option>';
        }
        $html .= '<label for="admin_email_enable">Admin Email Enable </label>';
        $html .= '<select onclick="enableAdminEmail(this,'.$rowIndex.')" class="appointment-setting-select" name="'. $this->getElement()->getName().'[admin_email_enable][]'.'" style="">'.$custEmailEnableOpt.'</select>';
        $html .= '</div>';
        
        $isAdminEmailEnable = $this->_getValue('admin_email_enable/' . $rowIndex);
        $adminContainerClass = ($isAdminEmailEnable)?"active":"";
        
        $html .= '<div id="admin-email-container-'.$rowIndex.'" class="admin-email-container '.$adminContainerClass.'">';
        
        $html .= '<div class="appointment-setting-common apt-row-1 left">';
        $html .= '<label for="admin_email_template">Admin Email Template  </label>';
        $html .= $this->prepareAdminEmailTemplate($rowIndex);
        $html .= '</div>';
        
        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="admin_email_template_cancel">Admin Email Template Cancel  </label>';
        $html .= $this->prepareAdminEmailTemplate($rowIndex,"cancel");
        $html .= '</div>';
        
        $html .= '<div class="appointment-setting-common apt-row-1 left">';
        $html .= '<label for="admin_email_template_modify">Admin Email Template Modify  </label>';
        $html .= $this->prepareAdminEmailTemplate($rowIndex,"modify");
        $html .= '</div>';
        
        $html .= '<div class="appointment-setting-common apt-row-1 right">';
        $html .= '<label for="admin_email_id">Admin Email Id </label>';
        $html .= '<input class="appointment-setting-input" name="'
            . $this->getElement()->getName() . '[admin_email_id][]" value="'
                . $this->_getValue('admin_email_id/' . $rowIndex) . '" ' . $this->_getDisabled() . '/> ';
        $html .= '</div>';
        
        $html .= '</div>';
        
        $html .= '<hr class="appointment-setting-hr">';
        
        
        $html .= '<div class="appointment-setting-common apt-row-1">';
        $html .= '<label for="piercers_available">Piercers Available </label>';
        $html .= '<textarea class="appointment-setting-textarea" name="'
            . $this->getElement()->getName() . '[piercers_available][]" value="'
                . $this->_getValue('piercers_available/' . $rowIndex) . '" ' . $this->_getDisabled() . '>'.$this->_getValue('piercers_available/' . $rowIndex).'</textarea> ';
        $html .= '</div>';
        
        
        $html .= '<div class="appointment-setting-common apt-row-1">';
        $html .= '<label for="piercing-pricing-block">Piercing Pricing </label>';
        $html .= $this->getCmsBlock($rowIndex);
        $html .= '</div>';
        
        
        
        
        
        /* $html .= '<div class="appointment-setting-common apt-row-1">';
        $html .= '<label for="piercing-pricing-block">Piercing Pricing </label>';
        $html .= '<input class="appointment-setting-input" name="'
            . $this->getElement()->getName() . '[piercing_pricing_block][]" value="'
                . $this->_getValue('piercing_pricing_block/' . $rowIndex) . '" ' . $this->_getDisabled() . '/> ';
        $html .= '</div>'; */
        
        
        
        $html .= '</div>';
        
        $html .= $this->_getRemoveRowButtonHtml();
        
        $html .= '</div>';
        
        $html .= '</div>';
        
        //$html .= $this->_getRemoveRowButtonHtml();
        
        
        //$html .= '</div>';
        
        $html .= '</li>';
                
        return $html;
    }
    
    protected function _getDisabled()
    {
        return $this->getElement()->getDisabled() ? ' disabled' : '';
    }
    
    protected function _getValue($key)
    {
        return $this->getElement()->getData('value/' . $key);
    }
    
    protected function _getSelected($key, $value)
    {
        return $this->getElement()->getData('value/' . $key) == $value ? 'selected="selected"' : '';
    }
    
    protected function _getAddRowButtonHtml($container, $template, $title='Add')
    {
        if (!isset($this->_addRowButtonHtml[$container])) {
            $this->_addRowButtonHtml[$container] = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('add ' . $this->_getDisabled())
            ->setLabel($this->__($title))
            ->setOnClick("Element.insert($('" . $container . "'), {bottom: $('" . $template . "').innerHTML});var selector = jQuery('ul#appointmentblocker_container li:last-child');selector.find('.fieldset').removeClass('unopen');selector.find('#appointments_opt-head').text('Store Settings')")
            ->setDisabled($this->_getDisabled())
            ->toHtml();
        }
        return $this->_addRowButtonHtml[$container];
    }
    
    protected function _getRemoveRowButtonHtml($selector = 'li', $title = 'Delete')
    {
        if (!$this->_removeRowButtonHtml) {
            $this->_removeRowButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('delete v-middle ' . $this->_getDisabled())
            ->setLabel($this->__($title))
            ->setOnClick("if(confirm('Are you sure you want to remove the store settings?')){Element.remove($(this).up('" . $selector . "'))}else{return false;}")
            ->setDisabled($this->_getDisabled())
            ->toHtml();
        }
        return $this->_removeRowButtonHtml;
    }
    
    private function getStoreName($rowIndex){
        $allStores = array();
        if (Mage::helper('core')->isModuleEnabled('Allure_Virtualstore')){
            $virtualStoreHelper = Mage::helper("allure_virtualstore");
            $allStores = $virtualStoreHelper->getVirtualStores();
        }else{
            $allStores = Mage::app()->getStores();
        }
        $flag = false;
        $storeName = "Store";
        foreach ($allStores as $_eachStoreId => $val)
        {
            $_storeName = $val->getName();
            $_storeId   = $val->getId();
            $selectedClass = "";
            if($this->_getValue('stores/' . $rowIndex) == $_storeId ){
                $flag = true;
                $storeName = $_storeName;
                break;
            }
        }
        $storeName .= " Settings";
        return $storeName;
    }
    
    private function prepareStoreData($rowIndex ,$styleCss){
        $allStores = array();
        if (Mage::helper('core')->isModuleEnabled('Allure_Virtualstore')){
            $virtualStoreHelper = Mage::helper("allure_virtualstore");
            $allStores = $virtualStoreHelper->getVirtualStores();
        }else{
            $allStores = Mage::app()->getStores();
        }

        foreach ($allStores as $_eachStoreId => $val)
        {
            $_storeName = $val->getName();
            $_storeId   = $val->getId();
            $selectedClass = "";
            if($this->_getValue('stores/' . $rowIndex) == $_storeId ){
                $selectedClass = "selected='selected'";
            }
            
            $countryStr .= '<option '.$selectedClass.'  value="'.$_storeId.'">'.$_storeName .'</option>';
        }
        return '<select class="appointment-setting-select" name="' . $this->getElement()->getName().'[stores][]' . '" style="">'.$countryStr.'</select>';
    }
    
    
    private function prepareTimeZone($rowIndex,$styleCss){
        $countryStr = "";
        $timezoneList = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        foreach (Mage::app()->getLocale()->getOptionTimezones() as $_eachTimeZoneId => $val)
        {
            $selectedClass = "";
            if($this->_getValue('timezones/' . $rowIndex) == $val[value] ){
                $selectedClass = "selected='selected'";
            }
            $zoneStr .= '<option '.$selectedClass.' value="'.$val[value].'">'.$val[label].'</option>';
        }
        return '<select class="appointment-setting-select" name="' . $this->getElement()->getName().'[timezones][]' . '" style="">'.$zoneStr.'</select>';
        
    }
    
    private function prepareWorkingTime($rowIndex,$wh=1,$styleCss){
        $arr = Mage::getSingleton("appointments/adminhtml_source_timing")->toOptionArray();
        
        $work_state = "start_work_time";
        if($wh == 2){
            $work_state = "end_work_time";
        }

        foreach ($arr as $val)
        {
            $selectedClass = "";
            if($this->_getValue($work_state.'/' . $rowIndex) == $val[value] ){
                $selectedClass = "selected='selected'";
            }
            $zoneStr .= '<option '.$selectedClass.' value="'.$val[value].'">'.$val[label].'</option>';
        }
        return '<select class="appointment-setting-select" name="' . $this->getElement()->getName().'['.$work_state.'][]' . '" style="">'.$zoneStr.'</select>';
        
    }
    
    
    private function prepareEmailTemplate($rowIndex,$state = null,$type = null){
        $arr = Mage::getSingleton("adminhtml/system_config_source_email_template")->toOptionArray();
        
        $email_template = "email_template_appointment".$type;
        if($state == "cancel"){
            $email_template = "email_template_appointment_cancel".$type;
        }elseif($state == "modify"){
            $email_template = "email_template_appointment_modify".$type;
        }elseif($state == "remind"){
            $email_template = "email_template_appointment_remind".$type;
        }elseif($state == "remind_day"){
            $email_template = "email_template_appointment_remind_day".$type;
        }elseif($state == "remind_week"){
            $email_template = "email_template_appointment_remind_week".$type;
        }elseif($state == "release"){
            $email_template = "email_template_release".$type;
        }elseif($state == "release_reminder_week"){
            $email_template = "email_template_release_remind_week".$type;
        }elseif($state == "release_reminder_day"){
            $email_template = "email_template_release_remind_day".$type;
        }


        foreach ($arr as $val)
        {
            $selectedClass = "";
            if($this->_getValue($email_template.'/' . $rowIndex) == $val[value] ){
                $selectedClass = "selected='selected'";
            }
            $zoneStr .= '<option '.$selectedClass.' value="'.$val[value].'">'.$val[label].'</option>';
        }
        return '<select class="appointment-setting-select" name="' . $this->getElement()->getName().'['.$email_template.'][]' . '" style="">'.$zoneStr.'</select>';
        
    }
    
    
    private function preparePircerEmailTemplate($rowIndex,$state = null){
        $arr = Mage::getSingleton("adminhtml/system_config_source_email_template")->toOptionArray();
        
        $email_template = "piercer_email_template";
        if($state == "cancel"){
            $email_template = "piercer_email_template_cancel";
        }elseif($state == "modify"){
            $email_template = "piercer_email_template_modify";
        }elseif($state == "welcome"){
            $email_template = "piercer_email_template_welcome";
        }

        foreach ($arr as $val)
        {
            $selectedClass = "";
            if($this->_getValue($email_template.'/' . $rowIndex) == $val[value] ){
                $selectedClass = "selected='selected'";
            }
            $zoneStr .= '<option '.$selectedClass.' value="'.$val[value].'">'.$val[label].'</option>';
        }
        return '<select class="appointment-setting-select" name="' . $this->getElement()->getName().'['.$email_template.'][]' . '" style="">'.$zoneStr.'</select>';
        
    }
    
    
    private function prepareAdminEmailTemplate($rowIndex,$state = null){
        $arr = Mage::getSingleton("adminhtml/system_config_source_email_template")->toOptionArray();
        
        $email_template = "admin_email_template";
        if($state == "cancel"){
            $email_template = "admin_email_template_cancel";
        }elseif($state == "modify"){
            $email_template = "admin_email_template_modify";
        }

        foreach ($arr as $val)
        {
            $selectedClass = "";
            if($this->_getValue($email_template.'/' . $rowIndex) == $val[value] ){
                $selectedClass = "selected='selected'";
            }
            $zoneStr .= '<option '.$selectedClass.' value="'.$val[value].'">'.$val[label].'</option>';
        }
        return '<select class="appointment-setting-select" name="' . $this->getElement()->getName().'['.$email_template.'][]' . '" style="">'.$zoneStr.'</select>';
        
    }
    
    
    private function getCmsBlock($rowIndex){

        $collection = Mage::getModel('cms/block')->getCollection()
        ->addFieldToFilter('identifier',array('like'=>'%appointment%'));
        foreach ($collection as $block) {
            $selectedClass = "";
            if($this->_getValue('piercing_pricing_block'.'/' . $rowIndex) == $block->getIdentifier() ){
                $selectedClass = "selected='selected'";
            }
            $zoneStr .= '<option '.$selectedClass.' value="'.$block->getIdentifier().'">'.$block->getTitle().'</option>';
        }
        return '<select class="appointment-setting-select" name="' . $this->getElement()->getName().'['.'piercing_pricing_block'.'][]' . '" style="">'.$zoneStr.'</select>';
        
    }
    
    
    
    /**
     * Enter description here...
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $id = $element->getHtmlId();
        
        //$html = '<td class="label"><label for="'.$id.'">'.$element->getLabel().'</label></td>';
        
        //$isDefault = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $isMultiple = $element->getExtType()==='multiple';
        
        // replace [value] with [inherit]
        $namePrefix = preg_replace('#\[value\](\[\])?$#', '', $element->getName());
        
        $options = $element->getValues();
        
        $addInheritCheckbox = false;
        if ($element->getCanUseWebsiteValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = $this->__('Use Website');
        }
        elseif ($element->getCanUseDefaultValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = $this->__('Use Default');
        }
        
        if ($addInheritCheckbox) {
            $inherit = $element->getInherit()==1 ? 'checked="checked"' : '';
            if ($inherit) {
                $element->setDisabled(true);
            }
        }
        
        if ($element->getTooltip()) {
            $html .= '<td class="value with-tooltip">';
            $html .= $this->_getElementHtml($element);
            $html .= '<div class="field-tooltip"><div>' . $element->getTooltip() . '</div></div>';
        } else {
            $html .= '<td class="value appointments-setting-custom_column" >';
            $html .= $this->_getElementHtml($element);
        };
        if ($element->getComment()) {
            $html.= '<p class="note"><span>'.$element->getComment().'</span></p>';
        }
        $html.= '</td>';
        
        if ($addInheritCheckbox) {
            
            $defText = $element->getDefaultValue();
            if ($options) {
                $defTextArr = array();
                foreach ($options as $k=>$v) {
                    if ($isMultiple) {
                        if (is_array($v['value']) && in_array($k, $v['value'])) {
                            $defTextArr[] = $v['label'];
                        }
                    } elseif (isset($v['value'])) {
                        if ($v['value'] == $defText) {
                            $defTextArr[] = $v['label'];
                            break;
                        }
                    } elseif (!is_array($v)) {
                        if ($k == $defText) {
                            $defTextArr[] = $v;
                            break;
                        }
                    }
                }
                $defText = join(', ', $defTextArr);
            }
            
            // default value
            $html.= '<td class="use-default">';
            $html.= '<input id="' . $id . '_inherit" name="'
                . $namePrefix . '[inherit]" type="checkbox" value="1" class="checkbox config-inherit" '
                    . $inherit . ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" /> ';
                    $html.= '<label for="' . $id . '_inherit" class="inherit" title="'
                        . htmlspecialchars($defText) . '">' . $checkboxLabel . '</label>';
            $html.= '</td>';
        }
        
        /* $html.= '<td class="scope-label">';
        if ($element->getScope()) {
            $html .= $element->getScopeLabel();
        }
        $html.= '</td>';
        
        $html.= '<td class="">';
        if ($element->getHint()) {
            $html.= '<div class="hint" >';
            $html.= '<div style="display: none;">' . $element->getHint() . '</div>';
            $html.= '</div>';
        }
        $html.= '</td>'; */
        
        return $this->_decorateRowHtml($element, $html);
    }
}