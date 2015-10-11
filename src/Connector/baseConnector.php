<?php

namespace RoyalMail\Connector;

abstract class baseConnector {

  protected 
    $config           = [],
    $adaptor_defaults = [];

  function __construct($config) {
    $this->configure(array_merge($this->adaptor_defaults, $config));
  }


  function request($request_type, $params = [], $config = []) {
    return $this->doRequest($request_type, $params, $config);
  }


  abstract protected function doRequest($request_type, $params = [], $config = []);


  function configure($config) {
    $this->config = $config;
  }


}