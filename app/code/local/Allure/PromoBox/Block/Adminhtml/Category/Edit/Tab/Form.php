<?php

class Allure_PromoBox_Block_Adminhtml_Category_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('promobox_category_form', array('legend' => Mage::helper('promobox')->__('Category information')));


        $fieldset->addType('promocustomtype', 'Allure_PromoBox_Block_Adminhtml_Category_Renderer_Promohtml');
        $fieldset->addField('category_id', 'promocustomtype', array(
            'name'      => 'category_id',
            'label'     => Mage::helper('promobox')->__('Category'),
        ));


        $dateTimeFormatIso = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);


        $fieldset->addField('start_date', 'datetime', array(
            'label' => Mage::helper('promobox')->__('Starting Date'),
            'name' => 'start_date',
            'time'      => true,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => $dateTimeFormatIso,
            'class'     => 'validate-date validate-date-range date-range-custom_theme-from'

        ));
        $fieldset->addField('end_date', 'datetime', array(
            'label' => Mage::helper('promobox')->__('Ending Date'),
            'name' => 'end_date',
            'time'      => true,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => $dateTimeFormatIso,
            'class'     => 'validate-date validate-date-range date-range-custom_theme-from'
        ));

        $fieldset->addField('starting_row', 'text', array(
            'label' => Mage::helper('promobox')->__('Starting row'),
            'class' => 'required-entry validate-digits',
            'required' => true,
            'name' => 'starting_row',
            'onchange'  => 'genrateRow()',

        ));


        $field = $fieldset->addField('row_gap', 'text', array(
            'label' => Mage::helper('promobox')->__('Row Gap'),
            'class' => 'required-entry validate-digits',
            'required' => true,
            'name' => 'row_gap',
            'onchange'  => 'genrateRow()',
        ));





        $fieldset->addField('size', 'select', array(
            'label' => Mage::helper('promobox')->__('Size'),
            'name' => 'size',
            'values' => array(
                array(
                    'value' => "one_by_two",
                    'label' => Mage::helper('promobox')->__('1X2'),
                ),
                array(
                    'value' => "two_by_two",
                    'label' => Mage::helper('promobox')->__('2X2'),
                ),
            ),
            'onchange'  => 'genrateRow()',
        ));

        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('promobox')->__('Status'),
            'name' => 'status',
            'values' => array(
                array(
                    'value' => "0",
                    'label' => Mage::helper('promobox')->__('Disable'),
                ),
                array(
                    'value' => "1",
                    'label' => Mage::helper('promobox')->__('Enable'),
                ),
            ),
        ));


        $fieldset->addType('promobox', 'Allure_PromoBox_Block_Adminhtml_Category_Renderer_Promobox');
        $fieldset->addField('promobox', 'promobox', array(
            'name'      => 'promobox',
            'label'     => Mage::helper('promobox')->__('Promo Boxes'),
        ));



        $field->setAfterElementHtml('<script>
//<![CDATA[

var promoHtml=" ";

function genrateRow() {
    
    var category = document.getElementById("category_id");
    var starting_row = document.getElementById("starting_row");
    var row_gap = document.getElementById("row_gap");
    var promobox=document.getElementById("promoboxes");
    var size = document.getElementById("size");
    var size_type=size.options[size.selectedIndex].value; 
    
     var row_count=category.options[category.selectedIndex].getAttribute(\'data-row-count\');
     var number_of_rows = parseInt(row_count);
   
       if(!starting_row.value || starting_row.value=="undefined" || !row_gap.value || row_gap.value=="undefined")
          return;
 
        if(parseInt(starting_row.value)<1)
        {
        alert("Starting Row Value must be greater than 0");
        starting_row.value="";
        return;
        }
        
        if(parseInt(starting_row.value)>=number_of_rows)
        {
        alert("Starting Row Value must be less than "+number_of_rows);
        starting_row.value="";
        return;
        }
        
        if(parseInt(row_gap.value)<1)
        {
        alert("Row Gap Value must be greater than zero");
        row_gap.value="";
        return;
        }

   
       
    
    for(var i=parseInt(starting_row.value);i<=number_of_rows;i++)
    {
        promoHtml+=bannerDropdown(i,size_type);
         
        if(size_type=="two_by_two")
           i++;
   
        i=i+parseInt(row_gap.value);
    }
    promobox.innerHTML=promoHtml;        
    promoHtml="";
}
 
function bannerDropdown(row_number,size_type) {
  var options = document.getElementById(size_type).innerHTML;
  var html="";
  html+="Row Number:"+row_number+"<br> ";
  html+="<select name=\'box["+row_number+"][promobox_banner_id]\' id=banner_"+row_number+">";
  html+="<option value=\'\'>Select Banner</option>";
  html+=options;
  html+="</select><br>";
  
  html+="Side:<br> ";
  html+="<select name=\'box["+row_number+"][side]\' id=side_"+row_number+">";
  html+="<option value=\'right\'>Right side of row</option>";
  html+="<option value=\'left\'>Left side of row</option>";
  html+="</select><br><br>";

  html+="<input type=\'hidden\' name=\'box["+row_number+"][row_number]\' value="+row_number+" >";
 
  
  return html;
}


//]]>
</script>');
        if (Mage::getSingleton('adminhtml/session')->getCategoryData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getCategoryData());
            Mage::getSingleton('adminhtml/session')->setCategoryData(null);
        } elseif (Mage::registry('category_data')) {
            $tmp = Mage::registry('category_data')->getData();
            $tmp['image'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'promobox' . DS . $tmp['image'];
            $form->setValues($tmp);
        }

        return parent::_prepareForm();
    }

}