<?php

namespace Ezpizee\ConnectorUtils;

class FileTokenHandler
{
    public static function hasToken(int $userId): bool {
        $file = self::filename($userId);
        if (file_exists($file)) {
            $ts = filemtime($file);
            $output = new EzpzAuthedUser(json_decode(file_get_contents($file), true));
            $expireIn = $ts + $output->getDataExpireIn();
            $now = strtotime('now');
            $diff = $expireIn - $now;
            $diffInMinute = $diff/(1000*60);
            if ($diffInMinute <= 0) {
                self::deleteTokenFile($userId);
                return false;
            }
            return true;
        }
        return false;
    }

    public static function getExpireIn(int $userId): int {
        $file = self::filename($userId);
        if (file_exists($file)) {
            $ts = filemtime($file);
            $output = new EzpzAuthedUser(json_decode(file_get_contents($file), true));
            $expireIn = $ts + $output->getDataExpireIn();
            $now = strtotime('now');
            $diff = $expireIn - $now;
            $diffInMinute = $diff/(1000*60);
            if ($diffInMinute <= 10) {
                self::deleteTokenFile($userId);
            }
            else {
                return (int)$diff;
            }
        }
        return 0;
    }

    public static function getTokenFromFile(int $userId): EzpzAuthedUser {
        $file = self::filename($userId);
        if (file_exists($file)) {
            $ts = filemtime($file);
            $output = new EzpzAuthedUser(json_decode(file_get_contents($file), true));
            $expireIn = $ts + $output->getDataExpireIn();
            $now = strtotime('now');
            $diff = $expireIn - $now;
            $diffInMinute = $diff/(1000*60);
            if ($diffInMinute <= 10) {
                self::deleteTokenFile($userId);
            }
            else {
                return $output;
            }
        }
        return new EzpzAuthedUser([]);
    }

    public static function writeTokenFile(int $userId, string $content): void {
        $file = self::filename($userId);
        file_put_contents($file, $content);
    }

    public static function deleteTokenFile(int $userId): void {
        $file = self::filename($userId);
        if (file_exists($file) && !is_dir($file)) {
            unlink($file);
        }
    }

    private static function filename(int $userId): string {
        if (!defined('ROOT_DIR')) {die('ROOT_DIR is not defined');}
        $file = ROOT_DIR.EZPIZEE_DS.'tmp'.EZPIZEE_DS.'token';
        if (strlen($userId) > 0) {
            $file = $file.EZPIZEE_DS.substr($userId,0,1);
            if (!file_exists($file)) {
                mkdir($file);
            }
            if (strlen($userId) > 1) {
                $file = $file.EZPIZEE_DS.substr($userId,1,1);
                if (!file_exists($file)) {
                    mkdir($file);
                }
                if (strlen($userId) > 2) {
                    $file = $file.EZPIZEE_DS.substr($userId,2,1);
                    if (!file_exists($file)) {
                        mkdir($file);
                    }
                }
            }
            $file = $file.EZPIZEE_DS.$userId.'.json';
        }
        return $file;
    }
}