<?php

namespace RoyalMail\tests\unit\Connector;

use atoum;


class soapConnector extends atoum {

  use \RoyalMail\tests\lib\TestDataLoader;

  function testGetEndpoint() {
    $this
      ->given($this->newTestedInstance->setEndpoint(ENDPOINT))
      ->string($this->testedInstance->getEndpoint())
      ->contains('ShippingAPI_V2_0_8.wsdl');
  }


  function testSchemaWSDLCompatible() {
    $requests = ['cancelShipment', 'createManifest'];

    foreach ($requests as $req) {
      $action = $this->getTestRequest($req, $with_response = TRUE);

      $this->array($action['request'])->isEqualTo($action['response']);

      $this
        ->given($this->newTestedInstance->setSoapClient($this->getMockSoapClient()))
        ->object($response = $this->testedInstance->doRequest($req, $action['request']))
        ->string($response->integrationHeader->version)
        ->isEqualTo("2");
    }
  }


  function testWSSecurity() {
    $this
      ->given($this->newTestedInstance->setSoapClient($this->getMockSoapClient()))
      ->object($this->testedInstance->doRequest('cancelShipment', $this->getTestRequest('cancelShipment')));

    $this
      ->string($this->testedInstance->getAPIFormattedRequest())
      ->contains('Username>blah<')
      ->contains('Created>' . date_create()->format('Y-m-d')) // Do not run this test at midnight! (Or get it wet).
      ->matches('/:Password>.+=</');
  }
}
