<?php 

namespace GR;

class Wordpress {

  public static function parse_config($path) {
    $a = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ;
    $ret = array() ;
    foreach ($a as $ln) {
      $regex_single = '/define\(\'(.+)\',\s*\'(.+)\'\)/' ;
      $regex_double = '/define\(\"(.+)\",\s*\"(.+)\"\)/' ;

      if (preg_match($regex_single, $ln, $match)) {
        $ret['constants'][$match[1]] = $match[2] ;
      }
      
      if (preg_match($regex_double, $ln, $match)) {
        $ret['constants'][$match[1]] = $match[2] ;
      }
      
    }
    
    return $ret ;
  }
  
  public static function get_database_credentials($root=false){
    $root = $root ?: getcwd();
    $f = $root . "/wp-config.php" ;
    $parsed = Wordpress::parse_config($f) ;
    return array(
      'host' => $parsed['constants']['DB_HOST'] ,
      'username' => $parsed['constants']['DB_USER'] ,
      'password' => $parsed['constants']['DB_PASSWORD'] ,
      'database' => $parsed['constants']['DB_NAME'] ,
    );
  }
}