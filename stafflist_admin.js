/* stafflist handlers */

//console.log("stafflist_admin.js Loaded");

	jQuery(document).ready(function() {
    jQuery('#stafflists input[type="text"]').addClass("idleField"); 
    jQuery('#newstaff   input[type="text"]').addClass("idleField");
    jQuery('#stafflists input[type="text"]').focus(function() {  
        jQuery(this).removeClass("idleField").addClass("focusField");  
        if (this.value == this.defaultValue){  
            this.select(); 
        }
        //console.log(jQuery(this).attr("id"));
    });  
    jQuery('#stafflists input[type="text"]').blur(function() {  
        jQuery(this).removeClass("focusField").addClass("idleField");
        var fd = jQuery(this);
        //if (jQuery.trim(fd.val()) == '' || fd.val() == unescape(this.defaultValue)){  
        if (fd.val() == unescape(this.defaultValue)){  
            this.value = (this.defaultValue ? unescape(this.defaultValue) : '');  
            //console.log("default value or blank");
        }
        else
        {	var fname = fd.attr('id').split(":");
        	var fval = fd.val();
        	//console.log(fname + " > " + fval);
        	updateField(fname,fval);
        }
    });
    jQuery('#doInsert').click(function(){
    	document.getElementById("warning").style.display = "none";
    	var rec = jQuery("#insertStaff").serialize();
    	insertRecord(rec);
    });
    
    jQuery("#stafflist_new").focus(function() {
    	console.log("NEW ROW TRIGGER FOCUSED");
    	
    	//fetch next id
    	var newid = false;
    	jQuery.post(ajax_object.ajaxurl, {
    		action: 'ajax_nextrow',
    		async: false
    	}, function(data) {
    		newid = data;
    		console.log("New ID: "+newid);
    		
    		if(newid>0 && jQuery('#staff_'+newid).length == 0){
    			
    			//clone row
    	    	var tr = jQuery("#stafflists tr:last");
    	    	jQuery(tr).clone().insertAfter(tr).find( 'input:text' ).val('');
    	    	
	        	//prepare fields with new id
	        	jQuery("#stafflists tr:last").find('input:text').each(function(e){
	        		var oldid = jQuery(this).attr("id").split(":");
	        		console.log(oldid);
	        		jQuery(this).attr("id",oldid[0]+":"+newid);
	        		console.log(jQuery(this).attr("id"));
	        	});
	        	
	        	jQuery("#stafflists tr:last").attr("id","staff_"+newid);
	        	
	        	//bind handler to new row
	            jQuery('#stafflists tr:last input[type="text"]').focus(function() {  
	                jQuery(this).removeClass("idleField").addClass("focusField");  
	                if (this.value == this.defaultValue){  
	                    this.select(); 
	                }
	            });
	            jQuery('#stafflists tr:last input[type="text"]').blur(function() {  
	                jQuery(this).removeClass("focusField").addClass("idleField");
	                var fd = jQuery(this);
	                if (fd.val() == unescape(this.defaultValue)){  
	                    this.value = (this.defaultValue ? unescape(this.defaultValue) : '');  
	                }
	                else
	                {	var fname = fd.attr('id').split(":");
	                	var fval = fd.val();
	                	if(false!=fname[1]) updateField(fname,fval);
	                }
	            });
	        	//focus first text field
	        	jQuery("#stafflists tr:last").find('input:first').focus();
    		}
    	});

    });
});

function updateField(fname,fval){
	//console.log("Updating field "+fname[0]+", "+fname[1]+", "+fval);
    showLoading(fname[0]+":"+fname[1]);  		// shows updating gif
	jQuery.post(ajax_object.ajaxurl, {
		action: 'ajax_update',
		fval: fval,
		fname: fname							// query is built in ajax function; returns true/false
	}, function(data) {
		//alert(data); 							// changes default value
		document.getElementById(fname[0]+":"+fname[1]).defaultValue = escape(fval);
		hideLoading(fname[0]+":"+fname[1]);		// hides updating gif
	});
	return;
}

var insertedRecords = 0;
function insertRecord(vals){
	
    showLoading("sl_first:0");  		// shows updating gif
    showLoading("sl_last:0" );  		// shows updating gif
    showLoading("sl_dept:0" );  		// shows updating gif
    showLoading("sl_email:0");  		// shows updating gif
    showLoading("sl_phone:0");  		// shows updating gif
    
	jQuery.post(ajax_object.ajaxurl, {
		action: 'ajax_insert',
		data: vals
	}, function(data) {
		console.log(data);
		if(data>0){
			insertedRecords+=1;
			document.getElementById("warning").innerHTML = "<strong>Note:</strong> [ "+insertedRecords+" ] records have been added to your staff directory.<br />This page will need a refresh when you're done adding records.<br />";
		}
		else
		{	document.getElementById("warning").innerHTML = "<strong>Note:</strong> No record was added to your staff directory.<br />Please verify that you provided at least 3 of the contact fields.<br />";
		}
		document.getElementById("warning").style.display = "block";
		
	    hideLoading("sl_first:0");  		// shows updating gif
	    hideLoading("sl_last:0" );  		// shows updating gif
	    hideLoading("sl_dept:0" );  		// shows updating gif
	    hideLoading("sl_email:0");  		// shows updating gif
	    hideLoading("sl_phone:0");  		// shows updating gif
	});
	return;
}

function newFormRecord(nextid) {
	
}

// update indicators
function showLoading(div){
	if(document.getElementById(div)) {
		document.getElementById(div).className = 'updateField';
	}
}
function hideLoading(div){
	if(document.getElementById(div)) {
		document.getElementById(div).className = 'idleField';
	}
}