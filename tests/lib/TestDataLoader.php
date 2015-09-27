<?php


namespace RoyalMail\tests\lib;

define('ENDPOINT', PROJECT_ROOT . '/reference/ShippingAPI_V2_0_8.wsdl');

require_once PROJECT_ROOT . '/tests/lib/MockSoapClient.php';

use \Symfony\Component\Yaml\Yaml;
use \RoyalMail\Request\Builder as ReqBuilder;
use \RoyalMail\tests\lib\MockSoapClient as MockSoap;

/**
 * Utility to pre-parse YAML request test files to add valid dates and similar.
 * + other shared utility functions that don't have their own home yet.
 * 
 * 
 */
trait TestDataLoader {

  function getMockSoapClient() {
    return new MockSoap(ENDPOINT, ['password' => 'blah', 'username' => 'blah', 'timezone' => 'BST', 'trace' => 1]);
  }


  function getTestRequest($req) {
    $test_schema = $this->getTestSchema('request_builder');

    $request  = array_merge($test_schema[$req]['valid']['request'], ['integrationHeader' => $test_schema['integrationHeader']['valid']['request']]);
    $response = array_merge($test_schema[$req]['valid']['expect'],  ['integrationHeader'  => $test_schema['integrationHeader']['valid']['expect']]);

    return [
      'request'  => ReqBuilder::build($req, $request, new \RoyalMail\Helper\Data()), 
      'response' => $response
    ];
  }


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