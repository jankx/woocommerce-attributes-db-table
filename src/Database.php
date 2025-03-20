<?php

namespace Jankx\Woocommerce\Attributes;

class Database
{
    public function createTables()
    {
        "
CREATE TABLE `xvn2_jankx_woo_attributes` (
  `product_id` bigint NOT NULL,
  `attribute` varchar(255) NOT NULL,
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `version` varchar(100) NOT NULL,
  `position` int DEFAULT NULL,
  `variation` tinyint(1) DEFAULT '0',
  `is_term` tinyint(1) DEFAULT '0',
  `created_on` datetime NOT NULL,
  `updated_on` datetime NOT NULL
) ENGINE=InnoDB;";

        "ALTER TABLE `xvn2_jankx_woo_attributes`
  ADD PRIMARY KEY (`product_id`,`attribute`,`value`),
  ADD KEY `attribute` (`attribute`);";
    }

    public function removeTables()
    {
    }

    public static function getWpdb()
    {
        return $GLOBALS['wpdb'];
    }
}
