<?php


namespace RoyalMail\tests;

use \Symfony\Component\Yaml\Yaml;

/**
 * Utility to pre-parse YAML request test files to add valid dates and similar.
 * 
 * 
 */
trait TestDataLoader {

  function getTestSchema($key) {
    return Yaml::parse($this->mergeGeneratedValues(file_get_contents(RESOURCES_DIR . '/' . $key . '_tests.yml')));
  }


  function mergeGeneratedValues($source) {
    return preg_replace_callback('/<<([^>]+)>>/', function($matches) {
      $parts = explode('|', $matches[1]);
      $method = array_shift($parts);

      return (method_exists($this, $method)) 
        ? call_user_func_array([$this, $method], $parts)
        : $matches[0];

    }, $source);
  }


  function dateVector($interval, $format = 'Y-m-d') {
    return '"' . date_create()->modify($interval)->format($format) . '"'; // return quoted string as otherwise the YAML loader seems to be objectifying it.
  }

}