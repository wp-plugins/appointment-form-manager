<?php
/*
Plugin Name: Appointment Form Manager
Description: Create custom appointment form using shortcode & get a full list of submitted form on admin panel. You can approve/cancel an appointment from plugin settings.
Version: 1.0.0
Author: Anil Meena
Author URI: http://anilmeena.com/
Plugin URI: http://divyanshiinfotech.com/
Donate link: http://divyanshiinfotech.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if( !defined( "AFM_ADMIN_URL" ) )
	define( "AFM_ADMIN_URL", admin_url() );
	
	define('ACFSURL', WP_PLUGIN_URL."/".dirname( plugin_basename( __FILE__ ) ) );

	define('ACFPATH', WP_PLUGIN_DIR."/".dirname( plugin_basename( __FILE__ ) ) );

register_activation_hook( __FILE__, 'afm_plugin_create_db' );

function afm_plugin_create_db() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'afm_list';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		fname varchar(55) DEFAULT '' NOT NULL,
		lname varchar(55) DEFAULT '' NOT NULL,
		phone varchar(15) DEFAULT '' NOT NULL,
		email varchar(55) DEFAULT '' NOT NULL,
		location varchar(55) DEFAULT '' NOT NULL,
		visit varchar(55) DEFAULT '' NOT NULL,
		comment text NOT NULL,
		callback_status varchar(10) DEFAULT 'No' NOT NULL,
		request_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		response_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

register_activation_hook( __FILE__, 'afm_plugins_default_mail_settings' );

function afm_plugins_default_mail_settings(){
		global $wpdb;
		if(get_option( 'afm_admin_mail_to_option' ) == ''){
			update_option( 'afm_admin_mail_to_option', get_option( 'admin_email' ) );
		}
		if(get_option( 'afm_admin_mail_subject' ) == ''){
			update_option( 'afm_admin_mail_subject', 'Regarding appointment form submission' );
		}
		if(get_option( 'afm_admin_mail_from_email' ) == ''){
			update_option( 'afm_admin_mail_from_email', get_option( 'admin_email' ) );
		}
		if(get_option( 'afm_admin_mail_from_name' ) == ''){
			update_option( 'afm_admin_mail_from_name', 'Appointment' );
		}
		//update_option( 'afm_admin_mail_to_bcc', $afm_admin_mail_to_bcc );
		if(get_option( 'afm_admin_send_success_msg' ) == ''){
			update_option( 'afm_admin_send_success_msg', "Thank you for booking an appointment with WP 4.2.2 Test, we will call you back to confirm a time and date within 48 hours." );
		}
		if(get_option( 'afm_admin_send_err_msg' ) == ''){
			update_option( 'afm_admin_send_err_msg', "An error found. Mail sending failed." );
		}
		if(get_option( 'afm_admin_send_name_msg' ) == ''){
			update_option( 'afm_admin_send_name_msg', get_bloginfo('name') );
		}
		if(get_option( 'afm_admin_send_email_msg' ) == ''){
			update_option( 'afm_admin_send_email_msg', get_option( 'admin_email' ) );
		}
		if(get_option( 'afm_admin_send_subject_msg' ) == ''){
			update_option( 'afm_admin_send_subject_msg', "Thank you for booking an appointment" );
		}
		if(get_option( 'afm_invalid_captcha_err_msg' ) == ''){
			update_option( "afm_invalid_captcha_err_msg", "Enter captcha is wrong." );
		}
		if(get_option( 'afm_null_captcha_err_msg' ) == ''){
			update_option( "afm_null_captcha_err_msg", "Captcha is required." );
		}
		if(get_option( 'afm_null_fname_err_msg' ) == ''){
			update_option( "afm_null_fname_err_msg", "First name is required." );
		}
		if(get_option( 'afm_null_lname_err_msg' ) == ''){
			update_option( "afm_null_lname_err_msg", "Last name is required." );
		}
		if(get_option( 'afm_null_phone_err_msg' ) == ''){
			update_option( "afm_null_phone_err_msg", "Phone number is required." );
		}
		if(get_option( 'afm_valid_phone_err_msg' ) == ''){
			update_option( "afm_valid_phone_err_msg", "Phone number is invalid." );
		}
		if(get_option( 'afm_null_email_err_msg' ) == ''){
			update_option( "afm_null_email_err_msg", "Email is required." );
		}
		if(get_option( 'afm_valid_email_err_msg' ) == ''){
			update_option( "afm_valid_email_err_msg", "Email is not valid." );
		}
		if(get_option( 'afm_null_location_err_msg' ) == ''){
			update_option( "afm_null_location_err_msg", "Location is required." );
		}
		if(get_option( 'afm_null_visit_err_msg' ) == ''){
			update_option( "afm_null_visit_err_msg", "Visit is required." );
		}
		if(get_option( 'afm_null_comment_err_msg' ) == ''){
			update_option( "afm_null_comment_err_msg", "Comment is required." );
		}
}

function afm_insert_visitor_mail_data($fname, $lname, $phone, $email, $location, $visit, $comment) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'afm_list';
	$wpdb->insert( 
		$table_name, 
		array( 
			'request_time' => current_time( 'mysql' ), 
			'fname' => $fname, 
			'lname' => $lname,
			'phone' => $phone,
			'email' => $email, 
			'location' => $location,
			'visit' => $visit, 
			'comment' => $comment
		) 
	);
}
 
function afm_display_html_form() {
?>
        <!--[pop up]-->
        	<div id="afm-popup-outer">
            		<!--[logo]-->
                    	<div class="afm-logo">

                        </div>
                    <!--[logo]-->
                    
                    <h2>Book an appointment</h2>
<?php
echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post" name="afmcontactform" id="afmcontactform" class="afm-form-outer">';
    echo '<div id="ajaxcontact-response" style="background-color:#E6E6FA ;color:blue;"></div>';
    echo '<div id="fname-wrapper">';
    echo '<label>First Name</label>';
    echo '<input type="text" name="afmfname" id="afmfname" pattern="[a-zA-Z0-9 ]+" value="' . ( isset( $_POST["afmfname"] ) ? esc_attr( $_POST["afmfname"] ) : '' ) . '" size="40" /><br>';
    echo '<div class="afm-null-error"><label></label>';
    echo '<p class="afm-error">' . get_option( "afm_null_fname_err_msg" ) . '</p></div>';
    echo '</div>';
    echo '<div id="lname-wrapper">';
    echo '<label>Surname</label>';
    echo '<input type="text" name="afmlname" id="afmlname" pattern="[a-zA-Z0-9 ]+" value="' . ( isset( $_POST["afmlname"] ) ? esc_attr( $_POST["afmlname"] ) : '' ) . '" size="40" /><br>';
    echo '<div class="afm-null-error"><label></label>';
    echo '<p class="afm-error">' . get_option( "afm_null_lname_err_msg" ) . '</p></div>';
    echo '</div>';
    echo '<div id="phone-wrapper">';
    echo '<label>Phone No</label>';
    echo '<input type="tel" name="afmphone" id="afmphone" pattern="[0-9 +-]+" value="' . ( isset( $_POST["afmphone"] ) ? esc_attr( $_POST["afmphone"] ) : '' ) . '" size="40" /><br>';
    echo '<div class="afm-null-error"><label></label>';
    echo '<p class="afm-error">' . get_option( "afm_null_phone_err_msg" ) . '</p></div>';
    echo '<div class="afm-valid-error"><label></label>';
    echo '<p class="afm-error">Please enter valid phone number.</p></div>';
    echo '</div>';
    echo '<div id="email-wrapper">';
    echo '<label>E-Mail</label>';
    echo '<input type="email" name="afmemail" id="afmemail" value="' . ( isset( $_POST["afmemail"] ) ? esc_attr( $_POST["afmemail"] ) : '' ) . '" size="40" />';
    echo '<div class="afm-null-error"><label></label>';
    echo '<p class="afm-error">' . get_option( "afm_null_email_err_msg" ) . '</p></div>';
    echo '<div class="afm-valid-error"><label></label>';
    echo '<p class="afm-error">' . get_option( "afm_valid_email_err_msg" ) . '</p></div><br>';
    echo '</div>';
    echo '<div id="location-wrapper">';
    echo '<label>Location</label>';
    echo '<select name="afmlocation" id="afmlocation">
		<option value="" disabled selected/>-- Select location --</option>
		<option value="Location-1" />Location 1</option>
		<option value="Location-2" />Location 2</option>
	 </select><br>';
    echo '<div class="afm-null-error"><label></label>';
    echo '<p class="afm-error">' . get_option( "afm_null_location_err_msg" ) . '</p></div>';
    echo '</div>';
    echo '<div id="visit-wrapper">';
    echo '<label>Type of Visit</label>';
    echo '<select name="afmvisit" id="afmvisit">
		<option value="" disabled selected/>-- Select Options --</option>
		<option value="Option-1" />Option 1</option>
		<option value="Option-2" />Option 2</option>
	 </select><br>';
    echo '<div class="afm-null-error"><label></label>';
    echo '<p class="afm-error">' . get_option( "afm_null_visit_err_msg" ) . '</p></div>';
    echo '</div>';
    echo '<div id="comment-wrapper">';
    echo '<label>Comment</label>';
    echo '<textarea rows="3" cols="35" name="afmcomment" id="afmcomment">' . ( isset( $_POST["afmcomment"] ) ? esc_attr( $_POST["afmcomment"] ) : '' ) . '</textarea><br>';
    echo '<div class="afm-null-error"><label></label>';
    echo '<p class="afm-error">' . get_option( "afm_null_comment_err_msg" ) . '</p></div>';
    echo '</div>';
    echo '<div id="captcha-wrapper">';

   $args = array(
     'chars_num'=> '6', // number of characters
     //'tcolor' => 'C5C6C8', // text color
    // 'ncolor' => 'f00', // noise color
     'dots' => 50, // number of dots
     'lines'=> 40, // number of lines,
     'width'=>'220', // number of width,
     'height'=>'70' // number of height
   );

	/* START 29-06-15 */

	$num1 = rand(0,10); // pick a random number from 0 to 10 inclusive
	$num2 = rand(0,10); // same idea
	$o = rand(0,2); // 0 = plus, 1 = minus, 2 = multiply
	/* This function will use the integer value of $operand to show either a plus, minus, or times. */
	function operand($o) {
	    switch($o) {
		 case 0: return "+"; break;
		 case 1: return "-"; break;
		 case 2: return "*"; break;
		 default: return "?"; break; //Remark: We shouldn't ever get down here.
	     }
	}
