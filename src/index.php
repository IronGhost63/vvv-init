<?php
namespace WP63\Tools;

require_once '../vendor/autoload.php';

class VVVInit {
  protected $terminal;
  protected $config;
  protected $config_path;

  function __construct( $argv ) {
    $this->config_path = $_SERVER['HOME'] . "/.vvv-init.json";
    $this->terminal = new \League\CLImate\CLImate;
    $this->loadConfigs();

    if( isset( $argv[1] ) ) {
      // Command is specified
    } else {
      // Command not specified
      // Fallback to default behavior: init project
      if( !$this->config->vvv_path ) {
        echo PHP_EOL;
        $this->terminal->backgroundRed('                               ');
        $this->terminal->backgroundRed('  VVV path is not configured.  ');
        $this->terminal->backgroundRed('                               ');
        echo PHP_EOL;
        $this->terminal->out('Use `vvv-init setPath path/to/vvv-custom.yml` to set VVV path');
        $this->terminal->out('Or use `vvv-init reset` to reset configuration file.');
        echo PHP_EOL;
        exit;
      }
    }
  }

  protected function loadConfigs() {
    $config = json_decode( $this->config_path );
    $this->config = $config;
  }

  protected function setConfig( $key, $value ) {
    $this->config->$key = $value;
  }

  protected function saveConfig() {

  }
}

(new VVVInit( $argv ));
