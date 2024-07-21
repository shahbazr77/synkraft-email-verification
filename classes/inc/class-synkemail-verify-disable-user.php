<?php

class Synk_Email_Disable_User {
	private static $instance;
	private static $user_meta_key = '_is_disabled';
	public static function get_instance() {
		if ( empty( self::$instance ) && ! ( self::$instance instanceof Synk_Email_Disable_User ) ) {
			self::$instance = new Synk_Email_Disable_User();
			self::$instance->synkemail_add_hooks();
			//do_action( 'disable_user_login.loaded' );
		}
		return self::$instance;
	}
	private function __construct() {

	}
	private function synkemail_add_hooks() {
		if ( is_admin() ) {
            add_filter( 'manage_users_custom_column',array($this, 'synkemail_users_column_content' ), 10, 3 );
			add_action( 'admin_footer-users.php',array($this, 'synkemail_manage_users_css'));
		}
		// Filters
		add_filter('authenticate',array($this,'synkemail_user_login'), 1000, 3 );
		add_filter('manage_users_columns',array( $this,'synkemail_manage_users_columns'));
	}

    public function synkemail_manage_users_columns( $defaults ) {
        $defaults['disable_user_login'] = __( 'Status', 'synkeverify' );
        return $defaults;
    }
	public function synkemail_user_login( $user, $username, $password ) {
		if ( is_a( $user, 'WP_User' ) ) {
			// Is the user logging in disabled?
			if ( $this->synkemail_is_user_disabled( $user->ID ) ) {
				do_action( 'disable_user_login.disabled_login_attempt', $user );
                $user_id=$user->ID;
                $user_meta = get_userdata($user_id);
                $user_email = $user_meta->user_email;
                $main_login_url=wp_login_url()."/?custom_resend_email_pro=true&user_id=$user_id";
                $email_sent_status= get_user_meta($user_id,'user_email_send_status',true);
                $synk_email_number_value = sanitize_text_field(get_option('synk-email-limit-email-send-pro','4'));
                $message ='<strong>'.esc_html__('Notification','synkeverify').'</strong><span id="custom-msg">:'.esc_html__('Your Account Not Verified Yet Please Send Email To Verify','synkeverify').'<br>';
                $message .='<div style="text-align: center;margin:15px 0px">
        <button class="ppppp" id="resend-email" data-user-id="'.$user_id.'" data-resend-count="'.$email_sent_status.'" data-email-set-count="'.$synk_email_number_value.'" style="display: inline-block; padding: 10px 20px; background-color: #0073e6; color: #fff; text-decoration: none;border:0px;cursor:pointer">'.esc_html__('Resend Verify Email','synkeverify').'</button>
    </div>';
                return new WP_Error( 'disable_user_login_user_disabled', apply_filters( 'disable_user_login.disabled_message', $message) );
				//return new WP_Error( 'disable_user_login_user_disabled', apply_filters( 'disable_user_login.disabled_message', __( '<strong>ERROR</strong>: Your Account Not Verified.', 'synkeverify' ) ) );
			}
		}
		//Pass on any existing errors
		return $user;
	}
	public function synkemail_users_column_content( $output, $column_name, $user_id ) {
		if ( $column_name == 'disable_user_login' ) {
			if ( get_the_author_meta( self::$user_meta_key, $user_id ) == 1 ) {
				return __( 'Unverified', 'synkeverify' );
			}else{
                return __( 'Verified', 'synkeverify' );
            }
		}

		return $output; // always return, otherwise we overwrite stuff from other plugins.
	}
	public function synkemail_manage_users_css() {
		echo '<style type="text/css">.column-disable_user_login { width: 80px; }</style>';
	}
	private function synkemail_is_user_disabled( $user_id ) {
	    $disabled = get_user_meta( $user_id, self::$user_meta_key, true );
		if ( $disabled == '1' ) {
			return true;
		}
		return false;
	}

}
//end class Synk_Email_Disable_User