?>
	<label for="math">What is <?php echo $num1 . "&nbsp;" . operand($o) . "&nbsp;" . $num2 . " = ?"; ?></label>
	<input type="text" id="math" name="userAnswer" size="3"></input>
	<input type="hidden" id="num1" name="num1" value="<?php echo $num1; ?>"></input>
	<input type="hidden" id="operand" name="operand" value="<?php echo $o; ?>"></input>
	<input type="hidden" id="num2" name="num2" value="<?php echo $num2; ?>"></input>

<?php echo '<div class="afm-null-error"><label></label>';
    echo '<p class="afm-error">' . get_option( "afm_null_captcha_err_msg" ) . '</p></div>';
    echo '<div class="afm-valid-error"><label></label>';
    echo '<p class="afm-error">' . get_option( "afm_invalid_captcha_err_msg" ) . '</p></div><br>';
    echo '</div>';
    echo '<label></label>';
    echo '<input type="button" value="Book Now" onclick="ajaxformsendmail(afmfname.value, afmlname.value, afmphone.value, afmemail.value, afmlocation.value, afmvisit.value, afmcomment.value, userAnswer.value, num1.value, operand.value, num2.value);" style="cursor: pointer">';
    echo '</form>';
?>
            </div>
        <!--[pop up]-->
