<?php

namespace RoyalMail\Response;

use \Symfony\Component\Yaml\Yaml;


class Interpreter extends \ArrayObject {
  
  use \RoyalMail\Validator\Validates;
  use \RoyalMail\Filter\Filters;
  use \RoyalMail\Helper\Structure;

  protected 
    $response_instance = NULL,
    $schema            = NULL,
    $security_info     = [],
    $errors            = [],
    $warnings          = [],
    $succeeded         = FALSE;


  function __construct($key = NULL, $response = NULL) {
    if (isset($key) && isset($response)) $this->loadResponse($key, $response);
  }


  function succeeded()   { return $this->succeeded; }

  function hasIssues()   { return $this->hasErrors() || $this->hasWarnings(); } // don't we wall.
  function hasErrors()   { return count($this->getErrors()); }
  function getErrors()   { return $this->errors; }
  function hasWarnings() { return count($this->getWarnings()); }
  function getWarnings() { return $this->warnings; }

  function getSecurityInfo() {
    return $this->security_info;
  }


  function getResponse() {
    return $this->getArrayCopy();
  }


  function loadResponse($key, $response) {
    $this->response_instance = $response;

    $result = self::build($key, $response);

    if (isset($result['META']['success']))  $this->succeeded     = $result['META']['success'];
    if (isset($result['META']['security'])) $this->security_info = $result['META']['security'];

    if (isset($result['RESPONSE']) && is_array($result['RESPONSE'])) $this->exchangeArray($result['RESPONSE']);

    return $this;
  }



  static function build($key, $response, $helper = NULL) {
    if (empty($response) && isset($helper['source'])) $response = $helper['source'];

    return self::processSchema(self::getResponseSchema($key), $response);
  }


  static function processSchema($schema, $response) {
    $built    = [];

    foreach ($schema['properties'] as $k => $map) {
      $built = self::addProperty($built, $map, $k, NULL, [], ['source' => $response]);
    }
    
    return $built;
  }


  static function addProperty($arr, $schema, $key, $val, $defaults, $helper) {
    $val = (empty($schema['_extract'])) ? $val : self::extractValue($helper['source'], $schema);

    return self::doAddProperty($arr, $schema, $key, $val, $defaults, $helper);
  }


  static function extractValue($response, $map) {
    foreach (explode('/', $map['_extract']) as $step) {
      if (! isset($response->$step)) {
        $response = NULL;
        break;
      
      } else $response = $response->$step;
    }

    return isset($map['_multiple']) ? [$response] : $response; // TODO: this will need updating depending on how multi-values are returned.
  }



  function toJSON() { }


  function serialise($result, $schema, $settings = []) {

  }


  static function getResponseSchema($response_name) {
    return Yaml::parse(dirname(__FILE__) . '/schema/' . $response_name . '.yml');
  }
}