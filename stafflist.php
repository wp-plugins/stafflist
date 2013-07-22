<?php
/*
Plugin Name: StaffList
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: A super simplified staff directory tool
Version: 0.93
Author: era404 Creative Group, Inc.
Author URI: http://www.era404.com
License: GPLv2 or later.
Copyright 2013  era404 Creative Group, Inc.  (email : in4m@era404.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/***********************************************************************************
*     Setup Plugin > Create Table
***********************************************************************************/
require_once("stafflist_setup.php");
// this hook will cause our creation function to run when the plugin is activated
register_activation_hook( __FILE__, 'stafflist_install' );

/***********************************************************************************
*     Globals
***********************************************************************************/
define('RECORDS_PER_PAGE', 25);
define('STAFFLIST_URL', admin_url() . 'options-general.php?page=stafflist');
$staffdb = $wpdb->prefix . "stafflist";

/***********************************************************************************
*     Setup Admin Menus
***********************************************************************************/
add_action( 'admin_menu', 'stafflist_admin_menu' );

function stafflist_admin_menu() {
	//$page = add_management_page( 'StaffList', 'StaffList', 'manage_options', 'stafflist', 'stafflist_plugin_options' );
	$page = add_menu_page('StaffList', 'StaffList', 'manage_options', 'stafflist', 'stafflist_plugin_options', plugins_url('stafflist/admin_icon.png') );
	add_action( 'admin_print_styles-' . $page, 'stafflist_admin_styles' );
}
add_action( 'admin_init', 'stafflist_admin_init' );
function stafflist_admin_init() {
	wp_register_style( 'stafflist_admin', plugins_url('stafflist_admin.css', __FILE__) );
}
function stafflist_admin_styles() {
	wp_enqueue_style( 'stafflist_admin' );
}

