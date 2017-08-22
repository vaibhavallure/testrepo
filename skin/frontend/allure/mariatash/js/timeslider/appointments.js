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
	}
	if(document.getElementById("store-id")!= null){
		var storeid = document.getElementById("store-id").value;
	}
	var request = {
 				"qty":qty,
 				"store":storeid,
 				"date":todaysDate,
 				"id":Allure.appointmentId
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
 	var request = {
  				"qty":qty,
  				"store":storeid,
  				"date":todaysDate,
  				"id":Allure.appointmentId
  	 		};
  	 jQuery.ajax({
         	url : Allure.ajaxGetTimeUrl,
         	dataType : 'json',
  			type : 'POST',
  			data: {request:request,id:Allure.appointmentId},
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
	 /*jQuery( "#datepicker-13" ).datepicker({
		 
		 onSelect: function (date, instance) {			 
			 var qty = document.getElementById("count").value;
			 jQuery("#datepicker-13_hidden").val(date);
			 console.log(Allure.ajaxGetTimeUrl);
			 var request = {
						"qty":qty,
						"date":date
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
	 }).datepicker("show");*/
	 
	 
	//ajax start to get the working days of piercers according to store
	 	if(document.getElementById("store-id")!= null){
			var storeid = document.getElementById("store-id").value;
		}
	 	jQuery.ajax({
	 		url : Allure.ajaxGetWorkingDaysUrl,
			type : 'POST',
			dataType:'json',
			data: {storeid:storeid,id:Allure.appointmentId},
			success : function(response){
				jQuery("#fetchpickurday").html(response.output);
				if(response.schedule)
					jQuery("#piercer_schedule").html(response.schedule);
			}
     });
	 //ajax start to get the working days of piercers according to store
	 
		 
	 
	 //If store change pickurtime should display also change acc to store and date 
	 jQuery("#store-id").on("change",function(){
		 	//jQuery("#pick_ur_time_div").empty();
		 	var todaysDate = document.getElementById("datepicker-13_hidden").value;	
			//ajax start to pass the selected date to get the time
			var qty = document.getElementById("count").value;
			var storeid = document.getElementById("store-id").value;
			var request = {
		 				"qty":qty,
		 				"store":storeid,
		 				"date":todaysDate,
		 				"id":Allure.appointmentId
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
			//ajax end
		 	 
		 	//ajax start to get the working days of piercers according to store			 	
			 	jQuery.ajax({
			 		url : Allure.ajaxGetWorkingDaysUrl,			 		
					type : 'POST',
					dataType:'json',
					data: {storeid:storeid,id:Allure.appointmentId},
					success : function(response){
						jQuery("#fetchpickurday").html(response.output);
						if(response.schedule)
							jQuery("#piercer_schedule").html(response.schedule);
					}
		     });
			 //ajax start to get the working days of piercers according to store
		 	 
		 	 
		 	 
	 });
	 
	 
	 
	 //If the customer come from modified onclick link getthe date and show time start

	 if(document.getElementById("datepicker-13_hidden")!= null )
	 {
		var todaysDate = document.getElementById("datepicker-13_hidden").value;	
		//ajax start to pass the selected date to get the time
		var qty = document.getElementById("count").value;
		var storeid = document.getElementById("store-id").value;
		var request = {
	 				"qty":qty,
	 				"store":storeid,
	 				"date":todaysDate,
	 				"id":Allure.appointmentId
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
		//ajax end
	 }
	 //If the customer come from modified onclick link getthe date and show time end
}); 
 
 
 
 
 

 