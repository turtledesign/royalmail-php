<?php


namespace RoyalMail\tests\unit\Request;

use atoum;
use \RoyalMail\Request\Builder as ReqBuilder;
use \Symfony\Component\Yaml\Yaml;

class Builder extends atoum {

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

    foreach ($requests as $r) {
      $schema = $this->getRequestSchema($r);

      $this
        ->array(ReqBuilder::buildRequest($r, $schema['request'], $helper))
        ->isEqualTo($schema['expect']);
    }
  }




  function getRequestSchema($request_name, $type = 'valid') {
    return Yaml::parse(RESOURCES_DIR . '/request_builder_tests.yml')[$request_name][$type];
  }

}