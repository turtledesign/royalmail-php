<?php

namespace RoyalMail\Connector;

abstract class baseConnector {

  protected 
    $config           = [],
    $adaptor_defaults = [];

  function __construct($config) {
    $this->configure(array_merge($this->adaptor_defaults, $config));
  }


  function request($params) {
    return $this->doRequest($params);
  }


  abstract protected function doRequest($config, $request_type, $params);


  function configure($config) {
    $this->config = $config;
  }


}