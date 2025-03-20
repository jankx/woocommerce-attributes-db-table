<?php

use Jankx\Woocommerce\Attributes\Database;
use Jankx\Woocommerce\Attributes\Hooks;

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

if (!defined('JANKX_WOO_ATTRIBUTES_MAIN_FILE')) {
    define('JANKX_WOO_ATTRIBUTES_MAIN_FILE', __FILE__);
}

class Jankx_Woocommerce_Attributes_Bootstraper
{
    protected static $instance;

    protected $loadMode;

    protected $hook;
    protected $database;

    protected function __construct()
    {
        $this->loadMode = $this->checkLoadMode();
        if ($this->loadMode === 'plugin') {
            $this->loadComposer();
        }

        $this->database = new Database();
        $this->hook = new Hooks($this->database);
    }
    public function checkLoadMode()
    {
        return basename(realpath(__DIR__ . str_repeat('/..', 2))) === 'vendor'
            ? 'library'
            : 'plugin';
    }

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    protected function loadComposer()
    {
        $autoloader = implode(DIRECTORY_SEPARATOR, [realpath(__DIR__), 'vendor', 'autoload.php']);
        if (file_exists($autoloader)) {
            require_once $autoloader;
        }
    }
    public function init()
    {
        if ($this->loadMode === 'plugin') {
            register_activation_hook(JANKX_WOO_ATTRIBUTES_MAIN_FILE, [$this->database, 'createTables']);
        } else {
            add_action('after_switch_theme', [$this->database, 'createTables']);
        }
    }

    public function boot()
    {
        $this->hook->registerHooks();
    }
}

function jankx_woocommerce_ver_check()
{
    if (defined('WC_VERSION')) {
        return WC_VERSION;
    }
    return '< 3.0';
}


$bootstraper = Jankx_Woocommerce_Attributes_Bootstraper::getInstance();
$bootstraper->init();
$bootstraper->boot();
