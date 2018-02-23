var $j = jQuery;
if (typeof Allure == "undefined") {
	   var Allure = {};
}
$j(document).ready(function (){
	var data=getStoredData();
	$j("input:checkbox").change(function(){
		var selected = [];
        var ischecked= $j(this).is(':checked');
        
        var id=parseInt($j(this).attr('id'));
        var qty=parseInt($j('#max_qty_'+id).val());
        var is_custom=parseInt($j('#is_custom_'+id).val());
        var cost=parseFloat($j('#cost_'+id).val());
        var vendor_sku=$j('#vendor_sku_'+id).val();
    	var refence_no=$j('#refence_no').val();
    	var key=Allure.ViewPurchaseOrderFormKey;
        var comment=$j('#comment_'+id).val();
        var store=$j('#store').val();
        var totalAmount = parseInt($j('#order_total').val());
        console.log(totalAmount);
        if(ischecked){
	        if(qty<=0){
	        	 alert('Please Enter Qty Greater than 0.');
	        	 $j(this).removeAttr('checked');
	        }
	        else{ 
	        	totalAmount=totalAmount + (qty * cost);
	        	var include = 1;
	        	var item ={
	        			id,qty,cost,comment,include,store,is_custom,vendor_sku
	        	};
	        	console.log(item);
	        	$j.ajax({
	    	        url: Allure.AddPurchaseItem,
	    	        dataType : 'json',
	    			type : 'POST',
	    			data: {'item':item,'form_key':key,'refence_no':refence_no,'order_total':totalAmount},
	    			beforeSend: function() { $j('#loading-mask').show(); },
	    	        complete: function() { $j('#loading-mask').hide(); },
	    	        success: function(data) {
	    	            $j('#order_total').val(totalAmount);
	    	            $j('#max_qty_'+id).prop('disabled', true);
	    	            $j('#cost_'+id).prop('disabled', true);
	    	            $j('#comment_'+id).prop('disabled', true);
	    	            $j('#vendor_sku_'+id).prop('disabled', true);
	    	        }
	    	    });
	        }
        }else{
        	totalAmount=totalAmount - (qty * cost);
        	var include = 0;
        	var item ={
        			id,qty,cost,comment,include,store,is_custom,vendor_sku
        	};
        	$j.ajax({
    	        url: Allure.AddPurchaseItem,
    	        dataType : 'json',
    			type : 'POST',
    			data: {'item':item,'form_key':key,'refence_no':refence_no,'order_total':totalAmount},
    			beforeSend: function() { $j('#loading-mask').show(); },
    	        complete: function() { $j('#loading-mask').hide(); },
    	        success: function(data) {
    	        	 $j('#order_total').val(totalAmount);
    	        	 $j('#max_qty_'+id).prop('disabled', false);
    	        	 $j('#cost_'+id).prop('disabled', false);
    	             $j('#comment_'+id).prop('disabled', false);
    	             $j('#vendor_sku_'+id).prop('disabled', false);
    	        	
    	        }
    	    });
        	
         }
    }); 
	
	$j("#search").keyup(function(e){
		if(e.keyCode == 13){
		   var url ="";
		   url = location.href;
		   url = url.substr(0,url.indexOf("?"));
		   var searchText = $j(this).val();
		   location.href = url + "?search="+searchText;
		}
	});

	
	$j("#store").change(function() {
		var value =$j("#store" ).val();
		var key=Allure.ViewPurchaseOrderFormKey;
		$j.ajax({
	        url: Allure.InventoryStoreSwitch,
	        dataType : 'json',
			type : 'POST',
			data: {'value':value,'form_key':key},
			beforeSend: function() { $j('#loading-mask').show(); },
	        complete: function() { $j('#loading-mask').hide(); },
	        success: function(data) {
	        	window.location.reload();
	        }
	    });
			
	});

	$j(".create_po_btn").click(function() {
		/*var data = Allure.POData;
		var sessionData=Allure.POSessionData;
		var itemData = JSON.stringify(itemsData);*/
		var totalAmount = parseInt($j('#order_total').val());
		var store=$j('#store').val();
		var refence_no=$j('#refence_no').val();
		if(totalAmount > 0){
			if(confirm("Are you sure ?")){
			var key=Allure.ViewPurchaseOrderFormKey;
			$j.ajax({
		        url: Allure.InventoryPurcaseCreateOrder,
		        dataType : 'json',
				type : 'POST',
				data: {'form_key':key,'refence_no':refence_no,'store':store},
				beforeSend: function() { $j('#loading-mask').show(); },
		        complete: function() { $j('#loading-mask').hide(); },
		        success: function(data) {
		        
					//alert(data.message)
		        	window.location.reload();
		        }
		    });
		  }
		}
		else
			alert("Please select Item first.")
	});
	
	$j(".reset_po_btn").click(function() {
		/*var data = Allure.POData;
		var sessionData=Allure.POSessionData;
		var itemData = JSON.stringify(itemsData);*/
		   var store=$j('#store').val();
		
			if(confirm("Are you sure ?")){
			var key=Allure.ViewPurchaseOrderFormKey;
			$j.ajax({
		        url: Allure.Reset,
		        dataType : 'json',
				type : 'POST',
				data: {'form_key':key,'store':store},
				beforeSend: function() { $j('#loading-mask').show(); },
		        complete: function() { $j('#loading-mask').hide(); },
		        success: function(data) {
					//alert(data.message)
		        	window.location.reload();
		        }
		    });
		  }
	});
		
});

