<?php
//Exit if accessed directly
if(!defined('ABSPATH')){
    return;
}
class Synk_Email_Admin_main
{
    private $status_license;
    private $pro_image;
    protected static $instance = null;
    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function __construct(){
        add_action('admin_enqueue_scripts', array($this,'synk_email_admin_enqueue'));
        add_action('admin_menu', array($this,'synk_email_menu_settings'));
        if(!class_exists('Synkraft_Email_Verify_Pro') ){
            $this->status_license="disabled";
            $crown_imag=SYNKRAFT_Plugin_Url . 'assets/css/icons/crown.svg';
            $this->pro_image='<img src="'.$crown_imag.'" />';
        }

    }
    function synk_email_admin_enqueue()
    {
        wp_enqueue_style('synkemail-verify-admin-css', SYNKEmail_Verify_URL . '/admin/assets/css/synk-email-admin-css.css', null, SYNKEmail_Verify_VERSION);
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('synkemail-verify-admin-js', SYNKEmail_Verify_URL . '/admin/assets/js/synk-email-admin-js.js', array('jquery', 'wp-color-picker'), SYNKEmail_Verify_VERSION, true);
    }
    function synk_email_menu_settings(){
        add_menu_page('Synkraft Email Verification', 'Synk Email Verification', 'manage_options', 'synk_email_verify', array($this, 'synk_email_settings_fun'), 'dashicons-bell', 41);
        add_action('admin_init', array($this,'synk_email_settings'));
    }
    function synk_email_settings_fun(){
        include plugin_dir_path(__FILE__) . 'synk-email-settings.php';
        Synk_Email_Admin_settings::get_instance();
    }
    function synk_email_settings()
    {
        $diable_field="";
        $synk_email_option_val = sanitize_text_field(get_option('synk-email-on-off','true'));

        register_setting(
            'synk-email-verify-group',
            'synk-email-on-off'
        );
        add_settings_section(
            'synk-email-container-start',
            '',
            array($this,'synk_email_container_start'),
            'synk_email_verify'
        );
        add_settings_section(
            'synk-email-container-close',
            '',
            array($this,'synk_email_container_close'),
            'synk_email_verify'
        );
        add_settings_field(
            'synk-email-on-off',
            esc_html__('Register Email Verification','synkeverify'),
            array($this,'synk_email_verify_on_off'),
            'synk_email_verify',
            'synk-email-container-start'
        );
       if(class_exists('Synkraft_Email_Verify_Pro') ) {
           if ($synk_email_option_val == "true") {
               register_setting(
                   'synk-email-verify-group',
                   'synk-email-expiry-time-limit'
               );
               register_setting(
                   'synk-email-verify-group',
                   'synk-email-limit-email-send-pro'
               );
               register_setting(
                   'synk-email-verify-group',
                   'synk-email-resend-expiry'
               );
               add_settings_field(
                   'synk-email-expiry-time-limit',
                   esc_html__("Email Expire Token Time Limit", "synkeverify"),
                   array($this, 'synk_email_expiry_time_limit'),
                   'synk_email_verify',
                   'synk-email-container-start',
               );
               add_settings_field(
                   'synk-email-limit-email-send-pro',
                   esc_html__('Send Email Limit', 'synkeverify'),
                   array($this, 'synk_email_verify_limited_email'),
                   'synk_email_verify',
                   'synk-email-container-start',
               );
               add_settings_field(
                   'synk-email-resend-expiry',
                   esc_html__('Resend Expiry Limit', 'synkeverify'),
                   array($this, 'synk_email_verify_resend_expiry'),
                   'synk_email_verify',
                   'synk-email-container-start',
               );
           }
       }else {
           add_settings_section(
               'synk-email-container-disable-start',
               '',
               array($this,'synk_email_container_disable_start'),
               'synk_email_verify'
           );
           add_settings_field(
               'synk-email-expiry-time-limit',
               esc_html__("Email Expire Token Time Limit", "synkeverify"),
               array($this, 'synk_email_expiry_time_limit_disable'),
               'synk_email_verify',
               'synk-email-container-disable-start',
           );
           add_settings_field(
               'synk-email-limit-email-send-pro',
               esc_html__('Send Email Limit', 'synkeverify'),
               array($this, 'synk_email_verify_limited_email_disable'),
               'synk_email_verify',
               'synk-email-container-disable-start',
           );
           add_settings_field(
               'synk-email-resend-expiry',
               esc_html__('Resend Expiry Limit', 'synkeverify'),
               array($this, 'synk_email_verify_resend_expiry_disable'),
               'synk_email_verify',
               'synk-email-container-disable-start',
           );

           add_settings_section(
               'synk-email-container-disable-close',
               '',
               array($this,'synk_email_container_disable_close'),
               'synk_email_verify'
           );

       }


    }
    //Settings Section Callback

