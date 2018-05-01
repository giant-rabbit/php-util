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
      $result = preg_match("/DocumentRoot\s*{$escaped_site_root}(\/web)?\/?\s*$/m", $contents);
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

  public function getApacheConfFileContents() {
    $contents = $this->vhost_config_contents;
    if ($contents === NULL) {
      $contents = $this->findApacheConfFile();
    }

    return $contents;
  }

  public function setEnvVars($throw_exception = TRUE) {
    if ($contents = $this->getApacheConfFileContents()) {
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

  public function setServerHttpHost() {
    if ($contents = $this->getApacheConfFileContents()) {
      $server_name = $this->getServerName();
      if ($server_name === FALSE) {
        throw new \Exception("Unable to set server HTTP_HOST as the ServerName in the Apache config file could not be determined.");
      }
      putenv("HTTP_HOST={$server_name}");
    }
  }

  public function getServerName() {
    $server_name = FALSE;
    $pattern = preg_quote('ServerName', '/');
    $pattern = "/^.*$pattern.*\$/m";
    if (preg_match_all($pattern, $this->vhost_config_contents, $matches) > 1) {
      foreach ($matches[0] as $match) {
        $match = trim($match);
        $server_name_array = explode(' ', $match);
        $server_name = $server_name_array[1];
        break;
      }
    }

    return $server_name;
  }
}
