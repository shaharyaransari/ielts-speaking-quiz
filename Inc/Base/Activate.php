<?php

namespace ISQNS\Base;
use \ISQNS\Admin\Helpers;
class Activate{
    public static function activate(){
        global $wpdb;

        $tableName = $wpdb->prefix . 'isq_results'; // Prefix table name with WordPress prefix
        $charsetCollate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $tableName (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            quiz_id bigint(20) NOT NULL,
            try_number int NOT NULL DEFAULT 1,
            result_obj longtext NOT NULL,
            PRIMARY KEY  (id)
        ) $charsetCollate;";

        // Include upgrade.php for dbDelta()
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Create or update the table
        dbDelta($sql);

        // Flush Rewrite Rules

        flush_rewrite_rules();
    }
}