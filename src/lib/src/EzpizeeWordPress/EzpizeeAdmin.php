<?php

namespace EzpizeeWordPress;

use Ezpizee\ConnectorUtils\Client;
use Ezpizee\Utils\EncodingUtil;
use HandlebarsHelpers\Hbs;
use RuntimeException;

class EzpizeeAdmin
{
    const NONCE = EzpzClientConfig::NONCE;
    const ADMIN_PORTAL = "ezpizee_portal";
    const ADMIN_INSTALL = "ezpizee_install";
    const WP_PAGE_GENERAL_OPTIONS = 'options-general.php';
    const WP_PAGE_ADMIN = 'admin.php';
    private static $configFormData = [];

    public static function init()
    {
        self::loadResource();
        self::onActivated();
    }

    public static function adminMenu(): void
    {
        if (MainReactor::isInstalled())
        {
            self::addTopMenuItem();
        }

        self::addSubMenuItem();
    }

    public static function displayInstallPage()
    {
        self::onInstallConfigSubmit();
        include EZPIZEE_PLUGIN_ASSET_HTML . EZPIZEE_DS . 'install.php';
    }

    public static function displayPortalPage()
    {
        self::$configFormData = MainReactor::getConfig()->jsonSerialize();

        if (empty(self::$configFormData))
        {
            self::redirectToInstallPage(true);
        }
        else
        {
            $view = EzpizeeSanitizer::filterGET('view', FILTER_SANITIZE_STRING);
            EzpizeeSanitizer::sanitize($view, true);
            if ($view === 'api')
            {
                $endpoint = EzpizeeSanitizer::filterGET('endpoint', FILTER_SANITIZE_URL);
                EzpizeeSanitizer::sanitize($endpoint, true);
                if (strlen($endpoint) > 0) {
                    header('Content-Type: application/json');
                    $apiClient = new ApiClient(MainReactor::getConfig());
                    die(json_encode($apiClient->load($endpoint)));
                }
                else {
                    throw new RuntimeException('Invalid request', 500);
                }
            }
            else
            {
                $url = Client::cdnEndpointPfx(self::$configFormData['env']).Client::adminUri('wordpress');
                if (self::$configFormData['env'] === 'local') {
                    Client::setIgnorePeerValidation(true);
                }
                $output = Client::getContentAsString($url);
                self::formatOutput($output);
                echo $output;
            }
            exit;
        }
    }

    public static function getFormData(string $key, $default=''): string
    {
        return isset(self::$configFormData[$key]) ? self::$configFormData[$key] : $default;
    }

    public static function getInstallPageUrl(): string
    {
        return add_query_arg(array('page' => self::ADMIN_INSTALL), admin_url(self::WP_PAGE_GENERAL_OPTIONS));
    }

    public static function getPortalPageUrl(): string
    {
        return add_query_arg(array('page' => self::ADMIN_PORTAL), admin_url(self::WP_PAGE_ADMIN));
    }

    private static function loadResource()
    {
        wp_register_style('ezpz.css', plugin_dir_url( EZPIZEE_PLUGIN_MAIN_FILE ) . 'asset/css/ezpz.css', array(), EZPIZEE_WP_VERSION );
        wp_enqueue_style('ezpz.css');

        wp_register_script('ezpz.js', plugin_dir_url( EZPIZEE_PLUGIN_MAIN_FILE ) . 'asset/js/ezpz.js', array('jquery'), EZPIZEE_WP_VERSION );
        wp_enqueue_script('ezpz.js');
    }

    private static function onActivated(): void
    {
        // redirect to the install page on activated
        if (get_option('Activated_Ezpizee'))
        {
            delete_option('Activated_Ezpizee');
            self::redirectToInstallPage();
        }
    }

    private static function onInstallConfigSubmit(): void
    {
        if (!empty($_POST))
        {
            if (isset($_POST['_wpnonce']))
            {
                if (wp_verify_nonce($_POST['_wpnonce'], self::NONCE))
                {
                    if (self::isValidInstallConfigSubmission())
                    {
                        if (self::saveConfig())
                        {
                            echo Hbs::render(EZPIZEE_PLUGIN_ASSET_HBS. EZPIZEE_DS .'notice.hbs', [
                                'message' => __('Successfully saved', 'ezpizee'),
                                'type' => 'success'
                            ]);
                        }
                    }
                    else
                    {
                        echo Hbs::render(EZPIZEE_PLUGIN_ASSET_HBS. EZPIZEE_DS .'notice.hbs', [
                            'message' => __('Missing or invalid data', 'ezpizee'),
                            'type' => 'error'
                        ]);
                    }
                }
                else
                {
                    echo Hbs::render(EZPIZEE_PLUGIN_ASSET_HBS. EZPIZEE_DS .'notice.hbs', [
                        'message' => __('Invalid nonce', 'ezpizee'),
                        'type' => 'notice'
                    ]);
                }
            }
            else
            {
                echo Hbs::render(EZPIZEE_PLUGIN_ASSET_HBS. EZPIZEE_DS .'notice.hbs', [
                    'message' => __('Invalid request', 'ezpizee'),
                    'type' => 'error'
                ]);
            }
        }
        else
        {
            self::$configFormData = MainReactor::getConfig()->jsonSerialize();
        }
    }

