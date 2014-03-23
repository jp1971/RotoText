<?php
/*
Plugin name: RotoText
Description: Create and categorize text then display on even rotation
Version: 1.0.0
Author: Jameson Proctor
Author URI: http://jp1971.com/
License: GPL2
*/

/*  Copyright 2014 Jameson Proctor (jameson@jp1971.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

class KrnlRotoText {

	function get_next( $category = '' ) {

		global $wpdb;

		$table_name = $wpdb->prefix . 'roto_text';
	  	$sql = 'SELECT roto_text_id, text FROM '. $table_name . " WHERE visible='yes' ";
		$sql .= ( $category!='' ) ? " AND category = '$category'" : '' ;
		$sql .= ' ORDER BY timestamp, roto_text_id LIMIT 1 ';
		$row = $wpdb->get_row( $sql );
		
		// update the timestamp of the row we just seleted (used by rotator, not by random)
		if( intval( $row->roto_text_id ) ) {
			$sql = 'UPDATE ' . $table_name . ' SET timestamp = Now() WHERE roto_text_id = ' . intval( $row->roto_text_id );
			$wpdb->query( $sql );
		}
		
		// now we can safely render shortcodes without self recursion (unless there is only one item containing [randomtext] shortcode - don't do that, it's just silly!)
		$snippet = do_shortcode( $row->text );
		
		return $snippet;
	}
	
	function update( $new_instance, $old_instance ) {

	  	$instance = $old_instance;
	  	$instance['title'] = strip_tags( stripslashes( $new_instance['title'] ) );
		$instance['category'] = strip_tags( strip_tags( stripslashes( $new_instance['category'] ) ) );
		$instance['pretext'] = $new_instance['pretext'];
		$instance['posttext'] = $new_instance['posttext'];
		$instance['random'] = intval( $new_instance['random'] );

	  	return $instance;
	}
	
	function form( $instance ) {
		
		$instance = wp_parse_args( (array)$instance, array( 'title' => 'Random Text', 'category' => '', 'pretext' => '', 'posttext' => '' ) );
		
		$title = htmlspecialchars( $instance['title'] );
		$category = htmlspecialchars( $instance['category'] ) ;
		$pretext = htmlspecialchars( $instance['pretext'] );
		$posttext = htmlspecialchars( $instance['posttext'] );
		if( !isset( $instance['random'] ) ) { $instance['random'] = 0; }
  
		echo '<p>
				<label for="' . $this->get_field_name( 'title' ) . '">Title: </label> 
				<input type="text" id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name( 'title' ) . '" value="' . $title . '"/>
			</p><p>
				<label for="' . $this->get_field_name( 'pretext' ) . '">Pre-Text: </label> 
				<input type="text" id="' . $this->get_field_id( 'pretext' ) . '" name="' . $this->get_field_name( 'pretext' ) . '" value="' . $pretext . '"/>
			</p><p>
				<label for="' . $this->get_field_name( 'category' ) . '">Category: </label>
				<select id="' . $this->get_field_id( 'category' ) . '" name="' . $this->get_field_name( 'category' ) . '">
				<option value="">All Categories </option>';
		echo roto_text_get_category_options( $instance['category'] );
		echo '</select></p>
			<p>
				<label for="' . $this->get_field_name( 'posttext' ) . '">Post-Text: </label> 
				<input type="text" id="' . $this->get_field_id( 'posttext' ) . '" name="' . $this->get_field_name( 'posttext' ) . '" value="' . $posttext . '"/>
			</p>
			<p>
				<label for="' . $this->get_field_name( 'random' ) . '">Selection: </label> 
				<select id="' . $this->get_field_id( 'random' ).'" name="'.$this->get_field_name( 'random' ).'">
				<option value="1" '.selected( intval( $instance['random']), 1 ).'>Random</option>
				<option value="0" '.selected( intval( $instance['random']), 0 ).'>Rotation</option>
				</select><br/>
				<span class="description">Note: Random can be more intensive with large record sets, and some items may never appear.</span>
			</p>'; 
	}

	function enqueue_scripts() {
		wp_enqueue_script(
			'roto_text'//$handle
			,plugins_url() . "/roto-text/js/roto_text.js" //$src
			,array( 'jquery' ) //$deps (dependencies)
			,'1.0' //$ver
			,false //$in_footer
		);
		wp_localize_script( 'roto_text', 'krt_ajax', array( 'url' => home_url( 'wp-admin/admin-ajax.php' ), 'nonce' => wp_create_nonce( 'krt_ajax_nonce' ) ) );
	}
}

function roto_text( $category ) {
	$roto_text = new KrnlRotoText;
	echo $roto_text->get_next( $category );
}

function roto_text_get_category_options( $category='' ) {

	global $wpdb;

	$table_name = $wpdb->prefix . 'roto_text';
	$sql = 'SELECT category FROM ' . $table_name . ' GROUP BY category ORDER BY category';
	$rows = $wpdb->get_results( $sql );
	
	$option_nocategory = false;
	$nocategory_name = 'No Category';
	
	foreach ( $rows as $row ) {
		$selected = ( $category==$row->category ) ? 'SELECTED' : '';
		$categoryname = $row->category;		
		if ( trim( $categoryname ) == '' ) {
			$categoryname = $nocategory_name;
			$option_nocategory = true;
		}
		$result .= '<option value="' . $row->category . '" ' . $selected . '>' . $categoryname . ' </option>';
	}
	if ( !$option_nocategory )
		$result = '<option value="">' . $nocategory_name . ' </option>' . $result;
	return $result;
}

register_activation_hook( __FILE__, 'roto_text_install' );
function roto_text_install() {

	global $wpdb, $user_ID;

	$table_name = $wpdb->prefix . 'roto_text';
	// create the table if it doesn't exist 
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
		$sql = "CREATE TABLE `$table_name` (
			`roto_text_id` int(10) unsigned NOT NULL auto_increment,
			`category` varchar(32) character set utf8 NOT NULL,
			`text` text character set utf8 NOT NULL,
			`visible` enum('yes','no') NOT NULL default 'yes',
			`user_id` int(10) unsigned NOT NULL,
			`timestamp` timestamp NOT NULL default '0000-00-00 00:00:00',
			PRIMARY KEY  (`roto_text_id`),
			KEY `visible` (`visible`),
			KEY `category` (`category`),
			KEY `timestamp` (`timestamp`) 
		)";
		$results = $wpdb->query( $sql );
	}
}

if ( is_admin() ) {
	$plugin_basename = plugin_basename( __FILE__ ); 
	include 'roto_text_admin.php';
}

add_action( 'wp_enqueue_scripts', array( 'KrnlRotoText', 'enqueue_scripts' ) );

add_action( 'wp_ajax_krt_roto_text', 'krt_roto_text' );
add_action( 'wp_ajax_nopriv_krt_roto_text', 'krt_roto_text' );
	function krt_roto_text( ) {
		roto_text( $_GET['category'] );
		die;
	}	
?>