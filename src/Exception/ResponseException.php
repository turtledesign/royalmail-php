<?php

namespace RoyalMail\Exception;

class ResponseException extends RoyalMailException {

  protected $debug_info = [];

  function __construct($debug) {
    parent::__construct($debug['exception']->getMessage());

    $this->debug_info = $debug;
  }


  function getConnectionDebugInfo() {
    return $this->debug_info;
  }


  function getSoapFault() {
    return $this->get('exception');
  }


  function get($key) {
    return (isset($this->debug_info[$key])) ? $this->debug_info[$key] : NULL;
  }


  function __call($method, $args) {
    $key = preg_replace_callback('/[A-Z]/', function ($m) { return '_' . strtolower($m[0]); }, lcfirst(preg_replace('/^get/', '', $method)));

    if (isset($this->debug_info[$key])) return $this->get($key);
  }
}