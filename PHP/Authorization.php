<?php

class Authorization {

    private static $instance = null;
    private $isAuthorized = null;

    private function __construct() {
        $this->isAuthorized = self::isLocale();
        // add login system
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Authorization();
        }

        return self::$instance;
    }
    
    public function getIsAuthorized():bool {
        return $this->isAuthorized;
    }

    
    public static function getIP() {
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (array_key_exists('REMOTE_ADDR', $_SERVER)) {
            return $_SERVER['REMOTE_ADDR'];
        } elseif (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (defined('STDIN')) {
            return 'console';
        } else {
            die('<br><b>Server couldn\'t recognize your IP Adress, disable your Proxy/VPN or contact the web Administrator</b>');
        }
    }

    public static function isLocale():bool {
        $theIP = self::getIP();

        if ($theIP == '127.0.0.1' || $theIP == '::1' || $theIP == 'localhost' || $theIP == 'console' || $theIP == '82.209.15.155') {
            return true;
        }

        $theIPexploded = explode('.', $theIP);
        if (count($theIPexploded) == 4 && $theIPexploded[0] == 127 && $theIPexploded[1] == 0 && $theIPexploded[2] == 0 && $theIPexploded[3] > 0 && $theIPexploded[3] < 256) {
            return true;
        }

        return false;
    }

}
