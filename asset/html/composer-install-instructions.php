<?php defined('EZPIZEE_WP_VERSION') or die('Silent is gold'); ?>
<h3>Composer dependencies missing</h3>
<p>First run composer install, in order to use Ezpizee Joomla Connector</p>
<ol>
    <li>
        Required
        <ul>
            <li>Make sure <strong>curl</strong> is installed in your environment</li>
            <li>Make sure <strong>PHP phar</strong> is installed in your environment</li>
        </ul>
    </li>
    <li>Go to: <code><?php echo dirname(dirname(__DIR__));?></code></li>
    <li>
        Execute composer command(s)
        <ul>
            <li><code>php composer.phar install</code></li>
        </ul>
    </li>
    <li><a href="javascript:void(0)" onclick="window.location.reload()">Reload this page</a></li>
</ol>