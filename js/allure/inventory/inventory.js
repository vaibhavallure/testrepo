var $j = jQuery;
$j(document).ready(function (){
	
	//to clear session veriables
	var isRefreshUrl = getUrlParameter('p');
    if(!isRefreshUrl){
    	localStorage.removeItem('data');
    	localStorage.removeItem('data');
    }
	var data = JSON.parse(localStorage.getItem('data'));
	var refence_no=localStorage.getItem('refence_no')
	if(refence_no)
		$j('#refence_no').val(refence_no);
	if(data){
		var total =  updateTotal(data);
		$j('#order_total').val(total);
	}
	$j("input:checkbox").change(function(){
		var selected = [];
        var ischecked= $j(this).is(':checked');
        var id=parseInt($j(this).attr('id'));
        var qty=parseInt($j('#max_qty_'+id).val());
        var cost=parseFloat($j('#cost_'+id).val());
        var comment=$j('#comment_'+id).val();
        var totalAmount = parseInt($j('#order_total').val());
        if(ischecked){
	        if(qty<=0){
	        	 alert('Please Enter Qty Greater than 0.');
	        	 $j(this).removeAttr('checked');
	        }
	        else{ 
	        	totalAmount=totalAmount + (qty * cost);
	            selected.push({id,qty,cost,comment});
	             
	            data = JSON.parse(localStorage.getItem('data'));
	            if(data){
	            	data.push(selected);
	            }else{
	            	var data = new Array();
	            	data.push(selected);
	            }
	             //total =  updateTotal(data);
	            localStorage.setItem('data',JSON.stringify(data));
	            
	            $j('#order_total').val(totalAmount);
	            $j('#max_qty_'+id).prop('disabled', true);
	            $j('#comment_'+id).prop('disabled', true);
	        }
        }else{
        	
        	//If unchecked
        	var data = JSON.parse(localStorage.getItem('data'));
        	selected.push({id,qty,cost,comment});
        	 if(data){
            	 data.pop(selected);
            	 var totalAmount =  updateTotal(data);
         	 	 $j('#order_total').val(totalAmount);
             }else{
            	 data = selected;
             }
            localStorage.setItem('data',JSON.stringify(data));
            $j('#max_qty_'+id).prop('disabled', false);
            $j('#comment_'+id).prop('disabled', false);
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
		var data = JSON.parse(localStorage.getItem('data'));
		var refence_no=localStorage.getItem('refence_no');
		if(data){
			confirm("Are you sure ?");
			var key=Allure.ViewPurchaseOrderFormKey;
			$j.ajax({
		        url: Allure.InventoryPurcaseCreateOrder,
		        dataType : 'json',
				type : 'POST',
				data: {'data':data,'form_key':key,'refence_no':refence_no},
				beforeSend: function() { $j('#loading-mask').show(); },
		        complete: function() { $j('#loading-mask').hide(); },
		        success: function(data) {
		        	localStorage.removeItem('data');
		        	localStorage.removeItem('refence_no');
					//alert(data.message)
		        	window.location.reload();
		        }
		    });
		}
		else
			alert("Please select Item first.")
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
	$j.each( data, function( index, value ){
	    sum += value[0]['cost'] * value[0]['qty'];
	    $j('#'+value[0]['id']).prop( "checked", true );
	    $j('#max_qty_'+value[0]['id']).val(value[0]['qty']);
	    $j('#max_qty_'+value[0]['id']).prop('disabled', true);
	  /*  $j('#comment_'+value[0]['id']).val(value[0]['comment']);
	    $j('#comment_'+value[0]['id']).prop('disabled', true);*/
	    
	});
	return sum;
}	

function saveValue(e){
    var val = e.value; // get the value. 
    localStorage.setItem('refence_no', val);// Every time user writing something, the localStorage's value will override . 
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

