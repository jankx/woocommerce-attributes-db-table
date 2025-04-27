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
     * Summary of resetAttributesByProductAndAttribute
     * @param mixed $productId
     * @param mixed $attribute
     * @return bool|int
     */
    protected function resetAttributesByProductAndAttribute($productId, $attribute)
    {
        $wpdb = Database::getWpdb();
        return $wpdb->delete(Database::getAttributeTable(), [
            'product_id' => $productId,
            'attribute' => $attribute,
        ]);
    }


    protected function parseOptionsFromValue($value)
    {
        return explode('|', $value);
    }

    /**
     * Summary of saveMetasOnly
     * @param \WC_Product $product
     * @return void
     */
    public function saveMetasOnly($product)
    {
        $productAttributesCollection = get_post_meta($product->get_id(), '_product_attributes');
        $jankxAttributes = [];

        if (is_array($productAttributesCollection)) {
            foreach ($productAttributesCollection as $attributes) {
                foreach ($attributes as $attributeName => $data) {
                    if (empty($attributeName)) {
                        continue;
                    }
                    $isTerm = strpos($attributeName, 'pa_') === 0;


                    $options = $isTerm
                        ? wp_get_post_terms($product->get_id(), $attributeName, ['fields' => 'id=>slug'])
                        : $this->parseOptionsFromValue(array_get($data, 'value', ''));

                    $currentValues = $this->fetchAttributesByProductAndAttribute($product->get_id(), $attributeName);


                    foreach ($options as $termId => $option) {
                        $jankxAttribute = new Attribute($product->get_id(), $attributeName, $option);

                        $jankxAttribute->isTerm = $isTerm;
                        $jankxAttribute->version = jankx_woocommerce_ver_check();
                        $jankxAttribute->position = array_get($data, 'position');
                        $jankxAttribute->variation = array_get($data, 'variation', false);
                        $jankxAttribute->termId = $isTerm ? $termId : null;

                        $currentAttribute = isset($currentValues[$option]) ? $currentValues[$option] : null;
                        $jankxAttribute->createdOn = !is_null($currentAttribute)
                            ? $currentAttribute->created_at
                            : current_time('mysql');
                        if (is_null($jankxAttribute->createdOn)) {
                            $jankxAttribute->createdOn = current_time('mysql');
                        }
                        $jankxAttribute->updatedOn = current_time('mysql');

                        $jankxAttributes[] = $jankxAttribute;
                    }
                }
            }
        }

        // save to DB
        $resetedAttribute = [];
        foreach ($jankxAttributes as $jankxAttribute) {
            $resetKey = sprintf('%d-%s', $jankxAttribute->getProductId(), $jankxAttribute->getAttribute());
            if (!isset($resetedAttribute[$resetKey])) {
                $this->resetAttributesByProductAndAttribute($jankxAttribute->getProductId(), $jankxAttribute->getAttribute());
                $resetedAttribute[$resetKey] = true;
            }
            $jankxAttribute->save();
        }
    }
}
