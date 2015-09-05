<?php

namespace RoyalMail\tests\unit\Validator;

use atoum;

class Validator extends atoum {
  use \RoyalMail\Validator\Validator;



  function testNotBlank() {
    $this->string(self::constrain('not a blank value', 'NotBlank'))->isEqualTo('not a blank value');
  }


  function testCustomValidationException() {
    $this->exception(function () { self::constrain(NULL, 'NotBlank'); })->hasMessage('can not be blank'); // Default NotBlank message NULL != scalar.
    $this->exception(function () { self::constrain('', 'NotBlank'); })->hasMessage('can not be blank []');
    $this->exception(function () { self::constrain('', 'NotBlank', ['message' => 'foo']); })->hasMessage('foo []');
  }
}