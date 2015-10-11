<?php

namespace RoyalMail\Connector;

  /**
   * This fakes a response by loading the XML from response file for the appropriate request (and postfix, if set).
   * 
   * Allows for development and testing without using a live endpoint.
   * 
   */
class MockSoapClient extends TDSoapClient {

  protected $postfix = 'Response.xml';

  function __doRequest($request, $location, $action, $version, $one_way = 0) {
    return file_get_contents($this->config['static_responses'] . '/' . $action . $this->postfix);
  }


  function setPostfix($postfix) {
    $this->postfix = $postfix;

    return $this;
  }


}