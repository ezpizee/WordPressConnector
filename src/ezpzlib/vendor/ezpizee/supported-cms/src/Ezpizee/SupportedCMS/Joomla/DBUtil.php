<?php

namespace Ezpizee\SupportedCMS\Joomla;

defined('_JEXEC') or die;

class DBUtil
{
    public static function setAIStartNumber(string $tb, int $num) {
        $db = \Joomla\CMS\Factory::getDbo();
        $sql = "ALTER "."TABLE ".$tb." AUTO_INCREMENT = ".$num;
        $db->setQuery($sql)->execute();
    }

    public static function changeDBEngine(string $dbname, string $currentEngine, string $newEngine) {
        $db = \Joomla\CMS\Factory::getDbo();
        $sql = "SELECT TABLE_NAME"." FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = '".$dbname."' 
        AND ENGINE = '".$currentEngine."'";
        $rows = $db->setQuery($sql)->loadAssocList();
        foreach($rows as $row) {
            $tbl = $row['TABLE_NAME'];
            $sql = "ALTER TABLE"." `$tbl` COLLATE=".$newEngine;
            $db->setQuery($sql)->execute();
        }
    }

    public static function changeTableCollateInDB(string $dbname, string $collate) {
        $db = \Joomla\CMS\Factory::getDbo();
        $sql = "SELECT TABLE_NAME"." FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".$dbname."'";
        $rows = $db->setQuery($sql)->loadAssocList();
        foreach($rows as $row) {
            $tbl = $row['TABLE_NAME'];
            $sql = "ALTER TABLE'.' `$tbl` COLLATE=".$collate;
            $db->setQuery($sql)->execute();
        }
    }
}