<?php
}
 
function afm_form_shortcode() {
    ob_start();
    afm_display_html_form();
    return ob_get_clean();
}
 
add_shortcode( 'afm_contact', 'afm_form_shortcode' );

function afm_plugin_add_styles() {
	wp_register_style( 'afm-main-style', plugin_dir_url( __FILE__ ) . 'css/style.css' );
	wp_enqueue_style('afm-main-style');
	wp_register_style( 'afm-popup-style', plugin_dir_url( __FILE__ ) . 'css/popup.css' );
	wp_enqueue_style('afm-popup-style');
	wp_enqueue_script('ajaxcontact', ACFSURL.'/js/ajaxcontact.js', array('jquery'));
	wp_localize_script( 'ajaxcontact', 'ajaxcontactajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'wp_enqueue_scripts', 'afm_plugin_add_styles' );

function afm_plugin_admin_add_styles(){
	wp_register_style( 'afm-admin-style', plugin_dir_url( __FILE__ ) . 'lib/css/admin.css' );
	wp_enqueue_style('afm-admin-style');
	wp_register_script( 'afm-admin-script', plugin_dir_url( __FILE__ ) . 'lib/js/admin.js' );
	wp_enqueue_script('afm-admin-script');
}
add_action( 'admin_enqueue_scripts', 'afm_plugin_admin_add_styles' );

function set_html_content_type() {
	return 'text/html';
}

add_action( 'admin_menu', 'register_afm_settings_menu_page' );
function register_afm_settings_menu_page() {
	add_menu_page( 'AFM Settings', 'AFM Settings', 'manage_options', 'afm-plugin-settings-page', 'afm_plugin_settings_callback', '', 6 );
	add_submenu_page( 'afm-plugin-settings-page', 'Manage Mail List', 'Manage Mail List', 'manage_options', 'afm-manage-list-page', 'afm_manage_list_page_callback' );
	add_submenu_page( null, 'Edit Mail List', 'Edit Mail List', 'manage_options', 'afm-edit-mail-list', 'afm_edit_mail_list_callback' );
}

function afm_plugin_settings_callback() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	} ?>
	<div class="wrap">
	<h2>Appointment Form Manager Settings</h2>
	</div>

	<div class="wrap">
	<div class="afm-admin-form-container">
	<div class="afm-admin-form-wrapper">

	<?php 
	if(isset($_POST['afm_admin_mail_setting_saved'])){

		echo '<div id="afm-admin-email-saved-wrapper"><p class="option-saved-box"><strong>Options saved</strong></p></div>';

		$afm_admin_mail_id = sanitize_email($_POST['afm_admin_mail_id']);
		$afm_admin_mail_from_email = esc_attr($_POST['afm_admin_mail_from_email']);
		$afm_admin_mail_from_name = esc_attr($_POST['afm_admin_mail_from_name']);
		$afm_admin_mail_subject = sanitize_text_field($_POST['afm_admin_mail_subject']);
		$afm_admin_mail_to_bcc = sanitize_text_field($_POST['afm_admin_mail_to_bcc']);

		if(empty($afm_admin_mail_id)){
			$afm_admin_mail_id = get_option( 'admin_email' );
		}		
		if(empty($afm_admin_mail_to_bcc)){
			$afm_admin_mail_to_bcc = "no";
		}
		else if(!empty($afm_admin_mail_to_bcc)){
			$afm_admin_mail_to_bcc = "yes";
		}
		update_option( 'afm_admin_mail_to_option', $afm_admin_mail_id );
		update_option( 'afm_admin_mail_subject', $afm_admin_mail_subject );
		update_option( 'afm_admin_mail_from_email', $afm_admin_mail_from_email );
		update_option( 'afm_admin_mail_from_name', $afm_admin_mail_from_name );
		update_option( 'afm_admin_mail_to_bcc', $afm_admin_mail_to_bcc );
	}
	if(isset($_POST['afm_admin_msg_setting_saved'])){

		echo '<div id="afm-response-email-saved-wrapper"><p class="option-saved-box"><strong>Options saved</strong></p></div>';

		$afm_admin_send_success_msg = esc_textarea($_POST['afm_admin_send_success_msg']);
		$afm_admin_send_err_msg = esc_textarea($_POST['afm_admin_send_err_msg']);
		$afm_admin_send_name_msg = sanitize_text_field($_POST['afm_admin_send_name_msg']);
		$afm_admin_send_email_msg = sanitize_email($_POST['afm_admin_send_email_msg']);
		$afm_admin_send_subject_msg = sanitize_text_field($_POST['afm_admin_send_subject_msg']);

		if(empty($afm_admin_send_success_msg)){
			$afm_admin_send_success_msg = "Thank you for booking an appointment with " . get_bloginfo('name') . ", we will call you back to confirm a time and date within 48 hours.";
		}	
		if(empty($afm_admin_send_err_msg)){
			$afm_admin_send_err_msg = "Mail sending failed.";
		}	
		if(empty($afm_admin_send_name_msg)){
			$afm_admin_send_name_msg = get_bloginfo('name');
		}	
		if(empty($afm_admin_send_email_msg)){
			$afm_admin_send_email_msg = get_bloginfo('admin_email');
		}
		if(empty($afm_admin_send_subject_msg)){
			$afm_admin_send_subject_msg = "Thank you for booking an appointment";
		}

		update_option( 'afm_admin_send_success_msg', $afm_admin_send_success_msg );
		update_option( 'afm_admin_send_err_msg', $afm_admin_send_err_msg );
		update_option( 'afm_admin_send_name_msg', $afm_admin_send_name_msg );
		update_option( 'afm_admin_send_email_msg', $afm_admin_send_email_msg );
		update_option( 'afm_admin_send_subject_msg', $afm_admin_send_subject_msg );
	}
	?>

	<form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] );?>" method="post" enctype="multipart/form-data">
		<h3>Admin Email Settings</h3>
	<table>
		<tr><td><strong>Admin Mail:</strong></td><td><input type="text" name="afm_admin_mail_id" value="<?php echo ( get_option( 'afm_admin_mail_to_option') ? get_option( 'afm_admin_mail_to_option') : '' );?>"></td></tr>

		<!--<tr><td></td><td><input type="checkbox" name="afm_admin_mail_to_bcc" <?php echo ( (get_option( 'afm_admin_mail_to_bcc' ) == 'yes') ? 'checked="checked"' : '');?>>Please check this if you also want to send a copy of email to admin in bcc.</td></tr>-->

		<tr><td><strong>From Name:</strong></td><td><input type="text" name="afm_admin_mail_from_name" value="<?php echo ( get_option( 'afm_admin_mail_from_name') ? get_option( 'afm_admin_mail_from_name') : '' );?>"></td></tr>

		<tr><td><strong>From Email:</strong></td><td><input type="text" name="afm_admin_mail_from_email" value="<?php echo ( get_option( 'afm_admin_mail_from_email') ? get_option( 'afm_admin_mail_from_email') : '' );?>"></td></tr>

		<tr><td><strong>Subject:</strong></td><td><input type="text" name="afm_admin_mail_subject" value="<?php echo ( get_option( 'afm_admin_mail_subject') ? get_option( 'afm_admin_mail_subject') : '' );?>"></td></tr>

		<tr><td><input class="button-primary" type="submit" name="afm_admin_mail_setting_saved" value="Save"></td></tr>
	</table>
	</form>

	<form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] );?>" method="post" enctype="multipart/form-data">
		<h3>Response Email Settings</h3>
	<table id="afm-msg-table">
		<tr><td><strong>From Name:</strong></td><td><input type="text" name="afm_admin_send_name_msg" value="<?php echo ( get_option( 'afm_admin_send_name_msg') ? get_option( 'afm_admin_send_name_msg') : '' );?>"></td></tr>

		<tr><td><strong>From Email:</strong></td><td><input type="text" name="afm_admin_send_email_msg" value="<?php echo ( get_option( 'afm_admin_send_email_msg') ? get_option( 'afm_admin_send_email_msg') : '' );?>"></td></tr>

		<tr><td><strong>Subject:</strong></td><td><input type="text" name="afm_admin_send_subject_msg" value="<?php echo ( get_option( 'afm_admin_send_subject_msg') ? get_option( 'afm_admin_send_subject_msg') : '' );?>"></td></tr>
		<tr><td><strong>Sender's message was sent successfully:</strong></td><td><textarea rows="4" name="afm_admin_send_success_msg"><?php echo ( get_option( 'afm_admin_send_success_msg') ? get_option( 'afm_admin_send_success_msg') : '' );?></textarea></td></tr>

		<tr><td><strong>Sender's message was failed to send:</strong></td><td><textarea rows="4" name="afm_admin_send_err_msg"><?php echo ( get_option( 'afm_admin_send_err_msg') ? get_option( 'afm_admin_send_err_msg') : '' );?></textarea></td></tr>

		<tr><td><input class="button-primary" type="submit" name="afm_admin_msg_setting_saved" value="Save"></td></tr>
	</table>
	</form>
	</div>
	</div>
