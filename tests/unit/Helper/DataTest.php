<?php

namespace RoyalMail\tests\unit\Helper;

use atoum;

class Data extends atoum {

  // Spot check values to ensure that all the data files are loading up properly.
  // N.B. key and value aren't necessarily matched pairs.
  protected $data_checks = [
    'service_types'        => ['key' => 'T', 'value' => 'Tracked Returns', 'size' => 7],
    'bfpo_formats'         => ['key' => 'FAE', 'value' => 'SD 101 - 500G £2500 COMP', 'size' => 13],
    'country_codes'        => ['key' => 'ML', 'value' => 'CHRISTMAS ISLANDS (PACIFIC), KIRIBATI', 'size' => 260],
    'service_enhancements' => ['key' => '11', 'value' => 'SMS & E-Mail Notification', 'size' => 14],
    'service_formats'      => ['key' => 'F', 'value' => 'International Format Not Applicable', 'size' => 8],
    'service_offerings'    => ['key' => 'TSS', 'value' => 'INTL BUS MAIL L LTR ZERO SRT ECONOMY MCH', 'size' => 20],
  ];


  function testDataLoading() {
    foreach ($this->data_checks as $store => $check) {
      $this
        ->given($this->newTestedInstance)
        ->array($this->testedInstance[$store])
        ->hasSize($check['size'])
        ->hasKey($check['key'])
        ->contains($check['value']);
    }
  }
}