<?php

namespace RoyalMail\tests\unit\Response;

use atoum;
use \RoyalMail\Response\Interpreter as Inter;
use \RoyalMail\Connector\soapConnector as Soap;
use \Symfony\Component\Yaml\Yaml;
use \stdClass as Obj;

class Interpreter extends atoum {

  use \RoyalMail\tests\lib\TestDataLoader;

  function testValueExtraction() {
    $this->string(Inter::extractValue($this->getMockResponse(), ['_extract' => 'first']))->isEqualTo('Who');
    $this->string(Inter::extractValue($this->getMockResponse(), ['_extract' => 'roles/catcher']))->isEqualTo('Today');
    // $this->array(Inter::extractValue($this->getMockResponse(), 'first', ['_multiple' => TRUE]))->hasSize(1)->contains('Who');
  }


  function testIntepretation() {
    $schema = [
      'properties' => ['First'     => ['_extract' => 'first']],
    ];


    $this->array(Inter::processSchema($schema, $this->getMockResponse()))->isEqualTo(['First' => 'Who']);
  }


  function testResponseConversion() {
    $response = (new Soap())
                  ->setSoapClient($this->getMockSoapClient())
                  ->doRequest('cancelShipment', $this->getTestRequest('cancelShipment')['request']);
    
    $this->string($response->integrationHeader->version)->isEqualTo("2");

    $this
      ->given($this->newTestedInstance)
      ->object($this->testedInstance->loadResponse('cancelShipment', $response))
      
      ->array($this->testedInstance->getResponse())
        ->isEqualTo([
          'status'  => 'Cancelled',
          'updated' => date_create('2015-02-09T10:35:28.000+02:00'),
          'cancelled_shipments' =>  ['RQ221150275GB']
        ])

      ->array($this->testedInstance->getSecurityInfo())
        ->isEqualTo([
          'timestamp'      => date_create('2015-02-09T09:35:28'),
          'version'        => 2,
          'application_id' => '111111113',
          'transaction_id' => '420642961'
        ]);
  }





  function getMockResponse() {
    $response = new Obj;
    $response->first  = 'Who';
    $response->second = 'What';
    $response->third  = 'I Don\'t know';
    
    $response->field = new Obj;
    $response->field->left   = 'Why';
    $response->field->center = 'Because';

    $response->roles = new Obj;
    $response->roles->pitcher   = 'Tomorrow';
    $response->roles->catcher   = 'Today';
    $response->roles->shortstop = 'I Don\'t Give a Darn';

    return $response;
  }
}