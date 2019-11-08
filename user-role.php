<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
/*
Plugin Name: Users Role
Description: Users Role and IP Check plugin
Version:     1.0.0
Author:      KafleG
Author URI:  http://www.kafleg.com.np
License:     GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: users-role
*/
// Function to get the client IP address
function users_role_get_client_ip() {
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

add_action('admin_menu', 'users_role_setup_menu');
 
function users_role_setup_menu(){
        add_menu_page( 'Users Role Plugin Page', 'Users Role Plugin', 'manage_options', 'test-plugin', 'users_role_plugin_settings_page' );
}
function users_role_plugin_settings_page() {
?>
<div class="wrap">
<h1><?php esc_html__('Users Details', 'user-roles'); ?></h1>

<table style="width:100%" border="1">
  <tr>
    <th>Username</th>
    <th>Role</th>
    <th>IP Address</th>
    <th>Last Activity ( Date-Time)</th>
    <th>Status</th>
  </tr>
    <?php
    $users = get_users();
    // Array of WP_User objects.
    foreach ( $users as $user ) {
        $login_info =  get_user_meta( $user->ID, 'login_info' , true );
        ?>
        <tr>
            <td><?php echo $user->data->user_login;?></td>
            <td><?php echo implode(",",$user->roles); ?></td>
            <td><?php echo isset($login_info['login-ip'])?$login_info['login-ip']:'Unknown';?></td>
            <td><?php echo isset($login_info['last-activity'])?$login_info['last-activity']:'Unknown';?></td>
            <td><?php
                if(isset( $login_info['is-login'] ) && $login_info['is-login']){
                    echo 'Login';
                }
                else{
                    echo 'Logout';
                }
                ?></td>
        </tr>
        <?php
    }
    ?>

</table>

</div>
<?php }

function users_role_add_user_info_login($user_login, $user){
    $user_info = $user->data;
    $ip_address = users_role_get_client_ip();

    $main_array = array(
        'login-ip' => $ip_address,
        'last-activity' => date("Y/m/d").'-'.date("h:i:sa"),
        'is-login' => 1,
    );
    update_user_meta( $user_info->ID, 'login_info', $main_array );
}
add_action('wp_login', 'users_role_add_user_info_login',10,2);

function users_role_add_user_info_logout() {
    $user = wp_get_current_user();
    $user_info = $user->data;

    $login_info =  get_user_meta( $user->ID, 'login_info' , true );
    $login_info['last-activity'] = date("Y/m/d").'-'.date("h:i:sa");
    $login_info['is-login'] = 0;

    update_user_meta( $user_info->ID, 'login_info', $login_info );
}
add_action('clear_auth_cookie', 'users_role_add_user_info_logout', 10);