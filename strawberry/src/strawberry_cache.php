<?php

class StrawberryCache {
    
    private static $memcache;
    private static $use_memcache;
    private static $time = 30;
    
    public function __construct(){
        if(has_memcached() === true){
            self::$use_memcache = true;
        }
    }
    
    public static function set($key, $value){
        if(self::$use_memcache === true){
            self::$memcache->set($key, $value, 0, self::$time);
        } else {
            set_transient($key, $value, self::$time);
        }
    }
    
    public static function get($key){
        if(self::$use_memcache === true){
            $cache_data = self::$memcache->get($key);
        } else {
            $cache_data =  get_transient($key);
        }
        
        if($cache_data === false){
            return false;
        } else {
            return $cache_data;
        }
    }
    
    public static function time($seconds){
        self::$time = $seconds;
        return new self;
    }
    
    public static function memcache_connect($host = 'localhost', $port = 11211){
        self::$memcache = new Memcache;
        self::$memcache->connect($host, $port);  
        return new self;
    }
    
    private static function has_memcached(){
        if ( class_exists('Memcache') ) {
            self::memcache_connect();                      
            return true;
        } else {
            return false;
        }
    }
    
    
}