/***********************************************************************************
*     Add Required Scripts
***********************************************************************************/
function setup_staff_admin_scripts() {
	wp_enqueue_script( 'ajax-script', plugins_url('/stafflist_admin.js', __FILE__), array('jquery'), 1.0 ); 	// jQuery will be included automatically
	wp_localize_script( 'ajax-script', 'ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) ); 	// setting ajaxurl
}
add_action('wp_print_scripts', 'setup_staff_admin_scripts');
add_action( 'wp_ajax_ajax_update', 'ajax_update' ); 	//for updates
add_action( 'wp_ajax_ajax_insert', 'ajax_insert' ); 	//for updates
add_action( 'wp_ajax_ajax_nextrow', 'ajax_nextrow' ); 	//for updates

/***********************************************************************************
*     Build Admin Page
***********************************************************************************/
function stafflist_plugin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	global $wpdb,$staffdb;	//get database object
	
	//delete empty rows 
	deleteEmpty();

	//handle deleting records
	$cr = 0;
	if(isset($_GET['remove'])) {
		$q = "SELECT count(id) FROM $staffdb WHERE id={$_GET['remove']}"; $cr = $wpdb->get_var($q);
		$q = "DELETE FROM $staffdb WHERE id={$_GET['remove']}"; $wpdb->query($q);
	}


	//handle sorting
	$sorting = array("f" =>"sl_first ASC",
					 "f-"=>"sl_first DESC",
					 "l" =>"sl_last ASC",
					 "l-"=>"sl_last DESC",
					 "d" =>"sl_dept ASC",
					 "d-"=>"sl_dept DESC"
			);
	$s = $_GET['s'] = (@!isset($_GET['s']) || !array_key_exists($_GET['s'],$sorting) ? "l" : $_GET['s']);
	$sort = $sorting[$_GET['s']];

	//get count, first
	$count =  $wpdb->get_var("SELECT count(id) FROM $staffdb"); //echo "COUNT: $count<br /><br />";

	/* build page array [$pg]
	*  0:total records
	*  1:records per page << defined above
	*  2:total pages
	*  3:current page (also:$p)
	*  4:record start
	*  5:record end
	*/

	$pg = array($count,RECORDS_PER_PAGE,ceil($count/RECORDS_PER_PAGE));
	$p = $_GET['p'] = $pg[3] = (!isset($_GET['p']) || $_GET['p']<1 || $_GET['p']>$pg[2] ? 1 : $_GET['p']);
	$pg[4] = ($pg[1]*$pg[3])-$pg[1];
	$pg[5]=(($pg[4]+$pg[1])-1);
		
	//build query
	$q =   "SELECT * FROM {$staffdb} ORDER BY {$sort} LIMIT {$pg[4]},{$pg[5]}"; //echo $q;
	$staff = $wpdb->get_results($q, ARRAY_A);
	//myprint_r($staff);
	
	//build images table
	?>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" id="donate">
Hello Friend.<br /><br />
Donations are entirely optional.<br /> 
If <b>StaffList</b> has made your life easier, and you wish to say thank you, a Secure PayPal link has been provided below.
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="JT8N86V6D2SG6">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
<?php 
	
	echo "<h1>StaffList</h1><br />
		  A super simplified staff directory tool.<br /><br />
		  You can insert the directory into your WordPress page template with the following command:
		  <ul style='margin-top:10px;'>
		  	<tt style='color:red;'>&lt;?php</tt>
			<tt style='color:purple; font-weight:bold;'>new</tt>
			<tt> stafflist();</tt>
			<tt style='color:red;'>?&gt;</tt>
		 </ul>
		 <br /><br />
		 ";
	
	// handle pager
echo <<<EOINSERT
<div style='clear:both;'>
<h2>Add record to staff directory.</h2>
<form id='insertStaff'>
<table id='newstaff' style='border:1px solid #E8E8E8;'>
		<thead id='stafflisthead'><tr>
			<th>Last Name</th><th>&nbsp;</th><th>First Name</th><th>&nbsp;</th><th>Department</th>
			<th>&nbsp;</th><th>Email Address</th><th>&nbsp;</th><th>Phone / Ext</th><th>&nbsp;</th>
		</tr></thead>	
		<tr class='row'>
		<td><input type='text' id='sl_last:0'  name='sl_last'  value=' '  tabindex=1 /></td><td></td>
		<td><input type='text' id='sl_first:0' name='sl_first' value=' '  tabindex=2 /></td><td></td>
		<td><input type='text' id='sl_dept:0'  name='sl_dept'  value=' '  tabindex=3 /></td><td></td>
		<td><input type='text' id='sl_email:0' name='sl_email' value=' '  tabindex=4 /></td><td></td>
		<td><input type='text' id='sl_phone:0' name='sl_phone' value=' '  tabindex=5 /></td>
		<td><input type='image' id='doInsert' tabindex=6 name='doInsert' value='' onclick='javascript:void(0); return false;' /></td>
	</tr>
</table>
</form>
</div>
EOINSERT;
	
	echo "<div style='clear:both;'>
	  <h2>Full directory.</h2>";
	
	echo "<div id='warning' class='orange' style='display:".($cr>0?"block":"none").";'>".(($cr>0)?"<strong>NOTE:</strong> [ $cr ] Staff Record removed.":"").
		 "</div>";
	
	echo "<table id='stafflists' style='border:1px solid #E8E8E8;'>";
	echo "<thead id='stafflisthead'><tr>
			<th><a href='".STAFFLIST_URL."&s=l' title='Sort by Last Name A-Z' class='sort_a ".($_GET['s']=='l'?'selected':'')."'><span>Ascending</span></a> Last Name
				<a href='".STAFFLIST_URL."&s=l-' title='Sort by Last Name Z-A' class='sort_d ".($_GET['s']=='l-'?'selected':'')."'><span>Descending</span></a>
			</th><th>&nbsp;</th>
			<th><a href='".STAFFLIST_URL."&s=f' title='Sort by First Name Ascending' class='sort_a ".($_GET['s']=='f'?'selected':'')."'><span>Ascending</span></a> First Name
				<a href='".STAFFLIST_URL."&s=f-' title='Sort by First Name Descending' class='sort_d ".($_GET['s']=='f-'?'selected':'')."'><span>Descending</span></a>
			</th><th>&nbsp;</th>
			<th><a href='".STAFFLIST_URL."&s=d' title='Sort by Department Ascending' class='sort_a ".($_GET['s']=='d'?'selected':'')."'><span>Ascending</span></a> Department
				<a href='".STAFFLIST_URL."&s=d-' title='Sort by Department Descending' class='sort_d ".($_GET['s']=='d-'?'selected':'')."'><span>Descending</span></a>
			</th><th>&nbsp;</th>
			<th>Email Address</th>
			<th>&nbsp;</th>
			<th>Phone / Ext</th>
			<th>&nbsp;</th>
		</tr></thead>";
	
	$i=0;
	foreach($staff as $k=>$s){
		$rm = plugins_url('/delete.png', __FILE__);
		$ed = plugins_url('/edit.png', __FILE__);
		$del = "<a href='" . STAFFLIST_URL . "&remove={$s['id']}&p={$page}&s={$_GET['s']}' class='remove'
				   onclick='javascript:if(!confirm(\"Are you sure you want to delete this staff record?\")) return false;' 
				   title='Permanently Delete This Staff Record' target='_self' 
				/><img src='{$rm}' width='16' height='16' align='absmiddle' /></a>&nbsp;";
		$i++;

echo <<<EOHTML
	<tr class='row' id='staff_{$s['id']}'>
		<td><input type='text' id='sl_last:{$s['id']}' value='{$s['sl_last']}' /></td>
		<td></td>
		<td><input type='text' id='sl_first:{$s['id']}' value='{$s['sl_first']}' /></td>
		<td></td>
		<td><input type='text' id='sl_dept:{$s['id']}' value='{$s['sl_dept']}' /></td>
		<td></td>
		<td><input type='text' id='sl_email:{$s['id']}' value='{$s['sl_email']}' /></td>
		<td></td>
		<td><input type='text' id='sl_phone:{$s['id']}' value='{$s['sl_phone']}' /></td>
		<td>{$del}</td>
	</tr>
EOHTML;
	}
	
	echo "</table>
  		  <a href='javascript:void(0);' title='Add New Staff Record' id='stafflist_new' name='stafflist_new'>Add New Staff Record</a>";


echo "</div><br />";
echo "<div style='clear:both;'>&nbsp; Page: $p (".($pg[4]+1)." - ".($pg[5]+1)." of {$pg[0]}) ";
for($page=1;$page<=$pg[2];$page++){ echo "<p class='pager'><a href='".STAFFLIST_URL."&p={$page}&s={$_GET['s']}'>{$page}</a></p>"; }
echo "</div>";
}
/***********************************************************************************
 *     Build Frontend Directory Table
***********************************************************************************/
class stafflist {
	function stafflist() {
		//include styles
		wp_register_style('stafflist', plugins_url('stafflist.css', __FILE__) ); wp_enqueue_style('stafflist');
		
		//build table
		echo "<div id='staffwrapper'><div id='pagerblock'>
		  	<form id='stafflistctl'>
				<input type='hidden' id='sl_sort' value='l'>
				<input type='hidden' id='sl_page' value='1'>
				Search Directory: <input type='test' id='sl_search' value='{$limit['search']}' onkeyup='do_sl_search(this);'>
		  	</form></div><div id='staffdirectory'></div></div>";
	}
}
/***********************************************************************************
*     Frontend Helper functions
***********************************************************************************/
function ajax_build(){
	unset($_POST['action']);
	$limit = (empty($_POST) ? array("sort"=>"l","page"=>1,"search"=>"") : $_POST);

	global $wpdb,$staffdb;	//get database object
	
	//handle searching
	$w = (@!isset($limit['search']) || trim($limit['search']=="") ? false : $limit['search']);
	$where = ($w ? "WHERE sl_last LIKE '%{$w}%' OR  
				    sl_first LIKE '%{$w}%' OR  
					sl_dept LIKE '%{$w}%' " : "");
	
	//handle sorting
	$sorting =array("f" =>"sl_first ASC",
					"f-"=>"sl_first DESC",
					"l" =>"sl_last ASC",
					"l-"=>"sl_last DESC",
					"d" =>"sl_dept ASC",
					"d-"=>"sl_dept DESC"
	);
	$s = (@!isset($limit['sort']) || !array_key_exists($limit['sort'],$sorting) ? "l" : $limit['sort']);
	$sort = $sorting[$limit['sort']];
	
	//handle pager
	$count =  $wpdb->get_var("SELECT count(id) FROM $staffdb {$where}"); //echo "COUNT: $count<br /><br />";
	
	/* build page array [$pg]
	*  0:total records
	*  1:records per page << defined above
	*  2:total pages
	*  3:current page (also:$p)
	*  4:record start
	*  5:record end
	*/
	
	$pg = array($count,RECORDS_PER_PAGE,ceil($count/RECORDS_PER_PAGE));
	$p = $pg[3] = (!isset($limit['page']) || $limit['page']<1 || $limit['page']>$pg[2] ? 1 : $limit['page']);
	$pg[4] = ($pg[1]*$pg[3])-$pg[1];
	$pg[5]=(($pg[4]+$pg[1])-1);
	$limit['page'] = $pg;
	
	$pagerblock = ajax_build_header($limit);
	
	//build query
	$q =   "SELECT * FROM {$staffdb} {$where} ORDER BY {$sort} LIMIT {$pg[4]},{$pg[5]}"; //echo $q;

	$staff = $wpdb->get_results($q, ARRAY_A);
	foreach($staff as $i=>$s) {
		if(""!=trim($limit['search'])) {	//stylize matched characters
			$find = '/('.$limit['search'].')/i';
			$repl = '<strong>$1</strong>';
			$s['sl_first'] = preg_replace($find,$repl,$s['sl_first']);
			$s['sl_last']  = preg_replace($find,$repl,$s['sl_last']);
			$s['sl_dept']  = preg_replace($find,$repl,$s['sl_dept']);
		}
		echo "<tr><td><p class='contactcard'></p></td><td>{$s['sl_last']}</td><td>{$s['sl_first']}</td><td>{$s['sl_dept']}</td>
			      <td>".(""!=trim($s['sl_email']) && strstr($s['sl_email'],"@") ?
						 "<a href='mailto:{$s['sl_email']}' title='Email {$s['sl_first']} {$s['sl_last']}'>{$s['sl_email']}</a>" : 
						 $s['sl_email']).
				 "</td><td>{$s['sl_phone']}</td></tr>";
		}
	die("</table>{$pagerblock}");
}

