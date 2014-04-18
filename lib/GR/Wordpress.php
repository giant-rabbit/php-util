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
    $ret = false;
    if ($parsed) {
      $ret = array(
        'host' => $parsed['constants']['DB_HOST'] ,
        'username' => $parsed['constants']['DB_USER'] ,
        'password' => $parsed['constants']['DB_PASSWORD'] ,
        'database' => $parsed['constants']['DB_NAME'] ,
      );
    }
    return $ret;
  }
  
  public static function get_database_connection($root=false, $options=null) {
    $root = $root ?: getcwd();
    $creds = Wordpress::get_database_credentials($root) ;
    extract($creds) ;
    $dsn = "mysql:host={$host};dbname={$database}" ;
    $dbh = new \PDO($dsn, $username, $password, $options) ;
    $dbh->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION ); 
    $dbh->setAttribute( \PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC );
    return $dbh ;
  }
}