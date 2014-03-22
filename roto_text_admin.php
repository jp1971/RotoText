<?php

$roto_text_admin_url = admin_url().'options-general.php?page=rototext';

add_action('admin_menu', 'roto_text_menu');
function roto_text_menu() {
	add_options_page( 'RotoText', 'RotoText', 'update_plugins', 'rototext', 'roto_text_options' );
}

// Add settings link on plugin page
function roto_text_settings_link( $links ) { 
  $settings_link = '<a href="options-general.php?page=rototext">Settings</a>'; 
  array_unshift( $links, $settings_link ); 
  return $links; 
}
add_filter( "plugin_action_links_$plugin_basename", 'roto_text_settings_link' );


function roto_text_options() {
	if ( $_POST ) {
		// process the posted data and display summary page - not pretty :(
		roto_text_save( $_POST );
	}

	$action = isset( $_GET['action'] ) ? $_GET['action'] : false;
	switch( $action ){
		case 'new' :
			roto_text_edit();
			break;
		case 'edit' :
			$id = intval( $_GET['id'] );
			roto_text_edit( $id );
			break;
		case 'delete' :
			$id = intval( $_GET['id'] );
			check_admin_referer( 'roto_text_delete' . $id );
			roto_text_delete( $id );
			// now display summary page
			roto_text_list();
			break;
		default:
			roto_text_list();
	}
}

function roto_text_page_title( $suffix='' ) {
 return '
 <div id="icon-options-general" class="icon32"><br/></div><h2>RotoText '.$suffix.'</h2>
 ';
}

function roto_text_error( $text='An undefined error has occured.' ) {
	echo '<div class="wrap">' . roto_text_page_title( ' - ERROR!' ) . '<h3>' . $text . '</h3></div>';
}
 
function roto_text_list() {
	global $wpdb, $user_ID, $roto_text_admin_url;
	$table_name = $wpdb->prefix . 'roto_text';
	$pageURL = $roto_text_admin_url;
	$cat = isset( $_GET['cat'] ) ? $_GET['cat'] : false;
	$author_id = isset( $_GET['author_id'] ) ? intval( $_GET['author_id'] ) : 0;
	$where = $page_params = '';

	if( $cat ) {
		$where = " WHERE category = '$cat'";
		$page_params = '&cat='.urlencode( $cat );
	}
	if( $author_id ) {
		$where = " WHERE user_id = $author_id";
		$page_params .= '&author_id='.$author_id;
	}
	
	// pagination related

	$item_count = $wpdb->get_row( "Select count(*) items FROM $table_name $where" );
	if( isset( $item_count->items ) ) {
		$totalrows = 	$item_count->items;
	} else {
		echo '<h3>The expected database table "<i>' . $table_name . '</i>" does not appear to exist.</h3>';
	}
	
	$perpage = 20;
	$paged = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 0;
	$paged = $paged ? $paged : 1;

	$num_pages = 1 + floor( $totalrows / $perpage );

	if($paged > $num_pages) { $paged = $num_pages; }
	
	$del_paged = ( $paged > 1 ) ? '&paged='.$paged : ''; // so we stay on the current page if we delete an item
	
	$paging = paginate_links( array(
		'base' => $pageURL . $page_params . '%_%', // add_query_arg( 'paged', '%#%' ),
		'format' => '&paged=%#%',
		'prev_text' => __( '&laquo;' ),
		'next_text' => __( '&raquo;' ),
		'total' => $num_pages,
		'current' => $paged
		) );
	
	// now load the data to display

	$startrow = ( $paged - 1 ) * $perpage;	
	$rows = $wpdb->get_results( "SELECT * FROM $table_name $where ORDER BY roto_text_id LIMIT $startrow, $perpage" );
	$item_range = count( $rows );
	if( $item_range > 1 ) {
		$item_range = ( $startrow + 1 ) . ' - ' . ( $startrow + $item_range );
	}
	
	$author = array();

	?>
<div class="wrap">
	<h2>
		RotoText
		<input style="display: inline-block;" type="submit" class="add-new-h2" id="roto_text_add" name="roto_text_add" value="Add New" onclick="location.href='options-general.php?page=rototext&action=new'"/>
		<!-- <a href="http://vgrnt.jp1971.com/wp-admin/post-new.php" class="add-new-h2">Add New</a> -->
	</h2>
	<div class="tablenav">
		<div class="alignleft actions">
			
			Category: <select style="float: none;" id="roto_text_category" name="roto_text_category" onchange="javascript:window.location='<?php echo $pageURL . '&cat='; ?>'+(this.options[this.selectedIndex].value);">
			<option value="">View all categories </option>
			<?php echo roto_text_get_category_options( $cat ); ?>
			</select>
		</div>
		<div class="tablenav-pages">
			<span class="displaying-num">Displaying <?php echo $item_range . ' of ' . $totalrows; ?></span>
			<?php echo $paging; ?>
		</div>
	</div>

	<table class="widefat">
	<thead><tr>
		<th>ID</th>
		<th>Text</th>
		<th width="10%">Category</th>
		<th width="10%">Author</th>
		<th width="10%">Action</th>
	</tr></thead>
	<tbody>
<?php		
	$alt = '';
	foreach ( $rows as $row ) {
		$alt = ( $alt ) ? '' : ' class="alternate"'; // stripey :)
		if( !isset( $author[$row->user_id] ) ){
			$user_info = get_userdata( $row->user_id );
			$author[$row->user_id] = $user_info->display_name;
		}
		$bytes = strlen( $row->text );
		if( strlen( $row->text ) > 200 ) {
			$row->text = trim(mb_substr( $row->text, 0, 350, 'UTF-8' ) ) . '...';
		}
		echo '<tr' . $alt . '>
		<td>' . $row->roto_text_id . '</td>
		<td>' . esc_html($row->text) . '</td>
		<td><a href="' . $pageURL . '&cat=' . $row->category . '">' . $row->category . '</a><br /></td>
		<td class="author column-author"><a href="' . $pageURL . '&author_id=' . $row->user_id . '">' . $author[ $row->user_id ] . '</a><br />' . $bytes . ' bytes</td>
		<td><a href="' . $pageURL . '&action=edit&id=' . $row->roto_text_id . '">Edit</a><br />';
		$del_link = wp_nonce_url( $pageURL . $del_paged . '&action=delete&id=' . $row->roto_text_id, 'roto_text_delete'  .  $row->roto_text_id );
		echo '<a onclick="if ( confirm(\'You are about to delete post #' . $row->roto_text_id . '\n Cancel to stop, OK to delete.\') ) { return true; }return false;" href="' . $del_link . '" title="Delete this post" class="submitdelete">Delete</a>';
		echo '</td></tr>';		
	}
	echo '</tbody></table>';

  echo '</div>';
}