function setup_stafflist_scripts(){
	wp_enqueue_script( "stafflistscripts", plugin_dir_url( __FILE__ ) . '/stafflist.js', array( 'jquery' ) );
}

add_action('wp_print_scripts', 'setup_stafflist_scripts');
add_action('wp_ajax_ajax_build', 'ajax_build');
add_action('wp_ajax_nopriv_ajax_build', 'ajax_build');

function ajax_build_header($limit) {

	echo "<table id='stafflists'>";
	$pagerblock = "<div class='pageNum'>Page: {$limit['page'][3]} (".($limit['page'][4]+1)." - ".($limit['page'][0]<($limit['page'][5]+1)?$limit['page'][0]:($limit['page'][5]+1))." of {$limit['page'][0]})</div>";
					for($page=1;$page<=$limit['page'][2];$page++){ 
						$pagerblock.= "<p class='pager ".($page==$limit['page'][3]?"current":"")."'><a href='javascript:sl_page({$page});' id='sl_page:{$page}'>{$page}</a></p>"; 
					}
					$pagerblock.= "</div>";
	echo "$pagerblock";
	echo "<thead id='stafflisthead'><tr>
			<th>&nbsp;</th>
			<th><a href='javascript:sl_sort(\"l\");' title='Sort by Last Name A-Z' class='sort_a ".($limit['sort'] == "" || $limit['sort'] == "l" ? "selected" : "")."' id='sl_sort:l'><span>Ascending</span></a> Last Name
				<a href='javascript:sl_sort(\"l-\");' title='Sort by Last Name Z-A' class='sort_d ".($limit['sort'] == "l-" ? "selected" : "")."' id='sl_sort:l-'><span>Descending</span></a>
			</th>
			<th><a href='javascript:sl_sort(\"f\");' title='Sort by First Name Ascending' class='sort_a ".($limit['sort'] == "f" ? "selected" : "")."' id='sl_sort:f'><span>Ascending</span></a> First Name
				<a href='javascript:sl_sort(\"f-\");' title='Sort by First Name Descending' class='sort_d ".($limit['sort'] == "f-" ? "selected" : "")."' id='sl_sort:f-'><span>Descending</span></a>
			</th>
			<th><a href='javascript:sl_sort(\"d\");' title='Sort by Department Ascending' class='sort_a ".($limit['sort'] == "d" ? "selected" : "")."' id='sl_sort:d'><span>Ascending</span></a> Department
				<a href='javascript:sl_sort(\"d-\");' title='Sort by Department Descending' class='sort_d ".($limit['sort'] == "d-" ? "selected" : "")."' id='sl_sort:d-'><span>Descending</span></a>
			</th>
			<th>Email Address</th><th>Phone / Ext</th>
		</tr></thead>";
	return($pagerblock);
}


