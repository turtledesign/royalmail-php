<?php

namespace RoyalMail\Helper

/**
 * Helper to get value options &c.
 */
class Data extends \ArrayObject {


  function __construct() {
    return parent::__construct([
      'shipment_types' => ['Delivery', 'Return'],
    ]);
  }


  function offsetGet($key) {
    if (! $this->offsetExists($key)) $this->loadData($key);

    return parent::offsetGet($key);
  }



  protected function loadData($key) {
    $this->offsetSet($key, Yaml::parse(dirname(__FILE__) . '../data/' . $key . '.yml'));

    return $this;
  }
}