<?php
defined('EZPIZEE_WP_VERSION') or die('Silent is gold');

use Ezpizee\ConnectorUtils\Client;
use EzpizeeWordPress\EzpizeeAdmin;

$env = EzpizeeAdmin::getFormData('env');
?>
<div class="wrap">
    <h1><?php echo __('Ezpizee App Configuration', 'ezpizee');?></h1>
    <form action="<?php echo esc_url(EzpizeeAdmin::getInstallPageUrl()); ?>" method="post">
        <table class="form-table" role="presentation">
            <tr class="form-field form-required">
                <th scope="row"><label for="wpform_client_id"><?php echo __('Client ID', 'ezpizee');?></label></th>
                <td>
                    <input type="text" name="<?php echo Client::KEY_CLIENT_ID;?>" id="wpform_client_id"
                           placeholder="<?php echo __('Enter your Ezpizee Client ID', 'ezpizee');?>"
                           value="<?php echo EzpizeeAdmin::getFormData(Client::KEY_CLIENT_ID);?>" />
                </td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row"><label for="wpform_client_secret"><?php echo __('Client Secret', 'ezpizee');?></label></th>
                <td>
                    <input type="text" name="<?php echo Client::KEY_CLIENT_SECRET;?>" id="wpform_client_secret"
                           placeholder="<?php echo __('Enter your Ezpizee Client Secret', 'ezpizee');?>"
                           value="<?php echo EzpizeeAdmin::getFormData(Client::KEY_CLIENT_SECRET);?>" />
                </td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row"><label for="wpform_app_name"><?php echo __('App Name', 'ezpizee');?></label></th>
                <td>
                    <input type="text" name="<?php echo Client::KEY_APP_NAME;?>" id="wpform_app_name"
                           placeholder="<?php echo __('Enter a unique name for your installation', 'ezpizee');?>"
                           value="<?php echo EzpizeeAdmin::getFormData(Client::KEY_APP_NAME);?>" />
                </td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row"><label for="wpform_env"><?php echo __('Environment', 'ezpizee');?></label></th>
                <td>
                    <select name="<?php echo Client::KEY_ENV;?>" id="wpform_env">
                        <option value=""><?php echo __('Select an environment', 'ezpizee');?></option>
                        <option value="local"<?php echo $env==='local' ? ' selected' : '';?>>Local</option>
                        <option value="dev"<?php echo $env==='dev' ? ' selected' : '';?>>Development</option>
                        <option value="stage"<?php echo $env==='stage' ? ' selected' : '';?>>Staging</option>
                        <option value="prod"<?php echo $env==='production'||empty($env) ? ' selected' : '';?>>Production</option>
                    </select>
                </td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row">&nbsp;</th>
                <td>
                    <p class="submit">
                        <button type="submit" class="button button-primary"><?php echo __('Save Configuration', 'ezpizee');?></button>
                        <span>&nbsp;</span><span>&nbsp;</span><span>&nbsp;</span>
                        <?php if (!empty(self::$configFormData)) { ?>
                            <a href="<?php echo EzpizeeAdmin::getPortalPageUrl();?>">
                                <?php echo __('Go to Ezpizee Portal', 'ezpizee'); ?></a>
                        <?php } ?>
                    </p>
                </td>
            </tr>
        </table>
        <input type="hidden" name="action" value="install"/>
        <?php wp_nonce_field(EzpizeeAdmin::NONCE) ?>
    </form>
</div>