/**************************************************************************************************
*	Some useful functions
**************************************************************************************************/
function ajax_update() {
	global $wpdb,$staffdb;

	//build query from passed vars
	$fval = $_POST['fval'];
	$fname = $_POST['fname'];
	$q = "UPDATE $staffdb SET {$fname[0]} = '{$fval}' WHERE id = {$fname[1]}";
	//print_r($_POST); echo "Query: $q";

	$wpdb->query($q);
	die(); // stop executing script
}
function ajax_insert() {
	global $wpdb,$staffdb;
	parse_str($_POST['data']);
	parse_str($rec);
	if(trim($sl_first)=="" && trim($sl_last)=="" && trim($sl_dept)=="") die(-1);
	
	$wpdb->insert( $staffdb, array( 'sl_first' => $wpdb->escape($sl_first),
									'sl_last'  => $wpdb->escape($sl_last),
									'sl_phone' => $wpdb->escape($sl_phone),
									'sl_email' => $wpdb->escape($sl_email),
									'sl_dept'  => $wpdb->escape($sl_dept)
	));
	die("1"); // stop executing script
}
function ajax_nextrow() {
	global $wpdb,$staffdb;
	//get last ID (for inserts)
	$nextid = $wpdb->get_var("SELECT (max(id)+1) FROM {$staffdb} ORDER BY id ASC LIMIT 1");
	$wpdb->insert( $staffdb, array( 'id'=>$nextid ));
	exit($nextid);
}
function deleteEmpty() {
	global $wpdb,$staffdb;
	$dq = "DELETE FROM {$staffdb} WHERE ( sl_first='' OR sl_first IS NULL )
				                    AND ( sl_last='' OR sl_last IS NULL )
								    AND ( sl_dept='' OR sl_dept IS NULL )
								    AND ( sl_phone='' OR sl_phone IS NULL )
								    AND ( sl_email='' OR sl_email IS NULL )";
	$wpdb->query($dq);
}
function myprint_r($arr) { echo "<pre>"; print_r($arr); echo "</pre>"; return; }

?>