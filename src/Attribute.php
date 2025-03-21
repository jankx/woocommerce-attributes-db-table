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

    protected $createdOn;

    protected $updatedOn;


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

    public function __get($attr)
    {
        if (property_exists($this, $attr)) {
            return $this->$attr;
        }
        return null;
    }


    public function checkExists()
    {
        $wpdb = Database::getWpdb();
        $records = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}jankx_woo_attributes
                    WHERE {$wpdb->prefix}jankx_woo_attributes.product_id=%d AND {$wpdb->prefix}jankx_woo_attributes.attribute=%s AND {$wpdb->prefix}jankx_woo_attributes.value=%s",
                $this->getProductId(),
                $this->getAttribute(),
                $this->getValue()
            )
        );

        return $records > 0;
    }


    public function getProductId()
    {
        return $this->productId;
    }

    public function getAttribute()
    {
        return $this->attribute;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * Summary of save
     * @return bool|int
     */
    public function save()
    {
        $wpdb = Database::getWpdb();
        try {
            if ($this->checkExists()) {
                return $wpdb->update(
                    Database::getAttributeTable(),
                    [
                    'version' => $this->version,
                    'position' => $this->position,
                    'variation' => $this->variation,
                    'created_on' => $this->createdOn,
                    'updated_on' => $this->updatedOn,
                    'is_term' => $this->isTerm
                    ],
                    [
                    'product_id' => $this->productId,
                    'attribute' => $this->attribute,
                    'value' => $this->value
                    ]
                );
            } else {
                return $wpdb->insert(
                    Database::getAttributeTable(),
                    [
                    'product_id' => $this->productId,
                    'attribute' => $this->attribute,
                    'value' => $this->value,

                    'is_term' => $this->isTerm,

                    'version' => $this->version,
                    'position' => $this->position,
                    'variation' => $this->variation,
                    'created_on' => $this->createdOn,
                    'updated_on' => $this->updatedOn,
                    ],
                );
            }
        } catch (\Exception $e) {
            error_log($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }
}
