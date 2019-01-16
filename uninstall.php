<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

global $wpdb;
$table_name = $wpdb->prefix . "shipping_coordinadora_cities";
$sql = "DROP TABLE IF EXISTS $table_name";
$wpdb->query($sql);
delete_option('shipping_coordinadora_wc_cswc_version');