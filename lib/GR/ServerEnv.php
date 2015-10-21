<?php

namespace GR;

class ServerEnv {
  public $site_root;
  public $vhost_config_path;
  public $vhost_config_contents;

  public function __construct($site_root) {
    $this->site_root = $site_root;
  }

  public function findApacheConfFile() {
    $apache_site_enabled_dir_path = "/etc/apache2/sites-enabled";
    $dir = @opendir($apache_site_enabled_dir_path);
    if ($dir === FALSE) {
      return FALSE;
    }
    while (($file_name = readdir($dir)) !== FALSE) {
      $path = "$apache_site_enabled_dir_path/$file_name";
      if (!is_file($path)) {
        continue;
      }
      $contents = file_get_contents($path);
      $escaped_site_root = preg_quote($this->site_root, "/");
      $result = preg_match("/DocumentRoot\s*{$escaped_site_root}\/?\s*$/m", $contents);
      if ($result === 1) {
        $this->vhost_config_path = $path;
        $this->vhost_config_contents = $contents;
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

  public function setEnvVars($throw_exception = TRUE) {
    if ($contents = $this->findApacheConfFile()) {
      $env_vars = $this->getEnvVars();
      if ($env_vars === FALSE) {
        if ($throw_exception === TRUE) {
          throw new \Exception("Unable to find environment variables in the Apache configuration file.");
        }

        return FALSE;
      }
      foreach ($env_vars as $env_var_name => $env_var_value) {
        if (putenv("{$env_var_name}={$env_var_value}") === FALSE) {
          throw new \Exception("Unable to set {$env_var_name} environment variable.");
        }
      }
    } else {
      return FALSE;
    }
  }

  public function getEnvVars() {
    $pattern = preg_quote('SetEnv', '/');
    $pattern = "/^.*$pattern.*\$/m";
    if (preg_match_all($pattern, $this->vhost_config_contents, $matches) > 1) {
      $env_vars = array();
      foreach ($matches[0] as $match) {
        $match = trim($match);
        $env_var = explode(' ', $match);
        $env_vars[$env_var[1]] = $env_var[2];
      }

      return $env_vars;
    }

    return FALSE;
  }
}