function submitsearch(){
	var url ="";
	url = location.href;
	url = url.substr(0,url.indexOf("?"));
	var searchText = $j('#search').val();
	location.href = url + "?search="+searchText;
}

function getUrlParameter(sParam) {
	var sPageURL = decodeURIComponent(window.location.search.substring(1)),
	sURLVariables = sPageURL.split('&'),
    sParameterName,
    i;
	for (i = 0; i < sURLVariables.length; i++) {
		sParameterName = sURLVariables[i].split('=');
		if (sParameterName[0] === sParam) {
			return sParameterName[1] === undefined ? true : sParameterName[1];
		}
	}
}

//Update total 
function updateTotal(data){
	var sum = 0;
	data.forEach(function(value) {
		sum += value['cost'] * value['qty'];
	    $j('#'+value['item_id']).prop( "checked", true );
	    $j('#max_qty_'+value['item_id']).val(value['qty']);
	    $j('#max_qty_'+value['item_id']).prop('disabled', true);
	    $j('#comment_'+value['item_id']).val(value['comment']);
	    $j('#comment_'+value['item_id']).prop('disabled', true);
	    $j('#cost_'+value['item_id']).val(value['cost']);
	    $j('#cost_'+value['item_id']).prop('disabled', true);
	    $j('#vendor_sku_'+value['item_id']).val(value['vendor_sku']);
	    $j('#vendor_sku_'+value['item_id']).prop('disabled', true);
	});
	/*console.log(data.length);
	var sum = 0;
	$j.each( data, function( index, value ){
	    sum += value['cost'] * value['qty'];
	    $j('#'+value['id']).prop( "checked", true );
	    $j('#max_qty_'+value['id']).val(value ['qty']);
	    $j('#max_qty_'+value['id']).prop('disabled', true);
	    $j('#comment_'+value['id']).val(value ['comment']);
	    $j('#comment_'+value['id']).prop('disabled', true);
	    
	});*/
	
	/*for(var key in data){
		console.log(data[key]);
		$j("#max_qty_"+key).val(data[key].qty);
		$j('#max_qty_'+key).prop('disabled', true);
		$j("#comment_"+key).val(data[key].comment);
		$j('#comment_'+key).prop('disabled', true);
		if(data[key].include)
		$j("#"+key).prop( "checked", true );
	}*/
	$j('#order_total').val(sum)
	return sum;
}	



function checkCurrentAndTransferQty(e){
    var id = e.id; // get the value.
    var value =parseInt(e.value);
    var curentQty=parseInt(jQuery('#current_'+id).val());
    if(curentQty<value){
		alert('Requested quantity is not available in store')
		$j('#'+e.id).val(0);
    }
}

function updateReceivingTotalQty(e){
    var id = e.id; // get the value.
    var value =parseInt(e.value);
    var prevQty=parseInt(jQuery('#current_'+id).val());
    var qty = value+ prevQty;
    jQuery("#total_"+id).text(qty);
}
function resetSearch(){
	var url ="";
	url = location.href;
	url = url.substr(0,url.indexOf("?"));
	location.href=url;
}
 function getStoredData(){
    var store=$j('#store').val();
	var key=Allure.ViewPurchaseOrderFormKey;
	$j.ajax({
        url: Allure.GetStoredInfo,
        dataType : 'json',
		type : 'POST',
		data: {'form_key':key,'store':store},
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

