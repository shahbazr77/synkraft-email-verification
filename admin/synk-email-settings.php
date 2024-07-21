<?php
if(!defined('ABSPATH')){
    return;
}
class Synk_Email_Admin_settings{
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
        $pro_button_html="";
        $selected_plugin_name = isset($_GET['plugin_name']) ? $_GET['plugin_name'] : '';
        $selected_class_name = isset($_GET['plugin_class_name']) ? $_GET['plugin_class_name'] : '';
        $selected_class_function = isset($_GET['plugin_class_function']) ? $_GET['plugin_class_function'] : '';
        if(!empty($selected_class_name) && !empty($selected_class_function)) {
            if (class_exists($selected_class_name)) {
                $pro_button_html=active_deactive_option_page_button($selected_plugin_name,$selected_class_name,$selected_class_function);
            }
        }
        settings_errors(); ?>
        <div class="synk-container">
        <div class="synk-main">
            <form method="POST" action="options.php" class="synk-email-form">
                <?php settings_fields('synk-email-verify-group'); ?>
                <?php do_settings_sections('synk_email_verify'); ?>
                <div class="row my-4">
                    <div class="col-sm-6">
                        <?php
                        submit_button('Save Settings', 'child-plugin-button', 'submit', false);
                        ?>
                    </div>
                    <div class="col-sm-6">
                        <?php
                        echo $pro_button_html;
                        ?>

                    </div>
                </div>
            </form>
        </div>
        </div>
  <?php

    }

}


