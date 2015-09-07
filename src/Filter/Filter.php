<?php

namespace RoyalMail\Filter;

trait Filter {

  static function filter($val, $schema, $type = 'pre', $helper = NULL) {

    foreach (self::parseFilters($schema, $type) as $filter => $settings) {

      if ($settings === FALSE || @$settings['apply'] === FALSE) continue; // allow turn-off overrides.

      $val = call_user_func_array('self::do' . $filter, [$val, $settings]);
    }

    return $val;
  }



  static function parseFilters($schema, $type) {
    $filters = isset($schema['_' . $type . '_filter']) ? $schema['_' . $type . '_filter'] : [];

    if ($type == 'pre') { // Shortcut pre filter methods
      if (isset($schema['_default'])) $filters['Default'] = ['value' => $schema['_default']];
      if (isset($schema['_trim']))    $filters['Trim']    = TRUE; 
    }

    return $filters;
  }



  static function doTrim($val, $settings) { return trim($val); }



  static function doDefault($val, $settings) {
    return empty($val) ? $settings['value'] : $val; // FIXME: replaces all falsy values, better to make that configurable
  }
  
}