    function synk_email_container_start(){
        $tab = '<div class="main-settings">';  //Begin Main settings
        echo $tab;
        echo '<h4>'.esc_html__('General Registration Email Verification Options','synkeverify').'</h4>';
    }
    function synk_email_verify_on_off(){
        $synk_email_option_val = sanitize_text_field(get_option('synk-email-on-off','true'));
        if(!isset($synk_email_option_val)){
            $synk_email_option_val="true";
        }
        $html  = '<input class="form-check-input" type="checkbox" name="synk-email-on-off" id="synk-email-on-off" value="true"'.checked('true',$synk_email_option_val,false).'>';
        $html .= '<label for="xoo-cp-gl-atcem" class="ps-3">'.esc_html__('Register Verify Email Status.','synkeverify').'</label>';
        echo $html;
    }
    function synk_email_container_close(){
        ob_start();
        $html  = ob_get_clean();
        $html .= '</div>'; // End Advanced settings
        echo $html;
    }
    function synk_email_expiry_time_limit(){
        $synk_email_option_val = sanitize_text_field(get_option('synk-email-on-off','true'));
        if($synk_email_option_val=="true") {
            $synk_email_expiry_time_limit = sanitize_text_field(get_option('synk-email-expiry-time-limit', '3'));
            if ($synk_email_expiry_time_limit == "") {
                $synk_email_expiry_time_limit = "3";
            }
            $html = '<select name="synk-email-expiry-time-limit" id="synk-email-expiry-time-limit">
            <option value="1" ' . (($synk_email_expiry_time_limit === '1') ? 'selected' : '') . '>1 Hour</option>
            <option value="3" ' . (($synk_email_expiry_time_limit === '3') ? 'selected' : '') . '>3 Hours</option>
            <option value="6" ' . (($synk_email_expiry_time_limit === '6') ? 'selected' : '') . '>6 Hours</option>
            <option value="12" ' . (($synk_email_expiry_time_limit === '12') ? 'selected' : '') . '>12 Hours</option>
            <option value="24" ' . (($synk_email_expiry_time_limit === '24') ? 'selected' : '') . '>24 Hours</option>
        </select>';
            echo $html;
        }
    }
    function synk_email_verify_limited_email(){
        $synk_email_option_val = sanitize_text_field(get_option('synk-email-on-off','true'));
        if($synk_email_option_val=="true") {
            $synk_email_number_value = sanitize_text_field(get_option('synk-email-limit-email-send-pro', '4'));
            if ($synk_email_number_value == "") {
                $synk_email_number_value = "4";
            }
            $html = '<input type="number" class="input_attributes email-numbered" name="synk-email-limit-email-send-pro" min="1" id="synk-email-limit-email-send-pro" value="' . $synk_email_number_value . '" >';
            $html .= '<label for="synk-pop-sy-btnbr"></label>';
            echo $html;
        }
    }
    function synk_email_verify_resend_expiry(){
        $synk_email_option_val = sanitize_text_field(get_option('synk-email-on-off','true'));
        if($synk_email_option_val=="true") {
            $synk_email_resend_expiry = sanitize_text_field(get_option('synk-email-resend-expiry', '3'));
            if ($synk_email_resend_expiry == "") {
                $synk_email_resend_expiry = "3";
            }
            $html = '<select name="synk-email-resend-expiry" id="synk-email-resend-expiry">
            <option value="1" ' . (($synk_email_resend_expiry === '1') ? 'selected' : '') . '>1 Hour</option>
            <option value="3" ' . (($synk_email_resend_expiry === '3') ? 'selected' : '') . '>3 Hours</option>
            <option value="6" ' . (($synk_email_resend_expiry === '6') ? 'selected' : '') . '>6 Hours</option>
            <option value="12" ' . (($synk_email_resend_expiry === '12') ? 'selected' : '') . '>12 Hours</option>
            <option value="24" ' . (($synk_email_resend_expiry === '24') ? 'selected' : '') . '>24 Hours</option>
        </select>';
            echo $html;
        }
    }

