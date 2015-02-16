<?php
/*
Plugin Name: Excitel - Click to call
Description: Let your website visitors call you. Without a phone. Excitel enables voice calls through any computer, right from a webpage.
Version: 1.0
Author: Excitel
Author URI: http://excitel.ru/
*/
/*  Copyright 2014  Excitel  (email : support {at} excitel.ru)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if(session_id() == '')
    session_start();
require_once( plugin_dir_path( __FILE__ ) . 'class-voipApp-abstract.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class-voipApp.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class-voipApp-admin.php' );

register_activation_hook( __FILE__, array( 'voipApp', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'voipApp', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'voipApp', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'voipApp_Admin', 'get_instance' ) );

