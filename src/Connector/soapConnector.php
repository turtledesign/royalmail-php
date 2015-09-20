<?php

namespace RoyalMail\Connector;

ini_set("soap.wsdl_cache_enabled","1"); # Save a bit of network traffic and delay by caching the WSDL file.
                                        # TODO: Check whether the cache is going to need clearing when updating to new versions of WSDL.



/**
 * This trait handles the SOAP connection.  
 * Any class implementing it needs to have the getEndpoint() method implemented to return the API URL.
 * 
 */
trait soapConnector {

  /**
   * Send off the request to the Royal Mail API
   * 
   * @see baseConnector::doRequest()
   * 
   * @return \RoyalMail\Response\baseResponse Response class for the request sent.
   */
  function doRequest($request_type, $params = [], $config = []) {
    $config = array_merge(['timezone' => 'BST'], $config);

    return $this->getSecuredSoapClient($config)->__soapCall($request_type, $params);
  }





  /**
   * Create the soap client for the endpoint given and add the WSSE header.
   * 
   * @param array $config
   * 
   * @return SoapClient
   */
  protected function getSecuredSoapClient($config) {
    if (empty($this->soap_client)) $this->soap_client = $this->addSecurityHeader(new \RoyalMail\Connector\TDSoapClient($this->getEndpoint()), $config);

    return $this->soap_client;
  }


  /**
   * Add the WSSE security header { https://msdn.microsoft.com/en-gb/library/ms977327.aspx }
   * 
   * This is currently done by directly inserting the XML template given in the RM docs for simplicity.
   * Other possibilities: 
   *  - http://php.net/manual/en/soapclient.soapclient.php#97273 
   *  - https://github.com/BeSimple/BeSimpleSoapClient 
   *  - http://stackoverflow.com/questions/2987907/how-to-implement-ws-security-1-1-in-php5
   * 
   * @param SoapClient $soap_client
   * @param array      $config
   * 
   * @return SoapClient 
   */
  protected function addSecurityHeader($soap_client, $config) {
    $nonce = $this->getNonce();
    $ts    = $this->getTimestamp($config['timezone']);

    $header_xml = '
<soapenv:Header>
  <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecuritysecext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
  <wsse:Username>' . $config['username'] . '</wsse:Username>
  <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-tokenprofile-1.0#PasswordDigest">' . $this->getPasswordDigest($nonce, $ts, $config['password']) . '</wsse:Password>
  <wsse:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-messagesecurity-1.0#Base64Binary">' . base64_encode($nonce) . '</wsse:Nonce>
  <wsu:Created>' . $s . '</wsu:Created>
  </wsse:UsernameToken>
  </wsse:Security>
</soapenv:Header>
';

    return $soap_client;
  }


  protected function getNonce() {
    return mt_rand();
  }


  protected function getTimestamp($tz) {
    return (new \DateTime('now', new \DateTimeZone($tz)))->format('Y-m-d\TH:i:s.000\Z');
  }


  protected function getPasswordDigest($nonce, $ts, $pass) {
    return base64_encode(sha1($nonce . $ts . sha1($pass)));
  }



  /**
   * Make sure the response from the RM API seems kosher
   * 
   * @param StdObject $response
   * 
   * @throws \RoyalMail\Exception\ResponseException
   */
  protected function verifyResponse($response) {
    // Verify WS security values.

    return $responses;
  }
}