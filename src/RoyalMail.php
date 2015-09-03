<?php

namespace RoyalMail

class RoyalMail {


  protected $config = [
    'cache_wsdl' => TRUE,
    'timezone'   => 'UTC',
    'username'   => NULL,
    'password'   => NULL,
    'connector'  => 'static',       
  ];


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
} 