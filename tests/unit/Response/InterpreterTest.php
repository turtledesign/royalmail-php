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
    $mock_response = $this->getMockResponse();

    $this->string(Inter::extractValue($mock_response, ['_extract' => 'first']))->isEqualTo('Who');
    $this->array(Inter::extractValue($mock_response,  ['_extract' => 'first', '_multiple' => TRUE]))->hasSize(1)->contains('Who');
    $this->array(Inter::extractValue($mock_response,  ['_extract' => 'field', '_multiple' => TRUE]))->hasSize(2);

    $this->array(Inter::addProperty(
      [], 
      ['where' => ['_extract' => 'position'], 'who'   => ['_extract' => 'player']], 'field', 
      NULL, 
      [], 
      ['source' => $mock_response->field[0]]
    ))->isEqualTo(['field' => ['where' => 'left', 'who' => 'Why']]);
  }


  function testIntepretation() {
    $schema = [
      'properties' => ['First'     => ['_extract' => 'first']],
    ];

    $this->array(Inter::processSchema($schema, $this->getMockResponse()))->isEqualTo(['First' => 'Who']);

    $mock_response = $this->getMockResponse();

    $this->array(Inter::processSchema(
      ['properties' => ['where' => ['_extract' => 'position'], 'who'   => ['_extract' => 'player']]],
      $mock_response->field[0]
    ))->isEqualTo(['where' => 'left', 'who' => 'Why']);


    $nested_schema = [
      'properties' => [
        'field' => [
          '_extract' => 'field', 
          '_multiple' => TRUE,
          'where' => ['_extract' => 'position'],
          'who'   => ['_extract' => 'player'],
        ]
      ]
    ];

    $this
      ->array(Inter::processSchema($nested_schema, $mock_response))
      ->isEqualTo([
        'field' => [
          ['where' => 'left', 'who' => 'Why'],
          ['where' => 'center', 'who' => 'Because'],
        ]
      ]);
  }


  function testResponseConversions() {
    $requests = glob(PROJECT_ROOT . '/src/Response/schema/*.yml');
    $verify   = $this->getTestSchema('response_interpretation');

    foreach ($requests as $req) {
      $req = basename($req, '.yml');

      if (preg_match('/^integration/', $req)) continue;

      $expect = $verify[$req];
      $test   = $this->getTestRequest($req);

      $response = (new Soap())
                    ->setSoapClient($this->getMockSoapClient())
                    ->doRequest($req, $test['request']);
      
      $this->string($response->integrationHeader->version)->isEqualTo("2");

      $this
        ->given($this->newTestedInstance)
        ->object($response = $this->testedInstance->loadResponse($req, $response, ['params' => ['text_only' => TRUE]]));

      $this->array($response->getSecurityInfo())->isEqualTo($expect['security']);
      $this->array($response->getResponseEncoded())->isEqualTo($expect['response']);

      $this->boolean($response->hasErrors())->isFalse();
      $this->boolean($response->hasWarnings())->isFalse();
    }
  }


  function testBinaries() {
    $requests = ['printLabel'];

    foreach ($requests as $req) {
      $test   = $this->getTestRequest($req);

      $response = (new Soap())
                    ->setSoapClient($this->getMockSoapClient())
                    ->doRequest($req, $test['request']);

      $this
        ->given($this->newTestedInstance)
        ->object($response = $this->testedInstance->loadResponse($req, $response));

      $this->boolean($response->hasBinaries())->isTrue();

      if (! function_exists('finfo_open')) return;

      $finfangfo = finfo_open(FILEINFO_MIME_TYPE);

      foreach ($response->getBinariesInfo() as $key => $mime) {
        if (empty($response[$key])) continue;

        $test_file = TMP_DIR . '/' . $key . '.test';

        file_put_contents($test_file, $response[$key]);

        $this->string(finfo_file($finfangfo, $test_file))->isEqualTo($mime);

        unlink($test_file);
      }
    }
  }


  function testErrorResponses() {
    $errors = [
      'Single' => [[
          'code'    => 'E1084',
          'message' => 'shipmentType is a required field'
        ]],
    ];

    $errors['Multiple'] = array_merge($errors['Single'], [[
      'code'    => 'E1085',
      'message' => 'another error'
    ]]);


    foreach (array_keys($errors) as $layout) {
      $soap = (new Soap())
                      ->setSoapClient($this->getMockSoapClient()->setPostfix($layout . 'ErrorResponse.xml'))
                      ->doRequest('cancelShipment', $this->getTestRequest('cancelShipment')['request']);

      $this
        ->given($this->newTestedInstance)
        ->object($response = $this->testedInstance->loadResponse('cancelShipment', $soap, ['params' => ['text_only' => TRUE]]));

      $this->boolean($response->hasErrors())->isTrue();
      $this->boolean($response->hasWarnings())->isFalse();
      $this->boolean($response->succeeded())->isFalse();

      $this->array($response->getErrors())->isEqualTo($errors[$layout]);
    }
  }


  function testWarningResponses() {
    $warnings = [
      'Single' => [[
          'code'    => 'W1084',
          'message' => 'shipmentType would be nice to have...'
        ]],
    ];

    $warnings['Multiple'] = array_merge($warnings['Single'], [[
      'code'    => 'W1085',
      'message' => 'another warning'
    ]]);


    foreach (array_keys($warnings) as $layout) {
      $soap = (new Soap())
                      ->setSoapClient($this->getMockSoapClient()->setPostfix($layout . 'WarningResponse.xml'))
                      ->doRequest('cancelShipment', $this->getTestRequest('cancelShipment')['request']);

      $this
        ->given($this->newTestedInstance)
        ->object($response = $this->testedInstance->loadResponse('cancelShipment', $soap, ['params' => ['text_only' => TRUE]]));

      $this->boolean($response->hasErrors())->isFalse();
      $this->boolean($response->hasWarnings())->isTrue();
      $this->boolean($response->succeeded())->isTrue();

      $this->array($response->getWarnings())->isEqualTo($warnings[$layout]);
    }
  }


  function testWarningAndErrorResponse() {
    $soap = (new Soap())
              ->setSoapClient($this->getMockSoapClient()->setPostfix('ErrorAndWarningResponse.xml'))
              ->doRequest('cancelShipment', $this->getTestRequest('cancelShipment')['request']);

    $this
      ->given($this->newTestedInstance)
      ->object($response = $this->testedInstance->loadResponse('cancelShipment', $soap, ['params' => ['text_only' => TRUE]]));

    $this->boolean($response->hasErrors())->isTrue();
    $this->boolean($response->hasWarnings())->isTrue();
    $this->boolean($response->succeeded())->isFalse();

    $this->array($response->getWarnings())->isEqualTo([[
      'code'    => 'W1084',
      'message' => 'shipmentType would be nice to have...'
    ]]);

    $this->array($response->getErrors())->isEqualTo([[
      'code'    => 'E1084',
      'message' => 'shipmentType is a required field'
    ]]);
  }


  function testPostFilters() {
    $dates_schema = [
      'properties' => [
        'today'    => ['_extract' => 'roles/catcher', '_post_filter' => 'ObjectifyDate'],
        'tomorrow' => ['_extract' => 'roles/catcher', '_post_filter' => 'ObjectifyDate'],
      ]
    ];

    $this->array($response = Inter::processSchema($dates_schema, $this->getMockResponse()))->hasSize(2);

    $this->object($response['today'])->string($response['today']->format('d'))->isEqualTo(date_create()->format('d'));
  }



  function getMockResponse() {
    $field_left = new Obj;
    $field_left->position = 'left';
    $field_left->player   = 'Why';

    $field_center = new Obj;
    $field_center->position = 'center';
    $field_center->player   = 'Because';

    $response = new Obj;
    $response->first  = 'Who';
    $response->second = 'What';
    $response->third  = 'I Don\'t know';
    
    $response->field = [$field_left, $field_center];

    $response->roles = new Obj;
    $response->roles->pitcher   = date_create()->add(new \DateInterval('P1D'))->format('Y-m-d');
    $response->roles->catcher   = date_create()->format('Y-m-d');
    $response->roles->shortstop = 'I Don\'t Give a Darn';

    return $response;
  }
}