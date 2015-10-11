<?php

namespace RoyalMail\Connector;

class TDSoapClient extends \SoapClient {

  protected $config = [];

  private 
    $wss_ns = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd',
    $wsu_ns = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';


  function __construct($wsdl = NULL, $config = []) {
    $this->config = $config;
    
    parent::__construct($wsdl, array_merge(['trace' => 1], @$config['soap_client_options'] ?: []));

    if (isset($config['username'])) $this->addWSSecurityHeader($config);
  }


  /**
   * Add the WSSE security header { https://msdn.microsoft.com/en-gb/library/ms977327.aspx }
   *
   * c.f.  http://php.net/manual/en/soapclient.soapclient.php#114976 and others in thread.
   */
  function addWSSecurityHeader($config) {
    $config = array_merge(['timezone' => 'BST'], $config);

    $nonce = $this->getNonce();
    $ts    = $this->getTimestamp($config['timezone']);

    $auth = new \stdClass();
    $auth->Username = new \SoapVar($config['username'], XSD_STRING, NULL, $this->wss_ns, NULL, $this->wss_ns);
    $auth->Password = new \SoapVar($this->getPasswordDigest($nonce, $ts, $config['password']), XSD_STRING, NULL, $this->wss_ns, NULL, $this->wss_ns);
    $auth->Nonce    = new \SoapVar($nonce, XSD_STRING, NULL, $this->wss_ns, NULL, $this->wss_ns);
    $auth->Created  = new \SoapVar($ts, XSD_STRING, NULL, $this->wss_ns, NULL, $this->wsu_ns);

    $username_token = new \stdClass();
    $username_token->UsernameToken = new \SoapVar(@$auth, SOAP_ENC_OBJECT, NULL, $this->wss_ns, 'UsernameToken', $this->wss_ns);

    $security_sv = new \SoapVar(
      new \SoapVar($username_token, SOAP_ENC_OBJECT, NULL, $this->wss_ns, 'UsernameToken', $this->wss_ns),
      SOAP_ENC_OBJECT, NULL, $this->wss_ns, 'Security', $this->wss_ns
    );

    $this->__setSoapHeaders([new \SoapHeader($this->wss_ns, 'Security', $security_sv, TRUE)]);
  }


  function getNonce() { return mt_rand(); }


  function getTimestamp($tz) { return (new \DateTime('now', new \DateTimeZone($tz)))->format('Y-m-d\TH:i:s.000\Z'); }


  function getPasswordDigest($nonce, $ts, $pass) { return base64_encode(sha1($nonce . $ts . sha1($pass))); }
}