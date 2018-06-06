<?php
namespace WP63\Tools;

require_once '../vendor/autoload.php';

class VVVTools {
  protected $terminal;
  protected $config;
  protected $config_path;
  protected $argv;
  protected $site_name;
  protected $verbose = false;

  public function __construct( $argv ) {
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

  protected function generateCertificate( $path = './provision/ssl' ) {
    $dn = array(
      "countryName" => "TH",
      "stateOrProvinceName" => "Bangkok",
      "localityName" => "Lat Krabang",
      "organizationName" => "WP63",
      "organizationalUnitName" => "WP63 Development Team",
      "commonName" => sprintf( '%s.test', $this->site_name ),
      "emailAddress" => sprintf( 'dev@%s.test', $this->site_name )
    );

    // Generate a new private (and public) key pair
    $privkey = openssl_pkey_new();

    // Generate a certificate signing request
    $csr = openssl_csr_new($dn, $privkey);

    // You will usually want to create a self-signed certificate at this
    // point until your CA fulfills your request.
    // This creates a self-signed cert that is valid for 365 days
    $sscert = openssl_csr_sign($csr, null, $privkey, 365);

    // Now you will want to preserve your private key, CSR and self-signed
    // cert so that they can be installed into your web server.

    openssl_csr_export($csr, $csrout);

    $padding = $this->terminal->padding(40);

    openssl_x509_export($sscert, $certout);
    //$this->terminal->out( $certout );
    $padding->label('- SSL Certification')->result('done');
    openssl_pkey_export($privkey, $pkeyout);
    $padding->label('- SSL Private Key')->result('done');
    //$this->terminal->out( $pkeyout );

    if( $this->verbose ) {
      // Show any errors that occurred here
      while (($e = openssl_error_string()) !== false) {
        $this->terminal->to('error')->yellow('OpenSSL: ' . $e );
      }
    }

    //save certificate and privatekey to file
    file_put_contents( $path . '/' . $this->site_name . '.test.cert', $certout );
    file_put_contents( $path . '/' . $this->site_name . '.test.key', $pkeyout );
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
    $this->site_name = $response;
    echo PHP_EOL;

    // Create neccessary directories
    $this->terminal->green( '> Creating directory' );
    $padding = $this->terminal->padding(40);

    mkdir( './public_html', 0755 );
    $padding->label('- ./public_html')->result('done');

    mkdir( './provision/ssl', 0755, true );
    $padding->label('- ./provision/ssl')->result('done');

    echo PHP_EOL;

    $this->terminal->green( '> Creating neccesary files' );
    $this->generateCertificate();
  }
}

(new VVVTools( $argv ));
