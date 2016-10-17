<?php

namespace RoyalMail\Connector;

ini_set("soap.wsdl_cache_enabled","0"); # This may cause issues with PHP version changes: http://lornajane.net/posts/2015/soapfault-when-switching-php-versions


class soapConnector extends baseConnector {

  protected 
    $endpoint = NULL,
    $adaptor_defaults = [
      'soap_client' => '\RoyalMail\Connector\TDSoapClient',
    ],

    $request_input = [];


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
  function doRequest($action, $params = [], $config = []) {
    $this->request_input = ['action' => $action, 'parameters' => $params];

    return $this->getSoapClient($config)->__soapCall($action, [$params]);
  }


  function getDebugInfo() {
    return [
      'config'           => $this->config,
      'request_input'    => $this->request_input,
      'sent_request'     => $this->getAPIFormattedRequest(),
      'sent_headers'     => $this->getSoapClient()->__getLastRequestHeaders(),
      'response'         => $this->getSoapClient()->__getLastResponse(),
      'response_headers' => $this->getSoapClient()->__getLastResponseHeaders(),
    ];
  }


  function getAPIFormattedRequest() {
    return $this->getSoapClient()->__getLastRequest();
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