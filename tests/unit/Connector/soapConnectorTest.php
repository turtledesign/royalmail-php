<?php

namespace RoyalMail\tests\unit\Connector;

use atoum;


class soapConnector extends atoum {

  use \RoyalMail\tests\lib\TestDataLoader;

  /**
   * More or less just a smoke test - but will need updating if the endpoint does.
   */
  function testGetEndpoint() {
    $this
      ->given($this->newTestedInstance->setEndpoint(ENDPOINT))
      ->string($this->testedInstance->getEndpoint())
      ->contains('ShippingAPI_V2_0_8.wsdl');
  }



  function testSchemaWSDLCompatible() {
    $requests = ['cancelShipment', 'createManifest'];

    foreach ($requests as $req) {
      $action = $this->getTestRequest($req);

      $this->array($action['request'])->isEqualTo($action['response']);

      $this
        ->given($this->newTestedInstance->setSoapClient($this->getMockSoapClient()))
        ->object($response = $this->testedInstance->doRequest($req, $action['request']))
        ->string($response->integrationHeader->version)
        ->isEqualTo("2");
    }
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
}
