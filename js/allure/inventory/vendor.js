var $j = jQuery;
if (typeof Allure == "undefined") {
	   var Allure = {};
}
$j(document).ready(function (){
	var data=getStoredData();
	$j(".add_item").change(function(){
		var selected = [];
        var ischecked= $j(this).is(':checked');
        
        var id=parseInt($j(this).attr('id'));
        var qty=parseInt($j('#proposed_qty_'+id).val());
        var is_custom=parseInt($j('#is_custom_'+id).val());
      // var date=
    	var vendor_sku=$j('#vendor_sku_'+id).val();
    	var key=Allure.ViewPurchaseOrderFormKey;
        var comment=$j('#vendor_comment_'+id).val();
        var po_id=$j('#order_id').val();
        var date=$j('#date_'+id).val();
        
        //alert("id: "+id+" qty: "+qty+" is_custom:"+is_custom+" vendor_sku: "+vendor_sku+" comment: "+comment);

        if(ischecked){
	        	var include = 1;
	        	var item ={
	        			id,qty,comment,include,date,po_id,is_custom,vendor_sku,key,comment
	        	};
	        	$j.ajax({
	    	        url: Allure.AddVendorItem,
	    	        dataType : 'json',
	    			type : 'POST',
	    			data: {'item':item,'form_key':key},
	    			beforeSend: function() { $j('#loading-mask').show(); },
	    	        complete: function() { $j('#loading-mask').hide(); },
	    	        success: function(data) {
	    	            $j('#proposed_qty_'+id).prop('disabled', true);
	    	            $j('#vendor_comment_'+id).prop('disabled', true);
	    	            $j('#vendor_sku_'+id).prop('disabled', true);
	    	            $j('#date_'+id).prop('disabled', true);
	    	        }
	    	    });
	        
        }else{
        	var include = 0;
        	var item ={
        			id,qty,comment,po_id,include,date,is_custom,vendor_sku,key,comment
        	};
        	$j.ajax({
    	        url: Allure.AddVendorItem,
    	        dataType : 'json',
    			type : 'POST',
    			data: {'item':item,'form_key':key},
    			beforeSend: function() { $j('#loading-mask').show(); },
    	        complete: function() { $j('#loading-mask').hide(); },
    	        success: function(data) {
    	        	$j('#proposed_qty_'+id).prop('disabled', false);
    	            $j('#vendor_comment_'+id).prop('disabled', false);
    	            $j('#vendor_sku_'+id).prop('disabled', false);
    	            $j('#date_'+id).prop('disabled', false);
    	        	
    	        }
    	    });
        	
         }
    }); 
	

//Update total 
function updateTotal(data){
	
	data.forEach(function(value) {
	    $j('#'+value['product_id']).prop( "checked", true );
	    
	    $j('#proposed_qty_'+value['product_id']).val(value['shipped_qty']);
	    $j('#proposed_qty_'+value['product_id']).prop('disabled', true);
	    
	    $j('#vendor_comment_'+value['product_id']).val(value['vendor_comment']);
	    $j('#vendor_comment_'+value['product_id']).prop('disabled', true);
	    
	    
	    $j('#vendor_sku_'+value['product_id']).val(value['vendor_sku']);
	    $j('#vendor_sku_'+value['product_id']).prop('disabled', true);
	    
	    var date=formatDate(value['ship_date']);
	    $j('#date_'+value['product_id']).val(date);
	    $j('#date_'+value['product_id']).prop('disabled', true);
	});

}	

function formatDate(date){

	if (date != null){
		var dateObj = new Date(date);
		var month = dateObj.getUTCMonth() + 1; //months from 1-12
		var day = dateObj.getUTCDate();
		var year = dateObj.getUTCFullYear();
		return month+'/'+day+'/'+year;
	}else{
		return '';
	}
}

 function getStoredData(){
    var po_id=$j('#order_id').val();
	var key=Allure.ViewPurchaseOrderFormKey;
	$j.ajax({
        url: Allure.GetVendorSelectedItems,
        dataType : 'json',
		type : 'POST',
		data: {'form_key':key,'po_id':po_id},
		beforeSend: function() { $j('#loading-mask').show(); },
        complete: function() { $j('#loading-mask').hide(); },
        success: function(data) {
        	console.log(data.data);
        	if(data.data){
        			updateTotal(data.data);
	        	}
        }
    });
}
});
