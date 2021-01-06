<?php

namespace EzpizeeWordPress;

use wpdb;

class EzpzClientConfig
{
    const NONCE = 'ezpizee_app_config';

    public final static function deleteConfig(): void
    {
        global $wpdb;

        if ($wpdb instanceof wpdb)
        {
            $sql = 'DELETE'.' FROM '.$wpdb->prefix.'options WHERE option_name="'.self::NONCE.'"';
            $wpdb->query($sql);
        }
    }

    public static function insertConfig(array $data): void
    {
        global $wpdb;

        if ($wpdb instanceof wpdb)
        {
            $values = [
                "'".$wpdb->_escape(self::NONCE)."'",
                "'".$wpdb->_escape(json_encode($data))."'"
            ];
            $sql = 'INSERT'.' INTO '.$wpdb->prefix.'options(option_name,option_value) VALUES('.implode(',', $values).')';
            $wpdb->query($sql);
        }
    }

    public static function getConfig(): ConfigData
    {
        global $wpdb;

        if ($wpdb instanceof wpdb)
        {
            $sql = 'SELECT option_value' . ' FROM ' . $wpdb->prefix . 'options WHERE option_name="' . self::NONCE . '"';
            $row = $wpdb->get_row($sql);
            if (is_object($row) && isset($row->option_value)) {
                return new ConfigData(json_decode($row->option_value, true));
            }
        }

        return new ConfigData([]);
    }
}