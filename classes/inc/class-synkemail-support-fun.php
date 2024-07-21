<?php
if(!defined('ABSPATH')) {
    return;
}
class SYNK_Email_Support_Function{
    protected static $instance = null;
    public static function get_instance(){
        if(self::$instance===null){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct(){
        function synk_email_verify_nonce($nonce, $key)
        {
            if (!wp_verify_nonce($nonce, $key)) {
                $return = array('message' => esc_html__('Direct access not allowed', 'synkraft'));
                wp_send_json_error($return);
            }
        }

    }
}