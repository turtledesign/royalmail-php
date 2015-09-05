<?php


namespace RoyalMail\tests\unit\Request;

use atoum;
use \RoyalMail\Request\Builder    as ReqBuilder;
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
    $this->string(ReqBuilder::processProperty(['default' => 'foo'], @$not_defined))->isEqualTo('foo');
    $this->string(ReqBuilder::processProperty(['default' => 'bar'], '0'))->isEqualTo('bar'); // Beware.
  }


  function testIntegrationHeader() {
    $schema = $this->getRequestSchema('integrationHeader');

    $this
      ->array(ReqBuilder::buildRequest('integrationHeader', $schema['request']))
      ->isEqualTo($this->getExpectedResponse($schema));

  }


  function getRequestSchema($request_name, $type = 'valid') {
    return Yaml::parse(RESOURCES_DIR . '/request_builder_tests.yml')[$request_name][$type];
  }


  function getExpectedResponse($schema) {
    $merge = (isset($schema['expect']['merge'])) ? $schema['expect']['merge'] : [];

    return array_merge($schema['request'], $merge);
  }
}