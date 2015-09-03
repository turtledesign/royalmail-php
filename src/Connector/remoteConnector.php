<?php

namespace RoyalMail\Connector;

ini_set("soap.wsdl_cache_enabled","1"); # Save a bit of network traffic and delay by caching the WSDL file.
                                        # TODO: Check whether the cache is going to need clearing when updating to new versions of WSDL.



/**
 * This trait handles the SOAP connection.  
 * Any class implementing it needs to have the getEndpoint() method implemented to return the API URL.
 * 
 */
trait remoteConnector {

  function doRequest($config, $request_type, $params) {

  }


  protected function getSoapClient($config) {
    return $this->addSecurityHeader(new \SoapClient($this->getEndpoint() . '?wsdl'));
  }


  protected function addSecurityHeader($soap_client, $config) {

    $nonce = $this->getNonce();
    $ts    = $this->getTimestamp($config['timezone']);
    $pass_digest  = $this->getPasswordDigest($nonce, $ts, $config['password']);


    return $soap_client;
  }




  protected function getNonce() {
    return 1;
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

    return $this;
  }
}