function roto_text_edit( $roto_text_id = 0 ) {
	
	echo '<div class="wrap">';
	$title = '- Add New';
	if ( $roto_text_id ) {
		$title = '- Edit';
		
		global $wpdb;
		$table_name = $wpdb->prefix . 'roto_text';
		$sql = "SELECT * from $table_name where roto_text_id=$roto_text_id";
		$row = $wpdb->get_row( $sql );
		if ( !$row ) {
			$error_text = '<h2>The requested entry was not found.</h2>';
		}
	} else {
		$row = new stdClass();
		$row->text = '';
		$row->visible = 'yes';
	}
	echo roto_text_page_title( $title ); 
	
	if ( $roto_text_id && !$row ) {
		echo '<h3>The requested entry was not found.</h3>';
	} else {
	// display the add/edit form 
	global $roto_text_admin_url;
	
		echo '<form method="post" action="' . $roto_text_admin_url . '">
			' . wp_nonce_field( 'roto_text_edit' . $roto_text_id ) . '
			<input type="hidden" id="roto_text_id" name="roto_text_id" value="' . $roto_text_id . '">
			<h3>Text To Display</h3>
			<textarea name="roto_text_text" style="width: 80%; height: 100px;">' . apply_filters( 'format_to_edit', $row->text ) . '</textarea>
			<h3>Category</h3>
			<p>Select a category from the list or enter a new one.</p>
			<label for="roto_text_category">Category: </label><select id="roto_text_category" name="roto_text_category">'; 
		echo roto_text_get_category_options( $row->category );
		echo '</select></p>
			<p><label for="roto_text_category_new">New Category: </label><input type="text" id="roto_text_category_new" name="roto_text_category_new"></p>';
		echo '<div class="submit">
			<input class="button-primary" type="submit" name="roto_text_save" value="Save Changes" />
			</div>
			</form>
			
			<p>Return to <a href="' . $roto_text_admin_url . '">RotoText summary page</a>.</p>';
	}
  echo '</div>';	
}

function roto_text_save( $data ) {
	global $wpdb, $user_ID;
	$table_name = $wpdb->prefix . 'roto_text';
	
	$roto_text_id = intval( $data['roto_text_id'] );
	check_admin_referer( 'roto_text_edit' . $roto_text_id );
	
	$sqldata = array();
	$category_new = trim( $data['roto_text_category_new'] );
	$sqldata['category'] = ( $category_new ) ? $category_new : $data['roto_text_category'];
	$sqldata['user_id'] = $user_ID;
	$sqldata['visible'] = 'yes';
	
	$sqldata['text'] = trim( stripslashes( $data['roto_text_text'] ) );
	if ( $roto_text_id ) {
		$wpdb->update( $table_name, $sqldata, array( 'roto_text_id'=>$roto_text_id ) );
	} else {
		$wpdb->insert( $table_name, $sqldata );
	}
}

function roto_text_delete( $id ) {

	global $wpdb;

	$table_name = $wpdb->prefix . 'roto_text';
	$id = intval( $id );
	$sql = "DELETE FROM $table_name WHERE roto_text_id = $id";
	$wpdb->query( $sql );
}

?>