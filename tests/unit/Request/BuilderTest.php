<?php


namespace RoyalMail\tests\unit\Request;

use atoum;
use \RoyalMail\Request\Builder as ReqBuilder;
use \Symfony\Component\Yaml\Yaml;

class Builder extends atoum {

  use \RoyalMail\tests\TestDataLoader;

  /**
   * Verify all the request schema files load and match with their summary data.
   * 
   */
  function testGetRequestSchema() {
    foreach (Yaml::parse(RESOURCES_DIR . '/request_summaries.yml') as $request_name => $verify) {

      $this
        ->array(ReqBuilder::getRequestSchema($request_name)['properties'])
        ->hasSize($verify['size'])
        ->hasKey($verify['last_key']);
    
    }
  }


  function testValueDefaulting() {
    $this->string(ReqBuilder::processProperty(['_default' => 'foo'], @$not_defined))->isEqualTo('foo');
    $this->string(ReqBuilder::processProperty(['_default' => 'bar'], '0'))->isEqualTo('bar'); // Beware.
    $this->string(ReqBuilder::processProperty(['_default' => '0044'], NULL))->isEqualTo('0044');
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
    $requests = ['integrationHeader', 'createShipment'];
    $helper   = new \RoyalMail\Helper\Data();

    $test_schema = $this->getTestSchema('request_builder');

    foreach ($test_schema as $r => $s) {
      $valid = $s['valid'];

      $this
        ->array(ReqBuilder::buildRequest($r, $valid['request'], $helper))
        ->isEqualTo($valid['expect']);
    }
  }


}