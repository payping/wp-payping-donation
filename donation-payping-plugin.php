<?php

/*
Plugin Name:  Donation PayPing Plugin
Plugin URI:   https://payping.ir
Description:  Donation PayPing Plugin For Sites.
Version:      1.0.0
Author:       PayPing
Author URI:   https://payping.ir/
License: GPLv3 or later
*/

if(!defined('ABSPATH')) exit;

define('DonationPPDIR', plugin_dir_path( __FILE__ ));
define('DonationPPDU', plugin_dir_url( __FILE__ ));

require_once(DonationPPDIR . 'class-wp-payping-donation.php');