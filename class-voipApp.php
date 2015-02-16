<?php
class VoipApp {

    const VERSION = '1.0.0';

    protected $plugin_slug = 'VoipAppWidget';

    protected static $instance = null;

    private function __construct() {
        add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
    }

    public function get_table_handle()
{
    global $wpdb;
    return $wpdb->prefix . "voipAppWidget";
}

    public function get_plugin_slug() {
        return $this->plugin_slug;
    }

    public static function get_instance() {

        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public static function activate( ) {
        global $wpdb;
        add_option('myplug_modify_widget', 0);
        $VoipAppWidget_prefs_table = self::get_table_handle();
        $charset_collate = '';
        if (version_compare(mysql_get_server_info(), '4.1.0', '>=')) {
            $charset_collate = "DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci";
        }

        if ($wpdb->get_var("SHOW TABLES LIKE '$VoipAppWidget_prefs_table'") != $VoipAppWidget_prefs_table) {
            $sql = "CREATE TABLE `" . $VoipAppWidget_prefs_table . "` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `widget` VARCHAR(255) NOT NULL default '',
            UNIQUE KEY id (id)
        )$charset_collate";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }else{
            var_dump('Activate Error');die();
        }

    }

    public static function deactivate( ) {
        global $wpdb;
        $VoipAppWidget_prefs_table = self::get_table_handle();
        delete_option('myplug_modify_widget');
        $sql = "DROP TABLE $VoipAppWidget_prefs_table";
        $wpdb->query($sql);
    }

    public static function uninstall()
    {
        if ( ! current_user_can( 'activate_plugins' ) )
            return;

        check_admin_referer( 'bulk-plugins' );
        unregister_setting( 'voipApp-settings-group', 'voipApp-settings' );
        delete_option( 'voipApp-settings' );

    }

    public function load_plugin_textdomain() {
        load_plugin_textdomain( 'VoipAppWidget', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }
}

