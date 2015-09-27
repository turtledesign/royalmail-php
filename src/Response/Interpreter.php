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


  function loadResponse($key, $response, $helper = []) {
    $this->response_instance = $response;

    $result = self::build($key, $response, $helper);

    if (isset($result['META']['success']))  $this->succeeded     = $result['META']['success'];
    if (isset($result['META']['security'])) $this->security_info = $result['META']['security'];

    if (isset($result['RESPONSE']) && is_array($result['RESPONSE'])) $this->exchangeArray($result['RESPONSE']);

    return $this;
  }



  static function build($key, $response, $helper = NULL) {
    if (empty($response) && isset($helper['source'])) $response = $helper['source'];

    return self::processSchema(self::getResponseSchema($key), $response, $helper);
  }


  static function processSchema($schema, $response, $helper = []) {
    $built  = [];
    $helper = is_array($helper) ? array_merge(['source' => $response], $helper) : ['source' => $helper];

    foreach ($schema['properties'] as $k => $map) {
      $built = self::addProperty($built, $map, $k, NULL, [], $helper);
    }
    
    return $built;
  }


  static function addProperty($arr, $schema, $key, $val, $defaults, $helper) {
    $val = (empty($schema['_extract'])) ? $val : self::extractValue($helper['source'], $schema);

    if (! empty($schema['_multiple']) && count($stripped = self::stripMeta($schema))) {

      $schema = array_diff_key($schema, $stripped); // FIXME: This is patching to bypass the default Structure multi property handling
      unset($schema['_multiple']);                  
      
      $nest = [];

      foreach ($val as $multi) {
        $nest[] = self::processSchema(['properties' => $stripped], $multi, array_merge($helper, ['source' => $multi]));
      }

      $val = $nest;
    }

    return self::doAddProperty($arr, $schema, $key, $val, $defaults, $helper);
  }


  static function extractValue($response, $map) {
    foreach (explode('/', $map['_extract']) as $step) {
      if (! isset($response->$step)) {
        $response = NULL;
        break;
      
      } else $response = $response->$step;
    }

    if (isset($map['_multiple']) && ! is_array($response)) $response = [$response]; // Single entries for multi-optional values in SOAP elide the array.

    return $response;
  }


  function toJSON() { }


  function serialise($settings = []) {
    // Simplest probably to create a new instance and load the response with the 'text_only' parameter set - saves writing another scanner / parser.
    // could have filters that only run if 'text_only' is set to format values.
  }


  static function getResponseSchema($response_name) {
    return Yaml::parse(dirname(__FILE__) . '/schema/' . $response_name . '.yml');
  }
}