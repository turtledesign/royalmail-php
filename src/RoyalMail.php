<?php

namespace RoyalMail

class RoyalMail {


  protected $config = [
    'cache_wsdl' => TRUE,
    'timezone'   => 'UTC',
    'username'   => NULL,
    'password'   => NULL,
    'connector'  => 'static',
    'soap_opts'  => [],          # For proxy settings, etc.
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
  function __construct($args) {
    if (isset($args['config']) && is_array($args['config'])) $this->configure($args['config']);

  }



  function createShipment($args) {

  }



  function cancelShipment($args) {

  }


  function createManifest($args) {

  }


  function printDocument($args) {

  }


  function printLabel($args) {

  }


  function printManifest($args) {

  }


  function request1DRanges($args) {

  }


  function request2DItemIDRange($args) {

  }


  function updateShipment($args) {

  }


  static function send($config, $request_name, $params) {

  }



  /**
   * Get the appropriate connector class
   * 
   * @return \RoyalMail\Connector\baseConnector Variation on...
   */
  static function getConnector($config) {

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

    return $this;
  }


  function getConfig() {
    return $this->config;
  }


  function __call() {
    
  }
} 