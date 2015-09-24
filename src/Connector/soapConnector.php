<?php

namespace RoyalMail\Connector;

ini_set("soap.wsdl_cache_enabled","1"); # Save a bit of network traffic and delay by caching the WSDL file.
                                        # TODO: Check whether the cache is going to need clearing when updating to new versions of WSDL.



/**
 * This trait handles the SOAP connection.  
 * Any class implementing it needs to have the getEndpoint() method implemented to return the API URL.
 * 
 */
class soapConnector extends baseConnector {

  protected $endpoint = NULL;



  /**
   * Send off the request to the Royal Mail API
   * 
   * @see baseConnector::doRequest()
   * 
   * @return \RoyalMail\Response\baseResponse Response class for the request sent.
   */
  function doRequest($request_type, $params, $config = []) {
    $config = array_merge(['timezone' => 'BST'], $config);

    return $this->getSoapClient($config)->__soapCall($request_type, [$params]);
  }


  function getSoapClient($config = NULL) {
    if (empty($this->soap_client)) {
      $this->soap_client = new \RoyalMail\Connector\TDSoapClient($this->getEndpoint(), $config);
    }

    return $this->soap_client;
  }


  function setSoapClient($client, $config = NULL) {
    $this->soap_client = $client;

    return $this;
  }


  function setEndpoint($endpoint) { 
    $this->endpoint = $endpoint; 

    return $this;
  }


  function getEndpoint() {
    if (empty($this->endpoint)) throw new \InvalidArgumentException('No location or endpoint given for SOAP WSDL');

    return $this->endpoint;
  }



  /**
   * Make sure the response from the RM API seems kosher
   * 
   * @throws \RoyalMail\Exception\ResponseException
   */
  protected function verifyResponse($response) {
    // Verify WS security values.

    return $responses;
  }
}