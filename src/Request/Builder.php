<?php

namespace RoyalMail\Request;

use \Symfony\Component\Yaml\Yaml;
use \RoyalMail\Exception\BuilderSkipFieldException;

/**
 * Request builder utility - implemented with static methods as there shouldn't be any state or side-effects at this level.
 * 
 */
class Builder {

  use \RoyalMail\Validator\Validates;
  use \RoyalMail\Filter\Filters;



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



  /**
   * Add a property, processing as required and creating extra path elements.
   * 
   * @param array       $arr    The structure so far.
   * @param array       $schema Build map
   * @param string      $key    current key
   * @param mixed       $val
   * @param array       $defaults
   * @param ArrayObject $helper
   * 
   * @return array
   */
  static function addProperty($arr, $schema, $key, $val, $defaults = [], $helper = NULL) {
    try {
      $val = self::processProperty($schema, $val, $defaults, $helper);

    } catch (\RoyalMail\Exception\ValidatorException $e) {
      $errors[] = $key . ': ' . $e->getMessage(); 

    } catch (\RoyalMail\Exception\RequestException $re) {
      foreach ($re->getErrors() as $k_nested => $v) $errors[$k . ':' . $k_nested] = $v;
    
    } catch (\RoyalMail\Exception\BuilderSkipFieldException $e) { return $arr; } // Exception is notification that rules exclude this field.
     

    if (isset($schema['_key'])) { // TODO: Extract this.
      $top_ref = & $arr;

      foreach (explode('/', $schema['_key']) as $path) {   // If there is a _key: this/that path value it replaces the $key value entirely.
        if ($path === '~') $path = $key;                   
        
        if (empty($top_ref[$path])) $top_ref[$path] = [];  // New elements can be added to existing paths, so only create what isn't there.
        
        $top_ref = & $top_ref[$path];
      }
    
    } else $top_ref = & $arr[$key];
    
    $top_ref = $val;

    return $arr;
  }


  static function processProperty($schema, $val, $defaults = [], $helper = NULL) {
    switch (TRUE) {
      case isset($schema['_include']):  return self::processInclude($schema, $val, $defaults, $helper);
      case isset($schema['_multiple']): return self::processMultipleProperty($schema, $val, $defaults, $helper);
      default:                          return self::processSingleProperty($schema, $val, $defaults, $helper);
    }
  }


  /**
   * Process a single value to add to the structure: at this point just adding defaults and validating.
   * 
   * @param array $schema     instructions on how to process the value
   * @param mixed $val        submitted value.
   * @param array $defaults   cascaded schema (not value) defaults e.g. required: true
   * @param \ArrayObject      helper for get options.
   * 
   * @return mixed processed value or sub-structure.
   */
  static function processSingleProperty($schema, $val, $defaults = [], $helper = NULL) {
    if ($nested = self::getNested($schema)) {
      $nest = [];

      foreach ($nested as $k => $v) $nest = self::addProperty($nest, $schema[$k], $k, @$val[$k], $defaults, $helper);

      return $nest;
    }

    return self::validateAndFilter($schema, $val, $defaults, $helper);
  }


  static function processMultipleProperty($schema, $val, $defaults, $helper = NULL) {
    $single_schema = array_diff_key($schema, ['_multiple' => 1]);
    
    if (isset($schema['_multiple']['nest_key'])) $single_schema['_key'] = $schema['_multiple']['nest_key'];
    
    $multi_values = [];
    foreach ($val as $m) array_push($multi_values, self::addProperty([], $single_schema, '', $m, $defaults, $helper));

    return $multi_values;
  }


  static function processInclude($schema, $val, $defaults, $helper = NULL) {
    if (empty($defaults['_disable_includes'])) return self::build($schema['_include'], $val, $helper);

    throw new BuilderSkipFieldException; // Testing is simpler if we can check requests atomically.
  }


  static function validateAndFilter($schema, $val, $defaults, $helper = NULL) {
    $schema = array_merge((array) $defaults, $schema);
    
    $val = self::filter($val, $schema, $type = 'pre', $helper);

    self::validate($schema, $val, $helper);
      
    return self::filter($val, $schema, $type = 'post', $helper);
  }



  /**
   * parse out the instruction elements of the schema and just return sub-keys
   * 
   * @param array $schema
   * 
   * @return array
   */
  static function getNested($schema) {
    $nested = [];

    $nested = (isset($schema['_include']) && empty($defaults['_disable_includes'])) ? self::getRequestSchema($schema['_include'])['properties'] : [];

    if (is_array($schema)) foreach ($schema as $k => $v) if (! preg_match('/^_/', $k)) $nested[$k] = $v;

    return $nested;
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