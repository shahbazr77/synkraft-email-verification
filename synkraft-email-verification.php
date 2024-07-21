<?php /**
 * Plugin Name: Synkraft Email Verification
 * Plugin URI: https://synkemailverify/
 * Description: This plugin is essential for configure email for singup verification.
 * Version: 0.01
 * Author: Emails, Email Verification
 * Author URI: https://synkemailverify/
 * Text Domain: synkeverify
 * Requires at least:Synk_Email_Admin_main
 * Requires PHP:synk_email_settings_fun
 */

//Exit if accessed directly

if(!defined('ABSPATH')){
    return;
}
define("SYNKEmail_Verify_PATH", plugin_dir_path(__FILE__));
define("SYNKEmail_Verify_URL", plugins_url('',__FILE__));
define("SYNKEmail_Verify_VERSION",0.01);

class Synkraft_Email_Verify{

    protected static $instance = null;

    //Get instance
    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function __construct(){
        include_once SYNKEmail_Verify_PATH.'/admin/synk-email-admin.php';
        Synk_Email_Admin_main::get_instance();
            if(!class_exists('Synkraft_Email_Verify_Pro')) {
                $this->synkemail_verify_load_file();
            }
    }

   function synkemail_verify_load_file(){
           require_once SYNKEmail_Verify_PATH.'/classes/inc/class-synkemail-verify-scripts.php';
           Synk_Email_Verify_Scripts::get_instance();
           require_once SYNKEmail_Verify_PATH.'/classes/inc/class-synkemail-core-functions.php';
           Synk_Email_Core_Functions::get_instance();
           require_once SYNKEmail_Verify_PATH.'/classes/inc/class-synkemail-verify-disable-user.php';
           Synk_Email_Disable_User::get_instance();
           require_once SYNKEmail_Verify_PATH.'/classes/inc/class-synkemail-support-fun.php';
           SYNK_Email_Support_Function::get_instance();
           require_once SYNKEmail_Verify_PATH.'/classes/inc/synk-email-actions.php';
           SYNK_Email_Actions_Function::get_instance();
   }

}
Synkraft_Email_Verify::get_instance();