<?php }

function afm_manage_list_page_callback() {
	
	echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
		echo '<h2>Manage Mail List</h2>';
	echo '</div>';

	echo '<div class="wrap">';

	$afm_mail_list = afm_get_all_mail_list_data(); ?>

		<!-- start single-icon -->
	<div class="afm-mail-list-wrapper">
	<table id="afm-mail-list-table">
		<tr><td><strong>ID</strong></td><td><strong>First Name</strong></td><td><strong>Last Name</strong></td><td><strong>Phone</strong></td><td><strong>Email</strong></td><td><strong>Location</strong></td><td><strong>Visit</strong></td><td><strong>Callback</strong></td><td><strong>Actions</strong></td></tr>

	<?php $key = 1;
	foreach ($afm_mail_list as $list) :

	$uid = $list -> id;
	$fname = $list -> fname;
	$lname = $list -> lname; 
	$phone = $list -> phone; 
	$email = $list -> email; 
	$location = $list -> location;
	$visit = $list -> visit;
	$comment = $list -> comment;
	$callback_status = $list -> callback_status;
	$request_time = $list -> request_time;
	$response_time = $list -> response_time; ?>

		<tr><td><?php echo $key;?></td><td><?php echo $fname;?></td><td><?php echo $lname;?></td><td><?php echo $phone;?></td><td><?php echo $email;?></td><td><?php echo $location;?></td><td><?php echo $visit;?></td><td><?php echo $callback_status;?></td>
	<td>
		<!-- Start Edit Icon Form -->
		<form action="<?php echo AFM_ADMIN_URL; ?>admin.php?page=afm-edit-mail-list" method="post">
		<input type="hidden" name="afm_mail_uid" value="<?php echo $uid; ?>">
		<input class="button-primary" type="submit" value="Edit" name="afm_mail_list_edit"/>
		</form>
		<!-- End Edit Icon Form -->
	</td></tr>

	<?php $key++; endforeach; ?>
	</table>
	</div>
<?php }