    private static function isValidInstallConfigSubmission(): bool
    {
        self::$configFormData[Client::KEY_CLIENT_ID] = EzpizeeSanitizer::filterPOST(Client::KEY_CLIENT_ID, FILTER_SANITIZE_STRING);
        if (EncodingUtil::isValidMd5(self::$configFormData[Client::KEY_CLIENT_ID])) {
            self::$configFormData[Client::KEY_CLIENT_SECRET] = EzpizeeSanitizer::filterPOST(Client::KEY_CLIENT_SECRET, FILTER_SANITIZE_STRING);
            if (EncodingUtil::isValidMd5(self::$configFormData[Client::KEY_CLIENT_SECRET])) {
                self::$configFormData[Client::KEY_APP_NAME] = EzpizeeSanitizer::filterPOST(Client::KEY_APP_NAME, FILTER_SANITIZE_STRING);
                if (strlen(self::$configFormData[Client::KEY_APP_NAME]) > 0) {
                    self::$configFormData[Client::KEY_ENV] = EzpizeeSanitizer::filterPOST(Client::KEY_ENV, FILTER_SANITIZE_STRING);
                    if (strlen(self::$configFormData[Client::KEY_ENV])) {
                        EzpizeeSanitizer::sanitize(self::$configFormData);
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private static function saveConfig(): bool
    {
        if (!empty(self::$configFormData))
        {
            EzpzClientConfig::deleteConfig();
            EzpzClientConfig::insertConfig(self::$configFormData);

            if (MainReactor::getConfig()->isValid())
            {
                $tokenHandler = 'EzpizeeWordPress\TokenHandler';
                $response = Client::install(Client::DEFAULT_ACCESS_TOKEN_KEY, self::$configFormData, $tokenHandler);

                if (!empty($response))
                {
                    if (isset($response['code']) && (int)$response['code'] !== 200)
                    {
                        if ($response['message']==='ITEM_ALREADY_EXISTS')
                        {
                            echo Hbs::render(EZPIZEE_PLUGIN_ASSET_HBS. EZPIZEE_DS .'notice.hbs', [
                                'message' => __('Failed to install. App with the same name already exists.', 'ezpizee'),
                                'type' => 'error'
                            ]);
                        }
                        else
                        {
                            echo Hbs::render(EZPIZEE_PLUGIN_ASSET_HBS. EZPIZEE_DS .'notice.hbs', [
                                'message' => __($response['message'], 'ezpizee'),
                                'type' => 'error'
                            ]);
                        }
                    }
                    else
                    {
                        self::$configFormData = MainReactor::getConfig()->jsonSerialize();
                        return true;
                    }
                }
                else
                {
                    echo Hbs::render(EZPIZEE_PLUGIN_ASSET_HBS. EZPIZEE_DS .'notice.hbs', [
                        'message' => __('Unknown error occurred', 'ezpizee'),
                        'type' => 'error'
                    ]);
                }
            }
            else
            {
                self::$configFormData = [];
                echo Hbs::render(EZPIZEE_PLUGIN_ASSET_HBS. EZPIZEE_DS .'notice.hbs', [
                    'message' => __('Failed to save the configuration data', 'ezpizee'),
                    'type' => 'error'
                ]);
            }
        }

        return false;
    }

    private static function redirectToInstallPage(bool $force=false): void
    {
        if (!headers_sent())
        {
            wp_redirect(add_query_arg(array( 'page' => self::ADMIN_INSTALL), admin_url(self::WP_PAGE_GENERAL_OPTIONS)));
        }
        else if ($force === true)
        {
            echo '<script>window.location="'.add_query_arg(array('page'=>self::ADMIN_INSTALL), admin_url(self::WP_PAGE_GENERAL_OPTIONS)).'";</script>';
        }
    }

    private static function addTopMenuItem(): void
    {
        // add Ezpizee portal page (top level)
        add_menu_page(
            __('Ezpizee Portal', 'ezpizee'),
            __('Ezpizee Portal', 'ezpizee'),
            'manage_options',
            self::ADMIN_PORTAL,
            '\EzpizeeWordPress\EzpizeeAdmin::displayPortalPage',
            EZPIZEE_PLUGIN_URL_ROOT .'/asset/images/favicon-32x32.png',
            null
        );
    }

    private static function addSubMenuItem(): void
    {
        // add Ezpizee install page (submenu of Settings)
        add_options_page(
            __('Ezpizee', 'ezpizee'),
            __('Ezpizee', 'ezpizee'),
            'manage_options',
            self::ADMIN_INSTALL,
            '\EzpizeeWordPress\EzpizeeAdmin::displayInstallPage'
        );
    }

    private static function formatOutput(string &$output): void
    {
        $patterns = ["\n", "\r", "\t", "\s+", "{loginPageRedirectUrl}"];
        $replaces = ["", "", "", " ", "/wp-admin/"];
        $override = str_replace($patterns, $replaces, file_get_contents(EZPIZEE_PLUGIN_ASSET_DATA . EZPIZEE_DS . 'ezpz_admin_override.js'));
        $output = str_replace('<' . 'head>', '<' . 'head' . '><' . 'script>' . $override . '</' . 'script>', $output);
    }
}