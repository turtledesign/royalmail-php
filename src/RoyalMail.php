<?php

namespace RoyalMail;

if (! defined('MODULE_ROOT')) define('MODULE_ROOT', dirname(__FILE__) . '/../');

// These are used to provide (fast) canned responses when developing using the supplied sample responses.
define('STATIC_RESPONSE_DIRECTORY', MODULE_ROOT . 'reference/responses');
define('STATIC_ENDPOINT', MODULE_ROOT . 'reference/ShippingAPI_V2_0_9.wsdl');
define('STATIC_CLIENT', '\RoyalMail\Connector\MockSoapClient');


use \RoyalMail\Connector\soapConnector as Connector;
use \RoyalMail\Helper\Data             as Store;
use \RoyalMail\Helper\Development      as DevHelper;
use \RoyalMail\Request\Builder         as Builder;
use \RoyalMail\Response\Interpreter    as Interpreter;


class RoyalMail {
  protected 
    $connector   = NULL,
    $data_helper = NULL,
    $dev_helper  = NULL,
    $config = [
      'cache_wsdl'      => TRUE,
      'timezone'        => 'UTC',
      'username'        => NULL,
      'password'        => NULL,
      'application_id'  => NULL,
      'transaction_id'  => NULL,
      'mode'            => 'development',
      'request_schema'  => 'src/Request/schema',
      'response_schema' => 'src/Response/schema',
			'endpoint'				=> '',
      'soap_client_options'  => [
        'local_cert' => NULL,
        'trace'      => 1,
        'location'	 => ''
      ],
    ],

    $modes = [
      'development' => ['soap_client' => STATIC_CLIENT, 'endpoint' => STATIC_ENDPOINT, 'static_responses' => STATIC_RESPONSE_DIRECTORY],
      'onboarding'  => ['endpoint' => STATIC_ENDPOINT, 'soap_client_options' => ['location' => 'https://api.royalmail.net/shipping/v2']],
      'live'        => ['endpoint' => STATIC_ENDPOINT, 'soap_client_options' => ['location' => 'https://api.royalmail.com/shipping/v2']]
    ];



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
    if (empty($params['integrationHeader'])) $params = $this->addIntegrationHeader($params);

    return  $this->interpretResponse($action,
              $this->send($action,
                $this->buildRequest($action, $params, $config)
              )
            );
  }


  function buildRequest($action, $params, $config = []) {
    return Builder::build($action, $params, $this->getDataHelper($config));
  }


  function send($action, $request, $config = []) {
    try {
      return $this->getConnector()->request($action, $request, $config);

    } catch (\SoapFault $e) {
      return new \RoyalMail\Exception\ResponseException(array_merge(['exception' => $e], $this->getConnector()->getDebugInfo()));
    }
  }


  function interpretResponse($action, $response) {
    return new Interpreter($action, $response, $this->getDataHelper());
  }


  function addIntegrationHeader($params) {
    $params['integrationHeader'] = [
      'applicationId' => $this->getConfig()['application_id'],
      'transactionId' => $this->getConfig()['transaction_id'],
    ];

    if (! empty($params['transaction_id'])) {
      $params['integrationHeader']['transactionId'] = $params['transaction_id'];
      unset($params['transaction_id']);
    }

    return $params;
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
    $this->config = array_replace_recursive($this->config, $this->modes[@$config['mode'] ?: $this->config['mode']]);
    
    $this->config = array_replace_recursive($this->config, $config);
    
    return $this;
  }


  function getConfig($key = NULL) {
    return (empty($key)) ? $this->config : $this->config[$key];
  }


  function getAvailableActions() {
    $actions = [];

    foreach (glob(MODULE_ROOT . $this->getConfig('request_schema') . '/*.yml') as $schema) {
      $actions[] = basename($schema, '.yml');
    }

    return $actions;
  }


  function getDevelopmentHelper() {
    if (empty($this->dev_helper)) $this->dev_helper = new DevHelper($this->getConfig());

    return $this->dev_helper;
  }


  function __call($method, $args) {
    if (in_array($method, $this->getAvailableActions())) {
      return call_user_func_array([$this, 'processAction'], array_merge([$method], $args));
    }

    throw new \BadMethodCallException('Action "' . $method . '"" not configured.');
  }
} 