/* get_results() method will return all rows */
function afm_get_all_mail_list_data()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'afm_list';
    $results = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $table_name ORDER BY id DESC", ARRAY_A) );
    return $results;
}

function afm_edit_mail_list_callback(){
	echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
		echo '<h2>Edit Mail List<a href="'. get_bloginfo('siteurl') . '/wp-admin/admin.php?page=afm-manage-list-page" class="add-new-h2">View Mail List</a></h2>';
	echo '</div>';
	?>
<!-- wrap start -->
<div class="wrap">
<?php 
if(isset($_POST['afm_mail_list_edit'])):
	$uid = $_REQUEST['afm_mail_uid'];
	$afm_by_id  = afm_fetch_mail_by_id($uid); 
?>
<form method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] );?>" enctype="multipart/form-data">
<table class="afm-edit-mail-table">
         
        <tr valign="top">
        <th scope="row">First Name</th>
        <td><input type="hidden" name="afm_edit_mail_id" value="<?php echo $afm_by_id->id;?>" />
        <input type="text" name="afm_edit_mail_fname" value="<?php echo $afm_by_id->fname;?>" disabled/></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">Last Name</th>
        <td><input type="text" name="afm_edit_mail_lname" value="<?php echo $afm_by_id->lname;?>" disabled/></td>
        </tr>

        <tr valign="top">
        <th scope="row">Phone</th>
        <td><input type="text" name="afm_edit_mail_phone" value="<?php echo $afm_by_id->phone;?>" disabled/></td>
        </tr>

        <tr valign="top">
        <th scope="row">Email</th>
        <td><input type="text" name="afm_edit_mail_email" value="<?php echo $afm_by_id->email;?>" disabled/></td>
        </tr>

        <tr valign="top">
        <th scope="row">Location</th>
        <td><input type="text" name="afm_edit_mail_location" value="<?php echo $afm_by_id->location;?>" disabled/></td>
        </tr>

        <tr valign="top">
        <th scope="row">Visit</th>
        <td><input type="text" name="afm_edit_mail_visit" value="<?php echo $afm_by_id->visit;?>" disabled/></td>
        </tr>

        <tr valign="top">
        <th scope="row">Comment</th>
        <td><textarea name="afm_edit_mail_comment" disabled><?php echo $afm_by_id->comment;?></textarea></td>
        </tr>

        <tr valign="top">
        <th scope="row">Callback</th>
        <td>
		<?php if($afm_by_id->callback_status == 'Yes') { ?>
		<input type="radio" name="afm_edit_mail_callback_status" value="Yes" checked/>Yes
		<input type="radio" name="afm_edit_mail_callback_status" value="No"/>No
		<?php } else { ?>
		<input type="radio" name="afm_edit_mail_callback_status" value="Yes"/>Yes
		<input type="radio" name="afm_edit_mail_callback_status" value="No" checked/>No
		<?php } ?>
	</td>
        </tr>

        <tr valign="top">
        <th scope="row">Request Time</th>
        <td><input type="text" name="afm_edit_mail_request_time" value="<?php echo $afm_by_id->request_time;?>" disabled/></td>
        </tr>

        <tr valign="top">
        <th scope="row">Response Time</th>
        <td><input type="text" name="afm_edit_mail_response_time" value="<?php echo $afm_by_id->response_time;?>" disabled/></td>
        </tr>

    </table>

    <input class="button-primary" type="submit" name="afm_update_mail_list" value="Submit">

