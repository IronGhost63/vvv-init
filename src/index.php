<?php
namespace WP63\Tools;

require_once '../vendor/autoload.php';

class VVVTools {
  protected $terminal;
  protected $config;
  protected $config_path;
  protected $argv;

  function __construct( $argv ) {
    $this->argv = $argv;
    $this->config_path = $_SERVER['HOME'] . "/.vvv-init.json";
    $this->terminal = new \League\CLImate\CLImate;
    $this->loadConfigs();

    if( !isset( $this->config->vvv_path ) ) {
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

    if( isset( $this->argv[1] ) ) {
      if( !method_exists( $this, $this->argv[1] ) ) {
        $this->terminal->backgroundRed('                               ');
        $this->terminal->backgroundRed('        VVV Tools error        ');
        $this->terminal->backgroundRed('                               ');
        echo PHP_EOL;
        $this->terminal->to('error')->red(':: Method not exists');
        echo PHP_EOL;
        exit;
      }

      $this->{$this->argv[1]}();
    }
  }

  protected function loadConfigs() {
    $config = json_decode( $this->config_path );
    //$this->config = $config;

    $this->config = (object) [
      'vvv_path' => 'wow'
    ];
  }

  protected function setConfig( $key, $value ) {
    $this->config->$key = $value;
  }

  protected function saveConfig() {

  }

  public function init() {
    echo PHP_EOL;

    if( !is_writable('.') ) {
      $this->terminal->backgroundRed('                               ');
      $this->terminal->backgroundRed('        VVV Tools error        ');
      $this->terminal->backgroundRed('                               ');
      echo PHP_EOL;
      $this->terminal->to('error')->red(':: Current directory is not writable.');
      echo PHP_EOL;
      exit;
    }

    $this->terminal->backgroundGreen('                               ');
    $this->terminal->backgroundGreen('           VVV Tools           ');
    $this->terminal->backgroundGreen('                               ');
    echo PHP_EOL;

    $input = $this->terminal->input('Please enter your site name:');
    $response = $input->prompt();
    $this->terminal->green( sprintf( 'Your site name is: %s', $response ) );
    $this->terminal->green( sprintf( 'Website url will be: https://%s.test', $response ) );
    echo PHP_EOL;

    // Create neccessary directories
    $this->terminal->out(':: Creating directory');
    $padding = $this->terminal->padding(12);

    mkdir( './public_html', 0755 );
    $padding->label('- ./public_html')->result('done');

    mkdir( './provision/ssl', 0755, true );
    $padding->label('- ./provision/ssl')->result('done');

    echo PHP_EOL;

    $this->terminal->out(':: Creating neccesary files');
  }
}

(new VVVTools( $argv ));
