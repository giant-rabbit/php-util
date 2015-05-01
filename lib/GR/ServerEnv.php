<?php

namespace GR;

class ServerEnv {
  public $site_root;
  public $vhost_config_path;

  public function __construct($site_root) {
    $this->site_root = $site_root;
  }

  public function findApacheConfFile() {
    $apache_site_enabled_dir_path = "/etc/apache2/sites-enabled";
    $dir = opendir($apache_site_enabled_dir_path);
    if ($dir === FALSE) {
      throw new Exception("Error opening directory '$apache_site_enabled_dir_path': " . print_r(error_get_last(), TRUE));
    }
    while (($file_name = readdir($dir)) !== FALSE) {
      $path = "$apache_site_enabled_dir_path/$file_name";
      if (!is_file($path)) {
	continue;
      }
      $contents = file_get_contents($path);
      $escaped_site_root = preg_quote($this->site_root, "/");
      $result = preg_match("/DocumentRoot\s*{$escaped_site_root}\s*$/m", $contents);
      if ($result === 1) {
	$this->vhost_config_path = $path;
	return TRUE;
      }
    }
    return FALSE;
  }

  public function requireApacheConfFile() {
    if (!$this->findApacheConfFile()) {
      throw new \Exception("No apache virtualhost configuration file exists at {$this->vhost_config_path}.");
    }
  }

  public function setEnvVars() {
    $conf = new \Config();
    if ($this->findApacheConfFile()) {
      $vhost_config_root = $conf->parseConfig($this->vhost_config_path, 'apache');
      $vhost_config = $vhost_config_root->getItem('section', 'VirtualHost');
      $i = 0;
      while ($item = $vhost_config->getItem('directive', 'SetEnv', NULL, NULL, $i++)) {
        $env_variable = explode(' ', $item->content);
        if ($env_variable[0] == 'APP_ENV' || $env_variable[0] == 'APP_NAME') {
          if (putenv("{$env_variable[0]}={$env_variable[1]}") === FALSE) {
            throw new \Exception("Unable to set {$env_variable[0]} environment variable.");
          }
        }
      }
    }
  }
}