</form>
<?php endif; 

if ( isset($_POST['afm_update_mail_list']) ) :

$afm_edit_mail_id = sanitize_text_field($_POST['afm_edit_mail_id']);
$afm_edit_mail_fname = sanitize_text_field($_POST['afm_edit_mail_fname']);
$afm_edit_mail_lname = sanitize_text_field($_POST['afm_edit_mail_lname']);
$afm_edit_mail_phone = sanitize_text_field($_POST['afm_edit_mail_phone']);
$afm_edit_mail_email = sanitize_email($_POST['afm_edit_mail_email']);
$afm_edit_mail_location = sanitize_text_field($_POST['afm_edit_mail_location']);
$afm_edit_mail_visit = sanitize_text_field($_POST['afm_edit_mail_visit']);
$afm_edit_mail_comment = esc_textarea($_POST['afm_edit_mail_comment']);
$afm_edit_mail_callback_status = sanitize_text_field($_POST['afm_edit_mail_callback_status']);
$afm_edit_mail_request_time = sanitize_text_field($_POST['afm_edit_mail_request_time']);
$afm_edit_mail_response_time = sanitize_text_field($_POST['afm_edit_mail_response_time']);

	afm_mail_list_update_status($afm_edit_mail_callback_status, $afm_edit_mail_response_time, $afm_edit_mail_id)
	?>
	<a href="<?php echo get_bloginfo('siteurl');?>/wp-admin/admin.php?page=afm-manage-list-page/">Back to Manage Mail List</a>
<?php endif; ?>
</div>
<?php } 

function afm_fetch_mail_by_id($id){
    global $wpdb;
    $table_name = $wpdb->prefix . 'afm_list';
    $results = $wpdb->get_row( $wpdb->prepare("SELECT * FROM  $table_name  WHERE id = %d", $id, ARRAY_A) );
    return $results;
}

function afm_mail_list_update_status($status, $response, $afm_id){
	global $wpdb;
    $table_name = $wpdb->prefix . 'afm_list';
    $wpdb->update( 
	$table_name, 
	array( 
		'callback_status' => $status,
		'response_time' => current_time( 'mysql' )
	), 
	array( 'id' => $afm_id ), 
	array( 
		'%s',
		'%s'
	), 
	array( '%d' ) 
);
} 

