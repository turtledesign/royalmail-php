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
    $test_schema = $this->getTestSchema('request_builder');

    $request  = array_merge($test_schema['cancelShipment']['valid']['request'], ['integrationHeader' => $test_schema['integrationHeader']['valid']['request']]);
    $response = array_merge($test_schema['cancelShipment']['valid']['expect'], ['integrationHeader'  => $test_schema['integrationHeader']['valid']['expect']]);

    $helper = new \RoyalMail\Helper\Data();

    $this
      ->array($req_params = ReqBuilder::build('cancelShipment', $request, $helper))
      ->isEqualTo($response);

    $this
      ->given($this->newTestedInstance->setSoapClient(new MockSoap(ENDPOINT)))
      ->object($response = $this->testedInstance->doRequest('cancelShipment', $req_params, ['password' => 'blah', 'username' => 'blah']))
      ->string($response->integrationHeader->version)
      ->isEqualTo("2");
  }
}
