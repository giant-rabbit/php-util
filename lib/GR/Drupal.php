<?php 

namespace GR;

class Drupal {
  
  public static function parse_drupal_settings($path) {
    include($path) ;
    $ret = array() ;
    
    $ret['databases'] = $databases ;
    return $ret ;
  }
  
  public static function get_database_credentials($root=false) {
    $root = $root ?: getcwd();
    $f = $root . "/sites/default/settings.php" ;
    $parsed = Drupal::parse_drupal_settings($f) ;
    return array(
      'host'     => $parsed['databases']['default']['default']['host'] ,
      'username' => $parsed['databases']['default']['default']['username'] ,
      'password' => $parsed['databases']['default']['default']['password'] ,
      'database' => $parsed['databases']['default']['default']['database'] ,
    );
  }

  public static function get_database_connection($root=false, $options=null) {
    $root = $root ?: getcwd();
    $creds = $this->get_database_credentials($root) ;
    extract($creds) ;
    $dsn = "mysql:host={$host};dbname={$database}" ;
    $dbh = new \PDO($dsn, $username, $password, $options) ;
    $dbh->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION ); 
    $dbh->setAttribute( \PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC );
    return $dbh ;
  }
    
}