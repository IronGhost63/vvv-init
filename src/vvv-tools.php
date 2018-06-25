<?php
namespace WP63\Tools;

require_once '../vendor/autoload.php';

use Cocur\Slugify\Slugify;

error_reporting(E_ALL);

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
    $padding = $this->terminal->padding(40);
    // src: https://gist.github.com/dol/e0b7f084e2e7158efc87

    $keyConfig = [
      'private_key_type' => OPENSSL_KEYTYPE_RSA,
      'private_key_bits' => 2048,
    ];

    $sanDomains = [
      $this->site_name . '.test',
    ];

    $dn = [
      'commonName' => reset($sanDomains)
    ];

    $csrConfig = [
      'config' => __DIR__ . '/openssl.cnf',
      'req_extensions' => 'v3_req',
      'digest_alg' => 'sha256',
    ];

    $addPrefix = function ($value) {
      // Important: Sanatize domain value and check if a valid domain
      return 'DNS:' . $value;
    };

    $sanDomainPrefixed = array_map($addPrefix, $sanDomains);

    putenv('VVV_SSL_SAN=' . implode(',', $sanDomainPrefixed));

    $key = openssl_pkey_new($keyConfig);
    $csr = openssl_csr_new($dn, $key, $csrConfig);
    $sscert = openssl_csr_sign($csr, null, $key, 365);

    if (false === $csr) {
      $this->terminal->backgroundRed(' Errors occur while generating certification ');

      while (($e = openssl_error_string()) !== false) {
        $this->terminal->red( $e );
      }

      return false;
    } else {
      openssl_csr_export($csr, $csrout);
      openssl_x509_export($sscert, $csrout);
      openssl_pkey_export($key, $pkeyout);

      //save certificate and privatekey to file
      file_put_contents( $path . '/' . $this->site_name . '.test.cert', $csrout );
      $padding->label('- SSL Certification')->result('done');
      file_put_contents( $path . '/' . $this->site_name . '.test.key', $pkeyout );
      $padding->label('- SSL Private Key')->result('done');
    }
  }

  public function init() {
    $slugify = new Slugify();
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
    $response = $slugify->slugify($input->prompt());
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
    $padding->label('- vvv-hosts')->result('done');
    $padding->label('- vvv-init.sh')->result('done');
    $padding->label('- vvv-nginx.conf')->result('done');

    echo PHP_EOL;

    $this->terminal->green( '> Updating VVV configuration' );
    $padding->label('- vvv-custom.yml')->result('TBD');

    echo PHP_EOL;

    $this->terminal->border();

    echo PHP_EOL;

    $this->terminal->green( 'Your VVV project is <background_green><white> ready </white></background_green>!' );
    $this->terminal->out( 'Run `vagrant reload --provision` from VVV directory to take effect' );
    $this->terminal->out( sprintf( 'To access your website, put your web files in `public_html` directory. And goto https://%s.test', $this->site_name ) );

    echo PHP_EOL;
  }
}

(new VVVTools( $argv ));
