<?php

namespace RoyalMail\Connector;

ini_set("soap.wsdl_cache_enabled","1"); # Save a bit of network traffic and delay by caching the WSDL file.
                                        # TODO: Check whether the cache is going to need clearing when updating to new versions of WSDL.


class soapConnector extends baseConnector {

  protected 
    $endpoint = NULL,
    $adaptor_defaults = [
      'soap_client' => '\RoyalMail\Connector\TDSoapClient',
    ];


  function __construct($config = []) {
    parent::__construct($config);

    if (isset($config['endpoint']))    $this->setEndpoint($config['endpoint']);

    if (isset($config['soap_client'])) $this->loadSoapClient($this->config);
  }


  /**
   * Send off the request to the Royal Mail API
   * 
   * @see baseConnector::doRequest()
   * 
   * @return \RoyalMail\Response\baseResponse Response class for the request sent.
   */
  function doRequest($request_type, $params = [], $config = []) {
    return $this->getSoapClient($config)->__soapCall($request_type, [$params]);
  }


  function getSoapClient($config = NULL) {
    if (empty($this->soap_client)) $this->loadSoapClient(empty($config) ? $this->config : array_merge($this->config, $config));

    return $this->soap_client;
  }


  function loadSoapClient($config) {
    $config = array_merge(['endpoint' => $this->getEndpoint()], $config);

    $this->setSoapClient(new $config['soap_client']($config['endpoint'], $config));

    return $this;
  }


  function setSoapClient($client) {
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

    return $response;
  }
}