<?php

use Jankx\Woocommerce\Attribute\Database;
class Jankx_Woocommerce_Attributes_Installer {
    protected $database ;

    public function __construct()
    {
        $this->database = new Database();
    }
    public function uninstall() {
        $this->database->removeTables();
    }
}

$installer = new Jankx_Woocommerce_Attributes_Installer();
$installer->uninstall();
