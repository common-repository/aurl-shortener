<?php
/**
 * Plugin Name: AURL Shortener
 * Plugin URI: http://aurl.xyz
 * Description: This plugin allows you to shorten your URLs.
 * Version: 1.0
 * Author: Mahdi Maymandi-Nejad
 * License: MIT
 */

global $aurl_dbverison;

$aurl_dbversion = '1.0';
function aurl_installdb() {
	global $wpdb;
	global $cwidget_dbversion;
$tablename = $wpdb->prefix . 'aurlapi';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $tablename (
		id tinytext NOT NULL,
		apikey tinytext NOT NULL
		
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'jal_db_version', $aurl_dbversion );
}

register_activation_hook( __FILE__, 'aurl_installdb' );

add_action( 'admin_menu', 'aurl_menu' );

/** Step 1. */
function aurl_menu() {
	add_options_page( 'AURL Settings', 'AURL', 'manage_options', 'aurl-settings', 'aurl_settings' );
}

/** Step 3. */
function aurl_settings() {
    global $wpdb;
    global $wp;
    $tablename = $wpdb->prefix . 'aurlapi';
	$row = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aurlapi");
    $u_1 = menu_page_url( 'aurl-settings', 0);
	$url = wp_nonce_url("$u_1", 'aurl_set');
	
	echo '<div class="wrap">';
echo "<form method='POST' action='$url'";
echo '<p>Fill the form below. Write your api keys from AURL.XYZ</p>';
	echo "<p><input type='text' name='private' placeholder='API KEY'>";
			echo "<p><input type='submit' value='Submit'>";
			echo "</form>";
	echo '</div>';
	if (isset($_POST['private'])){
	    check_admin_referer( 'aurl_set' );
	    $admin = current_user_can( 'administrator' );
	    if ($admin = false){
	        echo "Can't change the API key.";
	    } else{
	    $key2 = $_POST["private"];
	    $key = sanitize_key($key2);
	    if ($row > 0){
	        $wpdb->update( 
$tablename, 
array( 
		'apikey' => $key,	// string
		'id' => '1'	// integer (number) 
	), 
	array( 'id' => 1 ), 
	array( 
		'%s',	// value1
		'%d'	// value2
	), 
	array( '%d' ) 

);

	    } else{

	        $wpdb->insert( 
	$tablename, 
	array( 
		'apikey' => $key, 
		'id' => '1',
	) 
);

	    }
	    }
}
}
add_shortcode( "shorten", "aurl_short" );

function aurl_short() { 
    global $wpdb;
   $url = get_permalink();
   $api_1 = $wpdb->get_row("SELECT apikey FROM {$wpdb->prefix}aurlapi WHERE id=1");
   $api = $api_1->apikey;
   $u_1 = "https://aurl.xyz/api/?key=$api&url=$url";
   $surl = wp_remote_get($u_1);
   $body = wp_remote_retrieve_body($surl);
   $data = json_decode($body, TRUE);
   $short = $data['short'];
   $ts = str_replace("https://", "", $short);
   $txt = "<a href='$short'>$ts</a>";
   return $txt;
} ?>