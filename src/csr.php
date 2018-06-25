<?php
$keyConfig = [
  'private_key_type' => OPENSSL_KEYTYPE_RSA,
  'private_key_bits' => 2048,
];

$key = openssl_pkey_new($keyConfig);

$sanDomains = [
  'mydomain.tld',
  'seconddomain.tld',
];
$dn = [
  'commonName' => reset($sanDomains),
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
$csr = openssl_csr_new($dn, $key, $csrConfig);
if (false === $csr) {
  while (($e = openssl_error_string()) !== false) {
    echo $e . '\n';
  }
  return;
}
openssl_csr_export($csr, $csrout);

$sscert = openssl_csr_sign($csr, null, $key, 365);

openssl_x509_export($sscert, $csrout);

echo $csrout;
