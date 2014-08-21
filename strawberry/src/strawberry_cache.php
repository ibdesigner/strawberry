<?php

class StrawberryCache {
    
    private static $memcache = false;
    private static $time = 30;
  
    
    public static function set($key, $value){
        if(self::has_memcached() === true){
            self::$memcache->set($key, $value, 0, self::$time);
        } else {
            set_transient($key, $value, self::$time);
        }
    }
    
    public static function get($key){
        if(self::has_memcached() === true){            
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
    
    public static function memcache_connect($host = '127.0.0.1', $port = 11211){
        self::$memcache = new Memcache;
        self::$memcache->connect($host, $port);  
        return new self;
    }
    
    private static function has_memcached(){
        if ( class_exists('Memcache') ) {
            if( self::$memcache === false ){
                self::memcache_connect();                      
            }
            return true;
        } else {
            return false;
        }
    }
  
}