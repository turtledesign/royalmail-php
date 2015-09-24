<?php

namespace RoyalMail\Helper;

use \RoyalMail\Exception\StructureSkipFieldException as SkipException;


trait Structure {

  static function addProperty($arr, $schema, $key, $val, $defaults = [], $helper = NULL) {
    try {
      $val = self::processProperty($schema, $val, $defaults, $helper);

    } catch (\RoyalMail\Exception\ValidatorException $e) {
      $errors[] = $key . ': ' . $e->getMessage(); 

    } catch (\RoyalMail\Exception\RequestException $re) {
      foreach ($re->getErrors() as $k_nested => $v) $errors[$k . ':' . $k_nested] = $v;
    
    } catch (SkipException $e) { return $arr; } // Exception is notification that rules exclude this field.
     

    return self::addToArray($arr, $val, $key, @$schema['_key']);
  }



  static function processProperty($schema, $val, $defaults = [], $helper = NULL) {
    switch (TRUE) {
      case isset($schema['_include']):  return self::processInclude($schema, $val, $defaults, $helper);
      case isset($schema['_multiple']): return self::processMultipleProperty($schema, $val, $defaults, $helper);
      default:                          return self::processSingleProperty($schema, $val, $defaults, $helper);
    }
  }


  static function processSingleProperty($schema, $val, $defaults = [], $helper = NULL) {
    if ($nested = self::stripMeta($schema)) {
      $nest = [];

      foreach ($nested as $k => $v) $nest = self::addProperty($nest, $schema[$k], $k, @$val[$k], $defaults, $helper);

      return $nest;
    }

    return self::validateAndFilter($schema, $val, $defaults, $helper);
  }


  static function processMultipleProperty($schema, $val, $defaults, $helper = NULL) {
    $single_schema = array_diff_key($schema, ['_multiple' => 1, '_key' => 1]);
    
    if (isset($schema['_multiple']['nest_key'])) $single_schema['_key'] = $schema['_multiple']['nest_key'];
    
    $multi_values = [];
    foreach ($val as $m) array_push($multi_values, current(self::addProperty([], $single_schema, '', $m, $defaults, $helper)));

    return $multi_values;
  }


  static function processInclude($schema, $val, $defaults, $helper = NULL) {
    if (empty($defaults['_disable_includes'])) return self::build($schema['_include'], $val, $helper);

    throw new SkipException; // Testing is simpler if we can check requests atomically.
  }


  static function validateAndFilter($schema, $val, $defaults, $helper = NULL) {
    $schema = array_merge((array) $defaults, $schema);
    
    $val = self::filter($val, $schema, $type = 'pre', $helper);

    self::validate($schema, $val, $helper);
      
    return self::filter($val, $schema, $type = 'post', $helper);
  }


  static function addToArray($arr, $val, $key = NULL, $path = NULL) {
    if (empty($key)) $key = key(array_slice($arr, -1, 1, TRUE)) + 1;

    if (! empty($path)) {

      $top_ref = & $arr;

      foreach (explode('/', $path) as $step) {   // If there is a _key: this/that path value it replaces the $key value entirely.
        if ($step === '~') $step = $key;                   
        
        if (empty($top_ref[$step])) $top_ref[$step] = [];  // New elements can be added to existing paths, so only create what isn't there.
        
        $top_ref = & $top_ref[$step];
      }
    
    } else $top_ref = & $arr[$key];

    $top_ref = $val;

    return $arr;
  }
  

  static function stripMeta($arr) {
    $s = [];

    if (is_array($arr)) foreach ($arr as $k => $v) if (! preg_match('/^_/', $k)) $s[$k] = $v;

    return $s;
  }
}