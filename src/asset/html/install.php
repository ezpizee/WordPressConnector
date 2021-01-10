<?php
defined('EZPIZEE_WP_VERSION') or die('Silent is gold');

use Ezpizee\ConnectorUtils\Client;
use EzpizeeWordPress\EzpizeeAdmin;

$env = EzpizeeAdmin::getFormData('env');
?>
<div class="wrap">
    <h1><?php esc_html_e('Ezpizee App Configuration', 'ezpizee'); ?></h1>
    <form action="<?php echo esc_url(EzpizeeAdmin::getInstallPageUrl()); ?>" method="post">
        <table class="form-table" role="presentation">
            <tr class="form-field form-required">
                <th scope="row"><label for="wpform_client_id"><?php esc_html_e('Client ID', 'ezpizee'); ?></label></th>
                <td>
                    <input type="text" name="<?php esc_attr_e(Client::KEY_CLIENT_ID, 'ezpizee'); ?>" id="wpform_client_id"
                           placeholder="<?php esc_attr_e('Enter your Ezpizee Client ID', 'ezpizee'); ?>"
                           value="<?php esc_attr_e(EzpizeeAdmin::getFormData(Client::KEY_CLIENT_ID), 'ezpizee'); ?>"/>
                </td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row"><label
                            for="wpform_client_secret"><?php esc_html_e('Client Secret', 'ezpizee'); ?></label></th>
                <td>
                    <input type="text" name="<?php esc_attr_e(Client::KEY_CLIENT_SECRET, 'ezpizee'); ?>" id="wpform_client_secret"
                           placeholder="<?php esc_attr_e('Enter your Ezpizee Client Secret', 'ezpizee'); ?>"
                           value="<?php esc_attr_e(EzpizeeAdmin::getFormData(Client::KEY_CLIENT_SECRET), 'ezpizee'); ?>"/>
                </td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row"><label for="wpform_app_name"><?php esc_html_e('App Name', 'ezpizee'); ?></label></th>
                <td>
                    <input type="text" name="<?php esc_attr_e(Client::KEY_APP_NAME, 'ezpizee'); ?>" id="wpform_app_name"
                           placeholder="<?php esc_attr_e('Enter a unique name for your installation', 'ezpizee'); ?>"
                           value="<?php esc_attr_e(EzpizeeAdmin::getFormData(Client::KEY_APP_NAME), 'ezpizee'); ?>"/>
                </td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row"><label for="wpform_env"><?php esc_attr_e('Environment', 'ezpizee'); ?></label></th>
                <td>
                    <select name="<?php esc_attr_e(Client::KEY_ENV, 'ezpizee'); ?>" id="wpform_env">
                        <option value=""><?php esc_attr_e('Select an environment', 'ezpizee'); ?></option>
                        <option value="local"<?php esc_html_e($env === 'local' ? ' selected' : '', 'ezpizee'); ?>>Local</option>
                        <option value="dev"<?php esc_html_e($env === 'dev' ? ' selected' : '', 'ezpizee'); ?>>Development</option>
                        <option value="stage"<?php esc_html_e($env === 'stage' ? ' selected' : '', 'ezpizee'); ?>>Staging</option>
                        <option value="prod"<?php esc_html_e($env === 'production' || empty($env) ? ' selected' : '', 'ezpizee'); ?>>
                            Production
                        </option>
                    </select>
                </td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row">&nbsp;</th>
                <td>
                    <p class="submit">
                        <button type="submit"
                                class="button button-primary"><?php esc_html_e('Save Configuration', 'ezpizee'); ?></button>
                        <span>&nbsp;</span><span>&nbsp;</span><span>&nbsp;</span>
						<?php if (!empty(self::$configFormData)) { ?>
                            <a href="<?php echo esc_url(EzpizeeAdmin::getPortalPageUrl()); ?>">
								<?php esc_html_e('Go to Ezpizee Portal', 'ezpizee'); ?></a>
						<?php } ?>
                    </p>
                </td>
            </tr>
        </table>
        <input type="hidden" name="action" value="install"/>
		<?php wp_nonce_field(EzpizeeAdmin::NONCE) ?>
    </form>
</div>