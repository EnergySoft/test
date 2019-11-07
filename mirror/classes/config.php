<?php 

class Config {
    private static $conf = Array();

    public static function set($key,$value){
        self::$conf[$key] = $value;
    }

    public static function get($key){
        return self::$conf[$key];
    }

}