<?php

namespace Jankx\Woocommerce\Attributes;

use Automattic\WooCommerce\Internal\ProductAttributesLookup\LookupDataStore as ProductAttributesLookupDataStore;

class Hooks
{
    const WOOCOMMERCE_PRODUCT_ATTRIBUTE_KEY = '_product_attributes';

    protected $database;

    public function __construct(Database &$database)
    {
        $this->database = $database;
    }

    public function registerHooks()
    {
        add_action('woocommerce_process_product_meta', [$this, 'saveMetas'], 10, 4);

        do_action('delete_post_meta', 10, 3);

        add_action('woocommerce_before_product_object_save', [$this, 'saveMetasOnly']);
    }

    public function cloneAttributes($a)
    {
        // if ($meta_key !== static::WOOCOMMERCE_PRODUCT_ATTRIBUTE_KEY) {
        //     return;
        // }


        // var_dump($a);die;
    }

    public function removeAttributes($meta_ids, $object_id, $meta_key)
    {
        if ($meta_key !== static::WOOCOMMERCE_PRODUCT_ATTRIBUTE_KEY) {
            return;
        }
    }

    protected function fetchAttributesByProductAndAttribute($productId, $attribute)
    {
        $wpdb = Database::getWpdb();
        $attributes = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}jankx_woo_attributes WHERE {$wpdb->prefix}jankx_woo_attributes.product_id=%d AND {$wpdb->prefix}jankx_woo_attributes.attribute=%s",
                $productId,
                $attribute
            )
        );

        $ret = [];
        if (!empty($attributes)) {
            foreach ($attributes as $attribute) {
                $ret[$attribute->value] = $attribute;
            }
        }
        return $ret;
    }


    /**
     * Summary of resetAttributeByProductAndAttribute
     * @param mixed $productId
     * @param mixed $attribute
     * @return bool|int
     */
    protected function resetAttributeByProductAndAttribute($productId, $attribute)
    {
        $wpdb = Database::getWpdb();

        return $wpdb->delete($wpdb->prefix . 'jankx_woo_attributes', [
            'product_id' => $productId,
            'attribute' => $attribute,
        ]);
    }


    /**
     * Summary of saveMetasOnly
     * @param \WC_Product $product
     * @return void
     */
    public function saveMetasOnly($product)
    {
        $changeset = $product->get_changes();
        $attributes = array_get($changeset, 'attributes', []);
        $jankxAttributes = [];

        foreach ($attributes as $attribute) {
            $data = $attribute->get_data();
            $attributeName = array_get($data, 'name');
            if (empty($attributeName)) {
                continue;
            }

            $options = array_get($data, 'options', []);
            $currentValues = $this->fetchAttributesByProductAndAttribute($product->get_id(), $attributeName);

            foreach ($options as $option) {
                $jankxAttribute = new Attribute($product->get_id(), $attributeName, $option);

                $jankxAttribute->version = jankx_woocommerce_ver_check();
                $jankxAttribute->position = array_get($data, 'position');
                $jankxAttribute->variation = array_get($data, 'variation', false);

                $currentAttribute = isset($currentValues[$option]) ? $currentValues[$option] : null;
                $jankxAttribute->createdOn = !is_null($currentAttribute) ? $currentAttribute->created_at : current_time('mysql');
                $jankxAttribute->updatedOn = current_time('mysql');

                $jankxAttribute->isTerm = strpos($attributeName, 'pa_') === 0;

                $jankxAttributes[] = $jankxAttribute;
            }
        }

        // reset db to clean attributes before update if nedded
        foreach ($attributes as $attribute) {
            $this->resetAttributeByProductAndAttribute(
                $product->get_id(),
                $attribute
            );
        }

        // save to DB
        foreach ($jankxAttributes as $jankxAttribute) {
            $jankxAttribute->save();
        }
    }
}
