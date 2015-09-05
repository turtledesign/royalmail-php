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
   * Build the full named request with the parameters given and the integrationHeader element added.
   * 
   * @param string $request_name
   * @param array  $params
   * 
   * @return array structured, validated, and modified request.
   */
  static function build($request_name, $params = []) {
    return array_merge(
      self::buildRequest(self::getRequestSchema('integrationHeader'), $params['integrationHeader']),
      self::buildRequest(self::getRequestSchema($request_name), $params[$request_name])
    );
  }



  /**
   * Build an individual request from schema and params.
   * 
   * @param string $request_name
   * @param array  $params
   * 
   * @return array
   */
  static function buildRequest($request_name, $params) {
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
    $built    = [];
    $errors   = [];
    $defaults = @$schema['defaults'] ?: [];

    try {
      foreach ($schema['properties'] as $k => $v) $built[$k] = self::processProperty(array_merge($defaults, $v), @$params[$k]);
    
    } catch (\RoyalMail\Validator\ValidatorException $e) { 
      $errors[$k] = $k . ': ' . $e->getMessage(); 

    } catch (\RoyalMail\Exception\RequestException $re) {
      foreach ($re->getErrors() as $k_nested => $v) $errors[$k . ':' . $k_nested] = $v;
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
    if (isset($schema['include'])) return self::build($schema['include'], $val);

    if (isset($schema['default']) && empty($val)) $val = $schema['default']; // CAVEAT: This will default all falsy values.

    if (isset($schema['validate'])) $val = self::validate($val, $schema['validate']);

    if ($nested = array_diff_key($schema, array_flip(['include', 'default', 'validate', 'required']))) {
      $nest = [];

      foreach ($nested as $k => $v) $nest[$k] = self::processProperty($schema[$k], $val[$k]);

      $val = $nest; // Can't have nested elements alongside scalar values.
    }

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