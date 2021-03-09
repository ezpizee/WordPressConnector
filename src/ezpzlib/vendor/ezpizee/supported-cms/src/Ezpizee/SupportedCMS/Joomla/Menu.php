<?php

namespace Ezpizee\SupportedCMS\Joomla;

defined('_JEXEC') or die;

use Exception;
use Ezpizee\SupportedCMS\Exception\Error;
use Ezpizee\Utils\Logger;
use Joomla\CMS\Factory;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

class Menu
{
    public static function getMenu($name = null, $options = array()): AbstractMenu
    {
        $menu = new AbstractMenu();
        try {
            $menu = Factory::getApplication()->getMenu($name, $options);
            if (!($menu instanceof AbstractMenu)) {
                new Error('Menu is not an instance of \Joomla\CMS\Menu\AbstractMenu', 500);
            }
        }
        catch (Exception $e) {
            Logger::error($e->getMessage());
            new Error($e->getMessage(), 500);
        }
        return $menu;
    }

    public static function getActiveMenu(): MenuItem
    {
        return self::getMenu()->getActive();
    }

    public static function getActiveMenuParams(): Registry
    {
        return self::getActiveMenu()->getParams();
    }

    public static function getItem(int $id): MenuItem
    {
        $menuItem = self::getMenu()->getItem($id);
        return empty($menuItem) ? new MenuItem([]) : $menuItem;
    }

    public static function getMenuItemParams(int $id): Registry
    {
        return self::getItem($id)->getParams();
    }

    public static function urlByItemId(int $id): string
    {
        $route = Route::_('index.php?Itemid='.$id);
        return empty($route) ? '' : $route;
    }
}