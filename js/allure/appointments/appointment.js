if (typeof Allure == "undefined") {
    var Allure = {};
} 
var count = 1;
 function increase(){			  
     count++;
     document.getElementById("count").value = count;
     //jQuery("#pick_ur_time_div").empty();
 
     	
	//ajax start to pass the selected date to get the time     
   /* var todaysDate = document.getElementById("datepicker-13_hidden").value;
	var qty = document.getElementById("count").value;
	var storeid = document.getElementById("store-id").value;*/
	if(document.getElementById("datepicker-13_hidden")!= null ){
		var todaysDate = document.getElementById("datepicker-13_hidden").value;
	}
	if(document.getElementById("count")!= null ){
		var qty = document.getElementById("count").value;

        /*--------------no of people limitation---------------start--------*/
        var qty_limit = document.getElementById("no_of_people_limit").value;
        if(qty_limit!="" && qty>qty_limit)
        {
            var no_limit=document.getElementById("no_of_people_limit");
            var msg=no_limit.dataset.popupmsg;
            var email=no_limit.dataset.storeemail;
            var phone=no_limit.dataset.storephone;

            msg=msg.replace("(email)",email);
            msg=msg.replace("(phone)",phone);

            alert(msg);
            document.getElementById("count").value=qty_limit;
            qty=qty_limit;
        }

        /*--------------no of people limitation---------------end--------*/
	}
	if(document.getElementById("store-id")!= null){
		var storeid = document.getElementById("store-id").value;
	}
	console.log("dasvsgv");
	var request = {
 				"qty":qty,
 				"store":storeid,
 				"date":todaysDate,
 				"id":Allure.appointmentId,
 				"form_key":Allure.adminFormKey
 	 		};
 	 jQuery.ajax({
        	url : Allure.adminAjaxGetTimeUrl,
        	dataType : 'json',
 			type : 'POST',
 			data: {request:request},
 			success : function(response){
 				jQuery("#pick_ur_time_div").html(response.output);
 				window.sample = 30;
 				var simple = jQuery("#appointemnet_form").find(".pick_your_time").append();
 				simple.sliderDemo();
 			}
        });
	//ajax end

 }
 function decrease(){
	 if (count > 1) {
     count--;		     
     document.getElementById("count").value = count;
     //jQuery("#pick_ur_time_div").empty();
   
    //ajax start to pass the selected date to get the time
    /*var todaysDate = document.getElementById("datepicker-13_hidden").value;
 	var qty = document.getElementById("count").value;
 	var storeid = document.getElementById("store-id").value;*/
    if(document.getElementById("datepicker-13_hidden")!= null ){
 		var todaysDate = document.getElementById("datepicker-13_hidden").value;
 	}
 	if(document.getElementById("count")!= null ){
 		var qty = document.getElementById("count").value;
 	}
 	if(document.getElementById("store-id")!= null){
 		var storeid = document.getElementById("store-id").value;
 	}
 	console.log("dasvsgv");
 	var request = {
  				"qty":qty,
  				"store":storeid,
  				"id":Allure.appointmentId,
  				"date":todaysDate,
  				"form_key":Allure.adminFormKey
  	 		};
  	 jQuery.ajax({
         	url : Allure.adminAjaxGetTimeUrl,
         	dataType : 'json',
  			type : 'POST',
  			data: {request:request},
  			success : function(response){
  				jQuery("#pick_ur_time_div").html(response.output);
  				window.sample = 30;
  				var simple = jQuery("#appointemnet_form").find(".pick_your_time").append();
  				simple.sliderDemo();
  			}
         });
 	//ajax end
   }
 } 
 
  
 
 
 jQuery( function() {
	 //Pick Ur Time
	 var simple = jQuery("#appointemnet_form").find(".pick_your_time").append();
	 simple.sliderDemo();
	 //Pick Ur Day	 	 
	 jQuery( "#datepicker-13" ).datepicker({
		 onSelect: function (date, instance) {	
			 console.log("dasvsgv");
			 var qty = document.getElementById("count").value;
			 jQuery("#datepicker-13_hidden").val(date);
			 console.log(Allure.ajaxGetTimeUrl);
			 var request = {
						"qty":qty,
						"date":date,
						"id":Allure.appointmentId,
						"form_key":Allure.adminFormKey
			 		};
			 jQuery.ajax({
		        	url : Allure.ajaxGetTimeUrl,
		        	dataType : 'json',
					type : 'POST',
					data: {request:request},
					success : function(response){
						jQuery("#pick_ur_time_div").html(response.output);
						window.sample = 30;
						 var simple = jQuery("#appointemnet_form").find(".pick_your_time").append();
						 simple.sliderDemo();
					}
		        });
		    }		 
	 }).datepicker("show");
	 
	 
	//ajax start to get the working days of piercers according to store
	 	if(document.getElementById("store-id")!= null){
			var storeid = document.getElementById("store-id").value;
		}
	
	 	jQuery.ajax({
	 		url : Allure.adminAjaxGetWorkingDaysUrl,
			type : 'POST',
			dataType:'json',
			data: {storeid:storeid,id:Allure.appointmentId,
				form_key:Allure.adminFormKey},
			success : function(response){
				jQuery("#fetchpickurday").html(response.output);	

				 //If the customer come from modified onclick link getthe date and show time start

				 if(document.getElementById("datepicker-13_hidden")!=null)
				 {
					var todaysDate = document.getElementById("datepicker-13_hidden").value;	
					console.log(todaysDate);
					//ajax start to pass the selected date to get the time
					var qty = document.getElementById("count").value;
					var storeid = document.getElementById("store-id").value;
					var request = {
				 				"qty":qty,
				 				"store":storeid,
				 				"date":todaysDate,
				 				"id":Allure.appointmentId,
				 				"form_key":Allure.adminFormKey
				 	 		};
				 	 jQuery.ajax({
				        	url : Allure.adminAjaxGetTimeUrl,
				        	dataType : 'json',
				 			type : 'POST',
				 			data: {request:request},
				 			success : function(response){
				 				jQuery("#pick_ur_time_div").html(response.output);
				 				window.sample = 30;
				 				var simple = jQuery("#appointemnet_form").find(".pick_your_time").append();
				 				simple.sliderDemo();
				 			}
				        });
					//ajax end
				 }
			}
     });
	 //ajax start to get the working days of piercers according to store
	 
		 
	 
	 //If store change pickurtime should display also change acc to store and date 
	 jQuery("#store-id").on("change",function(){
		 	//console.log("hi");
			//console.log(todaysDate);
		 	//jQuery("#pick_ur_time_div").empty();
		 	var todaysDate = document.getElementById("datepicker-13_hidden").value;	
			//ajax start to pass the selected date to get the time
			var qty = document.getElementById("count").value;
			var storeid = document.getElementById("store-id").value;
			//ajax start to get the working days of piercers according to store			 	
		 	jQuery.ajax({
		 		url : Allure.adminAjaxGetWorkingDaysUrl,			 		
				type : 'POST',
				dataType:'json',
				data: {storeid:storeid,id:Allure.appointmentId,
					form_key:Allure.adminFormKey},
				success : function(response){
					jQuery("#fetchpickurday").html(response.output);
					var todaysDate = document.getElementById("datepicker-13_hidden").value;	
					var request = {
				 				"qty":qty,
				 				"store":storeid,
				 				"date":todaysDate,
				 				"id":Allure.appointmentId,
				 				"form_key":Allure.adminFormKey
				 	 		};
					console.log(request);
					
				 	 jQuery.ajax({
				        	url : Allure.adminAjaxGetTimeUrl,
				        	dataType : 'json',
				 			type : 'POST',
				 			data: {request:request
				 				},
				 			success : function(response){
				 				jQuery("#pick_ur_time_div").html(response.output);
				 				window.sample = 30;
				 				var simple = jQuery("#appointemnet_form").find(".pick_your_time").append();
				 				simple.sliderDemo();
				 			}
				        });
					//ajax end
				 	 
				}
	      });
		 //ajax start to get the working days of piercers according to store
		 	
		 	
		 	 
		 	 
		 	 
	 });
	 
	 
	 
	 //If the customer come from modified onclick link getthe date and show time end
}); 
 
 
 
 
 

 