<?php
namespace WP63\Tools;

require_once '../vendor/autoload.php';

class VVVInit {
  protected $write;
  protected $config;
  protected $config_path;

  function __construct( $argv ) {
    $this->config_path = $_SERVER['HOME'] . "/.vvv-init.json";
    $this->write = new \League\CLImate\CLImate;
    $this->loadConfig();
  }

  protected function loadConfig() {
    $config = json_decode( $this->config_path );

    if( !$config ) {
      echo PHP_EOL;
      $this->write->backgroundRed('                               ');
      $this->write->backgroundRed('  VVV path is not configured.  ');
      $this->write->backgroundRed('                               ');
      echo PHP_EOL;
      $this->write->out('Use `vvv-init setPath path/to/vvv-custom.yml` to set VVV path');
      $this->write->out('Or use `vvv-init reset` to reset configuration file.');
      echo PHP_EOL;
    }

    $this->config = $config;
  }

  protected function setConfig( $key, $value ) {
    $this->config->$key = $value;
  }

  protected function saveConfig() {

  }
}

(new VVVInit( $argv ));
