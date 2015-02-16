<?php

class VoipApp_Admin extends VoipApp_Abstract{
    const MAIN_URL = 'http://excitel.ru/';

    protected static $instance = null;

    protected $plugin_screen_hook_suffix = null;

    protected $voipApp_auth = false;

    protected $api_key = null;

    protected $widgets = array();

    protected $return = array();

    public $widget = null;

    public $widget_link = 'http://excitel.ru/widget/';

    private function __construct() {
        $plugin 				= VoipApp::get_instance();
        $this->plugin_slug 		= $plugin->get_plugin_slug();
        $this->table_name       = $plugin->get_table_handle();
        $this->config_options 	= get_option( 'voipApp-settings' );
        $this->buttons_empty	= false;
        $this->voipApp_url 	= VoipApp_Abstract::VOIP_IP;
        $this->get_active_widget();
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
        add_action( 'wp_enqueue_scripts',array( $this, 'load_styles' ) );
        add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'init' ) );
        add_filter( 'wp_footer',  array( $this, 'show_widget' ));
        add_action( 'admin_footer', array( $this,'excitel_ajax_action') );


    }

    public function excitel_ajax_action() { ?>
        <script type="text/javascript" >

            function send_ajax(hash){
                var data = {
                    'widget': hash
                };
                jQuery.post("<?=$_SERVER['REQUEST_URI']?>", data, function(response) {
                    window.location.href = "<?=$_SERVER['REQUEST_URI']?>";
                });
            }
            function remove_ajax(hash){
                var data = {
                    'remove_widget': hash
                };
                jQuery.post("<?=$_SERVER['REQUEST_URI']?>", data, function(response) {
                    window.location.href = "<?=$_SERVER['REQUEST_URI']?>";
                });
            }

        </script> <?php
    }
    public static function get_instance() {
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function enqueue_admin_styles() {
        if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
            return;
        }
        $screen = get_current_screen();
        if ( $this->plugin_screen_hook_suffix == $screen->id ) {
            wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'css/voipAppWidget.css', __FILE__ ), array(), VoipApp::VERSION );
        }
    }

    function load_styles()
    {
        wp_register_style( $this->plugin_slug .'-main-styles', plugins_url( 'css/voipAppWidget.css', __FILE__ ), array(), VoipApp::VERSION, 'all');
        wp_enqueue_style($this->plugin_slug .'-main-styles');
    }


    public function add_plugin_admin_menu() {
        $icon =  plugins_url( 'images/logot3.svg', __FILE__ );
        $this->plugin_screen_hook_suffix = add_menu_page(
            __( 'Excitel - Click to call Tools', $this->plugin_slug ),
            __( 'Excitel', $this->plugin_slug ),
            'manage_options',
            $this->plugin_slug,
            array( $this, 'display_plugin_admin_page' ),$icon
        );
    }

    public function display_plugin_admin_page() {
       include('views/admin.php');
    }

    public function add_action_links( $links ) {
        $links[] = '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>';
        return $links;
    }

    function init() {
        register_setting( 'voipApp-settings-group', 'voipApp-settings' );
        $this->voipApp_auth = ( isset( $this->config_options['selected_config'] ) && $this->config_options['selected_config'] != '' ) ? true : false;
    } // init

    function config_page() {

        if( $_SERVER['REQUEST_METHOD'] == 'POST' && isset( $_POST['voipApp_auth'] ) ){
            if(!$this->voipApp_auth){
                $this->return = $this->authenticate(); // THIS IS AN ATTEMPT TO AUTHENTICATE; MAY RETURN EITHER FORM
            }
            if( isset( $this->return['message'] )){
                $this->voipApp_auth = false;
                $this->message = __('We could not find an account that matches that email and password combination.', $this->plugin_slug );
            }else if( empty( $this->return['available_configs'] ) ){
                $this->voipApp_auth = false;
                $this->buttons_empty = true;
            }else{
                $this->voipApp_auth = true;
                $this->config_options['available_configs'] = $this->return['available_configs'];
            }
        }

        if($this->voipApp_auth){
            $this->get_active_widget();
            $this->get_widgets();


        }
        if( $_SERVER['REQUEST_METHOD'] == 'POST' && isset( $_POST['widget'] ) ){
            $this->set_widget($_POST['widget']);
        }
        if( $_SERVER['REQUEST_METHOD'] == 'POST' && isset( $_POST['logout'] ) ){
            $this->logout();
        }
        if( $_SERVER['REQUEST_METHOD'] == 'POST' && isset( $_POST['get_widgets'] ) ){
            $this->get_widgets();
        }
        if( $_SERVER['REQUEST_METHOD'] == 'POST' && isset( $_POST['remove_widget'] ) ){
            $this->remove_widget();
        }
    } // End function config_page()

    private function logout(){
        $_SESSION['api_key'] = '';
        $_SESSION['active_widget'] = '';
        $_SESSION['widgets'] = '';
    }

    public function remove_widget(){
        global $wpdb;
        $sql_del = "TRUNCATE TABLE $this->table_name";
        $wpdb->query($sql_del);
        $_SESSION['active_widget'] = '';
    }

    function set_widget($widget){
        global $wpdb;
        $sql_del = "TRUNCATE TABLE $this->table_name";
        $wpdb->query($sql_del);
        $sql
            = "INSERT INTO $this->table_name (`widget`) VALUES('$widget')";
        $wpdb->query($sql);
        $_SESSION['active_widget'] = $widget;
    }

    protected function get_active_widget()
    {
        global $wpdb;
        $sql = "SELECT * FROM $this->table_name";
        $widgetId = $wpdb->get_row($sql);
        if ($widgetId) {
            $_SESSION['active_widget'] = $widgetId->widget;
            return $widgetId->widget;
        } else {
            $_SESSION['active_widget'] = '';
        }
    }

    function show_widget()
    {
        $hash = $this->get_active_widget();
        if(!$hash){
            return false;
        }
        print "<script type="."text/javascript"."> (function() { var widget = document.createElement('script'); widget.type = 'text/javascript'; widget.async = true; widget.src = document.location.protocol + '//stage.excitel.ru/widget.js?id=$hash'; var script = document.getElementsByTagName('script')[0]; script.parentNode.insertBefore(widget, script); })(); </script>";
    }

    function get_widgets(){
        $_SESSION['widgets'] = '';
        $url = '/api/widgets?api_key='.$_SESSION['api_key'];
        $widgets = $this->voipApp_curl_init($url,'','GET');
        if(empty($this->widgets)){
            if( $widgets['code'] == "200" ){
                foreach ($widgets['body']->widgets as $widget){
                    $_SESSION['widgets'][] = array('name'=>$widget->name,'hash'=>$widget->hash);
                }
            }else{
                var_dump('get_widgets CURL Error');
            }
        }
        return $this->widgets;
    }

    function authenticate() {
        $stack = array();
        $username = $_POST['voipapp_username'];
        $password = $_POST['voipapp_password'];
        if(empty($username) || empty($password)){
            $stack['message'] = 'Unable to Authenticate';
            return $stack;
        }
        $url = '/api/auth';


        $auth = $this->voipApp_curl_init($url,array( 'email' => $username, 'password' => $password ));
        if( $auth['code'] == "200" ){
            $configs = array();
            if( isset( $auth['body'] ) ) {
                $this->authenticated = true;
                    $this->api_key = $configs['api_key'] = $auth['body']->api_key;
                $stack['available_configs'] = $configs;
                $_SESSION['api_key'] = $this->api_key;
            }
        }else {
            $stack['message'] = 'Unable to Authenticate';
        }
        return $stack;
    }
}
