<?php


namespace RoyalMail\tests\lib;

define('ENDPOINT', MODULE_ROOT . 'reference/ShippingAPI_V2_0_8.wsdl');


use \RoyalMail\Connector\MockSoapClient as MockSoap;

/**
 * Utility to pre-parse YAML request test files to add valid dates and similar.
 * + other shared utility functions that don't have their own home yet.
 * 
 * 
 */
trait TestDataLoader {

  protected 
    $development_helper = NULL;

  function getMockSoapClient() {
    return new MockSoap(ENDPOINT, [
      'password' => 'blah', 
      'username' => 'blah', 
      'timezone' => 'BST', 
      'trace'    => 1,
      'static_responses' => MODULE_ROOT . 'reference/responses'
    ]);
  }


  function getDevelopmentHelper($config = []) {
    if (empty($this->development_helper)) $this->development_helper = new \RoyalMail\Helper\Development($config);

    return $this->development_helper;
  }


  function __call($method, array $args) {
    if (method_exists($this->getDevelopmentHelper(), $method)) return call_user_func_array([$this->getDevelopmentHelper(), $method], $args);

    return parent::__call($method, $args);
  }
}