<?php

//Exit if accessed directly
if (!defined('ABSPATH')) {
    return;
}

class Synk_Email_Core_Functions
{
    protected static $instance = null;
    public function __construct()
    {
        add_action('init',array($this,'synk_email_value_action'),11);
    }

    //Get class instance
    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    function synk_email_value_action(){
        $synk_email_status_value = sanitize_text_field(get_option('synk-email-on-off','true'));
        if($synk_email_status_value){
            add_action( 'user_register', array($this, 'check_register_users'), 999, 1 );
            add_action('init', array($this,'custom_email_verification_endpoint'),999,1);
            add_filter('login_message',array($this,'custom_login_message'));
            add_filter('login_message',array($this,'custom_resend_email'));
            add_action('woocommerce_thankyou',array($this, 'synkeamil_custom_check_mail'));
        }
    }
    function check_register_users( $user_id ) {
        $user_info = get_userdata($user_id);
        $code = md5(time());
        $string = array('id'=>$user_id, 'code'=>$code);
        $user_meta = get_userdata($user_id);
        $user_email = $user_meta->user_email;
        $user_roles = $user_meta->roles;
        update_user_meta($user_id, '_is_disabled', 1);
        update_user_meta($user_id,'user_old_role',$user_roles[0]);
        update_user_meta($user_id,'user_email_send_status',1);
        if (!is_wp_error($user_id)) {
            // Generate a unique verification token and store it in user meta
            $verification_token = wp_generate_password(32, false);
            update_user_meta($user_id, 'verification_token', $verification_token);
            $verification_link = site_url("/?token=$verification_token");
            $email_subject = esc_html__('Verify Your Email Address','synkeverify');
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
            $email_message = '<html><head><title>'.esc_html__('Verify Your Account','synkeverify').'</title></head>
            <body>
                <p>'.esc_html__('Hello','synkeverify').' '.$user_email.'</p>
                <p>'.esc_html__('Click the following Button to verify your email address:','synkeverify').'</p>
                <a href="'.$verification_link.'" target="_blank" style="background-color: #0073e6; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 5px;">'.esc_html__('Click To Verify','synkeverify').'</a>
                <p>'.esc_html__('Thank you','synkeverify').'.</p>
            </body>
            </html>';
            $email_status=wp_mail($user_email, $email_subject, $email_message, $headers);
            $sessions = WP_Session_Tokens::get_instance( $user_id );
            $sessions->destroy_all();
            if (class_exists('WooCommerce') && is_checkout()) {

            }else{
                $main_login_url = wp_login_url() . "/?custom_resend_email=true&user_id=$user_id";
                wp_redirect($main_login_url);
                die();
            }

        } else {
            return $user_id->get_error_message();
        }
    }
    function custom_email_verification_endpoint($request) {
        if (isset($_GET['token'])) {
            $token = sanitize_text_field($_GET['token']);
            $meta_key = 'verification_token';
            $meta_value = $token;

            $args = array(
                'meta_key' => $meta_key,
                'meta_value' => $meta_value,
                'fields' => 'ID', // You can change this to 'all' to get full user objects
            );
            $user_data = get_users($args);
            $user_id=isset($user_data[0]) ? $user_data[0]:'';
            if (!empty($user_data)) {
                if (is_array($user_data) || is_object($user_data)) {
                    foreach ($user_data as $user_id) {
                        $user = get_userdata($user_id);
                        delete_user_meta($user->ID, 'verification_token');
                        update_user_meta($user->ID, '_is_disabled', 0);
                        $main_login_url = wp_login_url() . "/?custom_message=true";
                        wp_redirect($main_login_url);
                        exit();
                    }
                }

            }else {
                echo "<div class='custom-message' style='background-color: #949494;position: absolute;top: 15px;left:40%;padding: 10px;z-index:9999;'>".esc_html__("Invalid verification token.")."</div>";

            }

        }
    }
    function custom_login_message($message) {
        if (isset($_GET['custom_message'])) {
            $message ='<div id="login_error"><strong>'.esc_html__('Notification','synkeverify').'</strong>: '.esc_html__('Thank you! Your account has been verified successfully, you can login now.','synkeverify').'<br></div>';
        }
        return $message;
    }
    function custom_resend_email($message) {
        if (isset($_GET['custom_resend_email'])) {
            $user_id=$_GET['user_id'];
            $user_meta = get_userdata($user_id);
            $user_email = $user_meta->user_email;
            $email_sent_status= get_user_meta($user_id,'user_email_send_status',true);
            $message ='<div id="login_error"><strong>'.esc_html__('Notification','synkeverify').'</strong><span id="custom-msg">:'.esc_html__('Thank you! We have sent you an verification email on ','synkeverify').'  <span id="email-name">'.$user_email.  '</span>  '.esc_html__('to login your account.','synkeverify').'  <span id="email-counter" style="background-color: #0c88b4;color: white;padding: 4px 8px;border-radius: 50%;width: 10px;height: 10px;display: table-cell;text-align: center;">'.$email_sent_status.'</span> Time</span><br></div>';
            $message .='<div style="text-align: center;margin:15px 0px">
        <button id="resend-email" data-resend-count="'.$email_sent_status.'" data-user-id="'.$user_id.'" style="display: inline-block; padding: 10px 20px; background-color: #0073e6; color: #fff; text-decoration: none;border:0px;cursor:pointer;">'.esc_html__('Resend Verify Email','synkeverify').'</button>
       <input type="hidden" id="resend-count" value="'.$email_sent_status.'">
    </div>';
        }
        return $message;
    }
    function synkeamil_custom_check_mail() {
        $user_id = get_current_user_id();
        $user_status=get_user_meta($user_id, '_is_disabled', 1);
        if($user_status==1){
            $main_login_url = wp_login_url();
           $mail_button_verify='<a href="'.$main_login_url.'" id="resend-email" data-user-id="'.$user_id.'" style="display: inline-block; padding: 10px 20px; background-color: #0073e6; color: #fff; text-decoration: none;border:0px;cursor:pointer;">'.esc_html__('Login To Verify','synkeverify').'</button>';
           echo "<div class='custom-message' style='background-color: #949494;position: absolute;top: 15px;left:25%;padding-left: 15px;z-index:9999;'>".esc_html__("Email Send To Your Account Verification or Click Button to Login ").$mail_button_verify."</div>";
            wp_logout();
        }else{

        }
    }
}