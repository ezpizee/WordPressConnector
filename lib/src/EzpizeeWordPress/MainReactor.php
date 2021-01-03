<?php

namespace EzpizeeWordPress;

class MainReactor
{
    private static $initiated = false;
    /**
     * @var ConfigData
     */
    private static $configData;

    public static function init()
    {
        if (!self::$initiated)
        {
            self::initHooks();
        }
    }

    public static function pluginActivation()
    {
        if ( version_compare( $GLOBALS['wp_version'], EZPIZEE_MINIMUM_WP_VERSION, '<' ) )
        {
            load_plugin_textdomain( 'ezpizee' );

            $message = '<strong>'.
                sprintf(esc_html__( 'Ezpizee %s requires WordPress %s or higher.' , 'ezpizee'),
                    EZPIZEE_WP_VERSION, EZPIZEE_MINIMUM_WP_VERSION ).'</strong> ' . sprintf(__(
                        'Please <a href="%1$s">upgrade WordPress</a> to a current version, or <a href="%2$s">'.
                        'downgrade to version 2.4 of the Ezpizee plugin</a>.', 'ezpizee'),
                    'https://codex.wordpress.org/Upgrading_WordPress',
                    'https://wordpress.org/extend/plugins/ezpizee/download/');

            self::bailOnActivation( $message );
        }
        elseif (! empty( $_SERVER['SCRIPT_NAME'] ) && false !== strpos( $_SERVER['SCRIPT_NAME'], '/wp-admin/plugins.php' ))
        {
            add_option( 'Activated_Ezpizee', true );
        }
    }

    public static function pluginDeactivation() {self::uninstall();}

    public static function getConfig(): ConfigData
    {
        self::loadConfigData();
        return self::$configData;
    }

    public static function isInstalled(): bool {return self::getConfig()->isValid();}

    private static function bailOnActivation( $message, $deactivate = true )
    {
        include EZPIZEE_PLUGIN_ASSET_HTML.DS.'bail-on-activation.php';
        if ( $deactivate ) {
            $plugins = get_option( 'active_plugins' );
            $ezpizee = plugin_basename( EZPIZEE_PLUGIN_DIR . 'ezpizee.php' );
            $update  = false;
            foreach ( $plugins as $i => $plugin ) {
                if ( $plugin === $ezpizee ) {
                    $plugins[$i] = false;
                    $update = true;
                }
            }
            if ( $update ) {
                update_option( 'active_plugins', array_filter( $plugins ) );
            }
        }
        exit;
    }

    private static function uninstall(): void
    {
        echo 'TODO';
    }

    private static function initHooks(): void
    {
        self::$initiated = true;
        self::loadConfigData();
    }

    private static function loadConfigData()
    {
        if (empty(self::$configData) || !self::$configData->isValid())
        {
            global $wpdb;
            $sql = 'SELECT option_value'.' FROM '.$wpdb->prefix.'options WHERE option_name="'.EzpizeeAdmin::NONCE.'"';
            $row = $wpdb->get_row($sql);
            if (is_object($row) && isset($row->option_value)) {
                self::$configData = new ConfigData(json_decode($row->option_value, true));
            }
        }
        if (empty(self::$configData))
        {
            self::$configData = new ConfigData([]);
        }
    }
}