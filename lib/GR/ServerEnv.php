<?php

namespace GR;

class ServerEnv {
  public $site_root;
  public $vhost_config_path;

  public function __construct($site_root) {
    $this->site_root = $site_root;
    $this->vhost_config_path = self::getConfPathFromSiteRoot($site_root);
  }

  public function apacheConfFileExists() {
    $file_exists = file_exists($this->vhost_config_path);
    if (!$file_exists) {
      // Newer versions of Ubuntu add a .conf to the end of vhost config files.
      $this->vhost_config_path = "{$this->vhost_config_path}.conf";
      $file_exists = file_exists($this->vhost_config_path);
    }
    return $file_exists;
  }

  public function requireApacheConfFile() {
    if (!$this->apacheConfFileExists()) {
      throw new \Exception("No apache virtualhost configuration file exists at {$this->vhost_config_path}.");
    }
  }

  public function setEnvVars() {
    $conf = new \Config();
    if ($this->apacheConfFileExists()) {
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

  public static function getConfPathFromSiteRoot($site_root) {
    $vhost_config_file_name = basename($site_root);
    return "/etc/apache2/sites-enabled/{$vhost_config_file_name}";
  }

}
