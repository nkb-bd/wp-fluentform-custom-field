<?php

/*
Plugin Name: Confirmation Field for Fluent Forms
Description: Custom Input Example For Fluent Forms
Version: 1.0
Author: Lukman Nakib
Author URI: https://fluentforms.com
Plugin URI: https://wpmanageninja.com/wp-fluent-form/
License: GPLv2 or later
Text Domain: ff_confirm_field
*/

defined('ABSPATH') or die;

define('FF_CONFIRM_FIELD_DIR_PATH', plugin_dir_path(__FILE__));

add_action('plugins_loaded', function () {
    include FF_CONFIRM_FIELD_DIR_PATH . '/Bootstrap.php';
    new FFConfirmField();
});

