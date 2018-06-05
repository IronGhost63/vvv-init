<?php
namespace WP63\Tools;

require_once '../vendor/autoload.php';

class VVVInit {
  private $write;
  private $config;

  function __construct( $argv ){
    $this->write = new \League\CLImate\CLImate;
    $this->loadConfig();
  }

  private function loadConfig(){
    $config_path = $_SERVER['HOME'] . "/.vvv-init.json";
    $config = json_decode( $config_path );

    if( !$config ) {
      echo PHP_EOL;
      $this->write->backgroundRed('                               ');
      $this->write->backgroundRed('  VVV path is not configured.  ');
      $this->write->backgroundRed('                               ');
      echo PHP_EOL;
      $this->write->out('Use `vvv-init setPath path/to/vvv-custom.yml` to set VVV path');
      $this->write->out('Or use `vvv-init reset` to reset configuration file.');
      echo PHP_EOL;
      exit;
    }

    $this->config = $config;
  }
}

(new VVVInit( $argv ));