    function synk_email_container_disable_start(){
        $tab = '<div class="main-settings-disable  '.$this->status_license.'">';  //Begin Main settings
        echo $tab;
    }
    function synk_email_expiry_time_limit_disable(){
            $synk_email_expiry_time_limit = sanitize_text_field(get_option('synk-email-expiry-time-limit', '3'));
            if ($synk_email_expiry_time_limit == "") {
                $synk_email_expiry_time_limit = "3";
            }

            $html = '<select class="form-control float-start" name="synk-email-expiry-time-limit" id="synk-email-expiry-time-limit" '.$this->status_license.'>
            <option value="1" ' . (($synk_email_expiry_time_limit === '1') ? 'selected' : '') . '>1 Hour</option>
            <option value="3" ' . (($synk_email_expiry_time_limit === '3') ? 'selected' : '') . '>3 Hours</option>
            <option value="6" ' . (($synk_email_expiry_time_limit === '6') ? 'selected' : '') . '>6 Hours</option>
            <option value="12" ' . (($synk_email_expiry_time_limit === '12') ? 'selected' : '') . '>12 Hours</option>
            <option value="24" ' . (($synk_email_expiry_time_limit === '24') ? 'selected' : '') . '>24 Hours</option>
        </select>'.$this->pro_image;
            echo $html;

    }
    function synk_email_verify_limited_email_disable(){
            $synk_email_number_value = sanitize_text_field(get_option('synk-email-limit-email-send-pro', '4'));
            if ($synk_email_number_value == "") {
                $synk_email_number_value = "4";
            }
            $html = '<input type="number" class="float-start input_attributes email-numbered form-control" name="synk-email-limit-email-send-pro" min="1" id="synk-email-limit-email-send-pro" value="' . $synk_email_number_value . '"  '.$this->status_license.'>'.$this->pro_image;
            $html .= '<label for="synk-pop-sy-btnbr"></label>';
            echo $html;

    }
    function synk_email_verify_resend_expiry_disable(){
            $synk_email_resend_expiry = sanitize_text_field(get_option('synk-email-resend-expiry', '3'));
            if ($synk_email_resend_expiry == "") {
                $synk_email_resend_expiry = "3";
            }
            $html = '<select class="float-start form-control" name="synk-email-resend-expiry" id="synk-email-resend-expiry" '.$this->status_license.'>
            <option value="1" ' . (($synk_email_resend_expiry === '1') ? 'selected' : '') . '>1 Hour</option>
            <option value="3" ' . (($synk_email_resend_expiry === '3') ? 'selected' : '') . '>3 Hours</option>
            <option value="6" ' . (($synk_email_resend_expiry === '6') ? 'selected' : '') . '>6 Hours</option>
            <option value="12" ' . (($synk_email_resend_expiry === '12') ? 'selected' : '') . '>12 Hours</option>
            <option value="24" ' . (($synk_email_resend_expiry === '24') ? 'selected' : '') . '>24 Hours</option>
        </select>'.$this->pro_image;
            echo $html;

    }
    function synk_email_container_disable_close(){
        ob_start();
        $html  = ob_get_clean();
        $html .= '</div>'; // End Advanced settings
        echo $html;
    }

}