<?php

namespace RoyalMail\Request;

use \Symfony\Component\Yaml\Yaml;


/**
 * Request builder utility - implemented with static methods as there shouldn't be any state or side-effects at this level.
 * 
 */
class Builder {

  use \RoyalMail\Validator\Validator;


  /**
   * Build the named request with the parameters given.
   * 
   * @param string $request_name
   * @param array  $params
   * 
   * @return array structured, validated, and modified request.
   */
  static function build($request_name, $params = []) {
    return self::processSchema(self::getRequestSchema($request_name), $params);
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
  static function processSchema($schema, $params) {
    $built  = [];
    $errors = [];

    try {
      foreach ($schema as $k => $v) $built[$k] = self::processProperty($v, @$params[$k]);
    } catch (Exception $e) {

    }

    if (! empty($errors)) throw (new \RoyalMail\Exception\RequestException())->withErrors($errors);

    return $built;
  }


  /**
   * Process a single value to add to the structure: at this point just adding defaults and validating.
   * 
   * @param array $schema instructions on how to process the value
   * @param mixed $val    submitted value.
   * 
   * @return mixed processed value or sub-structure.
   */
  static function processProperty($schema, $val) {
    if (isset($schema['default']) && empty($val)) $val = $schema['default']; // CAVEAT: This will default all falsy values.

    if (isset($schema['validate'])) self::validate($val, $schema['validate']);

    return $val;
  }


  /**
   * Request schemas are kept in YML files.   
   * These have the validation and defaults.
   * 
   * @return array
   */
  static function getRequestSchema($request_name) {
    return Yaml::parse(dirname(__FILE__) . '/schema/' . $request_name . '.yml');
  }
}