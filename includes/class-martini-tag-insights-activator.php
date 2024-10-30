<?php

class Martini_Tag_Insights_Activator
{
    public static function activate()
    {
        if ( !self::check_local_db() ) {
            self::create_table();
        }

        if ( !self::check_sync() ) {
            self::create_sync();
        }

        if ( !self::check_default_tags() ) {
            self::create_default_tags();
        }
    }

    public static function check_local_db()
    {
        global $wpdb;
        return $wpdb->get_var( "SHOW TABLES LIKE '" . MARTINI_TAG_INSIGHTS_DB_TABLE_NAME . "'" ) ? true : false;
    }

    public static function create_table()
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table_name = MARTINI_TAG_INSIGHTS_DB_TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$table_name} (
            id  bigint(20) unsigned NOT NULL auto_increment,
            name varchar(128) NOT NULL default '',
            value varchar(128) NOT NULL default '',
            PRIMARY KEY  (id)
        ) {$charset_collate}";

        dbDelta( $sql );
    }

    public static function check_sync()
    {
        global $wpdb;
        return $wpdb->get_var(
            "SELECT `name` FROM " . MARTINI_TAG_INSIGHTS_DB_TABLE_NAME . " WHERE name='settings-sync-posts';"
        ) ? true : false;
    }

    public static function create_sync()
    {
        global $wpdb;
        $wpdb->insert(
            MARTINI_TAG_INSIGHTS_DB_TABLE_NAME,
            [
                'name'  => 'settings-sync-posts',
                'value' => 1
            ],
            [
                '%s',
                '%s'
            ]
        );
    }

    public static function check_default_tags()
    {
        global $wpdb;
        return $wpdb->get_var(
            "SELECT `name` FROM " . MARTINI_TAG_INSIGHTS_DB_TABLE_NAME . " WHERE name='settings-default-tags';"
        ) ? true : false;
    }

    public static function create_default_tags()
    {
        global $wpdb;
        $wpdb->insert(
            MARTINI_TAG_INSIGHTS_DB_TABLE_NAME,
            [
                'name'  => 'settings-default-tags',
                'value' => 'Blog'
            ],
            [
                '%s',
                '%s'
            ]
        );
    }
}
