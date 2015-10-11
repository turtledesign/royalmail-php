<?php

namespace RoyalMail\Response;

use \Symfony\Component\Yaml\Yaml;
use \RoyalMail\Exception\StructureSkipFieldException as SkipException;

class Interpreter extends \ArrayObject {
  
  use \RoyalMail\Validator\Validates;
  use \RoyalMail\Filter\Filters;
  use \RoyalMail\Helper\Structure;

  protected 
    $response_instance = NULL,
    $response_schema   = NULL,
    $schema            = NULL,
    $security_info     = [],
    $errors            = [],
    $warnings          = [],
    $succeeded         = FALSE;


  function __construct($key = NULL, $response = NULL) {
    if (isset($key) && isset($response)) $this->loadResponse($key, $response);
  }


  function succeeded()   { return $this->succeeded; }

  function hasIssues()   { return $this->hasErrors() || $this->hasWarnings(); } 
  function hasErrors()   { return count($this->getErrors()) > 0; }
  function getErrors()   { return $this->errors; }
  function hasWarnings() { return count($this->getWarnings()) > 0; }
  function getWarnings() { return $this->warnings; }

  function hasBinaries() {
    foreach ($this->getBinaryKeys() as $bin) if (! empty($this[$bin])) return TRUE;

    return FALSE;
  }


  function getSecurityInfo() {
    return $this->security_info;
  }


  function getResponse() {
    return $this->getArrayCopy();
  }


  function loadResponse($key, $response, $helper = []) {
    $this->response_instance = $response;
    $this->response_schema   = self::getResponseSchema($key);

    $result = self::build($this->response_schema, $response, $helper);

    if (isset($result['META']['success']))              $this->succeeded     = $result['META']['success'];
    if (isset($result['META']['security']))             $this->security_info = $result['META']['security'];
    if (isset($result['META']['messages']['errors']))   $this->errors        = $result['META']['messages']['errors'];
    if (isset($result['META']['messages']['warnings'])) $this->warnings      = $result['META']['messages']['warnings'];

    if (isset($result['RESPONSE']) && is_array($result['RESPONSE'])) $this->exchangeArray($result['RESPONSE']);

    if ($this->hasErrors()) $this->succeeded = FALSE;

    return $this;
  }



  static function build($schema, $response, $helper = NULL) {
    if (empty($response) && isset($helper['source'])) $response = $helper['source'];

    if (is_scalar($schema)) $schema = self::getResponseSchema($schema);

    return self::processSchema($schema, $response, $helper);
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
    try {
      $val = (empty($schema['_extract'])) ? $val : self::extractValue($helper['source'], $schema);

    } catch (SkipException $e) {
      // pass for now - in some circumstances may be best to create an empty structure (defined in schema).
      return $arr;
    }

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
      if (! isset($response->$step)) throw new SkipException('value not present in response');

      $response = $response->$step;
    }

    if (isset($map['_multiple']) && ! is_array($response)) $response = [$response]; // Single entries for multi-optional values in SOAP elide the array.

    return $response;
  }


  function getResponseEncoded() {
    $arr = $this->getResponse();

    if (isset($this->response_schema['binaries'])) foreach (array_keys($this->response_schema['binaries']) as $bin) {
      if (! empty($arr[$bin])) $arr[$bin] = base64_encode($arr[$bin]);
    }

    return $arr;
  }



  function getBinaryKeys() {
    return array_keys($this->getBinariesInfo());
  }


  function getBinariesInfo() {
    return @$this->response_schema['binaries'] ?: [];
  }


  function serialise($settings = []) {
    // Simplest probably to create a new instance and load the response with the 'text_only' parameter set - saves writing another scanner / parser.
    // could have filters that only run if 'text_only' is set to format values.
  }


  function __toString() {
    return ''; // JSON
  }


  static function getResponseSchema($response_name) {
    return Yaml::parse(dirname(__FILE__) . '/schema/' . $response_name . '.yml');
  }
}