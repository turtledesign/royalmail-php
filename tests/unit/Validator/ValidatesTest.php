<?php

namespace RoyalMail\tests\unit\Validator;

use atoum;

class Validates extends atoum {
  use \RoyalMail\Validator\Validates;



  function testNotBlank() {
    $this->string(self::constrain('not a blank value', 'NotBlank'))->isEqualTo('not a blank value');
  }


  function testDate() {
    $date_constraint = [
      'format' => 'Y-m-d',
      'min'    => '+0 days',
      'max'    => '+30 days'
    ];

    // Too Soon!
    $this
      ->exception(function () use ($date_constraint) { self::constrain(date_create()->sub(new \DateInterval('P1D'))->format('Y-m-d'), 'Date', $date_constraint); })
      ->message
      ->contains('earlier than');


    // Too Late!
    $this
      ->exception(function () use ($date_constraint) { self::constrain(date_create()->add(new \DateInterval('P32D'))->format('Y-m-d'), 'Date', $date_constraint); })
      ->message
      ->contains('later than');


    // Too Late! (with DateTime object).
    $this
      ->exception(function () use ($date_constraint) { self::constrain(date_create()->add(new \DateInterval('P32D')), 'Date', $date_constraint); })
      ->message
      ->contains('later than');

    $ds = date_create()->format('Y-m-d');
    $this
      ->string(self::constrain($ds, 'Date', $date_constraint))
      ->isEqualTo($ds);

    $ds = date_create()->add(new \DateInterval('P29D'))->format('Y-m-d');
    $this
      ->string(self::constrain($ds, 'Date', $date_constraint))
      ->isEqualTo($ds);
  }


  function testEmail() {
    $this
      ->exception(function () { self::constrain('not.valid.email', 'Email', []); })
      ->message
      ->contains('not a valid email');
  }


  function testCustomValidationException() {
    $this->exception(function () { self::constrain(NULL, 'NotBlank'); })->hasMessage('can not be blank'); // Default NotBlank message NULL != scalar.
    $this->exception(function () { self::constrain('', 'NotBlank'); })->hasMessage('can not be blank []');
    $this->exception(function () { self::constrain('', 'NotBlank', ['message' => 'foo']); })->hasMessage('foo []');
  }


  function testHasValue() {
    $this->boolean(self::is(['foo' => 'bar'], ['required' => 'foo']))->isTrue();
    $this->boolean(self::is(['foo' => ['bar' => 'baz']], ['required' => 'foo.bar']))->isTrue();
  }
}