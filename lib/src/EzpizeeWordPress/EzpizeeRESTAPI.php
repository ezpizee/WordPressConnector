<?php

namespace EzpizeeWordPress;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class EzpizeeRESTAPI
{
    public static function init(): void
    {
        if (function_exists( 'register_rest_route'))
        {
            register_rest_route('ezpizee/api/v1', '/config', array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'permission_callback' => array('\EzpizeeWordPress\EzpizeeRESTAPI', 'privilegedPermissionCallback'),
                    'callback' => array('\EzpizeeWordPress\EzpizeeRESTAPI', 'getConfig')
                ),
                array(
                    'methods' => WP_REST_Server::EDITABLE,
                    'permission_callback' => array('\EzpizeeWordPress\EzpizeeRESTAPI', 'privilegedPermissionCallback'),
                    'callback' => array('\EzpizeeWordPress\EzpizeeRESTAPI', 'setConfig')
                ),
                array(
                    'methods' => WP_REST_Server::DELETABLE,
                    'permission_callback' => array('\EzpizeeWordPress\EzpizeeRESTAPI', 'privilegedPermissionCallback'),
                    'callback' => array('\EzpizeeWordPress\EzpizeeRESTAPI', 'deleteConfig')
                )
            ), true);
        }
    }

    public static function privilegedPermissionCallback()
    {
        return current_user_can('manage_options');
    }

    /**
     * Get the current Ezpizee API Config.
     *
     * @param WP_REST_Request|null $request
     *
     * @return WP_REST_Response
     */
    public static function getConfig(WP_REST_Request $request = null): WP_REST_Response
    {
        return rest_ensure_response(MainReactor::getConfig());
    }

    public static function setConfig(): WP_REST_Response
    {
        return self::getConfig();
    }

    public static function deleteConfig(): WP_REST_Response
    {
        return rest_ensure_response( true );
    }
}