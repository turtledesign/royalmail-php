<?php

namespace RoyalMail\Response;

use \Symfony\Component\Yaml\Yaml;
use \RoyalMail\Helper\Data as DH;


class Interpreter extends \ArrayObject {

  protected 
    $response_instance = NULL,
    $meta              = [],
    $errors            = [],
    $warnings          = [];


  function __construct($key = NULL, $response = NULL) {
    if (isset($key) && isset($response)) $this->loadResponse($key, $response);
  }


  function loadResponse($key, $response) {
    $this->response_instance = $response;

    return self::parseWithSchema(self::getResponseSchema($key), $response);
  }


  function parseWithSchema($schema, $response) {
    foreach ($schema['properties'] as $key => $field) $this->parseField($key, $response, $field);
  }


  function parseField($key, $response, $schema) {
    switch (TRUE) {
      case isset($schema['_include']):  return $this->parseInclude($key, $response, $schema);
      case isset($schema['_multiple']): return $this->parseMultiValue($key, $response, $schema);
      default:                          return $this->parseSingleValue($key, $response, $schema);
    }
  }


  function parseSingleValue($key, $response, $schema) {
    $val = self::extractValue($key, $response, $schema);


  }


  function parseMultiValue($key, $response, $schema) {

  }


  function parseInclude($key, $response, $schema) {

  }


  function addValue() {

  }


  static function extractValue($key, $response, $schema) {
    
  }


  function hasIssues() { return $this->hasErrors() || $this->hasWarnings(); } // don't we wall.
  function hasErrors() { return count($this->getErrors()); }
  function getErrors() { return []; }
  function hasWarnings() { return count($this->getWarnings()); }
  function getWarnings() { return []; }
  function toJSON() { }


  static function getResponseSchema($response_name) {
    return Yaml::parse(dirname(__FILE__) . '/schema/' . $response_name . '.yml');
  }
}