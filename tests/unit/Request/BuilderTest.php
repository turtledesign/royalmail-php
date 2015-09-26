<?php


namespace RoyalMail\tests\unit\Request;

use atoum;
use \RoyalMail\Request\Builder as ReqBuilder;
use \Symfony\Component\Yaml\Yaml;

class Builder extends atoum {

  use \RoyalMail\tests\lib\TestDataLoader;


  function testGetRequestSchema() { 
    foreach (self::getTestConfigs('request_summaries') as $request_name => $verify) {

      $this
        ->array(ReqBuilder::getRequestSchema($request_name)['properties'])
        ->hasSize($verify['size'])
        ->hasKey($verify['last_key']);
    
    }
  }


  function testValueDefaulting() {
    $this->string(ReqBuilder::processSingleProperty(['_default' => 'foo'], @$not_defined))->isEqualTo('foo');
    $this->string(ReqBuilder::processSingleProperty(['_default' => 'bar'], '0'))->isEqualTo('bar'); // Beware.
    $this->string(ReqBuilder::processSingleProperty(['_default' => '0044'], NULL))->isEqualTo('0044');
  }


  function testPathCreation() {
    $this
      ->array(ReqBuilder::addProperty(['us' => 'chickens'], ['_key' => 'foo/bar/baz'], 0, 'kaboom!'))
      ->isEqualTo(['us' => 'chickens', 'foo' => ['bar' => ['baz' => 'kaboom!']]]);

    $this
      ->array(ReqBuilder::addProperty([], ['_key' => 'foo/bar/baz'], 0, 'kaboom!'))
      ->isEqualTo(['foo' => ['bar' => ['baz' => 'kaboom!']]]);


    $this
      ->array(ReqBuilder::addProperty(['us' => 'chickens'], [], 'fizz', 'buzz'))
      ->isEqualTo(['us' => 'chickens', 'fizz' => 'buzz']);
  }


  function testValidRequests() {
    $helper   = new \RoyalMail\Helper\Data(['override_defaults' => ['_disable_includes' => TRUE]]);

    $test_schema = $this->getTestSchema('request_builder');

    foreach ($test_schema as $r => $s) {
      $valid = $s['valid'];

      $this
        ->array(ReqBuilder::build($r, $valid['request'], $helper))
        ->isEqualTo($valid['expect']);
    }
  }


  function testMultiplePropertyCreation() {
    $tests = self::getTestConfigs('misc_builder_tests');

    $musi = $tests['multiple_property_single_element'];
    $this
      ->array(ReqBuilder::processSchema($musi['schema'], $musi['values']))
      ->isEqualTo($musi['expect']);

  }



  static function getTestConfigs($key) {
    return Yaml::parse(RESOURCES_DIR . '/' . $key . '.yml');
  }


}