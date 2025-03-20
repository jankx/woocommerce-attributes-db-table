<?php

namespace Jankx\WooCommerce\Attributes;

class Attribute
{
    protected $productId;
    protected $attribute;

    protected $value;

    protected $isTerm = false;

    protected $version;

    protected $position;

    protected $variation;

    protected $createdAt;

    protected $updatedAt;


    public function __construct($productId, $atribute, $value)
    {
        $this->productId = $productId;
        $this->attribute = $atribute;
        $this->value = $value;
    }

    public function __set($attr, $value)
    {
        if (property_exists($this, $attr)) {
            $this->$attr = $value;
        }
    }

    public function checkExists()
    {
        $wpdb = Database::getWpdb();
        dd($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}jankx_woo_attributes
                    WHERE {$wpdb->prefix}jankx_woo_attributes.product_id=%d, {$wpdb->prefix}jankx_woo_attributes.attribute=%s",
            $this->getProductId(),
            $this->getAttribute()
        ));
        $isExists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}jankx_woo_attributes
                    WHERE {$wpdb->prefix}jankx_woo_attributes.product_id=%d, {$wpdb->prefix}jankx_woo_attributes.attribute=%s",
                $this->getProductId(),
                $this->getAttribute()
            )
        );

        dd($isExists);
    }


    public function getProductId()
    {
        return $this->productId;
    }

    public function getAttribute()
    {
        return $this->attribute;
    }

    public function save()
    {
        if ($this->checkExists()) {
        }
    }
}
