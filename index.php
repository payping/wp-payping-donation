<?php

/*
Plugin Name:  Donation PayPing Plugin
Plugin URI:   https://payping.ir
Description:  Donation PayPing Plugin For Sites.
Version:      1.0.0
Author:       PayPing
Author URI:   https://payping.ir/
*/

if(!defined('ABSPATH')) exit;

define('DPPDIR', plugin_dir_path( __FILE__ ));
define('DPPDU', plugin_dir_url( __FILE__ ));

require_once(DPPDIR . 'class-wp-payping-donation.php');