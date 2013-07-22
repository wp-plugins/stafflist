/* stafflist handlers */

//console.log("Stafflist.js Loaded");

jQuery(document).ready( function($) {
	build_stafflist();
});

function build_stafflist() {
	var data = { action: 'ajax_build' };
 	jQuery.post(ajax_object.ajaxurl, data, function(response) {
 		//console.log(response);
		jQuery("div#staffdirectory").html(response);
 	});
 	return false;
}
function sl_sort(dir){
	jQuery("#sl_sort").val(dir);
	jQuery("a[id^='sl_sort:']").each(function(){
		jQuery(this).removeClass("selected");
		var dirArrow = jQuery(this).attr("id").replace("sl_sort:","");
		if(dirArrow == dir) jQuery(this).addClass("selected");
	});
	refine_stafflist();
	return;
}
function sl_page(page){
	jQuery("#sl_page").val(page);
	jQuery("a[id^='sl_page:']").each(function(){
		jQuery(this).removeClass("selected");
		var curPage = jQuery(this).attr("id").replace("sl_page:","");
		if(curPage == page) jQuery(this).addClass("selected");
	});
	refine_stafflist();
	return;
}
function do_sl_search(){
	refine_stafflist();
	return;
}
function refine_stafflist(){
	var data = {
		action: 'ajax_build',
        sort: document.getElementById("sl_sort").value,
        page: document.getElementById("sl_page").value,
        search: document.getElementById("sl_search").value
	};
 	jQuery.post(ajax_object.ajaxurl, data, function(response) {
		jQuery("div#staffdirectory").html(response);
 	});
 	return false;
}