function ajaxcontact_show_contact(){ ?>

<form id="ajaxcontactform" action="" method="post"enctype="multipart/form-data">

<div id="ajaxcontact-text">

<div id="ajaxcontact-response" style="background-color:#E6E6FA ;color:blue;"></div>

<strong>Name </strong> <br/>

<input type="text" id="ajaxcontactname" name="ajaxcontactname"/><br />

<br/>

<strong>Email </strong> <br/>

<input type="text" id="ajaxcontactemail" name="ajaxcontactemail"/><br />

<br/>

<strong>Subject </strong> <br/>

<input type="text" id="ajaxcontactsubject" name="ajaxcontactsubject"/><br />

<br/>

<strong>Contents </strong> <br/>

<textarea id="ajaxcontactcontents" name="ajaxcontactcontents"  rows="10" cols="20"></textarea><br />

<a onclick="ajaxformsendmail(ajaxcontactname.value,ajaxcontactemail.value,ajaxcontactsubject.value,ajaxcontactcontents.value);" style="cursor: pointer"><b>Send Mail</b></a>
</div>
</form>

<?php }

function ajaxcontact_send_mail(){
	$results = '';

	$error = 0;

	$acffname = sanitize_text_field( $_POST['acffname'] );

	$acflname = sanitize_text_field( $_POST['acflname'] );

	$acfphone = filter_var(sanitize_text_field( $_POST['acfphone'] ), FILTER_SANITIZE_NUMBER_INT);

	$acfphone = str_replace('+', '', $acfphone);

	$acfphone = str_replace('-', '', $acfphone);

	$acfemail = sanitize_email( $_POST['acfemail'] );

	$acflocation = sanitize_text_field( $_POST['acflocation'] );

	$acfvisit = sanitize_text_field( $_POST['acfvisit'] );

	$acfcomment = esc_textarea( $_POST['acfcomment'] );

	$acfcaptcha = sanitize_text_field( $_POST['acfcaptcha'] );

	$num1 = filter_var(sanitize_text_field( $_POST['num1'] ), FILTER_SANITIZE_NUMBER_INT);

	$num1 = str_replace('+', '', $num1);

	$num1 = str_replace('-', '', $num1);

	$num2 = filter_var(sanitize_text_field( $_POST['num2'] ), FILTER_SANITIZE_NUMBER_INT);

	$operand = filter_var(sanitize_text_field( $_POST['operand'] ), FILTER_SANITIZE_NUMBER_INT);

	$userAnswer = filter_var(sanitize_text_field( $_POST['userAnswer'] ), FILTER_SANITIZE_NUMBER_INT);

	// Calculate the actual answer
	$actual = -999; # Init variable
	switch($operand) {
	    case 0: $actual = $num1 + $num2; break; // 0 = Addition
	    case 1: $actual = $num1 - $num2; break; // 1 = Subtraction
	    case 2: $actual = $num1 * $num2; break; // 2 = Multiplication
	}
	/* Check against the user's input and cancel form submission if it's incorrect */

if( strlen($userAnswer) == 0 ){
	$results = get_option( "afm_null_captcha_err_mg" );	
	echo '<style>#captcha-wrapper .afm-null-error{display: block;}#math{background: #f0bebe !important;}</style>';
	$error = 1;
}
else if(!empty($userAnswer) && ($userAnswer != $actual)) {
	$results = get_option( "afm_invalid_captcha_err_msg");
	echo '<style>#captcha-wrapper .afm-valid-error{display: block;}#math{background: #f0bebe !important;}</style>';
	$error = 1;
}
else{	
	$error = 0;
}

$admin_email = get_option('admin_email');
if( strlen($acffname) == 0 ){
	$results = get_option( "afm_null_fname_err_msg" );
	echo '<style>#fname-wrapper .afm-null-error{display: block;}#afmfname{background: #f0bebe !important;}</style>';
	$error = 1;
}
if( strlen($acflname) == 0 ){
	$results = get_option( "afm_null_lname_err_msg" );
	echo '<style>#lname-wrapper .afm-null-error{display: block;}#afmlname{background: #f0bebe !important;}</style>';
	$error = 1;
}
if( strlen($acfphone) == 0 ){
	$results = get_option( "afm_null_phone_err_msg" );
	echo '<style>#phone-wrapper .afm-null-error{display: block;}#afmphone{background: #f0bebe !important;}</style>';
	$error = 1;
}
else{
	if( ( strlen($acfphone) < 10 ) || ( strlen($acfphone) > 12 ) ){
	$results = get_option( "afm_valid_phone_err_msg" );
	echo '<style>#phone-wrapper .afm-valid-error{display: block;}#afmphone{background: #f0bebe !important;}</style>';
	$error = 1;
}
}
/*if( strlen($acfemail) == 0 ){
	$results = get_option( "afm_null_email_err_msg" );
	echo '<style>#email-wrapper .afm-null-error{display: block;}#afmemail{background: #f0bebe !important;}</style>';
	$error = 1;
}*/
if (!filter_var($acfemail, FILTER_VALIDATE_EMAIL)){ 
	$results = get_option( "afm_valid_email_err_msg" );
	echo '<style>#email-wrapper .afm-valid-error{display: block;}#afmemail{background: #f0bebe !important;}</style>';
	$error = 1;
}
if( strlen($acflocation) == 0 ){
	$results = get_option( "afm_null_location_err_msg" );
	echo '<style>#location-wrapper .afm-null-error{display: block;}#afmlocation{background: #f0bebe !important;}</style>';
	$error = 1;
}
if( strlen($acfvisit) == 0 ){
	$results = get_option( "afm_null_visit_err_msg" );
	echo '<style>#visit-wrapper .afm-null-error{display: block;}#afmvisit{background: #f0bebe !important;}</style>';
	$error = 1;
}
if( strlen($acfcomment) == 0 ){
	$results = get_option( "afm_null_comment_err_msg" );
	echo '<style>#comment-wrapper .afm-null-error{display: block;}#afmcomment{background: #f0bebe !important;}</style>';
	$error = 1;
}
if($error == 0){		
		echo '<style>#ajaxcontact-response{display:inline;}</style>';
		$to = get_option( 'afm_admin_mail_to_option' );
		$subject = get_option( 'afm_admin_mail_subject' );
		if(empty($subject)){
			$subject = "Appointment form submitted";
		}
		/*if($acflocation == "Bray"){
			$admin_mail = get_option('afm_admin_mail_bray_location');
		} elseif($acflocation == "Fairview") {
			$admin_mail = get_option('afm_admin_mail_fairview_location');
		}*/

		$admin_mail = get_option( 'afm_admin_mail_to_option' );

		$user_subject = "Thank you for booking an appointment";

		$mail_body = "<div><p><strong>First Name: </strong>$acffname</p><p><strong>Surname: </strong>$acflname</p><p><strong>Phone No: </strong>$acfphone</p><p><strong>E-mail: </strong>$acfemail</p><p><strong>Location: </strong>$acflocation</p><p><strong>Type of Visit: </strong>$acfvisit</p><p><strong>Comments: </strong>$acfcomment</p></div>";

		$user_mail_body = "<div><p><strong>" . get_option( 'afm_admin_send_success_msg') . "</strong></p></div>";

		//$custom_email = strtolower($acffname . "_" . $acflname . "@".ie");

		$from_name = get_option( 'afm_admin_mail_from_name' );
		$from_email = get_option( 'afm_admin_mail_from_email' );

		$res_from_name = get_option( 'afm_admin_send_name_msg' );
		$res_from_email = get_option( '$afm_admin_send_email_msg');
		if(empty($from_name)){
			$from_name = "$acffname $acflname";
		}
		/*if(empty($from_email)){
			$from_email = $custom_email;
		}*/
		if(empty($res_from_name)){
			$res_from_name = get_bloginfo('name');
		}
		if(empty($res_from_email)){
			$res_from_email = $to;
		}

		$headers[] = "From: $from_name <$from_email>";
		if(get_option( 'afm_admin_mail_to_bcc') == 'yes'){
		$headers[] = "Bcc: $to";
		}
		
		$user_headers[] = "Reply-To: " . $to;
		$user_headers[] = "From: $res_from_name <$res_from_email>";
		
			add_filter( 'wp_mail_content_type', 'set_html_content_type' );
			// If email has been process for sending, display a success comment
			if ( wp_mail( $admin_mail, $subject, $mail_body, $headers ) ) {
			    echo '<div>';
			    echo '<p class="afm-success">' . get_option( 'afm_admin_send_success_msg') . '</p>';
			    echo '</div>';

			// Response mail
			    wp_mail( $acfemail, $user_subject, $user_mail_body, $user_headers );

			    afm_insert_visitor_mail_data($acffname, $acflname, $acfphone, $acfemail, $acflocation, $acfvisit, $acfcomment);

			remove_filter( 'wp_mail_content_type', 'set_html_content_type' );

			} else {
			    echo '<p class="afm-error">' . get_option( 'afm_admin_send_err_msg') . '</p>';
			}
}

// Return the String

die($results);

}

// creating Ajax call for WordPress

add_action( 'wp_ajax_nopriv_ajaxcontact_send_mail', 'ajaxcontact_send_mail' );

add_action( 'wp_ajax_ajaxcontact_send_mail', 'ajaxcontact_send_mail' );

