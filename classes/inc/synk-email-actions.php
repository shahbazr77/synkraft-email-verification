<?php
if(!defined('ABSPATH')) {
    return;
}
class SYNK_Email_Actions_Function{
    protected static $instance = null;

    public static function get_instance(){
        if(self::$instance===null){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct(){
        add_action('wp_ajax_nopriv_send_verify_email', array($this,'send_verify_email_fun'));
        add_action('wp_ajax_send_verify_email', array($this,'send_verify_email_fun'));
    }

    function send_verify_email_fun(){
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : "";
        synk_email_verify_nonce($nonce, 'ajax-nonce');
        $user_id = isset($_POST['userid']) ? $_POST['userid'] : "";
        $resend_count = isset($_POST['resend_count']) ? $_POST['resend_count'] : "";

        if($user_id) {
            $user_info = get_userdata($user_id);
            $code = md5(time());
            $string = array('id' => $user_id, 'code' => $code);
            $user_meta = get_userdata($user_id);
            $user_email = $user_meta->user_email;
            $user_roles = $user_meta->roles;
            update_user_meta($user_id, '_is_disabled', 1);
            update_user_meta($user_id, 'user_old_role', $user_roles[0]);
            if($resend_count!=""){
                $resend_count=$resend_count+1;
                update_user_meta($user_id, 'user_email_send_status', $resend_count);
            }else{
                update_user_meta($user_id, 'user_email_send_status', 1);

            }
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
                $email_sent_status= get_user_meta($user_id,'user_email_send_status',true);
                $email_status = wp_mail($user_email, $email_subject, $email_message, $headers);
                $main_login_url=wp_login_url()."/?custom_resend_email=true&user_id=$user_id";
                $return = array('message' => "Successfully Sent Email",'email_name'=>$user_email,'email_count'=>$email_sent_status,'url_path'=>$main_login_url);

                wp_send_json_success($return);

//               if($email_status){
//                $return = array('message' => "Verify Email Sent '.$user_email.' Successfully '.$email_sent_status.'Time");
//                wp_send_json_success($return);
//               }else{
//                $return = array('message' => "Sorry Somthing Went Wrong");
//                wp_send_json_error($return);
//               }


            } else {
                return $user_id->get_error_message();
            }

        }


    }




}
