<?php

namespace RoyalMail\Request;

use \Symfony\Component\Yaml\Yaml;


/**
 * Request builder utility - implemented with static methods as there shouldn't be any state or side-effects at this level.
 * 
 */
class Builder {

  use \RoyalMail\Validator\Validates;
  use \RoyalMail\Filter\Filters;
  use \RoyalMail\Helper\Structure;



  /**
   * Build an individual request from schema and params.
   * 
   * @param string       $request_name
   * @param array        $params
   * @param \ArrayObject $helper
   * 
   * @return array
   */
  static function build($request_name, $params, $helper = NULL) {
    return self::processSchema(self::getRequestSchema($request_name), $params, $helper);
  }


  /**
   * Process the schema and params to validate and structure a request fragment.
   * 
   * @param array $schema instructions for processing the params.
   * @param array $params values to work with
   * 
   * @throws \RoyalMail\Exception\RequestException on validation failure with details of all failing field values.
   * 
   * @return array values structured for API request.
   */
  static function processSchema($schema, $params, $helper = NULL) {
    $built    = [];
    $errors   = [];

    (is_null($helper)) ? $helper = ['input' => $params] : $helper['input'] = $params;

    $schema['defaults'] = @$schema['defaults'] ?: [];

    if (isset($helper['override_defaults'])) $schema['defaults'] = array_merge($schema['defaults'], $helper['override_defaults']);

    try {
      foreach ($schema['properties'] as $k => $v) {
        $built = self::addProperty($built, $schema['properties'][$k], $k, @$params[$k], $schema['defaults'], $helper);
      }
    
    } catch (\RoyalMail\Exception\ValidatorException $e) {
      $errors[$k] = $k . ': ' . $e->getMessage(); 

    } catch (\RoyalMail\Exception\RequestException $re) {
      foreach ($re->getErrors() as $k_nested => $v) $errors[$k . ':' . $k_nested] = $v;
    }

    if (! empty($errors)) throw (new \RoyalMail\Exception\RequestException())->withErrors($errors);

    return $built;
  }


  static function addProperty($arr, $schema, $key, $val, $defaults = [], $helper = NULL) {
    return self::doAddProperty($arr, $schema, $key, $val, $defaults, $helper);
  }


  /**
   * Request schemas are kept in YML files.   
   * These have the structure, validation, and defaults.
   * 
   * @return array
   */
  static function getRequestSchema($request_name) {
    return Yaml::parse(dirname(__FILE__) . '/schema/' . $request_name . '.yml');
  }
}