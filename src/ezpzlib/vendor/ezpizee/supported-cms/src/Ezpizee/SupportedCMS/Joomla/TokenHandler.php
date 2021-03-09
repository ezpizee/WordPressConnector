<?php

namespace Ezpizee\SupportedCMS\Joomla;

use Exception;
use Ezpizee\MicroservicesClient\Token;
use Ezpizee\MicroservicesClient\TokenHandlerInterface;
use Ezpizee\Utils\Logger;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;

class TokenHandler implements TokenHandlerInterface
{
    private $key = '';

    public function __construct(string $key) {$this->key = $key;}

    public function keepToken(Token $token): void {
        if ($this->key) {
            $cVal = $this->getCVal();
            Factory::getSession()->set($cVal, serialize($token));
        }
    }

    public function getToken(): Token {
        if ($this->key) {
            $cVal = $this->getCVal();
            if (Factory::getSession()->has($cVal)) {
                $token = unserialize(Factory::getSession()->get($cVal));
                if ($token instanceof Token) {
                    return $token;
                }
            }
        }
        return new Token([]);
    }

    public function setCookie(string $name, string $value = null, int $expire=0, string $path=''): void {
        try {
            $app = Factory::getApplication();
            $path = empty($path)?($app->isClient('site')?'/':'/administrator/'):$path;
            $app->input->cookie->set($name, $value, $expire, $path);
        }
        catch (Exception $e) {
            Logger::error($e->getMessage());
        }
    }

    private function getCVal(): string {
        $cVal = null;
        if (isset($_COOKIE) && !empty($_COOKIE) && isset($_COOKIE[$this->key])) {
            $cVal = $_COOKIE[$this->key];
        }
        if (empty($cVal)) {
            $cVal = Factory::getApplication()->input->cookie->getString($this->key);
        }
        if (empty($cVal)) {
            $cVal = $this->key;
        }
        return $cVal;
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
        File::write($file, $content);
    }

    public static function deleteTokenFile(int $userId): void {
        $file = self::filename($userId);
        if (file_exists($file) && !is_dir($file)) {
            File::delete($file);
        }
    }

    private static function filename(int $userId): string {
        $file = JPATH_ROOT.EZPIZEE_DS.'tmp'.EZPIZEE_DS.'token';
        if (strlen($userId) > 0) {
            $file = $file.EZPIZEE_DS.substr($userId,0,1);
            if (strlen($userId) > 1) {
                $file = $file.EZPIZEE_DS.substr($userId,1,1);
                if (strlen($userId) > 2) {
                    $file = $file.EZPIZEE_DS.substr($userId,2,1);
                }
            }
            $file = $file.EZPIZEE_DS.$userId.'.json';
        }
        return $file;
    }
}
