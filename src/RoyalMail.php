<?php

namespace RoyalMail;

// These are used to provide (fast) canned responses when developing using the supplied sample responses.
define('STATIC_RESPONSE_DIRECTORY', dirname(__FILE__) . '/../reference/responses');
define('STATIC_ENDPOINT', dirname(__FILE__) . '/../reference/ShippingAPI_V2_0_8.wsdl');
define('STATIC_CLIENT', '\RoyalMail\Connector\MockSoapClient');


use \RoyalMail\Connector\soapConnector as Connector;
use \RoyalMail\Helper\Data             as Store;
use \RoyalMail\Request\Builder         as Builder;
use \RoyalMail\Response\Interpreter    as Interpreter;


class RoyalMail {
  protected 
    $connector   = NULL,
    $data_helper = NULL,
    $config = [
      'cache_wsdl' => TRUE,
      'timezone'   => 'UTC',
      'username'   => NULL,
      'password'   => NULL,
      'mode'       => 'development',

      'soap_client_options'  => [
        'local_cert' => NULL,
        'trace'      => 1,
      ],
    ],

    $modes = [
      'development' => ['soap_client' => STATIC_CLIENT, 'endpoint' => STATIC_ENDPOINT, 'static_responses' => STATIC_RESPONSE_DIRECTORY],
      'onboarding'  => ['endpoint' => ''],
      'live'        => ['endpoint' => ''],
    ];



  /**
   * NOTES:
   * 
   * Can probably replace the request methods with __call() then throw exception if there isn't a request schema file.
   * 1.  Create the request - this will take params given, validate them and merge in defaults and return an appropriate array structure.
   *     Can probably scrap the class per request approach as the schema files will handle the customisation.
   * 
   * 2.  Create a connector of the required type with the config options given and pass it the request to process.
   * 
   * 3.  The connector will verify the response (if required) and return a Response object which is passed to the calling software.
   * 
   * A. Exceptions can be left for the calling software to handle.
   */ 

  /**
   * Create New
   * 
   * @param array $args This should contain security details and config default overrides.
   * 
   */
  function __construct($args = []) {
    $this->configure($args);
  }


  function processAction($action, $params, $config = []) {
    return  $this->interpretResponse(
              $this->send($action,
                $this->buildRequest($action, $params, $config)
              )
            );
  }


  function buildRequest($action, $params, $config = []) {
    return Builder::build($action, $params, $this->getDataHelper($config));
  }


  function send($action, $request, $config = []) {
    return $this->getConnector()->request($action, $request, $config);
  }


  function interpretResponse($response) {

  }



  /**
   * Get the appropriate connector class
   * 
   * @return \RoyalMail\Connector\baseConnector Variation on...
   */
  function getConnector() {
    if (empty($this->connector)) $this->connector = new Connector($this->config);

    return $this->connector;
  }


  function getDataHelper($config = []) {
    if (empty($this->data_helper)) $this->data_helper = new Store($config);

    return $this->data_helper;
  }


  /**
   * Set up config values, these are merged with the defaults.
   * 
   * @param array 
   * 
   * @return RoyalMail\RoyalMail $this
   */
  function configure($config = []) {
    $this->config = array_merge($this->config, $config);

    $this->config = array_merge($this->config, $this->modes[$this->config['mode']]);

    return $this;
  }


  function getConfig() {
    return $this->config;
  }


  function __call($method, $args) {
    return parent::__call($method, $args);
  }
} 