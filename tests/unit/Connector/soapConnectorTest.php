<?php

namespace RoyalMail\tests\unit\Connector;

require_once PROJECT_ROOT . '/tests/lib/MockSoapClient.php';

use atoum;
use \RoyalMail\Request\Builder as ReqBuilder;
use \RoyalMail\tests\lib\MockSoapClient as MockSoap;

define('ENDPOINT', PROJECT_ROOT . '/reference/ShippingAPI_V2_0_8.wsdl');


class soapConnector extends atoum {

  use \RoyalMail\tests\TestDataLoader;

  /**
   * More or less just a smoke test - but will need updating if the endpoint does.
   */
  function testGetEndpoint() {
    $this
      ->given($this->newTestedInstance->setEndpoint(ENDPOINT))
      ->string($this->testedInstance->getEndpoint())
      ->contains('ShippingAPI_V2_0_8.wsdl');
  }



  function testXMLGeneration() {
    $action = $this->getTestRequest('cancelShipment');

    $this
      ->array($action['request'])
      ->isEqualTo($action['response']);

    $this
      ->given($this->newTestedInstance->setSoapClient($this->getMockSoapClient()))
      ->object($response = $this->testedInstance->doRequest('cancelShipment', $action['request']))
      ->string($response->integrationHeader->version)
      ->isEqualTo("2");
  }


  function testWSSecurity() {
    $action = $this->getTestRequest('cancelShipment');
    $client = $this->getMockSoapClient(['return_request' => TRUE]);

    $this
      ->given($this->newTestedInstance->setSoapClient($client))
      ->object($this->testedInstance->doRequest('cancelShipment', $action['request']));


    $this
      ->string($client->__getLastRequest())
      ->contains('Username>blah<')
      ->contains('Created>' . date_create()->format('Y-m-d')) // Do not run this test at midnight! (Or get it wet).
      ->matches('/:Password>\w+==</');
  }


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
}
