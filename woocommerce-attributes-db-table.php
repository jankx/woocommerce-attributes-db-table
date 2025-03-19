<?php
/**
 * Plugin Name: WooCommerce Attribute DB Table
 * Description: Create new tables for to store product attributes to custom query SQL
 * Version: 1.0.0
 * Author: Puleeno Nguyen
 * Author URI: https://puleeno.com
 * Plugin URI: https://github.com/jankx/woocommerce-attributes-db-table
 * Tag: woocommerce, performance
 */

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

class Jankx_Woocommerce_Attribute_Bootstraper {
    protected static $instance;

    protected function __construct() {
    }
    public function checkLoadMode() {
    }

    public function getInstance() {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
    }

    public function init() {

    }

    public function boot() {
    }
}


$bootstraper = new Jankx_Woocommerce_Attribute_Bootstraper();
$bootstraper->init();
$bootstraper->boot();
