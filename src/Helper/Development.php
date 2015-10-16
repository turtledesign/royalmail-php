<?php

namespace RoyalMail\Helper;

use \Symfony\Component\Yaml\Yaml;
use \RoyalMail\Request\Builder as ReqBuilder;

define('SCHEMA_DIR',  MODULE_ROOT . 'tests/resources');

class Development {

  protected $config = NULL;


  function __construct($config = []) {
    $this->config = $config;
  }


  function getTestRequest($req, $with_response = FALSE) {
    $built = ReqBuilder::build($req, $this->getSampleRequest($req), new \RoyalMail\Helper\Data());

    return ($with_response) ? ['request'  => $built, 'response' => $this->getSampleRequest($req, 'response')] : $built;
  }


  function getSampleRequest($action, $type = 'request') {
    $integration_schema = $this->getTestSchema('requests/integrationHeader');
    $test_schema        = $this->getTestSchema('requests/' . $action);

    if ($type == 'response') $type = 'expect';

    return array_merge($test_schema['valid'][$type], ['integrationHeader' => $integration_schema['valid'][$type]]);
  }


  function getTestSchema($key) {
    return Yaml::parse($this->mergeGeneratedValues(file_get_contents(SCHEMA_DIR . '/' . $key . '.yml')));
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