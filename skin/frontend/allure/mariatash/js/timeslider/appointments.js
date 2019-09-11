if (typeof Allure == "undefined") {
    var Allure = {};
} 
var count = 0;
 function increase(){			  
     count++;
     document.getElementById("count").value = count;
     //jQuery("#pick_ur_time_div").empty();
     jQuery('#pick_ur_time_div').find('input:hidden').val('');
     jQuery("#time_blocks").empty();
     	
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

            alert(msg);
            document.getElementById("count").value=qty_limit;
            count=qty=qty_limit;
        }

        /*--------------no of people limitation---------------end--------*/

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
         beforeSend: function() { jQuery('#appointment_loader').show(); },
         complete: function() { jQuery('#appointment_loader').hide(); },
         timeout: 30000,
         error: function(jqXHR) {
             if(jqXHR.status==0) {
                 alert(" fail to connect, please check your internet connection");
             }
         },
 			success : function(response){
 				jQuery("#pick_ur_time_div").html(response.output);
 				jQuery("#appointment-pricing").html(response.pricing_html);
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

         jQuery('#pick_ur_time_div').find('input:hidden').val('');
         jQuery("#time_blocks").empty();
   
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
         beforeSend: function() { jQuery('#appointment_loader').show(); },
         complete: function() { jQuery('#appointment_loader').hide(); },
         timeout: 30000,
         error: function(jqXHR) {
             if(jqXHR.status==0) {
                 alert(" fail to connect, please check your internet connection");
             }
         },
  			success : function(response){
  				jQuery("#pick_ur_time_div").html(response.output);
  				jQuery("#appointment-pricing").html(response.pricing_html);
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

     setSupportDetails(storeid);

	 	jQuery.ajax({
	 		url : Allure.ajaxGetWorkingDaysUrl,
			type : 'POST',
			dataType:'json',
			data: {storeid:storeid,id:Allure.appointmentId},
			success : function(response){
				jQuery("#fetchpickurday").html(response.output);
				if(response.schedule)
					jQuery("#piercer_schedule").html(response.schedule);
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
                         beforeSend: function() { jQuery('#appointment_loader').show(); },
                         complete: function() { jQuery('#appointment_loader').hide(); },
                         timeout: 30000,
                         error: function(jqXHR) {
                             if(jqXHR.status==0) {
                                 alert(" fail to connect, please check your internet connection");
                             }
                         },
				 			success : function(response){
				 				jQuery("#pick_ur_time_div").html(response.output);
				 				jQuery("#appointment-pricing").html(response.pricing_html);
				 				window.sample = 30;
				 				var simple = jQuery("#appointemnet_form").find(".pick_your_time").append();
				 				simple.sliderDemo();
				 			}
				        });
					//ajax end
				 }
				 //If the customer come from modified onclick link getthe date and show time end
			}
     });
	 //ajax start to get the working days of piercers according to store
	 
		 
	 
	 //If store change pickurtime should display also change acc to store and date 
	 jQuery("#store-id").on("change",function(){
		 	//jQuery("#pick_ur_time_div").empty();
         jQuery('#pick_ur_time_div').find('input:hidden').val('');
         jQuery("#time_blocks").empty();
         var selectedStore = jQuery(this).children("option:selected");

         if(typeof selectedStore.attr('data-url') !== "undefined"){
             window.location.replace(selectedStore.attr('data-url'));
         }else {
             var todaysDate = document.getElementById("datepicker-13_hidden").value;
             //ajax start to pass the selected date to get the time
             var qty = document.getElementById("count").value;
             var storeid = document.getElementById("store-id").value;
             //ajax start to get the working days of piercers according to store

             setSupportDetails(storeid);


             jQuery.ajax({
                 url: Allure.ajaxGetWorkingDaysUrl,
                 type: 'POST',
                 dataType: 'json',
                 data: {storeid: storeid, id: Allure.appointmentId},
                 success: function (response) {
                     jQuery("#fetchpickurday").html(response.output);
                     if (response.schedule)
                         jQuery("#piercer_schedule").html(response.schedule);
                     var todaysDate = document.getElementById("datepicker-13_hidden").value;
                     var request = {
                         "qty": qty,
                         "store": storeid,
                         "date": todaysDate,
                         "id": Allure.appointmentId
                     };
                     jQuery.ajax({
                         url: Allure.ajaxGetTimeUrl,
                         dataType: 'json',
                         type: 'POST',
                         data: {request: request},
                         beforeSend: function () {
                             jQuery('#appointment_loader').show();
                         },
                         complete: function () {
                             jQuery('#appointment_loader').hide();
                         },
                         timeout: 30000,
                         error: function (jqXHR) {
                             if (jqXHR.status == 0) {
                                 alert(" fail to connect, please check your internet connection");
                             }
                         },
                         success: function (response) {
                             jQuery("#pick_ur_time_div").html(response.output);
                             jQuery("#appointment-pricing").html(response.pricing_html);
                             window.sample = 30;
                             var simple = jQuery("#appointemnet_form").find(".pick_your_time").append();
                             simple.sliderDemo();
                         }
                     });
                     //ajax end

                 }
             });
             //ajax start to get the working days of piercers according to store
         }
	 });
	 
});


function setSupportDetails(storeid) {
    jQuery.ajax({
        url : Allure.getSupportDetailsActionUrl,
        type : 'POST',
        dataType:'json',
        data: {storeid:storeid},
        success : function(response) {
            console.log(response.message);
            jQuery('#no_of_people_limit').attr("data-popupmsg",response.message);


        }
    });
}
/*---------------------custom alert----------------------------*/

if(document.getElementById) {
    window.alert = function(txt) {
        createCustomAlert(txt);
    }
}



function createCustomAlert(txt) {
    d = document;
    if(d.getElementById("allureModalContainer")) return;

    mObj = d.getElementsByTagName("body")[0].appendChild(d.createElement("div"));
    mObj.id = "allureModalContainer";
    mObj.style.height = d.documentElement.scrollHeight + "px";

    alertObj = mObj.appendChild(d.createElement("div"));
    alertObj.id = "allureAlertBox";
    if(d.all && !window.opera) alertObj.style.top = document.documentElement.scrollTop + "px";
    alertObj.style.left = (d.documentElement.scrollWidth - alertObj.offsetWidth)/2 + "px";
    alertObj.style.visiblity="visible";

    /* h1 = alertObj.appendChild(d.createElement("h1"));
     h1.appendChild(d.createTextNode(ALERT_TITLE));*/

    msg = alertObj.appendChild(d.createElement("p"));
    //msg.appendChild(d.createTextNode(txt));
    msg.innerHTML = txt;

    btn = alertObj.appendChild(d.createElement("a"));
    btn.id = "closeBtn";
    btn.appendChild(d.createTextNode("OK"));
    btn.href = "#";
    btn.focus();
    btn.onclick = function() { removeCustomAlert(); }

    alertObj.style.display = "block";

}

function removeCustomAlert() {
    document.getElementsByTagName("body")[0].removeChild(document.getElementById("allureModalContainer"));
}
/*custom alert change------------------------------*/