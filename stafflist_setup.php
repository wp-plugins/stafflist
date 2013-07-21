<?php

function stafflist_install () {
	
	global $wpdb;
	$table_name = $wpdb->prefix . "stafflist";
	
	$sql = "CREATE TABLE $table_name (
	id mediumint(9) NOT NULL AUTO_INCREMENT,
	sl_first varchar(64) NOT NULL,
	sl_last varchar(64),
	sl_phone varchar(16),
	sl_email varchar(32),
	sl_dept varchar(32),
	UNIQUE KEY id (id)
	);";
	
	//echo "SQL: $sql";
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	
	echo "FOR UPDATE: $forUpdate";
	
	//insert sample data if none
	$count =  $wpdb->get_var("SELECT count(id) from $table_name");
	if($count < 1) stafflist_install_data();

	return;
}

function stafflist_install_data () {
	global $wpdb;
	$table_name = $wpdb->prefix . "stafflist";

	$rows_affected = $wpdb->insert( $table_name, array( 'sl_first' => 'Lucille', 
														'sl_last' => 'Bluth',
														'sl_phone' => '(212) 123-4567',
														'sl_email' => 'lbluth@bluthinc.com',
														'sl_dept' => 'Human Resources'
													  ));
	return;
}
?>
